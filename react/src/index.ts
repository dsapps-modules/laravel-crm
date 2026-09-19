export type CrmContact = {
  id: number;
  first_name: string;
  last_name: string | null;
  email: string | null;
  phone: string | null;
  company_id: number | null;
  archived_at: string | null;
};

export type CrmCompany = {
  id: number;
  name: string;
  legal_name: string | null;
  email: string | null;
  phone: string | null;
  archived_at: string | null;
};

export type CrmClientOptions = {
  baseUrl: string;
  fetch?: typeof fetch;
  headers?: HeadersInit;
};

export function createCrmClient(options: CrmClientOptions) {
  const request = options.fetch ?? fetch;
  const get = async (path: string): Promise<any> => {
    const response = await request(`${options.baseUrl}${path}`, { headers: options.headers });
    if (!response.ok) throw new Error(`CRM request failed: ${response.status}`);
    return response.json();
  };
  return {
    async listContacts(params: URLSearchParams = new URLSearchParams()): Promise<unknown> {
      return get(`/contacts?${params}`);
    },
    getSummary: () => get('/reports/summary'),
    listTasks: () => get('/tasks?scope=upcoming&per_page=5'),
    listOpportunities: () => get('/opportunities?status=open&per_page=100'),
  };
}

export * from './dashboard';
