<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $database = (string) config('database.connections.' . config('database.default') . '.database');

        if (in_array($database, ['gps_production', 'gps_production_test'], true)) {
            $this->markTestSkipped('Tests must not run against the production database (' . $database . ').');
        }
    }
}
