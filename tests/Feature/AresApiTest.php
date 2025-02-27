<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomerController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AresApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ares_api_validation_endpoint()
    {
        // Create a user
        $user = \App\Models\User::factory()->create();
        
        // Mock the HTTP response from ARES API
        Http::fake([
            'ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/*' => Http::response([
                'ico' => '12345678',
                'obchodniJmeno' => 'Test Company s.r.o.',
                'sidlo' => [
                    'ulice' => 'Testovací',
                    'cisloDomovni' => '123',
                    'obec' => 'Praha',
                    'psc' => '12345'
                ]
            ], 200)
        ]);
        
        // Test the endpoint
        $response = $this->actingAs($user)
                         ->postJson(route('customers.validate-ico'), [
                             'ico' => '12345678'
                         ]);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'name' => 'Test Company s.r.o.',
                         'address' => 'Testovací 123',
                         'city' => 'Praha',
                         'zip_code' => '12345'
                     ]
                 ]);
    }

    public function test_ares_api_validation_with_invalid_ico()
    {
        // Create a user
        $user = \App\Models\User::factory()->create();
        
        // Mock the HTTP response from ARES API for invalid ICO
        Http::fake([
            'ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/*' => Http::response([], 404)
        ]);
        
        // Test the endpoint
        $response = $this->actingAs($user)
                         ->postJson(route('customers.validate-ico'), [
                             'ico' => '00000000'
                         ]);
        
        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false
                 ]);
    }
}