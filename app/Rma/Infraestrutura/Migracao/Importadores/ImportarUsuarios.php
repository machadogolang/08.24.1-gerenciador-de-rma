<?php

namespace App\Rma\Infraestrutura\Migracao\Importadores;

use App\Identidade\Dominio\Papel;
use App\Models\User;
use App\Rma\Infraestrutura\Migracao\Concerns\ExecutaComRollbackEmDryRun;
use App\Rma\Infraestrutura\Migracao\ConexaoLegado;
use App\Rma\Infraestrutura\Migracao\RelatorioDeReconciliacao;
use App\Rma\Infraestrutura\Migracao\TabelaDeTraducao;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * `usuario` → `users` (`INV-RMA-06` §11). Dedup natural via `users.email` (já `UNIQUE`
 * desde a migration original do Laravel) — `updateOrCreate(['email' => ...], [...])` já
 * é idempotente por construção, sem precisar de coluna `id_legado`.
 *
 * **Senhas nunca são migradas como hash** — `Key1461`/`Key1581` são SHA1 sem salt,
 * irreversível para bcrypt. Cada usuário recém-criado ganha senha aleatória temporária
 * e dispara o fluxo nativo de "esqueci minha senha" do Laravel (só na criação, não em
 * updates subsequentes — idempotência não deve reenviar e-mail nem trocar a senha de
 * quem já trocou desde a migração).
 */
final class ImportarUsuarios
{
    use ExecutaComRollbackEmDryRun;

    public function __construct(
        private readonly ConexaoLegado $conexao = new ConexaoLegado,
    ) {}

    public function executar(RelatorioDeReconciliacao $relatorio, bool $dryRun = false): void
    {
        // A V3 (`LEG-RMA-003`) só tem reset de senha feito pelo administrador — não
        // existe fluxo de "esqueci minha senha" autosserviço com rota nomeada
        // `password.reset` (fora do escopo desta fase criar essa UI). O broker nativo
        // do Laravel (`Password::sendResetLink()`) ainda é o mecanismo certo para gerar
        // o token + disparar o e-mail (decisão já fixada em `INV-RMA-06` §11); só a URL
        // do botão do e-mail é construída sem depender da rota nomeada inexistente.
        ResetPassword::createUrlUsing(
            fn ($notifiable, string $token) => url('/reset-password/'.$token.'?email='.urlencode($notifiable->getEmailForPasswordReset()))
        );

        $origem = $this->conexao->usuario();
        $total = 0;
        $processados = 0;

        $this->executarComRollbackSeDryRun($dryRun, function () use ($origem, $relatorio, $dryRun, &$total, &$processados) {
            foreach ($origem as $linha) {
                $total++;

                $papel = TabelaDeTraducao::papel($linha->permissao !== null ? (int) $linha->permissao : null);

                if ($papel === null) {
                    $relatorio->registrarAnomalia(
                        'usuario',
                        $linha->id_usuario,
                        "permissao='{$linha->permissao}' fora do domínio confirmado (-1/1/2/3/4) — usuário importado com Papel::Bloqueado (fail-safe)"
                    );
                    $papel = Papel::Bloqueado;
                }

                $temaPreferido = TabelaDeTraducao::temaPreferido($linha->app);

                $user = User::query()->firstOrNew(['email' => $linha->email]);
                $novo = ! $user->exists;

                $user->fill([
                    'name' => $linha->nome,
                    'papel' => $papel,
                    'tema_preferido' => $temaPreferido,
                    'anotacao' => $linha->anotacao ?? '',
                ]);

                if ($novo) {
                    // `password` é obrigatório na criação (NOT NULL) — senha aleatória
                    // temporária, nunca reaproveitada em updates subsequentes
                    // (idempotência não deve trocar a senha de quem já trocou desde a
                    // migração).
                    $user->password = Hash::make(Str::random(40));
                    $user->created_at = $linha->data_de_cadastro;
                }

                $user->save();

                // ARQ-002 (`INV-RMA-10`): dry-run roda a tradução/gravação inteira para
                // detectar anomalia e contar corretamente (a transação é desfeita no
                // fim), mas o e-mail de redefinição de senha não é transacional — nunca
                // pode ser disparado de verdade num dry-run.
                if ($novo && ! $dryRun) {
                    Password::sendResetLink(['email' => $user->email]);
                }

                $processados++;
            }
        });

        $relatorio->contarOrigem('usuario', $total);
        $relatorio->contarDestino('users', $processados);
    }
}
