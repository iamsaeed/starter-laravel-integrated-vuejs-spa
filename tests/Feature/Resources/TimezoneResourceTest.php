<?php

namespace Tests\Feature\Resources;

use App\Models\Country;
use App\Models\Timezone;

class TimezoneResourceTest extends ResourceTestCase
{
    protected string $resourceKey = 'timezones';

    public function test_can_list_resources(): void
    {
        Timezone::factory()->count(15)->create();

        $response = $this->getJson($this->resourcePath, $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'display_name', 'offset_formatted'],
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ])
            ->assertJsonPath('total', 15);
    }

    public function test_can_create_resource(): void
    {
        $data = [
            'name' => 'America/New_York',
            'display_name' => 'Eastern Time',
            'abbreviation' => 'EST',
            'abbreviation_dst' => 'EDT',
            'offset' => -18000,
            'offset_formatted' => 'UTC-05:00',
            'uses_dst' => true,
            'region' => 'America',
            'is_active' => true,
            'is_primary' => false,
            'display_order' => 1,
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Timezone created successfully')
            ->assertJsonPath('data.name', 'America/New_York')
            ->assertJsonPath('data.display_name', 'Eastern Time');

        $this->assertDatabaseHas('timezones', [
            'name' => 'America/New_York',
            'display_name' => 'Eastern Time',
        ]);
    }

    public function test_can_create_timezone_with_countries(): void
    {
        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();

        $data = [
            'name' => 'Europe/London',
            'display_name' => 'GMT',
            'offset' => 0,
            'offset_formatted' => 'UTC+00:00',
            'region' => 'Europe',
            'is_active' => true,
            'countries' => [$country1->id, $country2->id],
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(201);

        $timezone = Timezone::where('name', 'Europe/London')->first();
        $this->assertCount(2, $timezone->countries);
    }

    public function test_can_show_resource(): void
    {
        $timezone = Timezone::factory()->create(['display_name' => 'Pacific Time']);

        $response = $this->getJson("{$this->resourcePath}/{$timezone->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.id', $timezone->id)
            ->assertJsonPath('data.display_name', 'Pacific Time');
    }

    public function test_can_update_resource(): void
    {
        $timezone = Timezone::factory()->create(['display_name' => 'Old Name']);

        $data = [
            'name' => $timezone->name,
            'display_name' => 'Updated Timezone',
            'is_active' => true,
        ];

        $response = $this->putJson("{$this->resourcePath}/{$timezone->id}", $data, $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Timezone updated successfully')
            ->assertJsonPath('data.display_name', 'Updated Timezone');

        $this->assertDatabaseHas('timezones', [
            'id' => $timezone->id,
            'display_name' => 'Updated Timezone',
        ]);
    }

    public function test_can_delete_resource(): void
    {
        $timezone = Timezone::factory()->create();

        $response = $this->deleteJson("{$this->resourcePath}/{$timezone->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Timezone deleted successfully');

        $this->assertDatabaseMissing('timezones', [
            'id' => $timezone->id,
        ]);
    }

    public function test_validation_fails_on_create(): void
    {
        $response = $this->postJson($this->resourcePath, [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_validation_fails_for_duplicate_name(): void
    {
        $existingTimezone = Timezone::factory()->create(['name' => 'America/Chicago']);

        $data = [
            'name' => 'America/Chicago',
            'display_name' => 'Central Time',
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_search_resources(): void
    {
        Timezone::factory()->create(['name' => 'America/New_York', 'display_name' => 'Eastern Time']);
        Timezone::factory()->create(['name' => 'America/Chicago', 'display_name' => 'Central Time']);
        Timezone::factory()->create(['name' => 'Europe/London', 'display_name' => 'GMT']);

        $response = $this->getJson("{$this->resourcePath}?search=America", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_by_display_name(): void
    {
        Timezone::factory()->create(['name' => 'America/New_York', 'display_name' => 'Eastern Standard Time']);
        Timezone::factory()->create(['name' => 'Europe/Paris', 'display_name' => 'Central European Time']);

        $response = $this->getJson("{$this->resourcePath}?search=Eastern", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.display_name', 'Eastern Standard Time');
    }

    public function test_can_sort_resources(): void
    {
        Timezone::factory()->create(['display_name' => 'Zulu Time']);
        Timezone::factory()->create(['display_name' => 'Alpha Time']);

        $response = $this->getJson("{$this->resourcePath}?sort=display_name&direction=asc", $this->authHeaders());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Alpha Time', $data[0]['display_name']);
    }

    public function test_can_filter_by_region(): void
    {
        Timezone::factory()->create(['region' => 'America']);
        Timezone::factory()->create(['region' => 'Europe']);
        Timezone::factory()->create(['region' => 'America']);

        $response = $this->getJson(
            "{$this->resourcePath}?filters[region]=America",
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_dst(): void
    {
        Timezone::factory()->create(['uses_dst' => true]);
        Timezone::factory()->create(['uses_dst' => false]);
        Timezone::factory()->create(['uses_dst' => true]);

        $response = $this->getJson(
            "{$this->resourcePath}?filters[uses_dst]=1",
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_active_status(): void
    {
        Timezone::factory()->create(['is_active' => true]);
        Timezone::factory()->create(['is_active' => false]);
        Timezone::factory()->create(['is_active' => true]);

        $response = $this->getJson(
            "{$this->resourcePath}?filters[is_active]=1",
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_bulk_delete_resources(): void
    {
        $timezone1 = Timezone::factory()->create();
        $timezone2 = Timezone::factory()->create();
        $timezone3 = Timezone::factory()->create();

        $response = $this->postJson(
            "{$this->resourcePath}/bulk/delete",
            ['ids' => [$timezone1->id, $timezone2->id]],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('affected', 2);

        $this->assertDatabaseMissing('timezones', ['id' => $timezone1->id]);
        $this->assertDatabaseMissing('timezones', ['id' => $timezone2->id]);
        $this->assertDatabaseHas('timezones', ['id' => $timezone3->id]);
    }

    public function test_can_bulk_update_resources(): void
    {
        $timezone1 = Timezone::factory()->create(['is_active' => true]);
        $timezone2 = Timezone::factory()->create(['is_active' => true]);

        $response = $this->postJson(
            "{$this->resourcePath}/bulk/update",
            [
                'ids' => [$timezone1->id, $timezone2->id],
                'data' => ['is_active' => false],
            ],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('affected', 2);

        $this->assertDatabaseHas('timezones', ['id' => $timezone1->id, 'is_active' => false]);
        $this->assertDatabaseHas('timezones', ['id' => $timezone2->id, 'is_active' => false]);
    }

    public function test_eager_loads_countries_relationship(): void
    {
        $timezone = Timezone::factory()->create();
        $country = Country::factory()->create();
        $timezone->countries()->attach($country->id);

        $response = $this->getJson("{$this->resourcePath}/{$timezone->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'countries' => [
                        '*' => ['id', 'display'],
                    ],
                ],
            ]);
    }
}
