<?php

namespace App\Rma\Aplicacao\Alertas;

/**
 * Composição das 11 regras de leitura da Fase 5 num único array `titulo => Collection`.
 * Extraído na correção de fidelidade Fase 8 (2026-08-25): antes desta classe, a mesma
 * lista de 11 chamadas estava duplicada em `PainelDeAlertasController` (tela dedicada,
 * `rmas.alertas`) e precisava ser repetida em `RmaController::index` (aba
 * "Início"/"Pág. Inicial" dos dois temas, "CENTRO DE AVISOS E RELATORIOS" — mesmo
 * conteúdo, achado confirmado por captura de referência do legado). Nenhuma regra de
 * negócio nova — só remove a duplicação entre os dois controllers.
 */
final class ListarGruposDeAlertas
{
    public function __construct(
        private readonly RecebidosSemEncaminhar30Dias $recebidosSemEncaminhar30Dias,
        private readonly NaoVaiDarGarantia $naoVaiDarGarantia,
        private readonly NfRetornoPendenteDeLancar $nfRetornoPendenteDeLancar,
        private readonly ProtocoloAbertoNaoEncaminhado $protocoloAbertoNaoEncaminhado,
        private readonly GarantiaFornecedorExpirada $garantiaFornecedorExpirada,
        private readonly GarantiaFornecedorExpirandoEm30Dias $garantiaFornecedorExpirandoEm30Dias,
        private readonly PrazoDestinatarioEstourado $prazoDestinatarioEstourado,
        private readonly PrioridadeAltaSemEncaminhar $prioridadeAltaSemEncaminhar,
        private readonly SemNotaFiscal $semNotaFiscal,
        private readonly SemNumeroDeSerie $semNumeroDeSerie,
        private readonly UrgenciaPorThreshold $urgenciaPorThreshold,
    ) {
    }

    /**
     * @return array<string, \Illuminate\Database\Eloquent\Collection>
     */
    public function listar(): array
    {
        return [
            'Recebidos há mais de 30 dias sem encaminhar' => $this->recebidosSemEncaminhar30Dias->listar(),
            'Não vai dar garantia' => $this->naoVaiDarGarantia->listar(),
            'NF de retorno pendente de lançar' => $this->nfRetornoPendenteDeLancar->listar(),
            'Protocolo aberto não encaminhado' => $this->protocoloAbertoNaoEncaminhado->listar(),
            'Garantia do fornecedor expirada' => $this->garantiaFornecedorExpirada->listar(),
            'Garantia do fornecedor expirando em até 30 dias' => $this->garantiaFornecedorExpirandoEm30Dias->listar(),
            'Prazo do destinatário estourado' => $this->prazoDestinatarioEstourado->listar(),
            'Prioridade alta sem encaminhar' => $this->prioridadeAltaSemEncaminhar->listar(),
            'Sem nota fiscal' => $this->semNotaFiscal->listar(),
            'Sem número de série' => $this->semNumeroDeSerie->listar(),
            'Urgência por valor (threshold R$75)' => $this->urgenciaPorThreshold->listar(),
        ];
    }
}
