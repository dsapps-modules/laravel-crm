# Contatos, documentos e endereço

Contatos e empresas aceitam os campos `document_type`, `document`, `postal_code`, `street`, `number`, `complement`, `district`, `city`, `state` e `country`.

O package normaliza CPF/CNPJ e CEP para somente dígitos, valida os dígitos verificadores e impede documentos duplicados dentro do mesmo tipo de entidade.

## Consulta de CNPJ

```http
GET /api/crm/v1/lookups/cnpj/04252011000110
```

## Consulta de CEP

```http
GET /api/crm/v1/lookups/cep/01001-000
```

As duas rotas exigem autenticação e `crm.contacts.manage`. O retorno é padronizado para que a interface hospedeira preencha os dados da empresa ou endereço.

O provider padrão usa BrasilAPI e pode ser substituído por implementação própria de `CnpjLookupProvider` e `PostalCodeLookupProvider`. Os resultados ficam em cache conforme `CRM_LOOKUPS_CACHE_TTL`.

```env
CRM_LOOKUPS_HTTP_TIMEOUT=8
CRM_LOOKUPS_CACHE_TTL=86400
CRM_LOOKUPS_CNPJ_BASE_URL=https://brasilapi.com.br/api/cnpj/v1
CRM_LOOKUPS_CEP_BASE_URL=https://brasilapi.com.br/api/cep/v1
```
