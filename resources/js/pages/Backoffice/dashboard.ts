export type StatusTone = "neutral" | "positive" | "warning" | "danger" | "navy";

export type DashboardMetric = {
  label: string;
  value: string;
  change: string;
  trend: "up" | "down";
};

export type SampleLocation = {
  id: string;
  top: string;
  left: string;
  status: "collected" | "pending";
};

export type SampleRow = {
  campaign: string;
  planned: string;
  collected: string;
  completion: string;
  status: string;
  tone: StatusTone;
};

export type AlertItem = {
  id: number;
  title: string;
  detail: string;
  tone: StatusTone;
};

export type PriorityItem = {
  label: string;
  value: string;
  tone: StatusTone;
};

export type ValidationStep = {
  id: number;
  label: string;
  state: "complete" | "active" | "waiting";
};

export const dashboardMetrics: DashboardMetric[] = [
  {
    label: "Samples processed",
    value: "23,193",
    change: "+12.6% this week",
    trend: "up",
  },
  {
    label: "Active campaigns",
    value: "18",
    change: "3 need attention",
    trend: "down",
  },
  {
    label: "Pending collection",
    value: "433",
    change: "8.3% of total",
    trend: "up",
  },
];

export const sampleLocations: SampleLocation[] = [
  { id: "L-011", top: "17%", left: "25%", status: "collected" },
  { id: "L-012", top: "29%", left: "45%", status: "collected" },
  { id: "L-014", top: "21%", left: "60%", status: "collected" },
  { id: "L-019", top: "42%", left: "31%", status: "pending" },
  { id: "L-023", top: "50%", left: "54%", status: "collected" },
  { id: "L-026", top: "38%", left: "75%", status: "collected" },
  { id: "L-028", top: "65%", left: "22%", status: "collected" },
  { id: "L-031", top: "70%", left: "43%", status: "pending" },
  { id: "L-033", top: "58%", left: "66%", status: "collected" },
  { id: "L-038", top: "79%", left: "81%", status: "collected" },
];

export const sampleRows: SampleRow[] = [
  {
    campaign: "North Bay",
    planned: "620",
    collected: "602",
    completion: "97%",
    status: "Collected",
    tone: "positive",
  },
  {
    campaign: "Easton School",
    planned: "440",
    collected: "416",
    completion: "95%",
    status: "In analysis",
    tone: "navy",
  },
  {
    campaign: "Riverside",
    planned: "330",
    collected: "284",
    completion: "86%",
    status: "Pickup queued",
    tone: "warning",
  },
  {
    campaign: "Marlow Clinic",
    planned: "275",
    collected: "198",
    completion: "72%",
    status: "Needs review",
    tone: "danger",
  },
];

export const trendValues = [32, 46, 38, 62, 51, 73, 64, 82, 71, 88];

export const campaignProgress = [
  { label: "Participation", value: 79, color: "bg-heymo-red" },
  { label: "Collection", value: 61, color: "bg-heymo-navy" },
  { label: "Lab analysis", value: 42, color: "bg-heymo-positive" },
];

export const recentAlerts: AlertItem[] = [
  {
    id: 1,
    title: "Pickup route delayed",
    detail: "Riverside route is 38 minutes behind.",
    tone: "warning",
  },
  {
    id: 2,
    title: "Results ready",
    detail: "North Bay cohort report is ready to review.",
    tone: "positive",
  },
  {
    id: 3,
    title: "Consent check",
    detail: "12 participants need signed consent.",
    tone: "danger",
  },
];

export const priorityItems: PriorityItem[] = [
  { label: "Pending", value: "112", tone: "warning" },
  { label: "Collected", value: "321", tone: "navy" },
  { label: "In analysis", value: "64", tone: "positive" },
];

export const initialValidationSteps: ValidationStep[] = [
  { id: 1, label: "Intake", state: "complete" },
  { id: 2, label: "Consent", state: "complete" },
  { id: 3, label: "Review", state: "active" },
  { id: 4, label: "Release", state: "waiting" },
];
