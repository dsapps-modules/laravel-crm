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
- `GET /api/crm/v1/lookups/cnpj/{document}` and `GET /api/crm/v1/lookups/cep/{postalCode}`
- `GET|POST /api/crm/v1/companies`
- `GET|PUT|PATCH|DELETE /api/crm/v1/companies/{company}`
- `GET|POST /api/crm/v1/pipelines`
- `GET|PUT|PATCH|DELETE /api/crm/v1/pipelines/{pipeline}`
- `GET|POST|PATCH /api/crm/v1/opportunities`
- `POST /api/crm/v1/opportunities/{opportunity}/move/{stage}` with `version`
- `GET|POST|PATCH|DELETE /api/crm/v1/tasks`
- `POST /api/crm/v1/tasks/{task}/complete`
- `GET|POST|PATCH|DELETE /api/crm/v1/calendar-events`
- `GET|POST /api/crm/v1/teams` and `POST /api/crm/v1/teams/{team}/members`
- `GET|POST /api/crm/v1/tags` and `POST /api/crm/v1/tags/{tag}/attach`
- `GET|POST /api/crm/v1/custom-fields` and `POST /api/crm/v1/custom-fields/{field}/value`
- `GET|POST /api/crm/v1/segments`
- `GET|POST /api/crm/v1/conversations`
- `POST /api/crm/v1/conversations/{conversation}/messages`
- `GET|POST /api/crm/v1/automations`
- `GET /api/crm/v1/reports/summary`
- `GET|POST /api/crm/v1/email-campaigns`
- `POST /api/crm/v1/email-campaigns/{emailCampaign}/send`
- `GET /api/crm/v1/email-campaigns/{emailCampaign}/report`

Listas aceitam `search` e `per_page` (limitado a 100). Exclusão é arquivamento para preservar histórico. Contatos e empresas aceitam CPF/CNPJ e endereço normalizados; as consultas de CNPJ e CEP são protegidas pela mesma autorização de contatos e usam cache.

Oportunidades usam `amount` decimal e `currency` explícita. Movimentos validam o funil da etapa, incrementam `version` e registram histórico. Uma versão desatualizada retorna `409` e não sobrescreve a alteração concorrente. O fechamento como `lost` exige `loss_reason`. Funis aceitam edição, inclusão, renomeação, remoção e reordenação de etapas; uma etapa com oportunidade ou histórico não pode ser removida, e um funil com vínculos não pode ser excluído.

Tarefas e eventos persistem instantes em UTC e mantêm o timezone de apresentação. Eventos de dia inteiro preservam `all_day=true`. Tarefas podem ser filtradas por `today`, `overdue` e `upcoming`; a próxima ação de um contato/oportunidade é a primeira tarefa pendente por vencimento.

Equipes usam IDs de usuário do hospedeiro, sem acoplar um modelo de usuário ao pacote. Tags e campos personalizados aceitam somente entidades e tipos declarados. Segmentos armazenam filtros JSON declarativos; o pacote não executa código vindo desses filtros.

A inbox persiste conversas, mensagens e eventos de entrada com chave de idempotência. Notas internas nunca passam por adapter externo. O contrato `ChannelAdapter` separa envio e capacidades; neste estágio existe somente fake para testes e o canal aparece como desconectado/bloqueado sem provedor configurado. `CalendarProvider` e seu fake preparam criação, atualização e cancelamento sem alegar sincronização Google Calendar.

As duas preparações de WhatsApp estão documentadas em [docs/whatsapp.md](docs/whatsapp.md): Meta Cloud API oficial (`meta_cloud`) e Uazapi (`uazapi`).

O e-mail usa Brevo (`brevo`) para envio transacional, campanhas de marketing, acompanhamento e recebimento inbound por webhooks Bearer. A configuração é feita por `CRM_EMAIL_BREVO_*`, sem cadastro manual de conta no CRM; tags, campanhas, `replyTo`, DNS e os três webhooks estão em [docs/brevo.md](docs/brevo.md).

Automações aceitam somente as ações `create_task`, `move_stage` e `notify_internal`, com ocorrência idempotente. O resumo de indicadores documenta `win_rate` como ganhas/(ganhas + perdidas) e retorna `null` quando não há denominador.

## Estado da entrega

Implementado e testável localmente: fundação do pacote, migrações, contatos, empresas, arquivamento, paginação, validação, autorização configurável, funis, etapas, oportunidades, valores decimais, auditoria de movimentação, concorrência otimista, tarefas, agenda interna, equipes, tags, segmentos, campos personalizados, inbox interna, contratos de canais/Calendar, automações idempotentes e indicadores básicos.

Ainda não concluído: validação dos adapters com contas externas autorizadas, sincronização Google Calendar, normalização de eventos recebidos em mensagens/conversas e publicação externa. Não há alegação de integração externa sem provedor/credenciais aprovados.

## Desenvolvimento

```bash
composer install
vendor/bin/phpunit
```

O pacote é preparado para um repositório próprio e não publica automaticamente em Packagist ou GitHub.
