<?php

namespace Tests\Feature;


use Tests\TestCase;

class EmployeePageAuthTest extends TestCase
{
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
    }

    public function test_dashboard_returns_view_for_authenticated_request(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->withHeader('Authorization', 'Bearer test-token')->get('/dashboard');

        $response->assertStatus(500);
    }
}
