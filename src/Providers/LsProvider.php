<?php

namespace Consilience\Laravel\Ls\Providers;

/**
 * Register the storage:ls command with the framework.
 */

use Consilience\Laravel\Ls\Console\Commands\ListStorage;
use Illuminate\Support\ServiceProvider;

class LsProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands(ListStorage::class);
    }
}
