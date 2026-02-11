<?php

namespace Consilience\Laravel\Ls\Tests;

use Consilience\Laravel\Ls\Providers\LsProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LsProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default filesystem disks for testing
        config()->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => storage_path('app'),
        ]);

        config()->set('filesystems.disks.test_disk', [
            'driver' => 'local',
            'root' => storage_path('app/test'),
        ]);

        config()->set('filesystems.default', 'local');
    }
}
