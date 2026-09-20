# E-mail com Brevo

O provider de e-mail é `brevo`. As credenciais da conta são criptografadas pelo cast do modelo `ChannelAccount`.

```json
{
  "api_key": "xkeysib-...",
  "sender_email": "crm@example.com",
  "sender_name": "CRM",
  "webhook_token": "um-token-aleatorio"
}
```

Para enviar, crie uma conta `channel=email`, `provider=brevo` e `status=connected`, depois crie uma mensagem com `recipient`, `subject`, `body`, `message_type` (`text` ou `html`) e `idempotency_key`. O adapter chama `POST /v3/smtp/email`, usando o header `api-key` documentado pela Brevo. Cada envio inclui a tag configurada em `CRM_EMAIL_BREVO_APP_TAG`, permitindo separar eventos desta aplicação de outros sistemas na mesma conta Brevo.

Quando `CRM_EMAIL_BREVO_REPLY_DOMAIN` estiver configurado, a primeira mensagem de uma conversa recebe automaticamente um `replyTo` no formato `{token}@{domínio}`. O token é persistido na conversa para associar respostas futuras.

## Webhooks

Configure dois webhooks separados na Brevo:

### Acompanhamento transacional

Use o tipo `transactional` para eventos como `delivered`, `opened`, `click`, `hardBounce`, `softBounce`, `blocked`, `spam`, `invalid`, `deferred` e `unsubscribed`.

```text
/api/crm/v1/webhooks/brevo
```

O alias explícito também está disponível em `/api/crm/v1/webhooks/brevo/transactional`. Configure autenticação Bearer com o mesmo `webhook_token`. Eventos são filtrados pela tag da aplicação, persistidos em `crm_inbound_events`, deduplicados por mensagem/evento/instante e atualizam o status da mensagem local.

### Recebimento de respostas

Use o tipo `inbound`, evento `inboundEmailProcessed`, domínio de recebimento dedicado e a URL:

```text
/api/crm/v1/webhooks/brevo/inbound
```

O domínio de recebimento deve ser diferente do domínio usado para envio. Na zona DNS do subdomínio, configure:

```text
MX  reply.seudominio.com.br  10  inbound1.sendinblue.com.
MX  reply.seudominio.com.br  20  inbound2.sendinblue.com.
```

O domínio também precisa estar verificado na Brevo. O endpoint aceita o payload `items`, deduplica por UUID/Message-ID, associa respostas por `InReplyTo` ou pelo token do endereço `replyTo`, cria a mensagem inbound na conversa e cria uma nova conversa quando não encontra associação.

O webhook resolve internamente a única configuração `email/brevo` da aplicação. O `channelAccount` não faz parte da URL. A tabela `crm_channel_accounts` é criada pela migration do pacote e armazena a configuração local do canal; ela não representa múltiplas aplicações CRM.

## Operação segura

- Use somente remetentes/domínios verificados na Brevo.
- Nunca versiona API keys, tokens ou payloads reais.
- Só marque a conta como `connected` após validar credenciais e remetente.
- O pacote não envia mensagens reais nos testes; eles usam `Http::fake`.
- Eventos inbound podem conter PII e anexos; o host deve definir retenção e política de acesso antes da produção.

## Fontes oficiais

- Envio transacional: https://developers.brevo.com/reference/send-transac-email
- Webhooks: https://developers.brevo.com/docs/how-to-use-webhooks
- Segurança dos webhooks: https://developers.brevo.com/docs/secured-webhooks
