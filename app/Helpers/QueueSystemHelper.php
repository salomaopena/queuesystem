<?php

if (!function_exists('showValidationErrors')) {
    function showValidationErrors($fieldName, $validationErrors)
    {
        if ($validationErrors->has($fieldName)) {
            return '<div class="text-red-500 text-sm mt-1 italic">'
                . $validationErrors->first($fieldName) . '</div>';
        } else {
            return '';
        }
    }
}


if (!function_exists('showServerError')) {
    function showServerError()
    {
        if (session()->has('server_error')) {
            return '<div class="text-red-500 text-sm mt-1 italic">'
                . session()->get('server_error') . '</div>';
        } else {
            return '';
        }
    }
}


if (!function_exists('getFormatedTicketNumber')) {
    function getFormatedTicketNumber($ticketNumber, $prefix = null, $totalDigits = 3)
    {
        $result = '';

        // prefix
        if ($prefix) {
            $result = $prefix;
        }

        // numbers
        if ($totalDigits > 0) {
            $result .= str_pad($ticketNumber, $totalDigits, '0', STR_PAD_LEFT);
        }

        return $result;
    }
}


if (!function_exists('getTicketStateText')) {
    function getTicketStateText($states)
    {
        $rules = [
            'waiting' => 'Aguardando',
            'called' => 'Atendido',
            'not_attended' => 'Não atendido',
            'dismissed' => 'Dispensado'
        ];

        return $rules[$states] ?? 'Desconhecido';
    }
}

if (!function_exists('getQueueStateIcon')) {

    function getQueueStateIcon($state)
    {
        $icons = [
            'active' => '<i class="fa-regular fa-circle-check text-green-700" title="Ativa"></i>',
            'inactive' => '<i class="fa-regular fa-circle-xmark text-red-700" title="Inativa"></i>',
            'done' => '<i class="fa-solid fa-ban text-slate-300" title="Concluído"></i>'
        ];

        return $icons[$state];
    }

}


if (!function_exists('getQueueStateText')) {

    function getQueueStateText($state)
    {
        $rules = [
            'active' => 'Ativa',
            'inactive' => 'Invativa',
            'done' => 'Terminada'
        ];

        return $rules[$state] ?? 'Desconhecido';
    }

}