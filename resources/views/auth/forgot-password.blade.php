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
    <div class="text-gray-700 mb-4">
      Ingresá tu email y te enviaremos un enlace para restablecer tu contraseña.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
      <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 p-3">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf

      <div class="mb-4">
        <label class="block text-base font-medium text-gray-800 mb-2" for="email">Correo electrónico</label>
        <input id="email"
               name="email"
               type="email"
               value="{{ old('email') }}"
               required
               autofocus
               class="w-full border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500">
        @error('email')
          <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="w-full bg-blue-700 text-white font-semibold px-5 py-2 hover:bg-blue-800">
        Enviar enlace
      </button>

      <div class="text-center mt-4 text-gray-700">
        <a href="{{ route('login') }}" class="hover:underline">Volver al login</a>
      </div>
    </form>
  </div>
</x-guest-layout>
