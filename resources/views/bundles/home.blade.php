<x-layouts.auth-layout subtitle="{{ empty($subtitle) ? '' : $subtitle }}">

    <div class="main-card overflow-auto">

        <div class="flex justify-between items-center">
            <p class="title-2">Bundle de filas</p>
        </div>

        <hr class="my-4">

        <div class="mb-4">
            <a href="{{ route('bundle.create') }}" class="btn"> <i class="far fa-plus me-2"></i> Criar novo bundle</a>
        </div>

        @if($bundles->isEmpty())
        <p class="text-slate-400 text-center my-12"> Nunhum bundle encontrado</p>
        @else

        <table class="table-responsive" id="table-bundle">
            <thead class="bg-black text-white">
                <tr>
                    <th class="w-[35%]">Nome</th>
                    <th class="w-[20%]">Número de filas</th>
                    <th class="w-[25%]">Credenciais</th>
                    <th class="w-[20%]"></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($bundles as $bundle)
                <tr class="{{ $bundle->deleted_at !== null ? 'text-red-500' : '' }}">
                    <td>{{ $bundle->name }}</td>
                    <td>{{ count(json_decode($bundle->queues)) }}</td>
                    <td>{{ $bundle->credential_username }}</td>
                    <td>
                        <div class="flex justify-end gap-2">
                            @if($bundle->deleted_at === null)
                            <a href="{{ route('bundle.edit', ['id' => Crypt::encrypt($bundle->id)]) }}" class="btn btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>
                            @endif

                            @if($bundle->deleted_at === null)
                            <a href="{{ route('bundle.delete', ['id' => Crypt::encrypt($bundle->id)]) }}" class="btn-red btn-sm"><i class="fa-solid fa-trash"></i></a>
                            @else
                            <a href="{{ route('bundle.restore', ['id' => Crypt::encrypt($bundle->id)]) }}" class="btn-white btn-sm"><i class="fa-solid fa-trash-arrow-up"></i></a>
                            @endif


                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @endif

    </div>


    <script>
        // Exibir a mensagem de sucesso por 5 segundos
        $(document).ready(() => {
            $("#table-bundle").DataTable({
                language: {
                    url: "{{ asset('assets/datatables/pt-PT.json') }}"
                }
            });
        });
    </script>

</x-layouts.auth-layout>