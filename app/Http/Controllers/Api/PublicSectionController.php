<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SectionApplication;
use App\Models\UserNotification;
use App\Models\SectionReview;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sport_type' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'age_group' => ['nullable', 'string', 'max:255'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sections = Section::query()
            ->with([
                'organizator:id,name,email,phone,location',
                'address',
                'schedules',
            ])
            ->withCount('applications')
            ->withAvg('reviews', 'rating')
            ->where('status', Section::STATUS_ACTIVE)
            ->when(filled($data['search'] ?? null), function ($query) use ($data) {
                $search = trim((string) $data['search']);

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sport_type', 'like', "%{$search}%");
                });
            })
            ->when(filled($data['sport_type'] ?? null), fn ($query) => $query->where('sport_type', $data['sport_type']))
            ->when(filled($data['age_group'] ?? null), fn ($query) => $query->where('age_group', $data['age_group']))
            ->when(isset($data['price_min']), fn ($query) => $query->where('price', '>=', $data['price_min']))
            ->when(isset($data['price_max']), fn ($query) => $query->where('price', '<=', $data['price_max']))
            ->when(filled($data['city'] ?? null), function ($query) use ($data) {
                $query->whereHas('address', fn ($addressQuery) => $addressQuery->where('city', $data['city']));
            })
            ->latest('id')
            ->get();

        $filterMeta = [
            'sport_types' => Section::query()
                ->whereNotNull('sport_type')
                ->where('sport_type', '!=', '')
                ->distinct()
                ->orderBy('sport_type')
                ->pluck('sport_type')
                ->values(),
            'cities' => \App\Models\SectionAddress::query()
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->distinct()
                ->orderBy('city')
                ->pluck('city')
                ->values(),
            'age_groups' => Section::query()
                ->whereNotNull('age_group')
                ->where('age_group', '!=', '')
                ->distinct()
                ->orderBy('age_group')
                ->pluck('age_group')
                ->values(),
        ];

        return response()->json([
            'sections' => $sections->map(fn (Section $section) => $this->serializeSection($section, true, false))->values(),
            'filters' => $filterMeta,
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $section = Section::query()
            ->with([
                'organizator:id,name,email,phone,location',
                'address',
                'schedules',
                'reviews.user:id,name,avatar',
            ])
            ->withCount('applications')
            ->withAvg('reviews', 'rating')
            ->where('slug', $slug)
            ->where('status', Section::STATUS_ACTIVE)
            ->firstOrFail();

        /** @var User|null $user */
        $user = $request->user('sanctum');
        $existingReview = $user
            ? SectionReview::query()
                ->where('section_id', $section->id)
                ->where('user_id', $user->id)
                ->first()
            : null;
        $existingApplication = $user
            ? SectionApplication::query()
                ->where('section_id', $section->id)
                ->where('user_id', $user->id)
                ->first()
            : null;

        return response()->json([
            'section' => $this->serializeSection($section, true, true),
            'application_meta' => [
                'enabled' => (bool) $user,
                'requires_auth' => !$user,
                'already_applied' => (bool) $existingApplication,
                'status' => SectionApplication::STATUS_PENDING,
                'application_status' => $existingApplication?->status,
            ],
            'review_meta' => [
                'enabled' => $user?->role === User::ROLE_USER && ! $existingReview,
                'requires_auth' => !$user,
                'already_reviewed' => (bool) $existingReview,
            ],
        ]);
    }

    public function getReviews(string $slug): JsonResponse
    {
        $section = Section::query()
            ->with(['reviews.user:id,name,avatar'])
            ->withAvg('reviews', 'rating')
            ->where('slug', $slug)
            ->where('status', Section::STATUS_ACTIVE)
            ->firstOrFail();

        return response()->json([
            'average_rating' => $section->reviews_avg_rating ? round((float) $section->reviews_avg_rating, 1) : null,
            'reviews' => $section->reviews->map(fn (SectionReview $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reviewed_at' => $review->reviewed_at,
                'user' => [
                    'id' => $review->user?->id,
                    'name' => $review->user?->name,
                    'avatar' => $review->user?->avatar,
                ],
            ])->values(),
        ]);
    }

    public function apply(Request $request, string $slug): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Authentication required.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $section = Section::query()
            ->where('slug', $slug)
            ->where('status', Section::STATUS_ACTIVE)
            ->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $existingApplication = SectionApplication::query()
            ->where('section_id', $section->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingApplication && $existingApplication->status === SectionApplication::STATUS_PENDING) {
            return response()->json([
                'message' => 'Jūs jau esat pieteicies un gaidāt apstiprinājumu.',
                'application' => [
                    'id' => $existingApplication->id,
                    'status' => $existingApplication->status,
                ],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $application = SectionApplication::query()->updateOrCreate(
            [
                'section_id' => $section->id,
                'user_id' => $user->id,
            ],
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => SectionApplication::STATUS_PENDING,
            ]
        );

        UserNotification::query()->create([
            'user_id' => $section->organizator_id,
            'type' => 'new_section_application',
            'title' => 'Jauns pieteikums uz sekciju',
            'message' => "{$application->name} pieteicās uz {$section->title}",
            'link' => '/organizator',
        ]);

        return response()->json([
            'message' => 'Pieteikums veiksmigi nosutits.',
            'application' => [
                'id' => $application->id,
                'status' => $application->status,
            ],
        ]);
    }

    public function addReview(Request $request, string $slug): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Authentication required.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($user->role !== User::ROLE_USER) {
            return response()->json(['message' => 'Only users can leave reviews.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $section = Section::query()
            ->where('slug', $slug)
            ->where('status', Section::STATUS_ACTIVE)
            ->firstOrFail();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:3000'],
        ]);

        $existingReview = SectionReview::query()
            ->where('section_id', $section->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Jūs jau atstājāt atsauksmi.',
                'errors' => [
                    'review' => ['Jūs jau atstājāt atsauksmi.'],
                ],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $review = SectionReview::query()->create([
            'section_id' => $section->id,
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'reviewed_at' => now(),
        ]);

        $review->loadMissing('user:id,name,avatar');
        $section->loadAvg('reviews', 'rating');

        return response()->json([
            'message' => 'Atsauksme saglabata.',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reviewed_at' => $review->reviewed_at,
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'avatar' => $review->user->avatar,
                ],
            ],
            'average_rating' => $section->reviews_avg_rating ? round((float) $section->reviews_avg_rating, 1) : null,
        ]);
    }

    public function storeReview(Request $request, string $slug): JsonResponse
    {
        return $this->addReview($request, $slug);
    }

    protected function serializeSection(Section $section, bool $includeOrganizator = false, bool $includeReviews = false): array
    {
        $payload = [
            'id' => $section->id,
            'slug' => $section->slug,
            'title' => $section->title,
            'sport_type' => $section->sport_type,
            'age_group' => $section->age_group,
            'description' => $section->description,
            'price' => $section->price,
            'image' => $section->image,
            'image_url' => $section->image ? "/storage/{$section->image}" : null,
            'location' => $section->location,
            'status' => $section->status,
            'applications_count' => $section->applications_count ?? null,
            'average_rating' => $section->reviews_avg_rating ? round((float) $section->reviews_avg_rating, 1) : null,
            'address' => $section->address ? [
                'city' => $section->address->city,
                'street' => $section->address->street,
                'full_address' => $section->address->full_address,
                'coordinates' => $section->address->coordinates,
            ] : null,
            'schedules' => $section->schedules->map(fn ($schedule) => [
                'id' => $schedule->id,
                'day_of_week' => $schedule->day_of_week,
                'is_day_off' => (bool) $schedule->is_day_off,
                'start_time' => substr((string) $schedule->start_time, 0, 5),
                'end_time' => substr((string) $schedule->end_time, 0, 5),
            ])->values(),
        ];

        if ($includeOrganizator) {
            $payload['organizator'] = [
                'id' => $section->organizator?->id,
                'name' => $section->organizator?->name,
                'email' => $section->organizator?->email,
                'phone' => $section->organizator?->phone,
                'location' => $section->organizator?->location,
            ];
        }

        if ($includeReviews) {
            $payload['reviews'] = $section->reviews->map(fn (SectionReview $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'reviewed_at' => $review->reviewed_at,
                'user' => [
                    'id' => $review->user?->id,
                    'name' => $review->user?->name,
                    'avatar' => $review->user?->avatar,
                ],
            ])->values();
        }

        return $payload;
    }
}
