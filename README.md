# Laravel Storage ls

List the files of Laravel file systems (aka disks) through a simple artisan command.

Laravel abstracts filesystems, both local and remote,
using the [flysystem package](https://flysystem.thephpleague.com/docs/) and its adapters.
The abstract unit on Laravel or Lumen for a filesystem is a *disk*,
and each *disk* is configured in `config/filesystems.php`.
This package provides an artisan command (`storage:ls`) to quickly and easily peek into a *disk*
to see files and directories.

The command is read-only, so will not allow files or directories to be added,
removed or modified.
Any excpetions are left purposely unhandled, so it can aid diagnosing connection problems.
There is no interaction expected, so the command can be used in an automated pipeline,
and also in environments such as *vapor* that allow you to run artisan commands,
but offer no interactive shell.

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://poser.pugx.org/consilience/laravel-storage-ls/downloads?format=flat)](https://packagist.org/packages/consilience/laravel-storage-ls)
[![Latest Stable Version](https://poser.pugx.org/consilience/laravel-storage-ls/v/stable)](https://packagist.org/packages/consilience/laravel-storage-ls)
[![Latest Unstable Version](https://poser.pugx.org/consilience/laravel-storage-ls/v/unstable?format=flat)](https://packagist.org/packages/consilience/laravel-storage-ls)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/consilience/laravel-storage-ls/badges/quality-score.png?format=flat)](https://scrutinizer-ci.com/g/consilience/laravel-storage-ls)

# Usage

## List storage disks

```bash
$ php artisan storage:ls
```

This will return a list of the available disks with the default flagged [\*]:

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

# or

$ php artisan storage:ls -d s3
```

```bash
# Short format
$ php artisan storage:ls -d local
.gitignore
public

# Long format (directory flag, size bytes, date/time UTC, file or directory name)
$ php artisan storage:ls -d local -l
-         14 2019-03-05 14:27:03 .gitignore
d          0 2019-08-21 11:19:46 public
```

## List files and directories in given directory

```bash
$ php artisan storage:ls -d s3 my-folder/sub-folder
```

## List files and directories recursively

```bash
$ php artisan storage:ls -d local -R
/:
.gitignore
public

/public:
dirA
dirB
xyzFile

public/dirA:

public/dirB:
foobarFile
```

Similarly, in long format:

```bash
$ php artisan storage:ls -d local -Rl
/:
-         14 2019-03-05 14:27:03 .gitignore
d          0 2019-08-21 22:16:46 public

/public:
d          0 2019-08-21 22:16:43 dirB
d          0 2019-08-21 22:17:08 dirB
-          6 2019-08-21 21:54:54 xyzFile

public/dirA:

public/dirB:
-          5 2019-08-21 22:17:08 foobarFile
```

# Installation

## Laravel and Lumen

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
