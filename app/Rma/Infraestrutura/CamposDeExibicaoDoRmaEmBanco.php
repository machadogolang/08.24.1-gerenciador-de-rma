<?php

namespace App\Rma\Infraestrutura;

use App\Models\Rma as RmaEloquent;

/**
 * Leitura de apresentacao do detalhe de RMA (PAR-DET-V1-01/PAR-DET-V2-01).
 *
 * O agregado de dominio (`App\Rma\Dominio\Rma`) permanece enxuto: as colunas
 * historicas preservadas pela Fase 9 (`2026_09_02_000001_add_campos_historicos_
 * de_migracao_to_rmas_table`) nao tem dono de regra de negocio, mas fazem parte da
 * paridade visual dos detalhes V1/V2. Este leitor e o unico ponto que devolve esses
 * campos para a camada de apresentacao, usando o model Eloquent somente dentro da
 * infraestrutura (mesma regra de `App\Models\Rma`).
 *
 * Nenhuma migration nova. Campos nulos/vazios sao devolvidos como `null`/string
 * vazia - a view mostra o que existe e documenta a lacuna quando nao existe.
 */
final class CamposDeExibicaoDoRmaEmBanco
{
    /**
     * @return array<string, mixed>
     */
    public function obter(int $id): array
    {
        $model = RmaEloquent::query()->findOrFail($id);

        $destinatarioTipo = $model->destinatario_type;
        $destinatarioId = $model->destinatario_id;
        $destinatarioNome = $model->destinatario_nome_legado;

        if ($destinatarioTipo !== null && $destinatarioId !== null && class_exists($destinatarioTipo)) {
            $destinatario = $destinatarioTipo::query()->find($destinatarioId);
            if ($destinatario !== null) {
                $destinatarioNome = $destinatario->nome;
            }
        }

        return [
            'numero_legado' => $model->numero_legado,
            'numero_da_empresa' => $model->numero_da_empresa,
            'nf_devolucao_de_venda' => $model->nf_devolucao_de_venda,
            'nf_entrada_cliente_legado' => $model->nf_entrada_cliente_legado,
            'nf_retorno_cliente_legado' => $model->nf_retorno_cliente_legado,
            'nf_remessa' => $model->nf_remessa,
            'nf_remessa_emissao' => $model->nf_remessa_emissao,
            'nf_remessa_chave' => $model->nf_remessa_chave,
            'nf_retorno_numero' => $model->nf_retorno_numero,
            'nf_retorno_emissao' => $model->nf_retorno_emissao,
            'nf_retorno_chave' => $model->nf_retorno_chave,
            'rastreio_ida' => $model->rastreio_ida,
            'rastreio_retorno' => $model->rastreio_retorno,
            'cliente_email_legado' => $model->cliente_email_legado,
            'destinatario_email_legado' => $model->destinatario_email_legado,
            'destinatario_fone_legado' => $model->destinatario_fone_legado,
            'destinatario_nome_legado' => $model->destinatario_nome_legado,
            'descricao_final_legado' => $model->descricao_final_legado,
            'destinatario_type' => $destinatarioTipo,
            'destinatario_id' => $destinatarioId,
            'destinatario_nome' => $destinatarioNome,
        ];
    }
}
