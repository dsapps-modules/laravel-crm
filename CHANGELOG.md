# Changelog

## Unreleased

### Added

- Pacote Laravel CRM independente com provider, configuração e migrations versionadas.
- API versionada para contatos e empresas com busca, paginação, validação e arquivamento.
- Vínculos externos com unicidade por origem, tipo e ID.
- Resolver de autorização substituível e cliente React/TypeScript inicial.
- Funis, etapas e oportunidades com API, valor decimal, motivo de perda e histórico de etapas.
- Controle de concorrência otimista para movimentações de Kanban, retornando conflito sem sobrescrita silenciosa.
- Tarefas pendentes/concluídas/canceladas, filtros de acompanhamento e resolução de próxima ação.
- Agenda interna com eventos, timezone de apresentação e persistência de instantes UTC.
- Equipes com atribuição idempotente, tags, segmentos declarativos e campos personalizados tipados.
- Inbox interna com conversas, mensagens idempotentes, deduplicação de eventos recebidos e bloqueio de canais desconectados.
- Contratos `ChannelAdapter` e `CalendarProvider` com fakes para testes, sem declarar integrações externas concluídas.
- Automações declarativas com ações permitidas, execução idempotente e indicadores básicos com denominador explícito.
