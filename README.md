# laravel-storage-ls

List the contents of Laravel file systems.

# Usage

## List storage disks

```bash
$ php artisan storage:ls
```

This will return a list of the available disks

```text
Available disks:
+-----------+--------+
| name      | driver |
+-----------+--------+
| local [*] | local  |
| public    | local  |
| s3        | s3     |
+-----------+--------+
```

## List files/directories in given disk

```bash
$ php artisan storage:ls --disk=s3
```

```bash
$ ./artisan storage:ls --disk=local
          14 2019-03-05 14:27:03 .gitignore
d          0 2019-08-21 11:19:46 public
```

## List files/directories in given disk, in given directory

```bash
$ php artisan storage:ls --disk=s3 --dir='my-folder/sub-folder'
```

# Installation

## Laravel/Lumen

Until published to Packagist, pull the development version from github by adding the
repository to `composer.json`:

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/consilience/laravel-storage-ls.git"
        }
    ],
    ...
```

```bash
composer require consilience/laravel-storage-ls
```

There is no further configuration to do on Laravel.

### Lumen

Since Lumen does not do discovery on service providers, the provider needs
to be manually registered in `bootstrap/app.php`:

```php
$app->register(Consilience\Laravel\Ls\Providers\LsProvider::class);
```
