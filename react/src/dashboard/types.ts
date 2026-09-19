export type DashboardSummary = {
  open_opportunities: number;
  won_opportunities: number;
  lost_opportunities: number;
  overdue_tasks: number;
  win_rate: number | null;
  win_rate_denominator: number;
};

export type DashboardTask = {
  id: number;
  title: string;
  priority: 'low' | 'normal' | 'high' | 'urgent';
  due_at: string | null;
  status: string;
};

export type DashboardOpportunity = {
  id: number;
  title: string;
  pipeline_stage_id: number;
  amount: string | null;
  currency: string | null;
};

export type DashboardData = {
  summary: DashboardSummary;
  tasks: DashboardTask[];
  opportunities: DashboardOpportunity[];
};

export type CrmDashboardProps = {
  data?: DashboardData;
  loading?: boolean;
  error?: string | null;
  onRefresh?: () => void;
  onOpenTasks?: () => void;
  onOpenPipeline?: () => void;
};
