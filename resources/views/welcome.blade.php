<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Coming Soon</title>
    
    <!-- Styles -->
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-900 dark:to-gray-800 min-h-screen flex items-center justify-center">
    <div class="text-center px-4">
        <div class="mb-8">
            <h1 class="text-6xl md:text-8xl font-bold text-primary-600 dark:text-primary-400 mb-4">
                Coming Soon
            </h1>
            <p class="text-xl md:text-2xl text-gray-700 dark:text-gray-300">
                We're working on something amazing. Stay tuned!
            </p>
        </div>
        
        <div class="mt-12">
            <p class="text-gray-600 dark:text-gray-400">
                {{ config('app.name', 'Laravel') }}
            </p>
        </div>
    </div>
</body>
</html>
