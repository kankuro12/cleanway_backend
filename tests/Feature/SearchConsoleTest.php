<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_search_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::create(['name' => 'Acme Corp', 'active' => true]);
        $property = Property::create([
            'name' => 'Metro Plaza',
            'address' => '100 Queen St',
            'client_id' => $client->id,
            'active' => true,
        ]);

        $task = Task::create([
            'title' => 'Clean Reception',
            'property_id' => $property->id,
            'status' => Task::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($admin)->get(route('search'));
        $response->assertOk();

        // Search with query
        $searchResponse = $this->actingAs($admin)->get(route('search', ['q' => 'Metro']));
        $searchResponse->assertOk()
            ->assertSee('Metro Plaza');
    }
}
