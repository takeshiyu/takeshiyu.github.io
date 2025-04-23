---
title: "From Schema Dumps to Migrations: Laravel Package Testing"
date: 2025-04-23
author: Takeshi Yu
gravatar: 3075c9103f3dddee28d255b45df7bfc4c58459e182f8c600a07930856e97dc39
twitter: '@takeshi_hey'
---

In enterprise application development, code modularity and testability are crucial. When I began refactoring a large, complex legacy system into modular packages based on PHP 8.4, I assumed setting up the testing environment would be a simple step. However, this seemingly mundane task consumed an entire day and taught me valuable lessons about testing Laravel packages.

---

My starting point was straightforward:

Refactor an overly complex legacy system into modular packages based on PHP 8.4
Gradually migrate functionality into independent packages for better maintainability
Add comprehensive tests for each component to ensure system stability
Make these modules reusable across existing and future projects

Since I needed compatibility with the existing system, I needed to use the exact same database structure in the testing environment as in production. The most intuitive approach was to use Laravel's schema dump feature to export the existing database structure and then rebuild it in the testing environment.

## First Attempt: Schema Dump Loading Challenges

As a Laravel developer, I naturally chose [Orchestra Testbench](https://packages.tools/getting-started.html) as my testing tool. It can simulate a Laravel application environment, making it perfect for package development.
My initial plan was direct:

* Use Laravel's php artisan `schema:dump` to export the database structure
* Save the `mysql-schema.sql` file in the `database/schema` directory
* Load this schema file when starting tests

But I quickly discovered that things weren't as simple as I had imagined.

## The Environment Variables Puzzle

The first issue appeared when reading environment variables:

```php
// In TestCase.php
protected function defineEnvironment($app): void
{
    tap($app['config'], function (Repository $config) {
        $config->set('database.default', 'testing');
        $config->set('database.connections.testing', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'testbench'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),  // Couldn't read password from .env!
            'prefix' => '',
        ]);
    });
}
```
I was confused when the `env()` function couldn't read the database credentials I had set in my .env file. Later I found out this is actually expected behavior for Laravel package development tools. According to the official documentation:

> ### env limitation
>
> The env environment variables are only applied when using the CLI and will not be used when running tests.

This explanation from the [Laravel Package Development official documentation](https://packages.tools/getting-started/configuration.html#environment-variables) clearly explains why my environment variables weren't working — they simply aren't applied when running tests!
This meant Orchestra Testbench doesn't automatically load the `.env` file from the package root directory, and I needed to find another solution.
After trying several approaches, I eventually used Dotenv to load it directly:

```php
protected function setUp(): void
{
    \Dotenv\Dotenv::createImmutable(__DIR__.'/../')->load();

    parent::setUp();

    // Other initialization...
}
```

## The Schema Loading Dilemma

After solving the environment variables issue, I encountered a more challenging problem: loading the schema file in the TestCase. I naively thought this should be as simple as in a standard Laravel application. However, that wasn't the case.
I tried loading the schema directly in the setUp method:

```php
protected function loadDBSchema()
{
    $schemaPath = __DIR__.'/../database/schema/mysql-schema.sql';

    if (file_exists($schemaPath)) {
        \Illuminate\Support\Facades\DB::unprepared(file_get_contents($schemaPath));
    }
}
```

But this led to a confusing error:

```shell
RuntimeException: A facade root has not been set.
```

This indicated that the DB Facade wasn't properly initialized when I tried to use it.
More challenging still, when I solved the Facade issue, I encountered foreign key constraint problems:

```shell
QueryException: SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table 'tenants'
```

This happened because the table creation order in the schema file didn't match the foreign key constraints.

## The RefreshDatabase Revelation

After multiple attempts, I began to suspect that the RefreshDatabase trait might be conflicting with my schema loading method. Indeed, this was the core of the problem.

```php
// pest.php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('*');
```

The `RefreshDatabase` trait resets the database structure during testing, which means:

1. Even if I successfully loaded the schema, `RefreshDatabase` might reset it afterward
2. Or, `RefreshDatabase` might initialize the database before my schema was loaded

## The Final Solution: Using Migrations Instead of Schema Dumps

Orchestra Testbench actually provides a better way to handle this situation — using standard Laravel migrations instead of directly executing schema dump files.

The key was using Testbench's `defineDatabaseMigrations` method and the `workbench_path` helper function:

```php
/**
 * Define database migrations.
 *
 * @return void
 */
protected function defineDatabaseMigrations()
{
    $this->loadMigrationsFrom(
        workbench_path('database/migrations')
    );
}
```

This method allows me to separate test-specific migrations from the package's own migrations, placing them in the `workbench/database/migrations` directory.
Specifically, I created a migration file named `0000_00_00_000000_import_schema.php` to ensure it would run first:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schemaPath = __DIR__.'/../schema/mysql-schema.sql';

        if (file_exists($schemaPath)) {
            DB::unprepared(file_get_contents($schemaPath));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
```
This migration file is simple yet effective: it reads and executes the schema dump file in the `up()` method, without needing to do anything in the `down()` method.

My solution included:

* Creating migration files in the `workbench/database/migrations` directory to rebuild the same database structure as production
* Using the `defineDatabaseMigrations` method in `TestCase.php` to load these migrations
* Setting environment variables in `phpunit.xml.dist`, and copying it to `phpunit.xml` for local development or CI/CD environments

```xml
<!-- phpunit.xml.dist -->
<php>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_HOST" value="127.0.0.1"/>
    <env name="DB_PORT" value="3306"/>
    <env name="DB_DATABASE" value="testbench"/>
    <env name="DB_USERNAME" value="root"/>
    <env name="DB_PASSWORD" value=""/>
</php>
```

The advantages of this approach are:

* Fully following Laravel's migration system, avoiding the difficulties of directly executing SQL
* Seamless integration with the `RefreshDatabase` trait
* Separation of test-specific migrations from the package's own migrations, maintaining a clear structure
* Environment variables set through `phpunit.xml`, avoiding hardcoded sensitive information

## Lessons Learned and Best Practices

This day, though frustrating, gave me a deep understanding of Laravel package testing best practices:

1. **Use Migrations Instead of Schema Dumps:** Although using schema dumps directly might seem more straightforward, using standard Laravel migrations avoids many hidden issues.
2. **Properly Separate Test Environments:** Use workbench_path to separate test-specific migrations from the package's own functionality.
3. **Handle Environment Variables Correctly:** Set environment variables through phpunit.xml, rather than relying on .env files.
4. **Understand Testbench's Lifecycle:** Orchestra Testbench has subtle but crucial differences from standard Laravel applications.
    - **Bootstrap Process Differences:** Testbench creates a streamlined version of a Laravel application that doesn't execute all the startup processes of a standard Laravel application.
    - **Facade Initialization Timing:** In Testbench, Facade initialization occurs at specific times. Using them too early (like in the setUp method) can lead to "A facade root has not been set" errors.
    - **RefreshDatabase Execution Order:** This trait resets the database before each test method executes, but the actual database reset happens before the first test (initial migration) and before each subsequent test (rolling back to the initial state and re-running migrations).
    - **Environment Variable Handling:** As mentioned earlier, the env() function behaves differently in tests compared to normal Laravel applications.
    - **Service Provider Loading Timing:** The registration and booting of your package service providers also differs from standard Laravel applications and must be explicitly specified through the getPackageProviders method.

Understanding these differences can help you avoid many difficult-to-diagnose issues and design more stable and reliable tests.





