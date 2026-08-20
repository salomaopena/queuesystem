<x-layouts.auth-layout subtitle="{{ empty($subtitle) ? '' : $subtitle }}">

    <div class="main-card overflow-auto">

        <div class="flex justify-between items-center">
            <p class="title-2">Bundle de filas</p>
        </div>

        <hr class="my-4">

        <div class="mb-4">
            <a href="#" class="btn"> <i class="far fa-plus me-2"></i> Criar novo bundle</a>
        </div>

        @if($bundles->isEmpty())
            <p class="text-slate-400 text-center my-12"> Nunhum bundle encontrado</p>
        @else

            <table class="table-responsive" id="table-bundle">
                <thead class="bg-black text-white">
                    <tr>
                        <th>Nome</th>
                        <th>Número de filas</th>
                        <th>Credenciais</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($bundles as $bundle)
                        <tr>
                            <td>[Nome]</td>
                            <td>[Número de filas]</td>
                            <td>[Credenciais]</td>
                            <td>[Acções]</td>
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