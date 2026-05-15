import { apiRequest } from './api';

export function register(form) {
  return apiRequest('/register', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function login(form) {
  return apiRequest('/login', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function forgotPassword(form) {
  return apiRequest('/forgot-password', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function resetPassword(form) {
  return apiRequest('/reset-password', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function confirmEmailVerification({ id, hash, token, expires }) {
  const query = new URLSearchParams({
    expires,
    signature: token,
  });

  return apiRequest(`/verify-email/${id}/${hash}?${query.toString()}`);
}

export function getUser() {
  return apiRequest('/user');
}

export function getAdminPanel() {
  return apiRequest('/admin');
}

export function getAdminUsers() {
  return apiRequest('/admin/users');
}

export function getFilteredAdminUsers(params = {}) {
  const query = new URLSearchParams();

  if (params.search) {
    query.set('search', params.search);
  }

  if (params.role) {
    query.set('role', params.role);
  }

  if (params.page) {
    query.set('page', params.page);
  }

  if (params.perPage) {
    query.set('per_page', params.perPage);
  }

  const suffix = query.toString() ? `?${query.toString()}` : '';

  return apiRequest(`/admin/users${suffix}`);
}

export function updateAdminUserRole(id, role) {
  return apiRequest(`/admin/users/${id}/role`, {
    method: 'PATCH',
    body: JSON.stringify({ role }),
  });
}

export function updateAdminUserStatus(id, status) {
  return apiRequest(`/admin/users/${id}/status`, {
    method: 'PUT',
    body: JSON.stringify({ status }),
  });
}

export function deleteAdminUser(id) {
  return apiRequest(`/admin/users/${id}`, {
    method: 'DELETE',
  });
}

export function getOrganizatorPanel() {
  return apiRequest('/organizator');
}

export function getPublicSections(params = {}) {
  const query = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      query.set(key, value);
    }
  });

  const suffix = query.toString() ? `?${query.toString()}` : '';

  return apiRequest(`/sections${suffix}`);
}

export function getPublicSection(slug) {
  return apiRequest(`/sections/${slug}`);
}

export function createSectionApplication(slug, form) {
  return apiRequest(`/sections/${slug}/applications`, {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function createPieraksts(form) {
  return apiRequest('/pieraksti', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function createSectionReview(slug, form) {
  return apiRequest(`/sections/${slug}/reviews`, {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function updateOrganizatorProfile(form) {
  return apiRequest('/organizator/profile', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function createOrganizatorSection(formData) {
  return apiRequest('/organizator/sections', {
    method: 'POST',
    body: formData,
  });
}

export function updateOrganizatorSection(id, formData) {
  return apiRequest(`/organizator/sections/${id}`, {
    method: 'POST',
    body: formData,
  });
}

export function deleteOrganizatorSection(id) {
  return apiRequest(`/organizator/sections/${id}`, {
    method: 'DELETE',
  });
}

export function createOrganizatorSchedule(sectionId, form) {
  return apiRequest(`/organizator/sections/${sectionId}/schedules`, {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function deleteOrganizatorSchedule(id) {
  return apiRequest(`/organizator/schedules/${id}`, {
    method: 'DELETE',
  });
}

export function updateOrganizatorApplicationStatus(id, status) {
  return apiRequest(`/organizator/applications/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  });
}

export function getNotifications() {
  return apiRequest('/notifications');
}

export function markNotificationRead(id) {
  return apiRequest(`/notifications/${id}/read`, {
    method: 'POST',
  });
}

export function updateProfileName(form) {
  return apiRequest('/profile/name', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function updateProfilePassword(form) {
  return apiRequest('/profile/password', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function updateProfileEmail(form) {
  return apiRequest('/profile/email', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function becomeOrganizator(form) {
  return apiRequest('/become-organizator', {
    method: 'POST',
    body: JSON.stringify(form),
  });
}

export function uploadUserAvatar(formData) {
  return apiRequest('/user/avatar', {
    method: 'POST',
    body: formData,
  });
}

export function deleteUser() {
  return apiRequest('/profile/delete', {
    method: 'DELETE',
  });
}

export function logout() {
  return apiRequest('/logout', {
    method: 'POST',
  });
}

export function resendVerificationEmail() {
  return apiRequest('/email/verification-notification', {
    method: 'POST',
  });
}
