<?php

namespace Tests\Concerns;

/**
 * Puts a real, authenticated staff session in place so guarded admin routes
 * are reachable. Posts real credentials against the `web` guard rather than
 * writing the session key directly, so tests exercise the same path a person
 * does. `admin@inofarma.co.id` / `password` is the Super Admin seeded by
 * `StaffSeeder` — call `$this->seed()` before this in `setUp()`.
 */
trait SignsInAsAdmin
{
    protected function signInAsAdmin(string $email = 'admin@inofarma.co.id', string $password = 'password'): static
    {
        $this->post('/admin/masuk', ['email' => $email, 'password' => $password]);

        return $this;
    }
}
