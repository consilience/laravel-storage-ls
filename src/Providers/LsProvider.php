<?php

namespace Consilience\Laravel\Ls\Providers;

/**
 *
 */

use Consilience\Laravel\Ls\Console\Commands\ListStorage;
use Illuminate\Support\ServiceProvider;

class LsProvider extends ServiceProvider
{
    public function register(){
        $this->commands(ListStorage::class);
    }
}
