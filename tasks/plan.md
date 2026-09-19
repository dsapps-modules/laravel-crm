# Plano de implementação: DS Apps Laravel CRM

## Visão geral

Pacote Laravel independente para CRM comercial, com API versionada e biblioteca React separada. A primeira entrega implementa a fundação instalável e o fluxo de pessoas/empresas; as demais capacidades seguem em fatias verticais.

## Decisões

- Pacote Composer `dsapps/laravel-crm`, sem dependência de modelos do hospedeiro.
- Tabelas com prefixo configurável, `crm_` por padrão, e API `/api/crm/v1`.
- Autenticação usa o middleware configurado pelo hospedeiro; autorização é delegada a um resolver configurável.
- Vínculos externos identificam origem, tipo e ID sem sobrescrever dados do hospedeiro.
- WhatsApp, e-mail e Calendar serão adapters; sem provedor aprovado, não serão declarados como integração concluída.

## Fases

### Fundação

- [ ] Contatos, empresas, vínculos externos, configuração, provider e migrations
- [ ] API autenticada/configurável, validação, paginação e autorização
- [ ] Testes de instalação e comportamento

### CRM comercial

- [x] Funis, etapas, oportunidades, concorrência e auditoria
- [x] Tarefas, agenda interna, lembretes e próxima ação
- [ ] Equipes, atribuição, tags, segmentos e campos personalizados

### Comunicação e automação

- [ ] Inbox e contratos de canal
- [ ] Adapters reais após escolha de provedores
- [ ] CalendarProvider, fake e preparação para Google Calendar
- [ ] Automações idempotentes, indicadores e dashboard

### Publicação

- [ ] React/TypeScript com exports estáveis e tema por slots
- [ ] OpenAPI, guia de atualização, workers/scheduler e matriz de suporte
- [ ] Suite completa, revisão de segurança, changelog e release

## Riscos

| Risco | Mitigação |
| --- | --- |
| Modelo de usuário varia entre hospedeiros | Middleware e resolver injetáveis; nenhum modelo concreto no núcleo |
| Integrações têm estados e capacidades diferentes | Contratos por canal, IDs externos opacos e estados explícitos |
| Escopo grande demais para uma entrega | Fatias verticais com testes e commits independentes |

## Pendências externas

- Provedor de WhatsApp ainda não definido.
- Solução de envio/recebimento de e-mail ainda não definida.
- Credenciais reais não estão autorizadas nesta etapa.
