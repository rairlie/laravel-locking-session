# laravel-locking-session

This package implements session locking in Laravel by wrapping an exclusive lock around the underlying session driver. As such it should work with any session backend - cookies, files, database, etc. (only files and cookies currently tested).

It addresses the problem where session data is lost due to concurrent requests updating the session at the same time. One instance where this may happen is when making simultaneous XHR requests.

##### Example scenario:
Consider the case were a variable COUNTER is a value stored in the session, and two requests attempt to increment it at the same time. Without session locking:

    Request A: Read session data: COUNTER = 1
    Request B: Read session data: COUNTER = 1
    Request A: COUNTER = COUNTER + 1
    Request B: COUNTER = COUNTER + 1
    Request A: Write session data: COUNTER = 2
    Request B: Write session data: COUNTER = 2

Final result: COUNTER = 2

With session locking in place, this becomes:

    Request A: Lock session
    Request A: Read session data: COUNTER = 1
    Request B: Attempt to lock session - already locked, must wait
    Request A: COUNTER = COUNTER + 1
    Request A: Write session data: COUNTER = 2
    Request A: Release lock
    Request B: Acquires lock
    Request B: Read session data: COUNTER = 2
    Request B: COUNTER = COUNTER + 1
    Request B: Write session data: COUNTER = 3
    Request B: Release lock

Final result: COUNTER = 3

Session locking ensures correctness at the costs of effectively serialising concurrent requests accessing the session. If you have some concurrent requests that don't use the session, disabling session middleware one those requests allows them to still be concurrent.

## Installation
    composer require rairlie/laravel-locking-session
    composer install
In your Laravel app, edit config/app.php and replace the default session handler with the locking one:

    config/app.php:
    - Illuminate\Session\SessionServiceProvider::class,
    + Rairlie\LockingSession\LockingSessionServiceProvider::class,

## Requirements
1. Write access to the system temp dir
2. POSIX file system locking e.g. *NIX, Windows (untested).
