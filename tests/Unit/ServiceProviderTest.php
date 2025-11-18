<?php

namespace Consilience\Laravel\Ls\Tests\Unit;

use Consilience\Laravel\Ls\Console\Commands\ListStorage;
use Consilience\Laravel\Ls\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_command()
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('storage:ls', $commands);
        $this->assertInstanceOf(ListStorage::class, $commands['storage:ls']);
    }

    public function test_command_has_correct_signature()
    {
        $command = Artisan::all()['storage:ls'];

        $this->assertEquals('storage:ls', $command->getName());
        $this->assertEquals('List the contents of a filesystem disk', $command->getDescription());
    }
}
