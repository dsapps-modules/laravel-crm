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
  return {
    async listContacts(params: URLSearchParams = new URLSearchParams()): Promise<unknown> {
      const response = await request(`${options.baseUrl}/contacts?${params}`, { headers: options.headers });
      if (!response.ok) throw new Error(`CRM request failed: ${response.status}`);
      return response.json();
    },
  };
}
