<x-layouts.auth-layout subtitle="{{ empty($subtitle) ? 'Dashboard' : $subtitle }}">
    <div class="main-card">
        @if(session()->has('message'))
            <div class="bg-green-800 text-white p-2 rounded-lg w-full mb-4" id="dashboard_message">
                {{ session('message') }}
            </div>
        @endif
        <p class="title-2 mb-4">Bem-vindo ao {{ config('app.name') }}</p>


    </div>

    <script>
        // Exibir a mensagem de sucesso por 5 segundos
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = document.querySelector('#dashboard_message');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.remove();
                }, 3000);
            }
        });
    </script>

</x-layouts.auth-layout>