<x-mail::message>
# RMA #{{ $rma->id }} concluído

- **Descrição:** {{ $rma->descricao }}
- **Modelo:** {{ $rma->modelo ?? '—' }}
- **SN:** {{ $rma->sn ?? '—' }}
- **Solução:** {{ $rma->solucao?->value ?? '—' }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
