<?php

namespace Consilience\Laravel\Ls\Console\Commands;

/**
 *
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use DateTimeInterface;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class ListStorage extends Command
{
    /**
     * flyssytem object types, because flysystem does not have its own
     * constants for these.
     */
    const TYPE_DIR = 'dir';
    const TYPE_FILE = 'file';

    /**
     * Counts up the number of objects (files or directories) fetched.
     * Used for output formatting.
     */
    protected $dirIndex = 0;

    protected $signature = 'storage:ls
        {directory? : list a given directory}
        {--d|disk= : select the filesystem disk}
        {--l|long : long format}
        {--R|recursive : list subdirectories recursively}';

    protected $description = 'List the contents of a file storage disk';

    public function handle()
    {
        $disks = config('filesystems.disks');

        if ($disks === null) {
            $this->error('No disks defined on this system');
            return 1;
        }

        $selectedDisk = $this->option('disk') ?? '';
        $defaultDisk = config('filesystems.default');

        if ($selectedDisk !== '' && ! array_key_exists($selectedDisk, $disks)) {
            $this->error(sprintf('Selected disk "%s" does not exist', $selectedDisk));
            $selectedDisk = '';
        }

        if ($selectedDisk === '') {
            $this->info('Available disks:');

            $this->table(
                ['name', 'driver'],
                collect($disks)->map(function ($disk, $name) use ($defaultDisk) {
                    return [
                        $name . ($defaultDisk === $name ? ' [*]' : ''),
                        $disk['driver'] ?? 'unknown',
                    ];
                })
            );
            return;
        }

        $selectedDir = $this->argument('directory') ?? '/';
        $recursive = $this->option('recursive');
        $longFormat = $this->option('long');

        $this->listDirectory($selectedDisk, $selectedDir, $recursive, $longFormat);
    }

    /**
     * List the contents of one directory and recurse if necessary.
     *
     * @param string $disk the name of the laravel filessystem disk
     * @param string $directory the path from the root of the disk, leading "/" optional
     * @param bool $recursive true to recurse into sub-directories
     * @param bool $longFormat true to output long format, with sizes and timestamps
     *
     * @return void
     */
    protected function listDirectory(
        string $disk,
        string $directory,
        bool $recursive,
        bool $longFormat
    ) {
        $content = Storage::disk($disk)->listContents($directory);

        // If we are recursing into subdirectories, then display the directory
        // before listing the contents.
        // Precede with a blank line after the first directory.

        if ($recursive) {
            if ($this->dirIndex) {
                $this->line('');
            }

            $this->dirIndex++;

            $this->line($directory . ':');
        }

        // To collect directories as we go through.

        $subDirs = [];

        $dt = new DateTimeImmutable();

        foreach ($content as $item) {
            $basename = $item['basename'] ?? 'unknown';
            $dirname = $item['dirname'] ?? '/';

            $pathname = $dirname . '/' . $basename;

            $size = $item['size'] ?? 0;

            $type = $item['type'] ?? static::TYPE_FILE;

            // Some drivers do not supply the file size by default,
            // so make another call to get it.

            if ($size === 0 && $type === static::TYPE_FILE && $longFormat) {
                try {
                    $size = Storage::disk($disk)->getSize($pathname);
                } catch (Throwable $e) {
                    // Some drivers throw exceptions in some circumstances.
                    // We just catch and ignore.
                }
            }

            // Format the timestamp if present.
            // Just going down the seconds for now, and UTC is implied.

            $timestamp = $item['timestamp'] ?? null;

            if ($timestamp !== null) {
                $datetime = $dt
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->setTimestamp($timestamp)
                    ->format('Y-m-d H:i:s');
            } else {
                $datetime = '';
            }

            // Two output formats at present: long and not long.

            if ($longFormat) {
                $this->line(sprintf(
                    '%1s %10d %s %s',
                    $type === static::TYPE_DIR ? 'd' : '-',
                    $size,
                    $datetime,
                    $basename
                ));
            } else {
                $message = sprintf('%s', $basename);

                if ($type === static::TYPE_FILE) {
                    $this->info($message);
                } else {
                    $this->warn($message);
                }
            }

            // Collect the list of sub-directories as we go through.

            if ($recursive && $type === static::TYPE_DIR) {
                $subDirs[] = $pathname;
            }
        }

        // If recursing, go through the sub-directories collected.

        if ($recursive && $subDirs) {
            foreach ($subDirs as $subDir) {
                $this->listDirectory($disk, $subDir, $recursive, $longFormat);
            }
        }
    }
}
