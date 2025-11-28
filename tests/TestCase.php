<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = false;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the three core roles that the application requires
        \App\Models\Role::insert([
            ['name' => 'executive'],
            ['name' => 'manager'],
            ['name' => 'associate'],
        ]);
    }
}
