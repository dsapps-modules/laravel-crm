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

Para enviar, crie uma conta `channel=email`, `provider=brevo` e `status=connected`, depois crie uma mensagem com `recipient`, `subject`, `body`, `message_type` (`text` ou `html`) e `idempotency_key`. O adapter chama `POST /v3/smtp/email`, usando o header `api-key` documentado pela Brevo.

Para receber eventos transacionais, configure na Brevo um webhook para:

```text
/api/crm/v1/webhooks/brevo
```

Configure autenticação Bearer no webhook da Brevo com o mesmo `webhook_token`. O endpoint aceita evento único ou lote, persiste o payload bruto em `crm_inbound_events` e deduplica por `message-id`/ID do evento. A normalização de entregas em mensagens e conversas deve ocorrer em uma etapa posterior, após definir os estados de negócio do host.

O webhook resolve internamente a única configuração `email/brevo` da aplicação. O `channelAccount` não faz parte da URL. A tabela `crm_channel_accounts` é criada pela migration do pacote e armazena a configuração local do canal; ela não representa múltiplas aplicações CRM.

## Operação segura

- Use somente remetentes/domínios verificados na Brevo.
- Nunca versiona API keys, tokens ou payloads reais.
- Só marque a conta como `connected` após validar credenciais e remetente.
- O pacote não envia mensagens reais nos testes; eles usam `Http::fake`.

## Fontes oficiais

- Envio transacional: https://developers.brevo.com/reference/send-transac-email
- Webhooks: https://developers.brevo.com/docs/how-to-use-webhooks
- Segurança dos webhooks: https://developers.brevo.com/docs/secured-webhooks
