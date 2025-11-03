# Laravel Boost Guidelines

These guidelines are specifically curated by Laravel maintainers for this application. Follow these closely to enhance development satisfaction with Laravel applications.

## Package Versions

This application uses the following Laravel ecosystem packages:

- PHP: 8.2.28
- Laravel Framework: v12
- Laravel Prompts: v0
- Laravel Sanctum: v4
- Laravel MCP: v0
- Laravel Pint: v1
- Laravel Sail: v1
- PHPUnit: v11
- Vue: v3
- Tailwind CSS: v4

## Conventions

Follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.

Use descriptive names for variables and methods. For example, use `isRegisteredForDiscounts` not `discount()`.

Always check for existing components to reuse before writing a new one.

## Verification Scripts

Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture

Stick to existing directory structure. Do not create new base folders without approval.

Do not change the application's dependencies without approval.

## Frontend Bundling

If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

Be concise in your explanations. Focus on what's important rather than explaining obvious details.

## Documentation Files

Only create documentation files if explicitly requested by the user.

## Laravel Boost Tools

Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

### Artisan Commands

Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

### URLs

Whenever you share a project URL with the user, use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

### Tinker / Debugging

Use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.

Use the `database-query` tool when you only need to read from the database.

### Browser Logs

You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.

Only recent browser logs will be useful. Ignore old logs.

### Searching Documentation (Critically Important)

Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance.

Pass an array of packages to filter on if you know you need docs for particular packages.

The search-docs tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.

You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.

Search the documentation before making code changes to ensure we are taking the correct approach.

Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.

Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

You can and should pass multiple queries at once. The most relevant results will be returned first.

1. **Simple Word Searches with auto-stemming** - query=authentication - finds 'authenticate' and 'auth'
2. **Multiple Words (AND Logic)** - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. **Quoted Phrases (Exact Position)** - query="infinite scroll" - Words must be adjacent and in that order
4. **Mixed Queries** - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. **Multiple Queries** - queries=["authentication", "middleware"] - ANY of these terms

## PHP Standards

### Control Structures

Always use curly braces for control structures, even if it has one line.

### Constructors

Use PHP 8 constructor property promotion in `__construct()`.

Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations

Always use explicit return type declarations for methods and functions.

Use appropriate PHP type hints for method parameters.

### Comments

Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something very complex going on.

### PHPDoc Blocks

Add useful array shape type definitions for arrays when appropriate.

### Enums

Enum keys should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Laravel Standards

### Commands

Use `php artisan make:` commands to create new files (migrations, controllers, models, etc.).

If you're creating a generic PHP class, use `artisan make:class`.

Pass `--no-interaction` to all Artisan commands to ensure they work without user input. Also pass the correct `--options` to ensure correct behavior.

### Database

Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.

Use Eloquent models and relationships before suggesting raw database queries.

Avoid `DB::`. Prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.

Generate code that prevents N+1 query problems by using eager loading.

Use Laravel's query builder for very complex database operations.

### Model Creation

When creating new models, create useful factories and seeders for them too.

### APIs & Eloquent Resources

For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation

Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.

Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

Use environment variables only in configuration files. Never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing

When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.

Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.

When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error

If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Laravel 12 Structure

Use the search-docs tool to get version specific documentation.

Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Structure Details

No middleware files in `app/Http/Middleware/`.

`bootstrap/app.php` is the file to register middleware, exceptions, and routing files.

`bootstrap/providers.php` contains application specific service providers.

No `app/Console/Kernel.php` - use `bootstrap/app.php` or `routes/console.php` for console configuration.

Commands auto-register - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.

Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10)`.

### Models

Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

## Laravel Pint Code Formatter

You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.

Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

## PHPUnit Testing

This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.

If you see a test using "Pest", convert it to PHPUnit.

Every time a test has been updated, run that singular test.

When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.

Tests should test all of the happy paths, failure paths, and weird paths.

You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests

Run the minimal number of tests, using an appropriate filter, before finalizing.

- To run all tests: `php artisan test`
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file)

## Tailwind CSS Standards

Use Tailwind CSS classes to style HTML. Check and use existing tailwind conventions within the project before writing your own.

Offer to extract repeated patterns into components that match the project's conventions (Blade, JSX, Vue, etc.).

Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically.

You can use the search-docs tool to get exact examples from the official documentation when needed.

### Spacing

When listing items, use gap utilities for spacing. Don't use margins.

### Dark Mode

If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

## Tailwind CSS v4

Always use Tailwind CSS v4. Do not use the deprecated utilities.

`corePlugins` is not supported in Tailwind v4.

In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3.

### Replaced Utilities

Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.

Opacity values are still numeric.

| Deprecated | Replacement |
|------------|-------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

## Test Enforcement

Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.

Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
