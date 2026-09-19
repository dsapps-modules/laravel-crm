import { useMemo } from 'react';
import type { CrmDashboardProps, DashboardOpportunity, DashboardTask } from './types';
import './CrmDashboard.css';

const money = (value: string | null, currency: string | null) => {
  if (value === null) return '—';
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency || 'BRL', maximumFractionDigits: 0 }).format(Number(value));
};

function StatCard({ label, value, note, tone }: { label: string; value: string; note: string; tone: string }) {
  return <article className={`crm-stat crm-stat--${tone}`}><span className="crm-stat__label">{label}</span><strong>{value}</strong><span className="crm-stat__note">{note}</span></article>;
}

function TaskList({ tasks, onOpen }: { tasks: DashboardTask[]; onOpen?: () => void }) {
  return <section className="crm-panel" aria-labelledby="crm-tasks-title">
    <div className="crm-panel__header"><div><p className="crm-eyebrow">Acompanhamento</p><h2 id="crm-tasks-title">Próximas tarefas</h2></div><button className="crm-link" onClick={onOpen}>Ver agenda <span aria-hidden="true">→</span></button></div>
    {tasks.length === 0 ? <p className="crm-empty" role="status">Nenhuma tarefa pendente. Seu próximo contato está em dia.</p> : <ul className="crm-task-list">{tasks.map(task => <li key={task.id}><span className={`crm-priority crm-priority--${task.priority}`} aria-label={`Prioridade ${task.priority}`} /><div><strong>{task.title}</strong><time dateTime={task.due_at ?? undefined}>{task.due_at ? new Date(task.due_at).toLocaleDateString() : 'Sem prazo'}</time></div><span className="crm-task-arrow" aria-hidden="true">↗</span></li>)}</ul>}
  </section>;
}

function PipelinePreview({ opportunities, onOpen }: { opportunities: DashboardOpportunity[]; onOpen?: () => void }) {
  const stages = useMemo(() => opportunities.reduce<Record<number, DashboardOpportunity[]>>((groups, opportunity) => { (groups[opportunity.pipeline_stage_id] ??= []).push(opportunity); return groups; }, {}), [opportunities]);
  return <section className="crm-panel crm-panel--pipeline" aria-labelledby="crm-pipeline-title">
    <div className="crm-panel__header"><div><p className="crm-eyebrow">Vendas</p><h2 id="crm-pipeline-title">Pipeline em movimento</h2></div><button className="crm-link" onClick={onOpen}>Abrir funil <span aria-hidden="true">→</span></button></div>
    {opportunities.length === 0 ? <p className="crm-empty" role="status">Nenhuma oportunidade aberta ainda.</p> : <div className="crm-pipeline-grid">{Object.entries(stages).slice(0, 4).map(([stage, items]) => <div className="crm-stage" key={stage}><div className="crm-stage__header"><span>Etapa {stage}</span><b>{items.length}</b></div>{items.slice(0, 3).map(item => <button className="crm-opportunity" key={item.id} onClick={onOpen}><span>{item.title}</span><small>{money(item.amount, item.currency)}</small></button>)}</div>)}</div>}
  </section>;
}

export function CrmDashboard({ data, loading = false, error = null, onRefresh, onOpenTasks, onOpenPipeline }: CrmDashboardProps) {
  if (loading) return <div className="crm-dashboard" aria-busy="true" aria-label="Carregando dashboard"><div className="crm-skeleton crm-skeleton--hero" /><div className="crm-skeleton-grid">{[1, 2, 3, 4].map(item => <div className="crm-skeleton" key={item} />)}</div></div>;
  if (error) return <div className="crm-dashboard crm-state"><div className="crm-state__icon" aria-hidden="true">!</div><h1>Não foi possível carregar o dashboard</h1><p>{error}</p>{onRefresh && <button className="crm-button crm-button--dark" onClick={onRefresh}>Tentar novamente</button>}</div>;
  if (!data) return <div className="crm-dashboard crm-state"><div className="crm-state__icon" aria-hidden="true">◌</div><h1>Seu CRM começa aqui</h1><p>Cadastre um contato ou oportunidade para ver sua operação nesta tela.</p></div>;
  const { summary } = data;
  return <main className="crm-dashboard"><header className="crm-dashboard__header"><div><p className="crm-eyebrow">Visão geral · hoje</p><h1>Bom dia, vamos mover negócios.</h1><p className="crm-dashboard__subtitle">Uma leitura rápida do que merece sua atenção agora.</p></div><button className="crm-button crm-button--dark" onClick={onOpenTasks}>+ Nova tarefa</button></header><section className="crm-stats" aria-label="Indicadores principais"><StatCard label="Oportunidades abertas" value={String(summary.open_opportunities)} note="em todos os funis" tone="coral" /><StatCard label="Taxa de ganho" value={summary.win_rate === null ? '—' : `${Math.round(summary.win_rate * 100)}%`} note={summary.win_rate === null ? 'sem fechamentos ainda' : `${summary.win_rate_denominator} fechamentos`} tone="teal" /><StatCard label="Tarefas atrasadas" value={String(summary.overdue_tasks)} note={summary.overdue_tasks ? 'precisam de atenção' : 'operação em dia'} tone="amber" /><StatCard label="Negócios ganhos" value={String(summary.won_opportunities)} note="histórico total" tone="ink" /></section><div className="crm-dashboard__grid"><TaskList tasks={data.tasks} onOpen={onOpenTasks} /><PipelinePreview opportunities={data.opportunities} onOpen={onOpenPipeline} /></div></main>;
}
