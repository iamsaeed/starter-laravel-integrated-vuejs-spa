@extends('layouts.guest')

@section('title', 'Resource CRUD System Documentation - ' . config('app.name'))
@section('description', 'Complete documentation for the Resource CRUD system - build powerful admin interfaces with minimal code.')

@push('styles')
<style>
    .code-block-wrapper {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .code-block-wrapper pre {
        margin: 0;
        background: #1a1a2e !important;
        font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.875rem;
        line-height: 1.6;
    }

    /* Dark mode: lighter background for better contrast */
    .dark .code-block-wrapper pre {
        background: #1e293b !important;
        border: 1px solid #334155;
    }

    .copy-button {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        padding: 0.5rem 0.75rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 0.375rem;
        color: #9ca3af;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        z-index: 10;
        font-family: system-ui, -apple-system, sans-serif;
    }

    .copy-button:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .copy-button.copied {
        background: rgba(16, 185, 129, 0.2);
        border-color: rgba(16, 185, 129, 0.4);
        color: #10b981;
    }

    .copy-icon {
        width: 0.875rem;
        height: 0.875rem;
    }

    /* Ensure code text is visible in both modes */
    .code-block-wrapper code {
        color: #e2e8f0 !important;
        font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.875rem;
        line-height: 1.6;
    }

    .dark .code-block-wrapper code {
        color: #e2e8f0 !important;
    }

    /* Inline code styling */
    code {
        font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.875em;
    }
</style>
@endpush

@section('content')
<div class="pt-24 pb-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- Sidebar Navigation -->
            <aside class="hidden lg:block w-64 flex-shrink-0">
                <nav class="sticky top-24 space-y-1 max-h-[calc(100vh-120px)] overflow-y-auto">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Documentation</h3>

                    <div class="space-y-1">
                        <a href="#overview" class="block py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Overview</a>
                        <a href="#getting-started" class="block py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Getting Started</a>
                        <a href="#creating-resource" class="block py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Creating a Resource</a>

                        <div class="mt-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Field Types</h4>
                            <div class="pl-3 space-y-1">
                                <a href="#field-common" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Common Methods</a>
                                <a href="#field-text" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Text</a>
                                <a href="#field-email" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Email</a>
                                <a href="#field-password" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Password</a>
                                <a href="#field-select" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Select</a>
                                <a href="#field-boolean" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Boolean</a>
                                <a href="#field-belongsto" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">BelongsTo</a>
                                <a href="#field-belongstomany" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">BelongsToMany</a>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Filters</h4>
                            <div class="pl-3 space-y-1">
                                <a href="#filters" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Filter Types</a>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Actions</h4>
                            <div class="pl-3 space-y-1">
                                <a href="#actions" class="block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Bulk Actions</a>
                            </div>
                        </div>

                        <a href="#api-endpoints" class="block py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors mt-4">API Endpoints</a>
                        <a href="#examples" class="block py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Examples</a>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 min-w-0">
                <article class="prose prose-gray dark:prose-invert max-w-none">

                    <h1 id="overview" class="text-4xl font-bold text-gray-900 dark:text-white mb-6">Resource CRUD System</h1>

                    <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
                        Build powerful, declarative admin interfaces with minimal code. Define your resource structure once, and automatically get complete REST APIs, filtering, searching, sorting, and relationship management.
                    </p>

                    <!-- Feature Grid -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-12 not-prose">
                        <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800">
                            <div class="flex items-center space-x-2 text-primary-600 dark:text-primary-400 mb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="font-semibold text-gray-900 dark:text-white">Complete REST API</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">All CRUD endpoints auto-generated</p>
                        </div>

                        <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800">
                            <div class="flex items-center space-x-2 text-primary-600 dark:text-primary-400 mb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="font-semibold text-gray-900 dark:text-white">Rich Field Types</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Text, Select, BelongsTo, and more</p>
                        </div>

                        <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800">
                            <div class="flex items-center space-x-2 text-primary-600 dark:text-primary-400 mb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="font-semibold text-gray-900 dark:text-white">Advanced Filtering</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Multiple filter types included</p>
                        </div>

                        <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800">
                            <div class="flex items-center space-x-2 text-primary-600 dark:text-primary-400 mb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="font-semibold text-gray-900 dark:text-white">Bulk Operations</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Delete and update multiple records</p>
                        </div>

                        <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800">
                            <div class="flex items-center space-x-2 text-primary-600 dark:text-primary-400 mb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="font-semibold text-gray-900 dark:text-white">Relationships</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Automatic eager loading & syncing</p>
                        </div>

                        <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-100 dark:border-primary-800">
                            <div class="flex items-center space-x-2 text-primary-600 dark:text-primary-400 mb-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="font-semibold text-gray-900 dark:text-white">Toggle Switches</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Quick boolean/enum updates via PATCH</p>
                        </div>
                    </div>

                    <h2 id="getting-started" class="text-3xl font-bold text-gray-900 dark:text-white mt-12 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Getting Started</h2>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">1. Create a Resource Class</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Resources live in <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono text-primary-600 dark:text-primary-400">app/Resources/</code> and extend the base <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono text-primary-600 dark:text-primary-400">Resource</code> class:</p>

                    <div class="code-block-wrapper">
                        <button class="copy-button" onclick="copyCode(this)">
                            <svg class="copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="copy-text">Copy</span>
                        </button>
                        <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto"><code>php artisan make:class Resources/PostResource</code></pre>
                    </div>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">2. Define Resource Properties</h3>

                    <div class="code-block-wrapper">
                        <button class="copy-button" onclick="copyCode(this)">
                            <svg class="copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="copy-text">Copy</span>
                        </button>
                        <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto"><code>&lt;?php

namespace App\Resources;

use App\Models\Post;

class PostResource extends Resource
{
    // Required: The Eloquent model class
    public static string $model = Post::class;

    // Required: Plural display name
    public static string $label = 'Posts';

    // Required: Singular display name
    public static string $singularLabel = 'Post';

    // Field to use as the title/name (default: 'id')
    public static string $title = 'title';

    // Enable/disable search (default: true)
    public static bool $searchable = true;

    // Columns to search (used when $searchable = true)
    public static array $search = ['title', 'content', 'author'];

    // Number of items per page (default: 15)
    public static int $perPage = 15;

    // Required: Define your fields
    public function fields(): array
    {
        return [
            // Fields here
        ];
    }
}</code></pre>
                    </div>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">3. Register Resource in Config</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Add your resource to <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono text-primary-600 dark:text-primary-400">config/resources.php</code>:</p>

                    <div class="code-block-wrapper">
                        <button class="copy-button" onclick="copyCode(this)">
                            <svg class="copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="copy-text">Copy</span>
                        </button>
                        <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto"><code>return [
    'users' => \App\Resources\UserResource::class,
    'posts' => \App\Resources\PostResource::class,
    'countries' => \App\Resources\CountryResource::class,
];</code></pre>
                    </div>

                    <p class="text-gray-700 dark:text-gray-300 mb-4">The key (<code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono text-primary-600 dark:text-primary-400">posts</code>) becomes the resource identifier used in API routes.</p>

                    <h2 id="creating-resource" class="text-3xl font-bold text-gray-900 dark:text-white mt-12 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Creating a Resource</h2>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Basic Structure</h3>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>&lt;?php

namespace App\Resources;

use App\Models\Post;
use App\Enums\PostStatus;
use App\Resources\Fields\{ID, Text, Textarea, Select, Date, BelongsTo};
use App\Resources\Filters\{SelectFilter, DateRangeFilter};

class PostResource extends Resource
{
    public static string $model = Post::class;
    public static string $label = 'Posts';
    public static string $singularLabel = 'Post';
    public static string $title = 'title';
    public static array $search = ['title', 'content'];
    public static int $perPage = 15;

    public function fields(): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->rules('required|string|max:255')
                ->sortable()
                ->searchable(),

            Textarea::make('Content')
                ->rules('required|string')
                ->hideFromIndex(),

            Select::make('Status')
                ->options(PostStatus::class)
                ->rules('required|in:draft,published,archived')
                ->sortable()
                ->toggleable(true, 'published', 'draft'),

            BelongsTo::make('Author', 'user_id')
                ->resource(UserResource::class)
                ->titleAttribute('name')
                ->rules('required|exists:users,id'),

            Date::make('Published At')
                ->sortable()
                ->nullable(),

            Date::make('Created At')
                ->sortable()
                ->exceptOnForm(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status')
                ->options(PostStatus::class)
                ->column('status'),

            DateRangeFilter::make('Published Date', 'published_at'),
        ];
    }

    public function with(): array
    {
        return ['user']; // Eager load relationships
    }
}</code></pre>

                    <h2 id="field-types" class="text-3xl font-bold text-gray-900 dark:text-white mt-12 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Field Types</h2>

                    <h3 id="field-common" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Common Field Methods</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Available on <strong class="text-gray-900 dark:text-white">all</strong> field types:</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>// Validation
->rules('required|string|max:255')
->rules(['required', 'string', 'max:255']) // Array format also supported
->nullable(true)

// Display control
->sortable(true)           // Enable sorting (default: false)
->searchable(true)         // Include in search (default: false)
->hideFromIndex()          // Hide from table view
->hideFromDetail()         // Hide from detail view
->hideFromForm()           // Hide from create/edit forms
->onlyOnIndex()            // Show only in table
->onlyOnDetail()           // Show only in detail view
->onlyOnForm()             // Show only in forms
->exceptOnForm()           // Show everywhere except forms

// Defaults and metadata
->default('value')         // Default value for new records
->placeholder('Enter text here')
->help('Helpful hint text for users')
->meta(['custom' => 'data']) // Add custom metadata</code></pre>

                    <h3 id="field-text" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Text Field</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Single-line text input for titles, names, and short text.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Fields\Text;

Text::make('Title')
    ->rules('required|string|max:255')
    ->sortable()
    ->searchable()
    ->placeholder('Enter title')
    ->maxLength(255)
    ->minLength(3)</code></pre>

                    <h3 id="field-email" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Email Field</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Email input field with built-in validation.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Fields\Email;

Email::make('Email')
    ->rules('required|email|unique:users,email')
    ->sortable()
    ->searchable()
    ->placeholder('user@example.com')</code></pre>

                    <h3 id="field-password" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Password Field</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Secure password input with context-aware rules.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Fields\Password;

Password::make('Password')
    ->rules('required|string|min:8')
    ->creationRules('required|string|min:8')
    ->updateRules('nullable|string|min:8')
    ->requiredOnCreate(true)
    ->requiredOnUpdate(false)
    ->creationPlaceholder('Enter password')
    ->updatePlaceholder('Leave blank to keep current password')</code></pre>

                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 mb-6">
                        <p class="text-sm text-blue-900 dark:text-blue-200">
                            <strong class="font-semibold">Note:</strong> Password fields are automatically hidden from index and detail views, and values are hashed using <code class="bg-blue-100 dark:bg-blue-800 px-2 py-1 rounded text-xs font-mono">bcrypt()</code>.
                        </p>
                    </div>

                    <h3 id="field-select" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Select Field</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Dropdown select field with support for Enums and arrays.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Fields\Select;
use App\Enums\Status;

// With Enum
Select::make('Status')
    ->options(Status::class)
    ->rules(['required', 'in:active,inactive'])
    ->sortable()
    ->default(Status::Active->value)
    ->toggleable(true, 'active', 'inactive')

// With array
Select::make('Category')
    ->options([
        'tech' => 'Technology',
        'design' => 'Design',
        'business' => 'Business',
    ])
    ->rules('required|in:tech,design,business')</code></pre>

                    <h3 id="field-boolean" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Boolean Field</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Checkbox or toggle switch for true/false values.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Fields\Boolean;

Boolean::make('Is Active', 'is_active')
    ->rules('nullable|boolean')
    ->sortable()
    ->default(true)
    ->trueLabel('Active')
    ->falseLabel('Inactive')
    ->toggleable(true)</code></pre>

                    <h3 id="field-belongsto" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">BelongsTo Field</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Foreign key relationship to another resource with searchable dropdown.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Fields\BelongsTo;

BelongsTo::make('Author', 'user_id')
    ->resource(UserResource::class)
    ->titleAttribute('name')
    ->rules('required|exists:users,id')
    ->searchable(true)
    ->sortable()</code></pre>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-6">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <strong class="font-semibold text-gray-900 dark:text-white">Features:</strong>
                        </p>
                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 mt-2 space-y-1">
                            <li>Automatically eager loads the relationship</li>
                            <li>Transforms to <code class="bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded text-xs font-mono">{id, display}</code> format in responses</li>
                            <li>Supports searchable dropdown in frontend</li>
                        </ul>
                    </div>

                    <h3 id="field-belongstomany" class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">BelongsToMany Field</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Many-to-many relationship with automatic pivot table syncing.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Fields\BelongsToMany;

BelongsToMany::make('Roles')
    ->resource(RoleResource::class)
    ->titleAttribute('name')
    ->rules('nullable|array')
    ->showOnIndex()</code></pre>

                    <h2 id="filters" class="text-3xl font-bold text-gray-900 dark:text-white mt-12 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Filters</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Filters allow users to narrow down resource listings with powerful query capabilities.</p>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">SelectFilter</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Dropdown filter for exact matches.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Filters\SelectFilter;
use App\Enums\Status;

// With Enum
SelectFilter::make('Status')
    ->options(Status::class)
    ->column('status')

// With array
SelectFilter::make('Region')
    ->options([
        'north' => 'North',
        'south' => 'South',
        'east' => 'East',
        'west' => 'West',
    ])
    ->column('region')

// With closure
SelectFilter::make('Category')
    ->options(fn () => Category::pluck('name', 'id')->toArray())
    ->column('category_id')</code></pre>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">DateRangeFilter</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Filter records by date range.</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Filters\DateRangeFilter;

DateRangeFilter::make('Created Date', 'created_at')
    ->column('created_at')</code></pre>

                    <h2 id="actions" class="text-3xl font-bold text-gray-900 dark:text-white mt-12 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Actions</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Actions perform operations on selected resources.</p>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">BulkDeleteAction</h3>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Actions\BulkDeleteAction;

BulkDeleteAction::make()
    ->confirmable('Are you sure you want to delete these items?')
    ->danger()</code></pre>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">BulkUpdateAction</h3>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>use App\Resources\Actions\BulkUpdateAction;

BulkUpdateAction::make()
    ->fields([
        'status' => Status::class,
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ])
    ->confirmable('Update selected items?')</code></pre>

                    <h2 id="api-endpoints" class="text-3xl font-bold text-gray-900 dark:text-white mt-12 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">API Endpoints</h2>

                    <p class="text-gray-700 dark:text-gray-300 mb-4">All resources automatically get the following RESTful endpoints:</p>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Base URL Pattern</h3>
                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>/api/resources/{resource}/{endpoint}</code></pre>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">List Resources (Index)</h3>
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 mb-4">
                        <code class="text-sm font-mono text-green-900 dark:text-green-200">GET /api/resources/{resource}</code>
                    </div>

                    <p class="text-gray-700 dark:text-gray-300 mb-2"><strong class="text-gray-900 dark:text-white">Query Parameters:</strong></p>
                    <ul class="list-disc list-inside text-gray-700 dark:text-gray-300 mb-4 space-y-1">
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono">search</code> - Search query string</li>
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono">perPage</code> - Items per page</li>
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono">page</code> - Page number</li>
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono">sort</code> - Column to sort by</li>
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono">direction</code> - Sort direction: <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-sm font-mono">asc</code> or <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-sm font-mono">desc</code></li>
                        <li><code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono">filters[key]</code> - Filter values</li>
                    </ul>

                    <p class="text-gray-700 dark:text-gray-300 mb-2"><strong class="text-gray-900 dark:text-white">Example:</strong></p>
                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>GET /api/resources/users?search=john&sort=created_at&direction=desc&filters[status]=active</code></pre>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Create Resource</h3>
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 mb-4">
                        <code class="text-sm font-mono text-blue-900 dark:text-blue-200">POST /api/resources/{resource}</code>
                    </div>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Update Resource (Full)</h3>
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 mb-4">
                        <code class="text-sm font-mono text-yellow-900 dark:text-yellow-200">PUT /api/resources/{resource}/{id}</code>
                    </div>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Patch Resource (Partial Update)</h3>
                    <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500 mb-4">
                        <code class="text-sm font-mono text-purple-900 dark:text-purple-200">PATCH /api/resources/{resource}/{id}</code>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Perfect for toggle switches and quick edits - validates only the fields being updated.</p>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Delete Resource</h3>
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 mb-4">
                        <code class="text-sm font-mono text-red-900 dark:text-red-200">DELETE /api/resources/{resource}/{id}</code>
                    </div>

                    <h2 id="examples" class="text-3xl font-bold text-gray-900 dark:text-white mt-12 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Complete Examples</h2>

                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-3">Blog Post Resource</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">A comprehensive example showing all major features:</p>

                    <pre class="text-gray-100 p-4 rounded-lg overflow-x-auto mb-6"><code>&lt;?php

namespace App\Resources;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Resources\Actions\{BulkDeleteAction, PublishAction};
use App\Resources\Fields\{ID, Text, Textarea, Select, Date, BelongsTo, BelongsToMany};
use App\Resources\Filters\{SelectFilter, DateRangeFilter};

class PostResource extends Resource
{
    public static string $model = Post::class;
    public static string $label = 'Blog Posts';
    public static string $singularLabel = 'Post';
    public static string $title = 'title';
    public static array $search = ['title', 'content', 'excerpt'];
    public static int $perPage = 20;

    public function fields(): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->rules('required|string|max:255')
                ->sortable()
                ->searchable(),

            Textarea::make('Content')
                ->rules('required|string')
                ->hideFromIndex(),

            Select::make('Status')
                ->options(PostStatus::class)
                ->rules(['required', 'in:draft,published,archived'])
                ->sortable()
                ->toggleable(true, 'published', 'draft'),

            BelongsTo::make('Author', 'user_id')
                ->resource(UserResource::class)
                ->titleAttribute('name')
                ->rules('required|exists:users,id')
                ->sortable(),

            BelongsToMany::make('Tags')
                ->resource(TagResource::class)
                ->titleAttribute('name')
                ->showOnIndex(),

            Date::make('Published At')
                ->sortable()
                ->nullable(),

            Date::make('Created At')
                ->sortable()
                ->exceptOnForm(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status')
                ->options(PostStatus::class),

            DateRangeFilter::make('Published Date', 'published_at'),
        ];
    }

    public function actions(): array
    {
        return [
            PublishAction::make(),
            BulkDeleteAction::make(),
        ];
    }

    public function with(): array
    {
        return ['user', 'tags'];
    }
}</code></pre>

                    <!-- CTA Section -->
                    <div class="mt-12 p-8 bg-gradient-to-r from-primary-50 to-purple-50 dark:from-primary-900/30 dark:to-purple-900/30 rounded-lg border border-primary-200 dark:border-primary-800/50 not-prose">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Ready to Get Started?</h3>
                        <p class="text-gray-700 dark:text-gray-300 mb-6">
                            The Resource CRUD system provides everything you need to build powerful admin interfaces with minimal code.
                            Start building your admin interface by creating a Resource class and let the system handle the rest!
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="/admin" class="btn-primary px-6 py-3 text-white rounded-lg font-semibold shadow-lg inline-block">
                                Try It Now
                            </a>
                            <a href="/#contact" class="inline-block px-6 py-3 rounded-lg font-semibold border-2 border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                Get Support
                            </a>
                        </div>
                    </div>

                </article>
            </main>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add copy buttons to all code blocks that don't have them
    document.querySelectorAll('pre:not(.code-block-wrapper pre)').forEach(function(pre) {
        // Skip if already has a copy button
        if (pre.parentElement.classList.contains('code-block-wrapper')) return;

        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-wrapper';

        // Create copy button
        const button = document.createElement('button');
        button.className = 'copy-button';
        button.onclick = function() { copyCode(this); };
        button.innerHTML = `
            <svg class="copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            <span class="copy-text">Copy</span>
        `;

        // Wrap the pre element
        pre.parentNode.insertBefore(wrapper, pre);
        wrapper.appendChild(button);
        wrapper.appendChild(pre);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;

            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offsetTop = target.offsetTop - 100;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Copy code function
function copyCode(button) {
    const wrapper = button.closest('.code-block-wrapper');
    const code = wrapper.querySelector('code');
    const text = code.textContent;

    // Copy to clipboard
    navigator.clipboard.writeText(text).then(function() {
        // Change button state
        button.classList.add('copied');
        const copyText = button.querySelector('.copy-text');
        const originalText = copyText.textContent;
        copyText.textContent = 'Copied!';

        // Update icon
        const icon = button.querySelector('.copy-icon');
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';

        // Reset after 2 seconds
        setTimeout(function() {
            button.classList.remove('copied');
            copyText.textContent = originalText;
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>';
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy:', err);
        const copyText = button.querySelector('.copy-text');
        copyText.textContent = 'Failed';
        setTimeout(function() {
            copyText.textContent = 'Copy';
        }, 2000);
    });
}
</script>
@endpush
@endsection
