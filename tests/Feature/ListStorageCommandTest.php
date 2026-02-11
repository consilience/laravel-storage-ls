<?php

namespace Consilience\Laravel\Ls\Tests\Feature;

use Consilience\Laravel\Ls\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class ListStorageCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test files and directories
        Storage::disk('local')->put('test-file.txt', 'test content');
        Storage::disk('local')->put('folder/nested-file.txt', 'nested content');
        Storage::disk('local')->put('folder/subfolder/deep-file.txt', 'deep content');
        Storage::disk('local')->makeDirectory('empty-folder');
    }

    protected function tearDown(): void
    {
        // Clean up test files
        Storage::disk('local')->deleteDirectory('folder');
        Storage::disk('local')->deleteDirectory('empty-folder');
        Storage::disk('local')->delete('test-file.txt');

        parent::tearDown();
    }

    public function test_command_lists_available_disks_when_no_disk_specified()
    {
        $this->artisan('storage:ls')
            ->expectsOutput('Available disks:')
            ->assertExitCode(0);
    }

    public function test_command_fails_with_invalid_disk()
    {
        $this->artisan('storage:ls', ['--disk' => 'invalid_disk'])
            ->expectsOutput('Selected disk "invalid_disk" does not exist')
            ->assertExitCode(0);
    }

    public function test_command_lists_files_in_root_directory()
    {
        $this->artisan('storage:ls', ['--disk' => 'local'])
            ->assertExitCode(0);
    }

    public function test_command_lists_files_in_specific_directory()
    {
        $this->artisan('storage:ls', ['--disk' => 'local', 'directory' => 'folder'])
            ->assertExitCode(0);
    }

    public function test_command_supports_disk_directory_colon_syntax()
    {
        $this->artisan('storage:ls', ['directory' => 'local:folder'])
            ->assertExitCode(0);
    }

    public function test_command_lists_files_recursively()
    {
        $this->artisan('storage:ls', ['--disk' => 'local', '--recursive' => true])
            ->assertExitCode(0);
    }

    public function test_command_lists_files_in_long_format()
    {
        $this->artisan('storage:ls', ['--disk' => 'local', '--long' => true])
            ->assertExitCode(0);
    }

    public function test_command_lists_files_recursively_in_long_format()
    {
        $this->artisan('storage:ls', [
            '--disk' => 'local',
            '--recursive' => true,
            '--long' => true
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_empty_directory()
    {
        $this->artisan('storage:ls', ['--disk' => 'local', 'directory' => 'empty-folder'])
            ->assertExitCode(0);
    }

    public function test_command_with_short_options()
    {
        $this->artisan('storage:ls', ['-d' => 'local', '-l' => true, '-R' => true])
            ->assertExitCode(0);
    }

    public function test_command_returns_error_when_no_disks_configured()
    {
        // Temporarily clear filesystems config
        config()->set('filesystems.disks', null);

        $this->artisan('storage:ls')
            ->expectsOutput('No disks defined on this system')
            ->assertExitCode(1);
    }
}
