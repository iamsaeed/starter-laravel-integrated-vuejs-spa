<?php

namespace Tests\Feature\Resources;

use App\Models\Country;
use App\Models\Timezone;

class CountryResourceTest extends ResourceTestCase
{
    protected string $resourceKey = 'countries';

    public function test_can_list_resources(): void
    {
        Country::factory()->count(10)->create();

        $response = $this->getJson($this->resourcePath, $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'region'],
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ])
            ->assertJsonPath('total', 10);
    }

    public function test_can_create_resource(): void
    {
        $data = [
            'code' => 'US',
            'code_alpha3' => 'USA',
            'numeric_code' => '840',
            'name' => 'United States',
            'region' => 'Americas',
            'currency_code' => 'USD',
            'phone_code' => '+1',
            'flag_emoji' => '🇺🇸',
            'is_active' => true,
            'display_order' => 1,
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Country created successfully')
            ->assertJsonPath('data.code', 'US')
            ->assertJsonPath('data.name', 'United States');

        $this->assertDatabaseHas('countries', [
            'code' => 'US',
            'name' => 'United States',
        ]);
    }

    public function test_can_create_country_with_timezones(): void
    {
        $timezone1 = Timezone::factory()->create();
        $timezone2 = Timezone::factory()->create();

        $data = [
            'code' => 'CA',
            'code_alpha3' => 'CAN',
            'numeric_code' => '124',
            'name' => 'Canada',
            'region' => 'Americas',
            'is_active' => true,
            'timezones' => [$timezone1->id, $timezone2->id],
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(201);

        $country = Country::where('code', 'CA')->first();
        $this->assertCount(2, $country->timezones);
    }

    public function test_can_show_resource(): void
    {
        $country = Country::factory()->create(['name' => 'Germany']);

        $response = $this->getJson("{$this->resourcePath}/{$country->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.id', $country->id)
            ->assertJsonPath('data.name', 'Germany');
    }

    public function test_can_update_resource(): void
    {
        $country = Country::factory()->create(['name' => 'Old Name']);

        $data = [
            'code' => $country->code,
            'code_alpha3' => $country->code_alpha3,
            'numeric_code' => $country->numeric_code,
            'name' => 'Updated Country',
            'region' => 'Europe',
            'is_active' => true,
        ];

        $response = $this->putJson("{$this->resourcePath}/{$country->id}", $data, $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Country updated successfully')
            ->assertJsonPath('data.name', 'Updated Country');

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'name' => 'Updated Country',
        ]);
    }

    public function test_can_delete_resource(): void
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson("{$this->resourcePath}/{$country->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'Country deleted successfully');

        $this->assertDatabaseMissing('countries', [
            'id' => $country->id,
        ]);
    }

    public function test_validation_fails_on_create(): void
    {
        $response = $this->postJson($this->resourcePath, [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);
    }

    public function test_validation_fails_for_duplicate_code(): void
    {
        $existingCountry = Country::factory()->create(['code' => 'US']);

        $data = [
            'code' => 'US',
            'name' => 'Another Country',
            'region' => 'Americas',
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_can_search_resources(): void
    {
        Country::factory()->create(['name' => 'United States', 'code' => 'US']);
        Country::factory()->create(['name' => 'United Kingdom', 'code' => 'GB']);
        Country::factory()->create(['name' => 'Germany', 'code' => 'DE']);

        $response = $this->getJson("{$this->resourcePath}?search=United", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_by_code(): void
    {
        Country::factory()->create(['name' => 'United States', 'code' => 'US']);
        Country::factory()->create(['name' => 'Germany', 'code' => 'DE']);

        $response = $this->getJson("{$this->resourcePath}?search=US", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'US');
    }

    public function test_can_sort_resources(): void
    {
        Country::factory()->create(['name' => 'Zambia', 'code' => 'ZM']);
        Country::factory()->create(['name' => 'Austria', 'code' => 'AT']);

        $response = $this->getJson("{$this->resourcePath}?sort=name&direction=asc", $this->authHeaders());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Austria', $data[0]['name']);
    }

    public function test_can_filter_by_region(): void
    {
        Country::factory()->create(['region' => 'Europe']);
        Country::factory()->create(['region' => 'Asia']);
        Country::factory()->create(['region' => 'Europe']);

        $response = $this->getJson(
            "{$this->resourcePath}?filters[region]=Europe",
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_active_status(): void
    {
        Country::factory()->create(['is_active' => true]);
        Country::factory()->create(['is_active' => false]);
        Country::factory()->create(['is_active' => true]);

        $response = $this->getJson(
            "{$this->resourcePath}?filters[is_active]=1",
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_bulk_delete_resources(): void
    {
        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();
        $country3 = Country::factory()->create();

        $response = $this->postJson(
            "{$this->resourcePath}/bulk/delete",
            ['ids' => [$country1->id, $country2->id]],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('affected', 2);

        $this->assertDatabaseMissing('countries', ['id' => $country1->id]);
        $this->assertDatabaseMissing('countries', ['id' => $country2->id]);
        $this->assertDatabaseHas('countries', ['id' => $country3->id]);
    }

    public function test_can_bulk_update_resources(): void
    {
        $country1 = Country::factory()->create(['is_active' => true]);
        $country2 = Country::factory()->create(['is_active' => true]);

        $response = $this->postJson(
            "{$this->resourcePath}/bulk/update",
            [
                'ids' => [$country1->id, $country2->id],
                'data' => ['is_active' => false],
            ],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('affected', 2);

        $this->assertDatabaseHas('countries', ['id' => $country1->id, 'is_active' => false]);
        $this->assertDatabaseHas('countries', ['id' => $country2->id, 'is_active' => false]);
    }

    public function test_eager_loads_timezones_relationship(): void
    {
        $country = Country::factory()->create();
        $timezone = Timezone::factory()->create();
        $country->timezones()->attach($timezone->id);

        $response = $this->getJson("{$this->resourcePath}/{$country->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'timezones' => [
                        '*' => ['id', 'display'],
                    ],
                ],
            ]);
    }
}
