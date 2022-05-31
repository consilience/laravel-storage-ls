<?php

namespace Consilience\Laravel\Ls\Console\Commands;

/**
 * This command uses the Laravel wrapper to scan and inspect
 * files and directories, so should work even when the file
 * system driver is not Flysystem. What we lose is the ability
 * to get sizes and modified times for directories. That info
 * is there for Flysystem drivers, but is discarded by Laravel
 * when it fetches its file and directory listings. Then
 * Flysystem does not have an API for fetching metadata for
 * directories; it only has this for files.
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class ListStorage extends Command
{
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

    protected $description = 'List the contents of a filesystem disk';

    public function handle()
    {
        // Collect input.

        $disks = config('filesystems.disks');

        if ($disks === null) {
            $this->error('No disks defined on this system');
            return 1;
        }

        $selectedDir = $this->argument('directory') ?? '/';

        $selectedDisk = $this->option('disk') ?? '';
        $defaultDisk = config('filesystems.default');

        $recursive = $this->option('recursive');
        $longFormat = $this->option('long');

        // Parse and validate input.

        if ($selectedDisk === '' && strpos($selectedDir, ':') !== false) {
            // User may be using the "disk:directory" format.

            [$diskSplit, $dirSplit] = explode(':', $selectedDir);

            if (array_key_exists($diskSplit, $disks)) {
                $selectedDisk = $diskSplit;
                $selectedDir = $dirSplit;
            }
        }

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

        // Do the listing.

        $this->listDirectory(Storage::disk($selectedDisk), $selectedDir, $recursive, $longFormat);
    }

    /**
     * List the contents of one directory and recurse if necessary.
     *
     * @param Filesystem $disk the name of the laravel filessystem disk
     * @param string $directory the path from the root of the disk, leading "/" optional
     * @param bool $recursive true to recurse into sub-directories
     * @param bool $longFormat true to output long format, with sizes and timestamps
     *
     * @return void
     */
    protected function listDirectory(
        Filesystem $disk,
        string $directory,
        bool $recursive,
        bool $longFormat
    ) {
        // If we are recursing into subdirectories, then display the directory
        // before listing the contents.
        // Precede with a blank line after the first directory.

        if ($recursive) {
            if ($this->dirIndex) {
                $this->line('');
            }

            $this->dirIndex++;

            $this->line(sprintf('%s:', $directory));
        }

        // To collect directories as we go through.

        $directories = $disk->directories($directory);
        $files = $disk->files($directory);

        $dt = new DateTimeImmutable();

        foreach($directories as $path) {
            $basename = basename($path);

            [
                'size' => $size,
                'lastModifiedFormatted' => $lastModifiedFormatted,
            ] = $this->metadata($disk, $path);

            if ($longFormat) {
                $this->warn(sprintf(
                    'd %10d %s %s',
                    $size,
                    $lastModifiedFormatted,
                    $basename
                ));
            } else {
                $message = sprintf('%s', $basename);

                $this->warn($message); // Orange for a directory.
            }
        }

        foreach ($files as $path) {
            $basename = basename($path);

            [
                'size' => $size,
                'lastModifiedFormatted' => $lastModifiedFormatted,
            ] = $this->metadata($disk, $path);

            if ($longFormat) {
                $this->info(sprintf(
                    '- %10d %s %s',
                    $size,
                    $lastModifiedFormatted,
                    $basename
                ));
            } else {
                $message = sprintf('%s', $basename);

                $this->info($message); // Green for a file.
            }
        }

        // If recursing, go through the sub-directories collected.

        if ($recursive && $directories) {
            foreach ($directories as $directory) {
                $this->listDirectory($disk, $directory, $recursive, $longFormat);
            }
        }
    }

    /**
     * Fetch the metadata for a file or directory.
     *
     * @param FileSystem $disk
     * @param string $path
     * @return array
     */
    protected function metadata(FileSystem $disk, string $path): array
    {
        try {
            $size = $disk->size($path);

        } catch (Throwable) {
            // Laravel does not support fetching the size of
            // a directory at this time. Most Flysystem drivers
            // DO support it, but only when using listContents().

            $size = 0;
        }

        try {
            $lastModified = $disk->lastModified($path);

        } catch (Throwable) {
            // Laravel does not support fetching the timestamp of
            // a directory at this time. Most Flysystem drivers
            // DO support it, but only when using listContents().

            $lastModified = null;
        }
    
        if ($lastModified !== null) {
            $lastModifiedFormatted = (new DateTimeImmutable())
                ->setTimezone(new DateTimeZone('UTC'))
                ->setTimestamp($lastModified)
                ->format('Y-m-d H:i:s');
        } else {
            // Length of 'YYYY-MM-DD HH:MM:SS'.

            $lastModifiedFormatted = '                   ';
        }

        return [
            'size' => $size, // bytes
            'lastModified' => $lastModified, // Unix timestamp|null
            'lastModifiedFormatted' => $lastModifiedFormatted, // string
        ];
    }
}
