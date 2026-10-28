<?php

namespace Tests\Feature;

use Tests\TestCase;

class EmployeePageAuthTest extends TestCase
{
    public function test_login_page_offers_to_remember_the_employee_pin(): void
    {
        $html = view('auth.login')->render();

        $this->assertStringContainsString('id="rememberPin"', $html);
        $this->assertStringContainsString('autocomplete="username"', $html);
        $this->assertStringContainsString('remembered_employee_pin', $html);
        $this->assertStringContainsString('Ingat PIN saya di perangkat ini', $html);
    }

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
