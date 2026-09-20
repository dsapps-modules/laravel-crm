# Funis e etapas

## Criar

```http
POST /api/crm/v1/pipelines
```

```json
{
  "name": "Vendas",
  "active": true,
  "stages": [
    {"name": "Novo", "position": 0},
    {"name": "Proposta", "position": 1}
  ]
}
```

## Editar

```http
PATCH /api/crm/v1/pipelines/{pipeline}
```

O campo `stages` é opcional quando apenas o nome ou a situação do funil for alterada. Quando enviado, cada etapa existente deve conter seu `id`; etapas novas não devem conter `id`. A posição determina a ordem e é normalizada pelo package.

```json
{
  "name": "Vendas B2B",
  "stages": [
    {"id": 12, "name": "Proposta", "position": 0},
    {"name": "Negociação", "position": 1}
  ]
}
```

Etapas omitidas são removidas somente quando não possuem oportunidades nem histórico de movimentação. Caso contrário, a API retorna `422`.

## Excluir

```http
DELETE /api/crm/v1/pipelines/{pipeline}
```

A exclusão é bloqueada com `422` quando existem oportunidades ou histórico comercial ligado ao funil.
