<x-layouts.auth-layout subtitle="{{ empty($subtitle) ? '' : $subtitle }}">
    <div class="main-card overflow-auto">

        <div class="flex justify-between items-center">
            <p class="title-2">Editar bundle</p>
            <a href="{{ route('bundles.home') }}" class="btn"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
        </div>

        <hr class="my-4">

        <div class="flex justify-between gap-10">

            <div class="w-full">

                <form action="{{ route('bundle.edit.submit') }}" method="post">

                    @csrf

                    <input type="hidden" name="bundle_id" value="{{ Crypt::encrypt($bundle->id) }}">
                    <input type="hidden" name="queue_list" value="{{ old('queue_list', json_encode($bundle_queue_list, true)) }}">

                    <div class="mb-4">
                        <label for="bundle_name" class="label">Nome do bundle</label>
                        <input type="text" id="bundle_name" name="bundle_name" class="input w-full"
                            placeholder="Nome do bundle" value="{{ old('bundle_name', $bundle->name) }}">
                        {!! showValidationErrors('bundle_name', $errors) !!}
                    </div>

                    <div class="mb-4 w-full">
                        <p class="label">Credencial username</p>
                        <p class="text-slate-400">{{ $bundle->credential_username }}</p>
                    </div>

                    <div class="mb-4">
                        <p class="title-3 mb-2">Filas de espera do bundle</p>
                        <div class="main-card !p-4 !bg-slate-100" id="div_queues">
                            {!! showValidationErrors('queue_list', $errors) !!}
                        </div>
                    </div>

                    <button type="submit" class="btn"><i class="fa-solid fa-check me-2"></i>Editar bundle</button>

                </form>

            </div>

            <div class="w-full">
                <p class="text-slate-600 font-bold">Filas de espera</p>
                @if($queues->isEmpty())
                <p class="text-slate-400 text-center mt-12"> Não existem filas de espera</p>
                @else
                <table id="table-queue" class="responsive">
                    <thead class="bg-black text-white">
                        <tr>
                            <th></th>
                            <th>Nome</th>
                            <th>Seviço</th>
                            <th>Balcão</th>
                            <th>Estado</th>
                            <th>previsualização</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($queues as $queue)
                        <tr>
                            <td> <button type="button" class="btn" id="btn_queue"
                                    data-queue-hash-code="{{ $queue->hash_code }}" data-queue-name="{{ $queue->name }}">
                                    <i class="fa-solid fa-circle-plus"></i> </button>
                            </td>
                            <td>{{ $queue->name }}</td>
                            <td>{{ $queue->sevice_name }}</td>
                            <td>{{ $queue->service_desk }}</td>
                            <td>
                                <span class="me-2">{!! getQueueStateIcon($queue->status) !!}</span>
                                {{ getQueueStateText($queue->status) }}
                            </td>
                            <td>
                                {!! getQueuePreview($queue) !!}

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

        </div>

    </div>

    <script>
        $(document).ready(() => {
            $("#table-queue").DataTable({
                language: {
                    url: "{{ asset('assets/datatables/pt-PT.json') }}"
                }
            });
        });


        // Gestão de queus
        let queues = getQueueListFromInputHidden();

        renderQueues(queues);

        document.querySelectorAll("#btn_queue").forEach(button => {
            button.addEventListener('click', function() {
                const queueHashCode = this.getAttribute('data-queue-hash-code');
                const queueName = this.getAttribute('data-queue-name');

                // Verificar se ja existe uma fila

                if (queues.some(queue => queue.hash_code === queueHashCode)) {
                    queues = queues.filter(queue => queue.hash_code !== queueHashCode)
                } else {
                    queues.push({
                        hash_code: queueHashCode,
                        name: queueName
                    });
                }

                renderQueues(queues);
            });
        });

        function renderQueues(queues) {
            let html = '';
            if (queues.length === 0) {
                html = '<p class="text-slate-400 text-center"> Não existem filas de espera</p>'
            } else {
                queues.forEach(queue => {
                    html += '<div class="flex bg-white justify-between items-center p-2 mb-1 rounded-lg border-gray-300">';
                    html += `<span class="font-bold">${queue.name}</span> <i class="text-red-500 cursor-pointer fa fa-regular fa-trash-can" onclick="deleteFromQueue('${queue.hash_code}')"></i>`;
                    html += '</div>';
                });
            }
            document.querySelector('#div_queues').innerHTML = html;

            // Atualizar o input hidden com o JSON
            document.querySelector('input[name="queue_list"]').value = JSON.stringify(queues)
        }

        function deleteFromQueue(hash_code) {
            queues = queues.filter(q => q.hash_code !== hash_code);
            renderQueues(queues);
        }

        function getQueueListFromInputHidden() {
            const queueListInput = document.querySelector('input[name="queue_list"]');
            if (queueListInput) {
                try {
                    return JSON.parse(queueListInput.value)
                } catch (e) {
                    return [];
                }
            }
            return [];
        }
    </script>

</x-layouts.auth-layout>