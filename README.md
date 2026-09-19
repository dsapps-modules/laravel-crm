# Laravel CRM

Pacote Laravel reutilizável para contatos, empresas e relacionamento comercial. A API e o núcleo PHP são independentes do modelo de usuário e da UI do hospedeiro.

## Instalação

```bash
composer require dsapps/laravel-crm
php artisan vendor:publish --tag=crm-config
php artisan migrate
```

Por padrão, a API usa `/api/crm/v1`, middleware `api` e `auth`, e tabelas com prefixo `crm_`. Ajuste `config/crm.php` ou as variáveis `CRM_API_PREFIX`, `CRM_TABLE_PREFIX` e `CRM_USER_MODEL`.

O usuário autenticado precisa autorizar `crm.contacts.manage` e `crm.companies.manage` pelo mecanismo do hospedeiro. Para uma integração com outro sistema de permissões, substitua `crm.authorization_resolver` por uma implementação de `AuthorizationResolver`.

## Endpoints iniciais

- `GET|POST /api/crm/v1/contacts`
- `GET|PUT|PATCH|DELETE /api/crm/v1/contacts/{contact}`
- `GET|POST /api/crm/v1/companies`
- `GET|PUT|PATCH|DELETE /api/crm/v1/companies/{company}`
- `GET|POST /api/crm/v1/pipelines`
- `GET /api/crm/v1/pipelines/{pipeline}`
- `GET|POST|PATCH /api/crm/v1/opportunities`
- `POST /api/crm/v1/opportunities/{opportunity}/move/{stage}` with `version`

Listas aceitam `search` e `per_page` (limitado a 100). Exclusão é arquivamento para preservar histórico.

Oportunidades usam `amount` decimal e `currency` explícita. Movimentos validam o funil da etapa, incrementam `version` e registram histórico. Uma versão desatualizada retorna `409` e não sobrescreve a alteração concorrente. O fechamento como `lost` exige `loss_reason`.

## Estado da entrega

Implementado e testável localmente: fundação do pacote, migrações, contatos, empresas, arquivamento, paginação, validação, autorização configurável, funis, etapas, oportunidades, valores decimais, auditoria de movimentação e concorrência otimista.

Ainda não concluído: funis/oportunidades, tarefas, inbox, adapters reais de WhatsApp/e-mail, sincronização Google Calendar, automações, indicadores e biblioteca React completa. Não há alegação de integração externa sem provedor/credenciais aprovados.

## Desenvolvimento

```bash
composer install
vendor/bin/phpunit
```

O pacote é preparado para um repositório próprio e não publica automaticamente em Packagist ou GitHub.
