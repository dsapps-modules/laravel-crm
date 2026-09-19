# @dsapps/crm-react

Componentes React/TypeScript do CRM sem dependência de biblioteca visual.

```tsx
import { CrmDashboardContainer, createCrmClient } from '@dsapps/crm-react';
import '@dsapps/crm-react/dashboard/CrmDashboard.css';

const client = createCrmClient({ baseUrl: '/api/crm/v1' });
<CrmDashboardContainer client={client} />;
```

O `CrmDashboard` também aceita `data` diretamente para fixtures ou integração com o estado do hospedeiro. Os tokens CSS usam `--crm-*` e podem ser substituídos sem editar o componente. O layout cobre 320px, tablet e desktop, com estados de carregamento, erro e vazio.
