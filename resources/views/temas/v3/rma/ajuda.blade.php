@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina detalhe-v3">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Central de Ajuda e Procedimentos</h1>
                <p class="pagina__resumo">
                    Guia operacional do fluxo de RMA: Entrada, Processamento e Saída.
                </p>
            </div>
        </div>

        <div class="detalhe-v3__secoes">
            <section class="cartao detalhe-v3__secao">
                <h2 class="detalhe-v3__titulo">As 3 Etapas do Fluxo de RMA</h2>
                <div style="display: flex; flex-direction: column; gap: 12px; line-height: 1.6;">
                    <p><strong>1. Entrada:</strong> Cadastro dos dados do produto e identificação da solicitação.</p>
                    <p><strong>2. Processamento:</strong> Recebimento físico, contato com destinatário e emissão de NF de remessa.</p>
                    <p><strong>3. Saída:</strong> Retorno do produto reparado, troca ou crédito concedido, assinalando a conclusão.</p>
                </div>
            </section>

            <section class="cartao detalhe-v3__secao">
                <h2 class="detalhe-v3__titulo">Instruções Operacionais Detalhadas</h2>
                <div style="line-height: 1.6; display: flex; flex-direction: column; gap: 12px;">
                    <p>
                        Todo o processo é conduzido pelo responsável técnico de RMA. Ao adicionar uma nova solicitação,
                        ela é alocada na fila de <strong>Entrada</strong> até a chegada física do produto.
                    </p>
                    <p>
                        Para avançar para <strong>Recebido</strong>, o equipamento deve estar em mãos e identificado
                        com NF de compra/venda e destinatário definido.
                    </p>
                    <p>
                        Ao <strong>Encaminhar</strong> para conserto externo em assistência técnica ou fornecedor,
                        insira a NF de remessa e acompanhe até o retorno do produto ao estoque ou cliente.
                    </p>
                </div>
            </section>
        </div>
    </div>
@endsection
