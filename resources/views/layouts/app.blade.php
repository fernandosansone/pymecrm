<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PymeCRM') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
      window.PYMECRM = window.PYMECRM || {};
      window.PYMECRM.routes = {
        contactsQuickStore: @json(route('contacts.quick-store')),
      };
    </script>
  </head>

  <body class="font-sans antialiased bg-gray-50">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">

      {{-- Topbar móvil --}}
      <div class="lg:hidden bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
          <button type="button"
                  class="inline-flex items-center px-3 py-2 rounded-xl ring-1 ring-gray-200"
                  @click="sidebarOpen = true">
            ☰
          </button>

          <div class="min-w-0 flex items-center gap-2">
            <img src="{{ asset('brand/logo.png') }}" alt="PymeCRM" class="h-7 w-7 rounded-full ring-1 ring-gray-200 bg-white p-1 object-contain">
            <div class="font-semibold text-gray-900 truncate">PymeCRM</div>
          </div>

          <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 underline underline-offset-4">
            Inicio
          </a>
        </div>
      </div>

      <div class="flex min-h-screen">
        {{-- Sidebar desktop --}}
        <div class="hidden lg:block">
          <x-sidebar />
        </div>

        {{-- Sidebar móvil (overlay) --}}
        <div x-show="sidebarOpen" class="lg:hidden fixed inset-0 z-50" style="display:none">
          <div class="absolute inset-0 bg-black/50" @click="sidebarOpen = false"></div>

          <div class="absolute inset-y-0 left-0 w-72 bg-white shadow-xl">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('brand/logo.png') }}" alt="PymeCRM" class="h-8 w-8 rounded-full ring-1 ring-gray-200 bg-white p-1 object-contain">
                <div class="min-w-0">
                  <div class="text-sm text-gray-500 leading-none">PymeCRM</div>
                  <div class="text-sm font-semibold text-gray-900 truncate">Gestión Comercial <span class="text-gray-500">(CRM)</span></div>
                </div>
              </div>

              <button class="px-3 py-2 rounded-xl ring-1 ring-gray-200" @click="sidebarOpen=false">✕</button>
            </div>

            <x-sidebar />
          </div>
        </div>

        {{-- Contenido --}}
        <div class="flex-1">
          @isset($header)
            <header class="bg-white border-b border-gray-100">
              <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
              </div>
            </header>
          @endisset

          <main>
            {{ $slot }}
          </main>
        </div>
      </div>
    </div>

    <x-followups-modal />
  </body>
</html>
