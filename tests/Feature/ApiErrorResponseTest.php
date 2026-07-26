<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiErrorResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_uses_the_standard_envelope(): void
    {
        $response = $this->postJson('/api/auth/register-client', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonPath('success', false);
    }

    public function test_unauthenticated_error_uses_the_standard_envelope(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonPath('success', false);
    }

    public function test_unknown_route_uses_the_standard_envelope(): void
    {
        $response = $this->getJson('/api/this-route-does-not-exist');

        $response->assertStatus(404)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJsonPath('success', false);
    }
}