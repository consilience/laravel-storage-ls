# laravel-storage-ls
List the contents of Laravel file systems.

# Usage
## List storage disks
```bash
$ php artisan storage:ls
```

## List files/directories in given disk
```bash
$ php artisan storage:ls --disk=s3
```

## List files/directories in given disk, in given directory
```bash
$ php artisan storage:ls --disk=s3 --dir='New folder'
```
