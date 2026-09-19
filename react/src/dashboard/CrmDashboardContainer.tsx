import { useCallback, useEffect, useState } from 'react';
import { createCrmClient } from '../index';
import { CrmDashboard } from './CrmDashboard';
import type { DashboardData } from './types';

type DashboardClient = ReturnType<typeof createCrmClient>;

export function CrmDashboardContainer({ client, onOpenTasks, onOpenPipeline }: { client: DashboardClient; onOpenTasks?: () => void; onOpenPipeline?: () => void }) {
  const [data, setData] = useState<DashboardData>();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const refresh = useCallback(async () => {
    setLoading(true); setError(null);
    try {
      const [summary, tasks, opportunities] = await Promise.all([client.getSummary(), client.listTasks(), client.listOpportunities()]);
      setData({ summary, tasks: tasks.data ?? [], opportunities: opportunities.data ?? [] });
    } catch (cause) { setError(cause instanceof Error ? cause.message : 'Erro inesperado ao carregar os dados.'); }
    finally { setLoading(false); }
  }, [client]);
  useEffect(() => { void refresh(); }, [refresh]);
  return <CrmDashboard data={data} loading={loading} error={error} onRefresh={() => void refresh()} onOpenTasks={onOpenTasks} onOpenPipeline={onOpenPipeline} />;
}
