<?php

namespace Consilience\Laravel\Ls\Console\Commands;

/**
 *
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use DateTimeInterface;

class ListStorage extends Command
{
    protected $signature = 'storage:ls
        {--disk= : select the filesystem disk}
        {--dir= : list a given directory}';

    protected $description = 'List the contents of a file storage disk';

    public function handle()
    {
        $disks = config('filesystems.disks');

        if ($disks === null) {
            $this->error('No disks defined on this system');
            return 1;
        }

        $selectedDisk = $this->option('disk', '');

        if ($selectedDisk != '' && ! array_key_exists($selectedDisk, $disks)) {
            $this->error(sprintf('Selected disk "%s" does not exist', $selectedDisk));
            $selectedDisk = '';
        }

        if ($selectedDisk === '') {
            $this->info('Available disks:');

            $this->table(
                ['name', 'driver'],
                collect($disks)->map(function ($disk, $key) {
                    return [$key, $disk['driver'] ?? 'unknown'];
                })
            );
            return;
        }

        $selectedDir = $this->option('dir', '/');

        $content = Storage::disk($selectedDisk)->listContents($selectedDir);

        foreach ($content as $item) {
            $basename = $item['basename'] ?? 'unknown';
            $size = $item['size'] ?? 0;
            $type = ($item['type'] === 'dir' ? 'd' : '');

            $timestamp = $item['timestamp'] ?? null;

            if ($timestamp !== null) {
                $datetime = date('Y-m-d H:i:s', $timestamp);
            } else {
                $datetime = '';
            }

            $this->info(sprintf(
                '%1s %10d %s %s',
                $type,
                $size,
                $datetime,
                $basename
            ));
        }
    }
}
