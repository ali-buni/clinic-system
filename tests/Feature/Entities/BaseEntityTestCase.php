<?php

namespace Tests\Feature\Entities;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TestsSeeder;
use Tests\TestCase;

abstract class BaseEntityTestCase extends TestCase
{
    protected bool $useHeavySeeder = false;

    protected string $entityName = '';

    protected function runSeed(): void
    {
        if ($this->useHeavySeeder) {
            $this->seed(DatabaseSeeder::class);
        } else {
            $this->seed(TestsSeeder::class);
        }
    }
}
