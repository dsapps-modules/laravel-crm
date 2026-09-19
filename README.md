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
- `GET|POST|PATCH|DELETE /api/crm/v1/tasks`
- `POST /api/crm/v1/tasks/{task}/complete`
- `GET|POST|PATCH|DELETE /api/crm/v1/calendar-events`
- `GET|POST /api/crm/v1/teams` and `POST /api/crm/v1/teams/{team}/members`
- `GET|POST /api/crm/v1/tags` and `POST /api/crm/v1/tags/{tag}/attach`
- `GET|POST /api/crm/v1/custom-fields` and `POST /api/crm/v1/custom-fields/{field}/value`
- `GET|POST /api/crm/v1/segments`
- `GET|POST /api/crm/v1/channel-accounts`
- `GET|POST /api/crm/v1/conversations`
- `POST /api/crm/v1/conversations/{conversation}/messages`
- `GET|POST /api/crm/v1/automations`
- `GET /api/crm/v1/reports/summary`

Listas aceitam `search` e `per_page` (limitado a 100). Exclusão é arquivamento para preservar histórico.

Oportunidades usam `amount` decimal e `currency` explícita. Movimentos validam o funil da etapa, incrementam `version` e registram histórico. Uma versão desatualizada retorna `409` e não sobrescreve a alteração concorrente. O fechamento como `lost` exige `loss_reason`.

Tarefas e eventos persistem instantes em UTC e mantêm o timezone de apresentação. Eventos de dia inteiro preservam `all_day=true`. Tarefas podem ser filtradas por `today`, `overdue` e `upcoming`; a próxima ação de um contato/oportunidade é a primeira tarefa pendente por vencimento.

Equipes usam IDs de usuário do hospedeiro, sem acoplar um modelo de usuário ao pacote. Tags e campos personalizados aceitam somente entidades e tipos declarados. Segmentos armazenam filtros JSON declarativos; o pacote não executa código vindo desses filtros.

A inbox persiste conversas, mensagens e eventos de entrada com chave de idempotência. Notas internas nunca passam por adapter externo. O contrato `ChannelAdapter` separa envio e capacidades; neste estágio existe somente fake para testes e o canal aparece como desconectado/bloqueado sem provedor configurado. `CalendarProvider` e seu fake preparam criação, atualização e cancelamento sem alegar sincronização Google Calendar.

Automações aceitam somente as ações `create_task`, `move_stage` e `notify_internal`, com ocorrência idempotente. O resumo de indicadores documenta `win_rate` como ganhas/(ganhas + perdidas) e retorna `null` quando não há denominador.

## Estado da entrega

Implementado e testável localmente: fundação do pacote, migrações, contatos, empresas, arquivamento, paginação, validação, autorização configurável, funis, etapas, oportunidades, valores decimais, auditoria de movimentação, concorrência otimista, tarefas, agenda interna, equipes, tags, segmentos, campos personalizados, inbox interna, contratos de canais/Calendar, automações idempotentes e indicadores básicos.

Ainda não concluído: adapters reais de WhatsApp/e-mail, sincronização Google Calendar, dashboard React completo e publicação externa. Não há alegação de integração externa sem provedor/credenciais aprovados.

## Desenvolvimento

```bash
composer install
vendor/bin/phpunit
```

O pacote é preparado para um repositório próprio e não publica automaticamente em Packagist ou GitHub.
