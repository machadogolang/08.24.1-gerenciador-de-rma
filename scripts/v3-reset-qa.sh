#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
    echo "Arquivo .env ausente. Copie .env.example antes de continuar." >&2
    exit 1
fi

if ! grep -Eq '^APP_ENV=local$' .env; then
    echo "Reset de QA recusado: APP_ENV precisa ser local." >&2
    exit 1
fi

if [ ! -x ./vendor/bin/sail ]; then
    echo "Laravel Sail ausente. Execute composer install antes de continuar." >&2
    exit 1
fi

echo "==> Recriando o banco V3 local com dados ficticios de QA..."
./vendor/bin/sail artisan migrate:fresh --seed

echo "==> Validando contagens e usuarios esperados..."
./vendor/bin/sail artisan tinker --execute='
$esperados = ["bloqueado@rma.local", "leitura@rma.local", "operador@rma.local", "supervisor@rma.local", "superadministrador@rma.local"];
$presentes = \App\Models\User::query()->whereIn("email", $esperados)->count();
$contagens = [
    "usuarios_qa" => $presentes,
    "clientes" => \App\Models\Cliente::query()->count(),
    "fabricantes" => \App\Models\Fabricante::query()->count(),
    "fornecedores" => \App\Models\Fornecedor::query()->count(),
    "assistencias_tecnicas" => \App\Models\AssistenciaTecnica::query()->count(),
    "rmas" => \App\Models\Rma::query()->count(),
];
if ($contagens !== ["usuarios_qa" => 5, "clientes" => 30, "fabricantes" => 10, "fornecedores" => 10, "assistencias_tecnicas" => 5, "rmas" => 60]) {
    throw new \RuntimeException("Base QA criada com contagens inesperadas: ".json_encode($contagens));
}
echo json_encode($contagens, JSON_PRETTY_PRINT);
'

echo "==> Base QA pronta em http://localhost:8095/login"
echo "    Usuarios: {bloqueado,leitura,operador,supervisor,superadministrador}@rma.local"
echo "    Senha local: password"
