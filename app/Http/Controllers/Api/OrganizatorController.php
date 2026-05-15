<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SectionAddress;
use App\Models\SectionApplication;
use App\Models\SectionSchedule;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizatorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->fresh();

        $sections = $user->sections()
            ->with([
                'address',
                'schedules',
                'applications.user:id,name,email',
            ])
            ->withCount('applications')
            ->withAvg('reviews', 'rating')
            ->latest('id')
            ->get();

        $applications = SectionApplication::query()
            ->with(['user:id,name,email', 'section:id,title,organizator_id'])
            ->whereHas('section', fn ($query) => $query->where('organizator_id', $user->id))
            ->latest('id')
            ->get();

        return response()->json([
            'message' => 'Organizatora panelis',
            'user' => $this->serializeUser($user),
            'sections' => $sections->map(fn (Section $section) => $this->serializeSection($section, true)),
            'application_summary' => [
                'pending' => $applications->where('status', SectionApplication::STATUS_PENDING)->count(),
                'approved' => $applications->where('status', SectionApplication::STATUS_APPROVED)->count(),
                'rejected' => $applications->where('status', SectionApplication::STATUS_REJECTED)->count(),
                'total' => $applications->count(),
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $user->forceFill([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'location' => $data['location'] ?? null,
        ])->save();

        return response()->json([
            'message' => 'Profils ir veiksmīgi atjaunināts',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    public function storeSection(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $this->validateSection($request);

        $section = Section::query()->create([
            'organizator_id' => $user->id,
            'title' => $data['title'],
            'slug' => $this->generateSlug($data['title']),
            'sport_type' => $data['sport_type'] ?? null,
            'age_group' => $data['age_group'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'image' => isset($data['image']) ? $data['image']->store('sections', 'public') : null,
            'location' => $data['location'] ?? null,
            'status' => $data['status'] ?? Section::STATUS_ACTIVE,
        ]);

        $this->syncAddress($section, $data);

        return response()->json([
            'message' => 'Sekcija veiksmīgi izveidota.',
            'section' => $this->serializeSection($section->fresh(['address', 'schedules', 'applications.user:id,name,email'])->loadCount('applications')->loadAvg('reviews', 'rating'), true),
        ]);
    }

    public function updateSection(Request $request, Section $section): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($section->organizator_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = $this->validateSection($request);
        $oldImage = $section->image;

        if (isset($data['image'])) {
            $section->image = $data['image']->store('sections', 'public');
        }

        $section->fill([
            'title' => $data['title'],
            'slug' => $this->generateSlug($data['title'], $section->id),
            'sport_type' => $data['sport_type'] ?? null,
            'age_group' => $data['age_group'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'location' => $data['location'] ?? null,
            'status' => $data['status'] ?? $section->status,
        ])->save();

        $this->syncAddress($section, $data);

        if (isset($data['image']) && $oldImage && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        return response()->json([
            'message' => 'Sekcija veiksmigi atjauninata.',
            'section' => $this->serializeSection($section->fresh(['address', 'schedules', 'applications.user:id,name,email'])->loadCount('applications')->loadAvg('reviews', 'rating'), true),
        ]);
    }

    public function destroySection(Request $request, Section $section): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($section->organizator_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        if ($section->image && Storage::disk('public')->exists($section->image)) {
            Storage::disk('public')->delete($section->image);
        }

        $section->delete();

        return response()->json([
            'message' => 'Sekcija veiksmigi dzesta.',
        ]);
    }

    public function storeSchedule(Request $request, Section $section): JsonResponse
    {
        $this->authorizeSection($request->user(), $section);

        $data = $request->validate([
            'day_of_week' => ['required', Rule::in($this->daysOfWeek())],
            'is_day_off' => ['nullable', 'boolean'],
            'start_time' => ['nullable', 'date_format:H:i', Rule::requiredIf(! $request->boolean('is_day_off'))],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time', Rule::requiredIf(! $request->boolean('is_day_off'))],
        ]);

        $isDayOff = (bool) ($data['is_day_off'] ?? false);

        $schedule = $section->schedules()->create([
            'day_of_week' => $data['day_of_week'],
            'is_day_off' => $isDayOff,
            'start_time' => $isDayOff ? null : $data['start_time'],
            'end_time' => $isDayOff ? null : $data['end_time'],
        ]);

        return response()->json([
            'message' => 'Grafiks pievienots.',
            'schedule' => $this->serializeSchedule($schedule),
        ]);
    }

    public function destroySchedule(Request $request, SectionSchedule $schedule): JsonResponse
    {
        $schedule->loadMissing('section');
        $this->authorizeSection($request->user(), $schedule->section);
        $schedule->delete();

        return response()->json([
            'message' => 'Grafika ieraksts dzests.',
        ]);
    }

    public function updateApplicationStatus(Request $request, SectionApplication $application): JsonResponse
    {
        $application->loadMissing('section', 'user:id,name,email');
        $this->authorizeSection($request->user(), $application->section);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                SectionApplication::STATUS_PENDING,
                SectionApplication::STATUS_APPROVED,
                SectionApplication::STATUS_REJECTED,
            ])],
        ]);

        $application->update([
            'status' => $data['status'],
        ]);

        if ($application->user_id) {
            UserNotification::query()->create([
                'user_id' => $application->user_id,
                'type' => 'application_status_updated',
                'title' => $data['status'] === SectionApplication::STATUS_APPROVED
                    ? 'Jūsu pieteikums ir apstiprināts'
                    : ($data['status'] === SectionApplication::STATUS_REJECTED
                        ? 'Jūsu pieteikums ir noraidīts'
                        : 'Jūsu pieteikuma statuss ir atjaunināts'),
                'message' => "Sekcija: {$application->section->title}",
                'link' => "/sekcija/{$application->section->slug}",
            ]);
        }

        return response()->json([
            'message' => 'Pieteikuma statuss atjauninats.',
            'application' => $this->serializeApplication($application->fresh(['user:id,name,email'])),
        ]);
    }

    protected function validateSection(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sport_type' => ['nullable', 'string', 'max:255'],
            'age_group' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string', 'max:255'],
            'coordinates' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                Section::STATUS_ACTIVE,
                Section::STATUS_HIDDEN,
            ])],
        ]);
    }

    protected function syncAddress(Section $section, array $data): void
    {
        $addressPayload = [
            'city' => $data['city'] ?? null,
            'street' => $data['street'] ?? null,
            'full_address' => $data['full_address'] ?? ($data['location'] ?? null),
            'coordinates' => $data['coordinates'] ?? null,
        ];

        if (collect($addressPayload)->filter(fn ($value) => filled($value))->isEmpty()) {
            $section->address()?->delete();
            return;
        }

        $section->address()->updateOrCreate([], $addressPayload);
    }

    protected function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'sekcija';
        $slug = $baseSlug;
        $suffix = 1;

        while (
            Section::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected function authorizeSection(User $user, Section $section): void
    {
        abort_unless($section->organizator_id === $user->id, JsonResponse::HTTP_FORBIDDEN, 'Forbidden.');
    }

    protected function daysOfWeek(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    }

    protected function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'location' => $user->location,
            'created_at' => $user->created_at,
        ];
    }

    protected function serializeSection(Section $section, bool $includeApplications = false): array
    {
        $payload = [
            'id' => $section->id,
            'organizator_id' => $section->organizator_id,
            'title' => $section->title,
            'slug' => $section->slug,
            'sport_type' => $section->sport_type,
            'age_group' => $section->age_group,
            'description' => $section->description,
            'price' => $section->price,
            'image' => $section->image,
            'image_url' => $section->image ? "/storage/{$section->image}" : null,
            'location' => $section->location,
            'status' => $section->status,
            'applications_count' => $section->applications_count ?? 0,
            'average_rating' => $section->reviews_avg_rating ? round((float) $section->reviews_avg_rating, 1) : null,
            'address' => $section->address ? [
                'city' => $section->address->city,
                'street' => $section->address->street,
                'full_address' => $section->address->full_address,
                'coordinates' => $section->address->coordinates,
            ] : null,
            'schedules' => $section->schedules->map(fn (SectionSchedule $schedule) => $this->serializeSchedule($schedule))->values(),
            'created_at' => $section->created_at,
            'updated_at' => $section->updated_at,
        ];

        if ($includeApplications) {
            $payload['applications'] = $section->applications->map(fn (SectionApplication $application) => $this->serializeApplication($application))->values();
        }

        return $payload;
    }

    protected function serializeSchedule(SectionSchedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'day_of_week' => $schedule->day_of_week,
            'is_day_off' => $schedule->is_day_off,
            'start_time' => $schedule->start_time ? Str::of($schedule->start_time)->beforeLast(':')->toString() : null,
            'end_time' => $schedule->end_time ? Str::of($schedule->end_time)->beforeLast(':')->toString() : null,
        ];
    }

    protected function serializeApplication(SectionApplication $application): array
    {
        return [
            'id' => $application->id,
            'section_id' => $application->section_id,
            'user_id' => $application->user_id,
            'name' => $application->name,
            'email' => $application->email,
            'phone' => $application->phone,
            'message' => $application->message,
            'status' => $application->status,
            'user' => $application->user ? [
                'id' => $application->user->id,
                'name' => $application->user->name,
                'email' => $application->user->email,
            ] : null,
            'created_at' => $application->created_at,
        ];
    }
}
