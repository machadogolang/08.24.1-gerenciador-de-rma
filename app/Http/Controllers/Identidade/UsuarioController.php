<?php

namespace App\Http\Controllers\Identidade;

use App\Compartilhado\Tenant\ContextoDeTenant;
use App\Http\Controllers\Controller;
use App\Identidade\Aplicacao\ResetarSenhaDeUsuario;
use App\Identidade\Aplicacao\ResumoDeAcessoDosUsuarios;
use App\Identidade\Aplicacao\SenhaAtualIncorretaException;
use App\Identidade\Aplicacao\TrocarPropriaSenha;
use App\Identidade\Dominio\Papel;
use App\Identidade\Dominio\TemaPreferido;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    /**
     * Lista usuários. Oculta SuperAdministrador de quem não é SuperAdministrador
     * (LEG-RMA-005) - usa o método nomeado do enum `Papel`, nunca ordinal/inteiro.
     */
    public function index(Request $request): View
    {
        Gate::authorize('gerenciar', User::class);

        $ator = $request->user();

        $usuarios = User::query()->orderBy('name')->get()
            ->when(
                ! $ator->papelAtivo()->podeGerenciarUsuarios() || $ator->papelAtivo() !== Papel::SuperAdministrador,
                fn ($usuarios) => $usuarios->reject(fn (User $u) => ($u->papelNaEmpresa(app(ContextoDeTenant::class)->empresaId()) ?? $u->papel)->ocultoDaListagemDeUsuarios())
            );

        $empresaIdView = app(ContextoDeTenant::class)->empresaId();

        return view_do_tema('identidade.usuarios', [
            'titulo' => 'Usuários',
            'usuarios' => $usuarios,
            'empresa_id' => $empresaIdView,
            // PAR15-USR-001 - QT Login / Ultimo login derivados de tentativas_de_acesso.
            'resumoDeAcesso' => app(ResumoDeAcessoDosUsuarios::class)->porUsuario(),
        ]);
    }

    /**
     * Troca o papel de um usuário (LEG-RMA-005).
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        Gate::authorize('gerenciar', User::class);
        // ARQ-003 (`INV-RMA-10`): Supervisor não pode operar sobre um
        // SuperAdministrador existente, nem por URL direta.
        Gate::authorize('gerenciarUsuario', $usuario);

        $dados = $request->validate([
            // `Rule::enum` exige backing type (tryFrom), que `Papel` deliberadamente não
            // tem (sem número mágico). Validação é contra os nomes reais dos cases.
            'papel' => ['required', Rule::in(array_column(Papel::cases(), 'name'))],
        ]);

        $papelPretendido = collect(Papel::cases())->firstWhere('name', $dados['papel']);

        // ARQ-003: nem por atribuição - Supervisor não pode promover ninguém (nem a si
        // próprio) a SuperAdministrador.
        abort_unless($request->user()->papelAtivo()->podeOperarSobrePapel($papelPretendido), 403);

        // EVO-SAAS-001 (S9): Papel vive no vínculo company_user da empresa ativa. O
        // espelho em users.papel só ocorre quando o usuário tem um único vínculo ativo
        // (compatibilidade); multi-vínculo mantém papéis independentes por empresa.
        $empresaId = app(ContextoDeTenant::class)->empresaId();
        if ($empresaId !== null) {
            $vinculo = CompanyUser::query()
                ->where('company_id', $empresaId)
                ->where('user_id', $usuario->id)
                ->first();

            if ($vinculo === null) {
                $usuario->empresas()->attach($empresaId, [
                    'papel' => $papelPretendido,
                    'ativo' => true,
                ]);
            } else {
                $vinculo->update(['papel' => $papelPretendido]);
            }

            if ($usuario->empresas()->wherePivot('ativo', true)->count() === 1) {
                $usuario->update(['papel' => $dados['papel']]);
            }
        } else {
            $usuario->update(['papel' => $dados['papel']]);
        }

        return back()->with('status', 'Papel atualizado.');
    }

    /**
     * Reseta a senha de outro usuário (LEG-RMA-003) - exige `podeGerenciarUsuarios()` e,
     * desde `ARQ-003`, que o ator possa operar sobre o papel do alvo; validado dentro do
     * próprio caso de uso.
     */
    public function resetarSenha(Request $request, User $usuario, ResetarSenhaDeUsuario $resetarSenhaDeUsuario): RedirectResponse
    {
        $dados = $request->validate([
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $resetarSenhaDeUsuario->resetar($request->user(), $usuario, $dados['nova_senha']);

        return back()->with('status', 'Senha do usuário redefinida.');
    }

    /**
     * PAR15-USR-001 - superficie dedicada 'Mudar permissao' do TEMA V2 (icone da
     * tabela de usuarios; fonte `15.8.1/subp/mudar_permissao.php`). O V1 mantem a
     * acao inline - esta organizacao e exclusiva do TEMA V2.
     */
    public function permissoes(Request $request, User $usuario): View|RedirectResponse
    {
        Gate::authorize('gerenciar', User::class);
        Gate::authorize('gerenciarUsuario', $usuario);

        if (! $this->temaEhV2($request)) {
            return redirect()->route('identidade.usuarios.index');
        }

        return view('temas.v2.identidade.usuarios-permissoes', [
            'titulo' => 'Mudar permissao',
            'usuario' => $usuario,
        ]);
    }

    /**
     * PAR15-USR-001/PAR15-USR-005 - superficie dedicada 'Resetar senha' do TEMA V2
     * (icone da tabela; fonte `15.8.1/subp/resetar_senha.php`). O POST seguro ja
     * existe (`identidade.usuarios.resetar-senha`, min 8 + confirmacao).
     */
    public function resetarSenhaForm(Request $request, User $usuario): View|RedirectResponse
    {
        Gate::authorize('gerenciar', User::class);
        Gate::authorize('gerenciarUsuario', $usuario);

        if (! $this->temaEhV2($request)) {
            return redirect()->route('identidade.usuarios.index');
        }

        return view('temas.v2.identidade.usuarios-resetar-senha', [
            'titulo' => 'Resetar senha',
            'usuario' => $usuario,
        ]);
    }

    /**
     * PAR15-USR-001/PAR15-USR-004 - superficie de confirmacao 'Apagar usuario' do
     * TEMA V2 (fonte `15.8.1/subp/apagar_usuario.php`). A exclusao definitiva segue
     * como decisao de produto (hard delete do Legacy cascatearia a auditoria); esta
     * tela preserva a organizacao/confirmacao sem inventar a acao destrutiva.
     */
    public function apagar(Request $request, User $usuario): View|RedirectResponse
    {
        Gate::authorize('gerenciar', User::class);
        Gate::authorize('gerenciarUsuario', $usuario);

        if (! $this->temaEhV2($request)) {
            return redirect()->route('identidade.usuarios.index');
        }

        return view('temas.v2.identidade.usuarios-apagar', [
            'titulo' => 'Apagar usuario',
            'usuario' => $usuario,
        ]);
    }

    /**
     * PAR15-USR-007 - superficie dedicada 'Novo usuario' do TEMA V2, fonte
     * `15.8.1/subp/novo_usuario.php` (nome/e-mail/senha/permissao). O V1 mantem a
     * organizacao propria (criacao nao era parte da gestao de usuarios do 14.6.1).
     */
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('gerenciar', User::class);

        if (! $this->temaEhV2($request)) {
            return redirect()->route('identidade.usuarios.index');
        }

        // PAR15-USR-007/009 - opcoes do select de permissao: os tres rotulos
        // historicos do 15.8.1 (`Bloqueado`, `Leitura`, `Leitura e modificacao`,
        // este ultimo = `Operador`) mais a extensao moderna permitida ao ator.
        // `podeOperarSobrePapel()` e aplicado aqui E no `store()` (defesa em
        // profundidade): SUPERVISOR nunca cria SUPERADMINISTRADOR.
        $ator = $request->user()->papelAtivo();
        $permitidos = collect(Papel::cases())
            ->filter(fn (Papel $papel): bool => $ator->podeOperarSobrePapel($papel))
            ->values();

        return view('temas.v2.identidade.usuarios-novo', [
            'titulo' => 'Novo usuario',
            'papeisHistoricos' => $permitidos
                ->filter(fn (Papel $papel): bool => in_array($papel, [
                    Papel::Bloqueado,
                    Papel::Leitura,
                    Papel::Operador,
                ], true))
                ->values(),
            'papeisModernos' => $permitidos
                ->reject(fn (Papel $papel): bool => in_array($papel, [
                    Papel::Bloqueado,
                    Papel::Leitura,
                    Papel::Operador,
                ], true))
                ->values(),
        ]);
    }

    /**
     * PAR15-USR-007 - cria o usuario no tenant corrente. Nunca reproduz o SHA1 do
     * Legacy: usa o cast `hashed` do model, CSRF, validacao, Policy e o vinculo
     * `company_user` da empresa ativa (mesma regra do update de papel).
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('gerenciar', User::class);

        if (! $this->temaEhV2($request)) {
            return redirect()->route('identidade.usuarios.index');
        }

        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'papel' => ['required', Rule::in(array_column(Papel::cases(), 'name'))],
        ]);

        $papelNovo = collect(Papel::cases())->firstWhere('name', $dados['papel']);

        // ARQ-003: Supervisor nao cria SuperAdministrador (nem promove ninguem a isso).
        abort_unless($request->user()->papelAtivo()->podeOperarSobrePapel($papelNovo), 403);

        $usuario = User::query()->create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'password' => $dados['password'],
            'papel' => $papelNovo,
        ]);

        $empresaId = app(ContextoDeTenant::class)->empresaId();
        if ($empresaId !== null) {
            $usuario->empresas()->attach($empresaId, ['papel' => $papelNovo, 'ativo' => true]);
        }

        return redirect()->route('identidade.usuarios.index')->with('status', 'Usuario cadastrado.');
    }

    private function temaEhV2(Request $request): bool
    {
        return ($request->attributes->get('temaAtivo') ?? TemaPreferido::V2) === TemaPreferido::V2;
    }

    /**
     * Página de perfil do próprio usuário autenticado (troca de senha + anotação
     * pessoal, LEG-RMA-004 / LEG-RMA-042).
     */
    public function perfil(Request $request): View
    {
        return view_do_tema('identidade.perfil', ['titulo' => 'Meu perfil', 'usuario' => $request->user()]);
    }

    /**
     * PAR-RES-E-04 - superficie dedicada de Anotacoes do TEMA V2 (Legacy
     * `15.8.1/page/anotacoes.php`: "QUADRO DE ANOTACOES", textarea propria). O V1
     * mantem o Quadro de Anotacoes da pagina inicial; o Tema V3 nao recebe esta
     * organizacao. Rota registrada apenas em `routes/tema-v2.php`.
     */
    public function anotacoes(Request $request): View|RedirectResponse
    {
        // V1 nao recebe esta organizacao (mantem o Quadro de Anotacoes da pagina
        // inicial); a superficie dedicada e exclusiva do TEMA V2 (PAR-RES-E-04).
        if (($request->attributes->get('temaAtivo') ?? TemaPreferido::V2) !== TemaPreferido::V2) {
            return redirect()->route('identidade.perfil.show');
        }

        return view('temas.v2.identidade.anotacoes', ['titulo' => 'Quadro de anotacoes', 'usuario' => $request->user()]);
    }

    /**
     * PAR-RES-E-04 - "Alterar senha" como superficie separada do TEMA V2 (Legacy
     * `15.8.1/subp/senha.php`, subpagina de Controle). Mantem a exigencia moderna de
     * senha atual + confirmacao (`TrocarPropriaSenha`), que o legado nao tinha.
     */
    public function alterarSenha(Request $request): View|RedirectResponse
    {
        // Mesmo criterio da anotacao: "Alterar senha" separado e do TEMA V2.
        if (($request->attributes->get('temaAtivo') ?? TemaPreferido::V2) !== TemaPreferido::V2) {
            return redirect()->route('identidade.perfil.show');
        }

        return view('temas.v2.identidade.senha', ['titulo' => 'Alterar senha']);
    }

    /**
     * Troca a própria senha (LEG-RMA-004) - TEMA V1 como especificação (RN-21).
     */
    public function atualizarSenha(Request $request, TrocarPropriaSenha $trocarPropriaSenha): RedirectResponse
    {
        $dados = $request->validate([
            'senha_atual' => ['required', 'string'],
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $trocarPropriaSenha->trocar($request->user(), $dados['senha_atual'], $dados['nova_senha']);
        } catch (SenhaAtualIncorretaException $e) {
            return back()->withErrors(['senha_atual' => $e->getMessage()]);
        }

        return back()->with('status', 'Senha atualizada com sucesso.');
    }
}
