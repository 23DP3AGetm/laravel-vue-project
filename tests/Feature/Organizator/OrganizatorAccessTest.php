<?php

namespace Tests\Feature\Organizator;

use App\Models\Section;
use App\Models\SectionApplication;
use App\Models\SectionSchedule;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizatorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizator_can_access_organizator_panel(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);
        $otherOrganizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);
        $ownSection = Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Futbols',
            'slug' => 'futbols',
            'description' => 'Grupu treniņi bērniem.',
            'price' => 25,
            'location' => 'Riga',
            'status' => Section::STATUS_ACTIVE,
        ]);
        SectionApplication::query()->create([
            'section_id' => $ownSection->id,
            'name' => 'Applicants',
            'email' => 'applicant@example.com',
            'status' => SectionApplication::STATUS_PENDING,
        ]);
        Section::query()->create([
            'organizator_id' => $otherOrganizator->id,
            'title' => 'Peldēšana',
            'slug' => 'peldesana',
            'description' => 'Cita organizatora sekcija.',
            'price' => 35,
            'location' => 'Jurmala',
            'status' => Section::STATUS_ACTIVE,
        ]);
        $token = $organizator->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/organizator')
            ->assertOk()
            ->assertJsonPath('user.role', User::ROLE_ORGANIZATOR)
            ->assertJsonPath('application_summary.pending', 1)
            ->assertJsonPath('application_summary.approved', 0)
            ->assertJsonPath('application_summary.rejected', 0)
            ->assertJsonCount(1, 'sections')
            ->assertJsonPath('sections.0.id', $ownSection->id)
            ->assertJsonPath('sections.0.organizator_id', $organizator->id)
            ->assertJsonPath('sections.0.slug', 'futbols')
            ->assertJsonPath('sections.0.status', Section::STATUS_ACTIVE)
            ->assertJsonPath('sections.0.applications_count', 1);
    }

    public function test_admin_cannot_access_organizator_panel(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $token = $admin->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/organizator')
            ->assertForbidden();
    }

    public function test_organizator_can_update_profile(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);
        $token = $organizator->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/organizator/profile', [
                'name' => 'Updated Organizator',
                'phone' => '+37120000000',
                'location' => 'Riga',
            ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Updated Organizator')
            ->assertJsonPath('user.phone', '+37120000000')
            ->assertJsonPath('user.location', 'Riga');
    }

    public function test_organizator_can_create_and_manage_own_section(): void
    {
        Storage::fake('public');

        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);
        $otherOrganizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);
        $foreignSection = Section::query()->create([
            'organizator_id' => $otherOrganizator->id,
            'title' => 'Sveša sekcija',
            'slug' => 'svesa-sekcija',
            'status' => Section::STATUS_ACTIVE,
        ]);
        $token = $organizator->createToken('api-token')->plainTextToken;

        $createResponse = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/organizator/sections', [
                'title' => 'Bokss',
                'description' => 'Vakara treniņi.',
                'price' => '30',
                'location' => 'Ogre',
                'status' => Section::STATUS_ACTIVE,
                'image' => UploadedFile::fake()->image('boxing.jpg'),
            ]);

        $createResponse
            ->assertOk()
            ->assertJsonPath('section.organizator_id', $organizator->id)
            ->assertJsonPath('section.title', 'Bokss')
            ->assertJsonPath('section.status', Section::STATUS_ACTIVE)
            ->assertJsonPath('section.slug', 'bokss');

        $sectionId = $createResponse->json('section.id');

        $this->assertDatabaseHas('sections', [
            'id' => $sectionId,
            'organizator_id' => $organizator->id,
            'title' => 'Bokss',
            'status' => Section::STATUS_ACTIVE,
            'slug' => 'bokss',
        ]);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post("/api/organizator/sections/{$sectionId}", [
                'title' => 'Bokss Pro',
                'description' => 'Atjaunināts apraksts.',
                'price' => '35',
                'location' => 'Riga',
                'status' => Section::STATUS_HIDDEN,
            ])
            ->assertOk()
            ->assertJsonPath('section.title', 'Bokss Pro')
            ->assertJsonPath('section.location', 'Riga')
            ->assertJsonPath('section.status', Section::STATUS_HIDDEN)
            ->assertJsonPath('section.slug', 'bokss-pro');

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/organizator/sections/{$foreignSection->id}", [
                'title' => 'Nedrīkst',
            ])
            ->assertForbidden();

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/organizator/sections/{$sectionId}")
            ->assertOk();

        $this->assertDatabaseMissing('sections', [
            'id' => $sectionId,
        ]);
    }

    public function test_organizator_can_manage_schedule_and_application_status(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $section = Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Volejbols',
            'slug' => 'volejbols',
            'status' => Section::STATUS_ACTIVE,
        ]);
        $application = SectionApplication::query()->create([
            'section_id' => $section->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => SectionApplication::STATUS_PENDING,
        ]);
        $token = $organizator->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/organizator/sections/{$section->id}/schedules", [
                'day_of_week' => 'monday',
                'start_time' => '18:00',
                'end_time' => '19:30',
            ])
            ->assertOk()
            ->assertJsonPath('schedule.day_of_week', 'monday');

        $scheduleId = SectionSchedule::query()->value('id');

        $this->assertDatabaseHas('section_schedules', [
            'id' => $scheduleId,
            'section_id' => $section->id,
            'day_of_week' => 'monday',
        ]);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson("/api/organizator/applications/{$application->id}", [
                'status' => SectionApplication::STATUS_APPROVED,
            ])
            ->assertOk()
            ->assertJsonPath('application.status', SectionApplication::STATUS_APPROVED);

        $this->assertDatabaseHas('section_applications', [
            'id' => $application->id,
            'status' => SectionApplication::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type' => 'application_status_updated',
        ]);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/organizator/schedules/{$scheduleId}")
            ->assertOk();

        $this->assertDatabaseMissing('section_schedules', [
            'id' => $scheduleId,
        ]);
    }

    public function test_organizator_can_mark_schedule_day_as_day_off(): void
    {
        $organizator = User::factory()->create([
            'role' => User::ROLE_ORGANIZATOR,
        ]);
        $section = Section::query()->create([
            'organizator_id' => $organizator->id,
            'title' => 'Teniss',
            'slug' => 'teniss',
            'status' => Section::STATUS_ACTIVE,
        ]);
        $token = $organizator->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/organizator/sections/{$section->id}/schedules", [
                'day_of_week' => 'sunday',
                'is_day_off' => true,
            ])
            ->assertOk()
            ->assertJsonPath('schedule.day_of_week', 'sunday')
            ->assertJsonPath('schedule.is_day_off', true)
            ->assertJsonPath('schedule.start_time', null)
            ->assertJsonPath('schedule.end_time', null);

        $this->assertDatabaseHas('section_schedules', [
            'section_id' => $section->id,
            'day_of_week' => 'sunday',
            'is_day_off' => 1,
        ]);
    }
}
