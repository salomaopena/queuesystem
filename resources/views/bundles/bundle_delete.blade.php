<x-layouts.auth-layout subtitle="{{ empty($subtitle) ? '' : $subtitle }}">
    <div class="main-card overflow-auto">
        <div class="flex justify-between items-center">
            <p class="title-2">Confirmar exclusão de bundle</p>
        </div>

        <hr class="my-4">

        <p>Tem certeza de que deseja excluir o bundle "<strong>{{ $bundle->name }}</strong>"?</p>

        <div class="mt-4 gap-2">
            <a href="{{ route('bundles.home') }}" class="btn !px-8">Não</a>
            <a href="{{ route('bundle.delete.confirm', ['id' => Crypt::encrypt($bundle->id)]) }}" class="btn-red !px-8">Sim</a>
        </div>
    </div>
</x-layouts.auth-layout>