<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function updateName(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $user->forceFill([
            'name' => $data['name'],
        ])->save();

        return response()->json([
            'message' => 'Lietotajvards veiksmigi atjauninats.',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Pasreizeja parole nav pareiza.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return response()->json([
            'message' => 'Parole veiksmigi nomainita.',
        ]);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $emailChanged = $data['email'] !== $user->email;

        $user->email = $data['email'];

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => $emailChanged
                ? 'E-pasts atjauninats. Nosutijam jaunu apstiprinasanas saiti.'
                : 'E-pasts nav mainits.',
            'user' => $user->fresh(),
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ], [
            'avatar.max' => 'Attela izmērs nedrīkst pārsniegt 2 MB.',
        ]);

        $user = $request->user();
        $oldAvatar = $user->avatar;
        $path = $data['avatar']->store('avatars', 'public');

        $user->forceFill([
            'avatar' => $path,
        ])->save();

        if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return response()->json([
            'message' => 'Avatar updated.',
            'avatar' => $user->avatar,
            'user' => $user->fresh(),
        ]);
    }

    public function becomeOrganizator(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if ($user->role === User::ROLE_ORGANIZATOR) {
            return response()->json([
                'message' => 'Tu jau esi organizators.',
                'user' => $user->fresh(),
            ]);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return response()->json([
                'message' => 'Admin lomu nevar mainit saja forma.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Pasreizeja parole nav pareiza.'],
            ]);
        }

        $user->forceFill([
            'role' => User::ROLE_ORGANIZATOR,
        ])->save();

        return response()->json([
            'message' => 'Tu veiksmigi kluvi par organizatoru.',
            'user' => $user->fresh(),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        Auth::guard('web')->logout();
        $user->delete();

        return response()->json([
            'message' => 'Konts dzests.',
            'redirect' => '/',
        ]);
    }
}
