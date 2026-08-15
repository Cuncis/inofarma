<?php

namespace Tests\Concerns;

/**
 * Puts a session admin in place so guarded admin routes are reachable.
 *
 * The prototype login accepts any credentials, so this posts real ones rather
 * than writing the session key directly — that way the tests exercise the same
 * path a person does, and they will keep working when a real guard replaces it.
 */
trait SignsInAsAdmin
{
    protected function signInAsAdmin(string $email = 'admin@inofarma.co.id'): static
    {
        $this->post('/admin/masuk', ['email' => $email, 'password' => 'apa saja']);

        return $this;
    }
}
