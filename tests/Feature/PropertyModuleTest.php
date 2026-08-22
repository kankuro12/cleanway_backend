<?php

namespace Tests\Feature;

use App\Domain\Properties\ResolvePropertyCoordinates;
use App\Models\Branch;
use App\Models\Property;
use App\Models\PropertyAssignment;
use App\Models\PropertyCategory;
use App\Models\PropertyTag;
use App\Models\Team;
use App\Models\User;
use App\Services\Geocoding\EffectiveRadiusResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PropertyModuleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function cleaner(): User
    {
        return User::factory()->create(['role' => User::ROLE_CLEANER]);
    }

    private function createProperty(array $overrides = []): Property
    {
        return Property::create(array_merge([
            'name' => 'HQ Office',
            'address' => '1 Queen Street, Auckland',
        ], $overrides));
    }

    public function test_fast_create_with_name_and_address_only(): void
    {
        Queue::fake();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('properties.store'), [
            'name' => 'Fast Property',
            'address' => '42 Test Street, Wellington',
        ])->assertRedirect();

        $this->assertDatabaseHas('properties', [
            'name' => 'Fast Property',
            'address' => '42 Test Street, Wellington',
            'geocode_status' => Property::GEOCODE_PENDING,
        ]);

        Queue::assertPushed(\App\Jobs\GeocodeProperty::class);
    }

    public function test_validation_requires_name_and_address(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('properties.store'), ['name' => 'Only Name'])
            ->assertSessionHasErrors('address');

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_cleaner_cannot_create_property(): void
    {
        $cleaner = $this->cleaner();

        $this->actingAs($cleaner)->post(route('properties.store'), [
            'name' => 'Nope',
            'address' => 'Somewhere',
        ])->assertForbidden();

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_geocode_success_via_places_details(): void
    {
        config(['services.google_places.key' => 'test-key']);

        Http::fake([
            '*/place/details/json*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'place_id' => 'ChIJabc123',
                    'formatted_address' => '1 Queen Street, Auckland CBD, Auckland 1010, New Zealand',
                    'geometry' => [
                        'location' => ['lat' => -36.8431487, 'lng' => 174.7653813],
                        'location_type' => 'ROOFTOP',
                    ],
                ],
            ]),
        ]);

        $property = $this->createProperty(['google_place_id' => 'ChIJabc123']);

        $result = app(ResolvePropertyCoordinates::class)->execute($property);

        $this->assertTrue($result['resolved']);
        $property->refresh();

        $this->assertEquals(Property::GEOCODE_RESOLVED, $property->geocode_status);
        $this->assertEquals(-36.8431487, $property->latitude);
        $this->assertEquals(Property::SOURCE_GOOGLE_PLACES, $property->location_source);
        $this->assertDatabaseHas('property_geocode_attempts', ['property_id' => $property->id, 'status' => 'resolved']);
    }

    public function test_geocode_failure_marks_property_failed(): void
    {
        config(['services.google_places.key' => 'test-key']);

        Http::fake([
            '*/geocode/json*' => Http::response(['status' => 'ZERO_RESULTS', 'results' => []]),
        ]);

        $property = $this->createProperty();

        $result = app(ResolvePropertyCoordinates::class)->execute($property);

        $this->assertFalse($result['resolved']);
        $property->refresh();

        $this->assertEquals(Property::GEOCODE_FAILED, $property->geocode_status);
        $this->assertNull($property->latitude);
        $this->assertDatabaseHas('property_geocode_attempts', ['property_id' => $property->id, 'status' => 'failed']);
    }

    public function test_google_down_still_allows_save(): void
    {
        $admin = $this->admin();
        config(['services.google_places.key' => null]);

        $this->actingAs($admin)->post(route('properties.store'), [
            'name' => 'Offline Save',
            'address' => '99 Unknown Road',
        ])->assertRedirect();

        $this->assertDatabaseHas('properties', ['name' => 'Offline Save']);
    }

    public function test_unchanged_address_is_not_regeocoded(): void
    {
        config(['services.google_places.key' => 'test-key']);

        $property = $this->createProperty([
            'geocode_status' => Property::GEOCODE_FAILED,
            'geocoded_at' => now()->subDay(),
        ]);

        Http::fake([
            '*/geocode/json*' => Http::response(['status' => 'OK', 'results' => [
                [
                    'formatted_address' => 'Somewhere',
                    'geometry' => ['location' => ['lat' => -36.0, 'lng' => 174.0], 'location_type' => 'ROOFTOP'],
                ],
            ]]),
        ]);

        // Same name+address as the stored hash → skip, stay failed.
        $result = app(ResolvePropertyCoordinates::class)->execute($property->fresh());

        $this->assertFalse($result['resolved']);
        $this->assertEquals(Property::GEOCODE_FAILED, $property->fresh()->geocode_status);
        Http::assertNothingSent();
    }

    public function test_manual_pin_adjusts_coordinates(): void
    {
        $property = $this->createProperty();

        $result = app(ResolvePropertyCoordinates::class)->execute($property, '-36.85', '174.77');

        $this->assertTrue($result['resolved']);
        $property->refresh();

        $this->assertEquals(Property::GEOCODE_MANUALLY_ADJUSTED, $property->geocode_status);
        $this->assertEquals(Property::SOURCE_MANUAL_PIN, $property->location_source);
        $this->assertEquals(-36.85, $property->latitude);
    }

    public function test_radius_fallback_chain(): void
    {
        $resolver = app(EffectiveRadiusResolver::class);

        // 1. Property-level wins.
        $p1 = $this->createProperty(['permitted_check_in_radius_meters' => 25]);
        $this->assertEquals(25, $resolver->resolve($p1));

        // 2. Category default.
        $category = PropertyCategory::create(['name' => 'Retail', 'slug' => 'retail', 'default_check_in_radius_meters' => 80]);
        $p2 = $this->createProperty(['property_category_id' => $category->id]);
        $this->assertEquals(80, $resolver->resolve($p2));

        // 3. Org-wide.
        config(['organization.default_check_in_radius_meters' => 200]);
        $p3 = $this->createProperty();
        $this->assertEquals(200, $resolver->resolve($p3));

        // 4. System fallback.
        config(['organization.default_check_in_radius_meters' => null]);
        $p4 = $this->createProperty();
        $this->assertEquals(EffectiveRadiusResolver::SYSTEM_FALLBACK_METERS, $resolver->resolve($p4));
    }

    public function test_assignment_with_primary_flag_and_dates(): void
    {
        $admin = $this->admin();
        $property = $this->createProperty();
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $this->actingAs($admin)->post(route('property-assignments.store', $property), [
            'assignable_type' => 'user',
            'assignable_id' => $supervisor->id,
            'assignment_role' => PropertyAssignment::ROLE_SUPERVISOR,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_primary' => 1,
            'reason' => 'Coverage',
        ])->assertRedirect();

        $this->assertDatabaseHas('property_assignments', [
            'property_id' => $property->id,
            'assignable_type' => 'user',
            'assignable_id' => $supervisor->id,
            'assignment_role' => 'supervisor',
            'is_primary' => 1,
            'reason' => 'Coverage',
        ]);

        // Second primary clears the first within the same role.
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $this->actingAs($admin)->post(route('property-assignments.store', $property), [
            'assignable_type' => 'user',
            'assignable_id' => $cleaner->id,
            'assignment_role' => PropertyAssignment::ROLE_CLEANER,
            'is_primary' => 1,
        ])->assertRedirect();

        $this->assertSame(1, PropertyAssignment::where('property_id', $property->id)->where('assignment_role', 'cleaner')->where('is_primary', true)->count());
    }

    public function test_search_and_filters(): void
    {
        $active = $this->createProperty(['name' => 'Alpha Tower', 'latitude' => -36.8, 'longitude' => 174.7, 'geocode_status' => Property::GEOCODE_RESOLVED]);
        $inactive = $this->createProperty(['name' => 'Beta Site', 'active' => false, 'geocode_status' => Property::GEOCODE_FAILED]);
        $tag = PropertyTag::create(['name' => 'VIP', 'slug' => 'vip']);
        $active->tags()->attach($tag);

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('properties').'?search=Alpha')->assertSee('Alpha Tower')->assertDontSee('Beta Site');
        $this->actingAs($admin)->get(route('properties').'?tag_id='.$tag->id)->assertSee('Alpha Tower')->assertDontSee('Beta Site');
        $this->actingAs($admin)->get(route('properties').'?missing_coords=1')->assertDontSee('Alpha Tower')->assertSee('Beta Site');
        $this->actingAs($admin)->get(route('properties').'?geocode_status=failed')->assertSee('Beta Site')->assertDontSee('Alpha Tower');
    }

    public function test_tag_merge_repoints_pivots(): void
    {
        $keep = PropertyTag::create(['name' => 'Keep', 'slug' => 'keep']);
        $merge = PropertyTag::create(['name' => 'Merge', 'slug' => 'merge']);
        $p1 = $this->createProperty(['name' => 'One']);
        $p2 = $this->createProperty(['name' => 'Two']);

        $p1->tags()->attach([$keep->id, $merge->id]);
        $p2->tags()->attach($merge->id);

        $admin = $this->admin();

        $this->actingAs($admin)->post(route('property-tags.merge'), [
            'keep_tag_id' => $keep->id,
            'merge_tag_id' => $merge->id,
        ])->assertRedirect(route('property-tags'));

        $this->assertSoftDeleted('property_tags', ['id' => $merge->id]);
        $this->assertDatabaseHas('property_tag', ['property_id' => $p2->id, 'property_tag_id' => $keep->id]);
        $this->assertSame(1, $p1->tags()->count());
        $this->assertSame(1, $p2->tags()->count());
    }

    public function test_api_property_search_and_show(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('test')->plainTextToken;
        $property = $this->createProperty(['name' => 'Searchable Plaza', 'latitude' => -36.8, 'longitude' => 174.7, 'geocode_status' => Property::GEOCODE_RESOLVED]);

        $this->withToken($token)->getJson('/api/v1/properties/search?search=Plaza')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Searchable Plaza');

        $this->withToken($token)->getJson("/api/v1/properties/{$property->id}")
            ->assertOk()
            ->assertJsonPath('data.uuid', $property->uuid);
    }

    public function test_api_property_create_and_permission_denied(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/properties', [
            'name' => 'API Property',
            'property_code' => 'API-101',
            'address' => '10 Api Lane',
        ])->assertCreated()->assertJsonPath('data.name', 'API Property');

        $cleaner = $this->cleaner();
        $cleanerToken = $cleaner->createToken('test')->plainTextToken;

        // Guards cache the resolved user per test process — reset between requests.
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->withToken($cleanerToken)->postJson('/api/v1/properties', [
            'name' => 'Denied',
            'property_code' => 'API-102',
            'address' => 'X',
        ])->assertForbidden();
    }

    public function test_property_archive_soft_deletes(): void
    {
        $admin = $this->admin();
        $property = $this->createProperty();

        $this->actingAs($admin)->delete(route('properties.destroy', $property))->assertRedirect(route('properties'));

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'active' => 0]);
    }

    public function test_supervisor_can_view_property_list(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $this->createProperty();

        $this->actingAs($supervisor)->get(route('properties'))->assertOk();
    }
}
