#!/usr/bin/env bash
#
# Check operacional dos relatorios (ADENDO P0/AD-03/AD-A.2).
#
# Os Feature tests com `RefreshDatabase` provam o SCHEMA do codigo, mas nao o
# banco persistente do Sail - foi exatamente esse ponto cego que produziu o
# incidente RMA-BUG-REL-SCHEMA-001 (migration pendente + HTTP 500 no RPEC).
# Este script faz um smoke HTTP REAL contra a app de pe.
#
# Uso:
#   ./scripts/qa-smoke-relatorios.sh            # usa defaults de QA local
#   RMA_QA_EMAIL=... RMA_QA_PASSWORD=... ./scripts/qa-smoke-relatorios.sh
#
set -euo pipefail

BASE_URL="${RMA_QA_BASE_URL:-http://localhost:8095}"
RMA_QA_EMAIL="${RMA_QA_EMAIL:-superadministrador@rma.local}"
RMA_QA_PASSWORD="${RMA_QA_PASSWORD:-password}"

COOKIES="$(mktemp)"
trap 'rm -f "$COOKIES"' EXIT

falhou=0

token() {
    curl -s -c "$COOKIES" "$BASE_URL/login" \
        | grep -o 'name="_token" value="[^"]*"' \
        | head -1 \
        | sed 's/.*value="//;s/"$//'
}

codigo() {
    curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -c "$COOKIES" "$BASE_URL$1"
}

csrf="$(token)"
login_status="$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -c "$COOKIES" \
    -X POST "$BASE_URL/login" \
    -d "_token=$csrf" \
    -d "email=$RMA_QA_EMAIL" \
    -d "password=$RMA_QA_PASSWORD")"

if [ "$login_status" != "302" ]; then
    echo "FALHOU: login devolveu HTTP $login_status (esperado 302)." >&2
    exit 1
fi

for url in \
    /v1/relatorios/rcd \
    /v1/relatorios/rpec \
    /v1/relatorios/rmpe \
    /v2/relatorios \
    /v2/creditos
do
    status="$(codigo "$url")"
    if [ "$status" = "200" ]; then
        echo "OK   $url -> 200"
    else
        echo "FALHOU $url -> $status (esperado 200)" >&2
        falhou=1
    fi
done

if [ "$falhou" != "0" ]; then
    echo "Check operacional dos relatorios FALHOU. Confira 'artisan migrate:status'." >&2
    exit 1
fi

echo "Check operacional dos relatorios OK."
