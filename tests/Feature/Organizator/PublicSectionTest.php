<?php

namespace Tests\Feature\Organizator;

use App\Models\Section;
use App\Models\SectionAddress;
use App\Models\SectionApplication;
use App\Models\UserNotification;
use App\Models\SectionReview;
use App\Models\SectionSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_sections_endpoint_returns_only_active_sections(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);

        $activeSection = Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Aktiva sekcija',
            'slug' => 'aktiva-sekcija',
            'status' => Section::STATUS_ACTIVE,
        ]);

        Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Hidden sekcija',
            'slug' => 'hidden-sekcija',
            'status' => Section::STATUS_HIDDEN,
        ]);

        $this
            ->getJson('/api/sections')
            ->assertOk()
            ->assertJsonCount(1, 'sections')
            ->assertJsonPath('sections.0.id', $activeSection->id)
            ->assertJsonPath('sections.0.slug', 'aktiva-sekcija');
    }

    public function test_public_section_detail_and_application_flow_work(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
            'name' => 'Organizators',
        ]);
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'name' => 'Janis',
            'email' => 'janis@example.com',
        ]);

        $section = Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Futbols',
            'slug' => 'futbols',
            'description' => 'Futbola nodarbibas.',
            'sport_type' => 'Futbols',
            'age_group' => '7-12',
            'status' => Section::STATUS_ACTIVE,
        ]);
        SectionAddress::query()->create([
            'section_id' => $section->id,
            'city' => 'Riga',
            'full_address' => 'Riga, Brivibas 1',
        ]);
        SectionSchedule::query()->create([
            'section_id' => $section->id,
            'day_of_week' => 'monday',
            'start_time' => '18:00:00',
            'end_time' => '19:30:00',
        ]);

        $this
            ->getJson('/api/sections/futbols')
            ->assertOk()
            ->assertJsonPath('section.id', $section->id)
            ->assertJsonPath('section.organizator.name', 'Organizators')
            ->assertJsonPath('section.address.city', 'Riga')
            ->assertJsonPath('section.schedules.0.day_of_week', 'monday')
            ->assertJsonPath('application_meta.requires_auth', true)
            ->assertJsonPath('application_meta.already_applied', false);

        $this
            ->withHeader('Authorization', 'Bearer '.$user->createToken('api-token')->plainTextToken)
            ->postJson('/api/sections/futbols/applications', [
                'name' => 'Janis',
                'email' => 'janis@example.com',
                'phone' => '+37120000000',
                'message' => 'Velos pieteikties.',
            ])
            ->assertOk()
            ->assertJsonPath('application.status', SectionApplication::STATUS_PENDING);

        $this->assertDatabaseHas('section_applications', [
            'section_id' => $section->id,
            'user_id' => $user->id,
            'email' => 'janis@example.com',
            'status' => SectionApplication::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $organizator->id,
            'type' => 'new_section_application',
        ]);

        $this
            ->withHeader('Authorization', 'Bearer '.$user->createToken('meta-token')->plainTextToken)
            ->getJson('/api/sections/futbols')
            ->assertOk()
            ->assertJsonPath('application_meta.enabled', true)
            ->assertJsonPath('application_meta.already_applied', true)
            ->assertJsonPath('application_meta.application_status', SectionApplication::STATUS_PENDING);

        $this
            ->withHeader('Authorization', 'Bearer '.$user->createToken('review-token')->plainTextToken)
            ->postJson('/api/sections/futbols/reviews', [
                'rating' => 5,
                'comment' => 'Loti patika nodarbibas.',
            ])
            ->assertOk()
            ->assertJsonPath('review.rating', 5);

        $this->assertDatabaseHas('section_reviews', [
            'section_id' => $section->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_public_sections_can_be_filtered(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);

        $basketball = Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Basketbols',
            'slug' => 'basketbols',
            'sport_type' => 'Basketbols',
            'age_group' => '13-16',
            'price' => 40,
            'status' => Section::STATUS_ACTIVE,
        ]);
        SectionAddress::query()->create([
            'section_id' => $basketball->id,
            'city' => 'Riga',
        ]);

        $swimming = Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Peldesana',
            'slug' => 'peldesana',
            'sport_type' => 'Peldesana',
            'age_group' => '7-12',
            'price' => 20,
            'status' => Section::STATUS_ACTIVE,
        ]);
        SectionAddress::query()->create([
            'section_id' => $swimming->id,
            'city' => 'Jurmala',
        ]);

        $this
            ->getJson('/api/sections?sport_type=Basketbols&city=Riga&price_min=30&age_group=13-16')
            ->assertOk()
            ->assertJsonCount(1, 'sections')
            ->assertJsonPath('sections.0.id', $basketball->id);
    }

    public function test_hidden_section_is_not_available_publicly(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);

        Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Hidden sekcija',
            'slug' => 'hidden-sekcija',
            'status' => Section::STATUS_HIDDEN,
        ]);

        $this
            ->getJson('/api/sections/hidden-sekcija')
            ->assertNotFound();
    }
}
