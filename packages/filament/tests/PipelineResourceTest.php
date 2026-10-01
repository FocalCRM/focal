<?php

declare(strict_types=1);

namespace Focal\Filament\Tests;

use Focal\Filament\Tests\Fixtures\User;
use Focal\Sales\Models\Pipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PipelineResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_pipelines_index(): void
    {
        $user = User::factory()->create();
        Pipeline::factory()->withStages()->count(2)->create();

        $response = $this->actingAs($user)->get('/admin/pipelines');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_access_create_pipeline_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/pipelines/create');

        $response->assertSuccessful();
    }

    public function test_authenticated_user_can_access_edit_pipeline_page(): void
    {
        $user = User::factory()->create();
        $pipeline = Pipeline::factory()->withStages()->create([
            'name' => 'Strategic Accounts Pipeline',
        ]);

        $response = $this->actingAs($user)->get("/admin/pipelines/{$pipeline->id}/edit");

        $response->assertSuccessful();
        $response->assertSee('Strategic Accounts Pipeline');
    }
}
