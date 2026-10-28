<x-guest-layout>
  <div class="text-center mb-6">
    <img src="{{ asset('brand/logo.png') }}"
        alt="PymeCRM"
        class="mx-auto h-16 w-16 sm:h-20 sm:w-20 object-contain rounded-full ring-1 ring-gray-200 bg-white p-1">

    <div class="mt-3 text-lg font-semibold text-gray-900">
      PymeCRM
    </div>
  </div>

  <div class="bg-white border border-gray-300 rounded-sm p-6">
    @if ($errors->any())
      <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 p-3">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ show:false }">
      @csrf

      <div class="mb-4">
        <label class="block text-base font-medium text-gray-800 mb-2" for="email">
          Nombre de usuario
        </label>

        <input id="email"
               name="email"
               type="email"
               value="{{ old('email') }}"
               required
               autofocus
               autocomplete="username"
               class="w-full border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500">
        @error('email')
          <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-4">
        <label class="block text-base font-medium text-gray-800 mb-2" for="password">
          Contraseña
        </label>

        <div class="relative">
          <input id="password"
                 name="password"
                 :type="show ? 'text' : 'password'"
                 required
                 autocomplete="current-password"
                 class="w-full border border-gray-300 px-3 py-2 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500">

          <button type="button"
                  class="absolute inset-y-0 right-0 px-3 text-gray-600 hover:text-gray-900"
                  @click="show = !show"
                  aria-label="Mostrar/ocultar contraseña">
            {{-- iconito ojo --}}
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>

        @error('password')
          <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
      </div>

      <div class="flex items-center justify-between mb-5">
        <label class="inline-flex items-center gap-2 text-gray-800">
          <input type="checkbox" name="remember" class="h-4 w-4 border-gray-300">
          <span>Recuérdame</span>
        </label>

        <button type="submit"
                class="bg-blue-700 text-white font-semibold px-5 py-2 hover:bg-blue-800">
          Ingresar
        </button>
      </div>

      @if (Route::has('password.request'))
        <div class="text-center text-gray-700">
          <a href="{{ route('password.request') }}" class="hover:underline">
            ¿Olvidaste tu contraseña?
          </a>
        </div>
      @endif
    </form>
  </div>
</x-guest-layout>
