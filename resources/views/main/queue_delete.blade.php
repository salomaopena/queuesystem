<x-layouts.auth-layout subtitle="{{ empty($subtitle) ? '' : $subtitle }}">
    <div class="main-card overflow-auto">
        <p class="title-2">Eliminar fila de espera</p>
        <hr class="my-4">

        <p class="text-slate-600 mb-4">Tem certeza que deseja eliminar a fila de espera?</p>

        <p class="text-lg text-zinc-600 font-bold mb-2">{{ $queue->name }}</p>
        <p class="text-sm text-zinc-400">{{ $queue->hash_code }}</p>
        <p class="text-sm text-zinc-400 ">Esta operação é reversível.</p>

        <div class="flex gap-4 mt-6">
            <a href="{{ route('dashboard') }}" class="btn !px-8">Não</a>
            <a href="{{ route('queue.delete.confirm', ['id' => Crypt::encrypt($queue->id)]) }}"
                class="btn-red !px-8">Sim</a>
        </div>
    </div>
</x-layouts.auth-layout>