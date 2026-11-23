<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_public_registration_is_disabled(): void
    {
        // Contas só podem ser criadas por um administrador.
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }
}
