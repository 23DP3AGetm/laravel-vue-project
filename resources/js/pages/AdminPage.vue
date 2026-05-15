<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import AuthCard from '../components/auth/AuthCard.vue';
import { loadUser } from '../auth';
import {
  deleteAdminUser,
  getFilteredAdminUsers,
  updateAdminUserRole,
  updateAdminUserStatus,
} from '../services/authService';

const PER_PAGE = 10;

const loading = ref(true);
const error = ref('');
const message = ref('');
const toastMessage = ref('');
const users = ref([]);
const currentUser = ref(null);
const deletingUserId = ref(null);
const activeDeleteUserId = ref(null);
const activeRoleEdit = ref(null);
const activeStatusEdit = ref(null);
const updatingRoleId = ref(null);
const updatingStatusId = ref(null);
const roleOptions = ['', 'admin', 'organizator', 'user'];
const statusOptions = ['active', 'blocked'];
const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  perPage: PER_PAGE,
  total: 0,
  from: 0,
  to: 0,
});
const filters = reactive({
  search: '',
  role: '',
});

let searchDebounceId = null;
let toastTimeoutId = null;
let lastRequestId = 0;

function formatDate(date) {
  if (!date) {
    return '-';
  }

  return new Date(date).toLocaleDateString('lv-LV', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function roleBadgeClass(role) {
  return `role-badge role-${role || 'user'}`;
}

function statusBadgeClass(status) {
  return status === 'blocked' ? 'status-badge badge-blocked' : 'status-badge badge-active';
}

function pageNumbers() {
  const pages = [];
  const start = Math.max(1, pagination.currentPage - 2);
  const end = Math.min(pagination.lastPage, start + 4);

  for (let page = start; page <= end; page += 1) {
    pages.push(page);
  }

  return pages;
}

function showToast(text) {
  toastMessage.value = text;

  if (toastTimeoutId) {
    clearTimeout(toastTimeoutId);
  }

  toastTimeoutId = setTimeout(() => {
    toastMessage.value = '';
  }, 2200);
}

async function loadUsers(page = pagination.currentPage, { silent = false } = {}) {
  const requestId = ++lastRequestId;

  if (!silent) {
    loading.value = true;
  }

  error.value = '';

  try {
    const response = await getFilteredAdminUsers({
      search: filters.search.trim(),
      role: filters.role,
      page,
      perPage: PER_PAGE,
    });

    if (requestId !== lastRequestId) {
      return;
    }

    users.value = response.users || [];
    currentUser.value = response.user || null;
    pagination.currentPage = response.pagination?.current_page || 1;
    pagination.lastPage = response.pagination?.last_page || 1;
    pagination.perPage = response.pagination?.per_page || PER_PAGE;
    pagination.total = response.pagination?.total || 0;
    pagination.from = response.pagination?.from || 0;
    pagination.to = response.pagination?.to || 0;
  } catch (e) {
    if (requestId !== lastRequestId) {
      return;
    }

    error.value = e.message || 'Neizdevas ieladet lietotajus.';
  } finally {
    if (requestId === lastRequestId) {
      loading.value = false;
    }
  }
}

function toggleDelete(userId) {
  activeRoleEdit.value = null;
  activeStatusEdit.value = null;
  activeDeleteUserId.value = activeDeleteUserId.value === userId ? null : userId;
}

function closeDelete() {
  activeDeleteUserId.value = null;
}

function toggleRoleEdit(userId) {
  if (updatingRoleId.value === userId) {
    return;
  }

  activeDeleteUserId.value = null;
  activeStatusEdit.value = null;
  activeRoleEdit.value = activeRoleEdit.value === userId ? null : userId;
}

function closeRoleEdit() {
  activeRoleEdit.value = null;
}

function toggleStatusEdit(userId) {
  if (updatingStatusId.value === userId) {
    return;
  }

  activeDeleteUserId.value = null;
  activeRoleEdit.value = null;
  activeStatusEdit.value = activeStatusEdit.value === userId ? null : userId;
}

function closeStatusEdit() {
  activeStatusEdit.value = null;
}

async function removeUser(user) {
  if (currentUser.value?.id === user.id) {
    return;
  }

  deletingUserId.value = user.id;
  error.value = '';
  message.value = '';

  try {
    const response = await deleteAdminUser(user.id);
    const nextPage = users.value.length === 1 && pagination.currentPage > 1
      ? pagination.currentPage - 1
      : pagination.currentPage;

    activeDeleteUserId.value = null;
    await loadUsers(nextPage, { silent: true });
    message.value = response.message || 'Lietotajs veiksmigi dzēsts.';
  } catch (e) {
    error.value = e.message || 'Neizdevas dzēst lietotaju.';
  } finally {
    deletingUserId.value = null;
  }
}

async function changeRole(user, nextRole) {
  const previousRole = user.role;

  if (nextRole === previousRole) {
    closeRoleEdit();
    return;
  }

  updatingRoleId.value = user.id;
  error.value = '';
  message.value = '';
  user.role = nextRole;

  try {
    const response = await updateAdminUserRole(user.id, nextRole);
    user.role = response.user?.role || nextRole;
    closeRoleEdit();

    if (currentUser.value?.id === user.id) {
      const refreshedUser = await loadUser();

      currentUser.value = refreshedUser || {
        ...currentUser.value,
        role: user.role,
      };
    }

    showToast('Saglabats');
  } catch (e) {
    user.role = previousRole;
    error.value = e.message || 'Neizdevas atjauninat lomu.';
  } finally {
    updatingRoleId.value = null;
  }
}

async function changeStatus(user, nextStatus) {
  const previousStatus = user.status;

  if (nextStatus === previousStatus) {
    closeStatusEdit();
    return;
  }

  updatingStatusId.value = user.id;
  error.value = '';
  message.value = '';
  user.status = nextStatus;

  try {
    const response = await updateAdminUserStatus(user.id, nextStatus);
    user.status = response.user?.status || nextStatus;
    closeStatusEdit();

    if (currentUser.value?.id === user.id) {
      currentUser.value = {
        ...currentUser.value,
        status: user.status,
      };
    }

    showToast('Status updated');
  } catch (e) {
    user.status = previousStatus;
    error.value = e.message || 'Neizdevas atjauninat statusu.';
  } finally {
    updatingStatusId.value = null;
  }
}

function goToPage(page) {
  if (page < 1 || page > pagination.lastPage || page === pagination.currentPage) {
    return;
  }

  closeDelete();
  closeRoleEdit();
  closeStatusEdit();
  loadUsers(page);
}

watch(
  () => filters.role,
  () => {
    closeDelete();
    closeRoleEdit();
    closeStatusEdit();
    loadUsers(1);
  },
);

watch(
  () => filters.search,
  () => {
    if (searchDebounceId) {
      clearTimeout(searchDebounceId);
    }

    searchDebounceId = setTimeout(() => {
      closeDelete();
      closeRoleEdit();
      closeStatusEdit();
      loadUsers(1);
    }, 300);
  },
);

onMounted(async () => {
  await loadUsers(1);
});

onBeforeUnmount(() => {
  if (searchDebounceId) {
    clearTimeout(searchDebounceId);
  }

  if (toastTimeoutId) {
    clearTimeout(toastTimeoutId);
  }
});
</script>

<template>
  <AuthCard class="profile-shell admin-shell">
    <div class="profile-container">
      <div class="profile-card admin-card">
        <div v-if="toastMessage" class="admin-toast" role="status" aria-live="polite">
          {{ toastMessage }}
        </div>

        <p class="section-label">Admin</p>
        <h1>Admin Panel</h1>

        <p class="auth-card__text">
          Lietotāju pārvaldība administratoriem
        </p>

        <p v-if="loading" class="auth-card__text">
          Ielade lietotajus...
        </p>

        <p v-if="error" class="auth-error">{{ error }}</p>
        <p v-if="message" class="auth-success">{{ message }}</p>

        <div v-if="!loading" class="admin-panel">
          <div class="admin-filters">
            <div class="admin-filters__grid">
              <label class="admin-filter-field">
                <span>Meklēt</span>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Lietotājvārds vai e-pasts"
                >
              </label>

              <label class="admin-filter-field">
                <span>Role</span>
                <select v-model="filters.role">
                  <option
                    v-for="role in roleOptions"
                    :key="role || 'all'"
                    :value="role"
                  >
                    {{ role || 'Visas' }}
                  </option>
                </select>
              </label>
            </div>
          </div>

          <div class="admin-summary profile-details__item">
            <span class="profile-label">Pašreizējā loma</span>
            <strong class="profile-value">{{ currentUser?.role || 'admin' }}</strong>
          </div>

          <div class="admin-table-card admin-table-shell">
            <table class="admin-table">
              <thead class="admin-header">
                <tr>
                  <th>ID</th>
                  <th>Lietotājvārds</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Reģistrēts</th>
                  <th class="admin-table__actions">Darbibas</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="user in users" :key="user.id">
                  <tr class="admin-row">
                    <td>{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                      <button
                        type="button"
                        :class="roleBadgeClass(user.role)"
                        :disabled="updatingRoleId === user.id"
                        @click="toggleRoleEdit(user.id)"
                      >
                        {{ updatingRoleId === user.id ? 'Saglabat...' : user.role }}
                      </button>
                    </td>
                    <td>
                      <button
                        type="button"
                        :class="statusBadgeClass(user.status)"
                        :disabled="updatingStatusId === user.id"
                        @click="toggleStatusEdit(user.id)"
                      >
                        {{ updatingStatusId === user.id ? 'Updating...' : user.status }}
                      </button>
                    </td>
                    <td>{{ formatDate(user.created_at) }}</td>
                    <td class="admin-table__actions">
                      <button
                        type="button"
                        class="profile-btn admin-delete-btn btn-delete"
                        :class="{ 'admin-delete-btn--self': currentUser?.id === user.id }"
                        :disabled="deletingUserId === user.id || currentUser?.id === user.id"
                        @click="toggleDelete(user.id)"
                      >
                        {{ currentUser?.id === user.id ? 'Tu' : (deletingUserId === user.id ? 'Dzes...' : 'Dzēst') }}
                      </button>
                    </td>
                  </tr>

                  <tr v-if="activeRoleEdit === user.id" class="role-expand-row">
                    <td colspan="7">
                      <div class="role-expand">
                        <div class="role-options">
                          <button
                            v-for="role in roleOptions.filter((role) => role)"
                            :key="role"
                            type="button"
                            class="role-options__button"
                            :class="[role, { active: role === user.role }]"
                            :disabled="updatingRoleId === user.id"
                            @click="changeRole(user, role)"
                          >
                            {{ role }}
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="activeStatusEdit === user.id" class="status-expand-row">
                    <td colspan="7">
                      <div class="status-expand">
                        <div class="status-options">
                          <button
                            v-for="status in statusOptions"
                            :key="status"
                            type="button"
                            class="status-options__button"
                            :class="[status, { active: status === user.status }]"
                            :disabled="updatingStatusId === user.id || (currentUser?.id === user.id && status === 'blocked')"
                            @click="changeStatus(user, status)"
                          >
                            {{ status }}
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>

                  <tr class="admin-table__expand-row">
                    <td colspan="7">
                      <transition name="slide-fade">
                        <div v-if="activeDeleteUserId === user.id" class="admin-delete-inline delete-expand">
                          <p>
                            Neatgriezeniski dzēsis lietotāju un visus datus.
                          </p>

                          <div class="actions">
                            <button
                              type="button"
                              class="cancel"
                              :disabled="deletingUserId === user.id"
                              @click="closeDelete"
                            >
                              Atcelt
                            </button>

                            <button
                              type="button"
                              class="profile-btn admin-delete-confirm-btn btn-delete-main"
                              :disabled="deletingUserId === user.id"
                              @click="removeUser(user)"
                            >
                              {{ deletingUserId === user.id ? 'Dzes...' : 'Dzēst neatgriezeniski' }}
                            </button>
                          </div>
                        </div>
                      </transition>
                    </td>
                  </tr>
                </template>

                <tr v-if="users.length === 0">
                  <td colspan="7" class="admin-table__empty">
                    Lietotaji netika atrasti.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="admin-footer">
            <p class="admin-results">
              {{ pagination.total ? `${pagination.from}-${pagination.to} no ${pagination.total}` : '0 rezultati' }}
            </p>

            <nav v-if="pagination.lastPage > 1" class="admin-pagination" aria-label="Pagination">
              <button
                type="button"
                class="admin-page-btn"
                :disabled="pagination.currentPage === 1"
                @click="goToPage(pagination.currentPage - 1)"
              >
                &larr;
              </button>

              <button
                v-for="page in pageNumbers()"
                :key="page"
                type="button"
                class="admin-page-btn"
                :class="{ 'admin-page-btn--active': page === pagination.currentPage }"
                @click="goToPage(page)"
              >
                {{ page }}
              </button>

              <button
                type="button"
                class="admin-page-btn"
                :disabled="pagination.currentPage === pagination.lastPage"
                @click="goToPage(pagination.currentPage + 1)"
              >
                &rarr;
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AuthCard>
</template>
