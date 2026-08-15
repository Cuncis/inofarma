<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Stub out the `@vite` directive for every test.
     *
     * Without this the suite only passes when `public/build/manifest.json`
     * happens to exist (or a `npm run dev` server is running and has written
     * `public/hot`) — otherwise every page render throws
     * ViteManifestNotFoundException. Asset bundling is not what these tests
     * cover, so they should not depend on a build artefact.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
