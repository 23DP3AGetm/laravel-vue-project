<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in([
                User::ROLE_USER,
                User::ROLE_ORGANIZATOR,
                User::ROLE_ADMIN,
            ])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $search = trim((string) ($data['search'] ?? ''));
        $role = $data['role'] ?? null;
        $perPage = (int) ($data['per_page'] ?? 10);

        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'is_blocked', 'created_at'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, fn ($query) => $query->where('role', $role))
            ->orderBy('id')
            ->paginate($perPage)
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status ?: ($user->is_blocked ? User::STATUS_BLOCKED : User::STATUS_ACTIVE),
                'created_at' => $user->created_at,
            ]);

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'user' => $request->user(),
        ]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in([
                User::ROLE_USER,
                User::ROLE_ORGANIZATOR,
                User::ROLE_ADMIN,
            ])],
        ]);

        $user->forceFill([
            'role' => $data['role'],
        ])->save();

        AdminAction::query()->create([
            'admin_id' => $request->user()->id,
            'action' => AdminAction::ACTION_CHANGE_ROLE,
            'target_user_id' => $user->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Loma veiksmigi atjauninata.',
            'user' => $user->fresh(),
        ]);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'Cannot block yourself',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_BLOCKED,
            ])],
        ]);

        $status = $data['status'];

        $user->forceFill([
            'status' => $status,
            'is_blocked' => $status === User::STATUS_BLOCKED,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'user' => $user->fresh(),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'Tu nevari dzest pats sevi.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        AdminAction::query()->create([
            'admin_id' => $request->user()->id,
            'action' => AdminAction::ACTION_DELETE_USER,
            'target_user_id' => $user->id,
            'created_at' => now(),
        ]);

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Lietotajs veiksmigi dzests.',
        ]);
    }
}
