export type BrandOption = {
  id: number;
  name: string;
  slug: string;
};

export type OverallMetrics = {
  landings: number;
  landed_visitors: number;
  captures: number;
  consented_visitors: number;
  campaigns_generated: number;
  active_campaigns: number;
  campaigns_sent: number;
  messages_generated: number;
  messages_sent: number;
  messages_opened: number;
  open_rate: number | null;
};

export type FunnelStep = {
  key: string;
  label: string;
  count: number;
  rate: number | null;
};

export type AngleMetric = {
  id: number;
  brand_id: number;
  name: string;
  slug: string;
  landing_identifier: string | null;
  landings: number;
  landed_visitors: number;
  captures: number;
  consented_visitors: number;
  campaigns_generated: number;
  active_campaigns: number;
  messages_sent: number;
  messages_opened: number;
  consent_rate: number | null;
  delivery_success_rate: number | null;
  open_rate: number | null;
};

export type DashboardResponse = {
  data: {
    brands: BrandOption[];
    overall: OverallMetrics;
    funnel: FunnelStep[];
    angles: AngleMetric[];
  };
};
