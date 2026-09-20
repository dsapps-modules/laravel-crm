# WhatsApp: Meta Cloud API e Uazapi

O CRM usa `ChannelAdapter` e escolhe o adapter pela configuração `channel`/`provider` da aplicação. Não é necessário criar uma conta de canal pela API.

## Meta Cloud API oficial

Provider: `meta_cloud`.

Configure as credenciais no `.env`:

```env
CRM_WHATSAPP_META_API_VERSION=v25.0
CRM_WHATSAPP_META_PHONE_NUMBER_ID=...
CRM_WHATSAPP_META_ACCESS_TOKEN=...
CRM_WHATSAPP_META_APP_SECRET=...
```

O envio usa o Graph API `/{version}/{phone-number-id}/messages`, token Bearer e payload de texto com `messaging_product`, `recipient_type`, `to`, `type` e `text`. Configure `CRM_WHATSAPP_META_API_VERSION` e, se necessário, `CRM_WHATSAPP_META_BASE_URL`.

O webhook exige `X-Hub-Signature-256` calculado com o App Secret. A URL é `/api/crm/v1/webhooks/whatsapp/meta_cloud`.

## Uazapi

Provider: `uazapi`.

Configure as credenciais no `.env`:

```env
CRM_WHATSAPP_UAZAPI_TOKEN=...
CRM_WHATSAPP_UAZAPI_WEBHOOK_TOKEN=...
```

O envio usa `POST /send/text`, header `token` e corpo `number`/`text`. Configure `CRM_WHATSAPP_UAZAPI_BASE_URL` quando a conta usar outro host.

O webhook compara o header `token` com `CRM_WHATSAPP_UAZAPI_WEBHOOK_TOKEN`, persiste o evento e deduplica por ID. A URL é `/api/crm/v1/webhooks/whatsapp/uazapi`.

## Conversas

Crie uma conversa informando somente o canal lógico e o provedor:

```json
{
  "channel": "whatsapp",
  "provider": "uazapi"
}
```

Para Meta, use `"provider": "meta_cloud"`. O package resolve o canal automaticamente e não expõe `channel_account_id` na API.

## Segurança e operação

- Nunca coloque tokens em `.env` versionado ou logs.
- Use `status=connected` somente após validar a conta; contas desconectadas são bloqueadas.
- Eventos são persistidos antes do processamento posterior e deduplicados por provedor.
- Notas internas usam `direction=internal` e nunca passam pelo adapter.
- Os testes usam `Http::fake`; ainda falta validar cada provedor com uma conta de teste autorizada.

## Fontes consultadas

- Meta/WhatsApp Cloud API: https://developers.facebook.com/docs/whatsapp/cloud-api/overview
- Coleção oficial Meta Cloud API: https://www.postman.com/meta/whatsapp-business-platform/documentation/wlk6lh4/whatsapp-cloud-api
- Uazapi Swagger: https://api.uzapi.com.br/swagger
- Uazapi termos e limites de uso: https://www.uazapi.com/terms
