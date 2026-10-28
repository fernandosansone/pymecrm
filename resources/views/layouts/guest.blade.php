<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PymeCRM') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
      <div class="w-full max-w-md">
        {{ $slot }}
      </div>
    </div>
  </body>
</html>
