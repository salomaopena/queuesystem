<x-layouts.guest-layout subtitle="{{ empty($subtitle) ? 'Credenciais' : $subtitle }}">

    <div class="flex flex-col justify-center h-screen items-center">
        <div class="flex items-center mb-4">
            <img src="{{ asset('assets/images/ticket_dispenser_logo.png') }}" alt="" srcset="" class="w-20 h-20">
            <p class="text-5xl font-bold text-slate-800">Dispensador</p>
        </div>

        <div class="main-card w-200">
            {!! ShowServerError() !!}
            <form action="{{ route('dispenser.credentials.submit') }}" method="post">
                @csrf
                <div class="mb-4">
                    <label for="credential_username" class="block text-sm font-medium text-gray-700">Credential username</label>
                    <input type="text" name="credential_username" id="credential_username" class="input w-full" value="{{ old('credential_username') }}">
                    {!! showValidationErrors('credential_username', $errors) !!}
                </div>
                <div class="mb-4">
                    <label for="credential_password" class="block text-sm font-medium text-gray-700">Credential password</label>
                    <input type="password" name="credential_password" id="credential_password" class="input w-full">
                    {!! showValidationErrors('credential_password', $errors) !!}
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="btn w-full"> <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Apresentar filas
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.guest-layout>