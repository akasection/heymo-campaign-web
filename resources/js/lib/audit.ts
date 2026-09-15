export type AuditVisitor = {
  id: number;
  preferred_name: string | null;
  email: string;
};

export type AuditRecord = {
  id: number;
  captured_at: string | null;
  landing_identifier: string;
  fingerprint: string | null;
  session_duration_seconds: number | null;
  attribution: Record<string, string> | null;
  device: Record<string, string> | null;
  age_group: string;
  sex: string;
  sub_interest: string;
  trigger: string;
  concern: string;
  visitor: AuditVisitor;
  angle: { id: number; name: string; slug: string };
  brand: { id: number; name: string };
  campaigns_count: number;
  campaigns_generated_count: number;
  messages_sent_count: number;
  messages_opened_count: number;
};

export type AuditCollectionResponse = {
  data: AuditRecord[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
  };
};

export type DeliveryEventDetail = {
  attempt_number: number;
  status: string;
  to_address: string | null;
  error_message: string | null;
  created_at: string | null;
};

export type EngagementEventDetail = {
  type: string;
  occurred_at: string | null;
};

export type ParsedMessage = {
  position?: number;
  role?: string;
  subject: string;
  headline: string;
  body_paragraphs: string[];
  evidence_ids?: string[];
};

export type GenerationAttemptDetail = {
  id: number;
  attempt_number: number;
  provider: string | null;
  model: string | null;
  prompt_version: string | null;
  status: string;
  violations: string[] | null;
  parsed_messages: ParsedMessage[] | null;
  error_message: string | null;
  created_at: string | null;
};

export type CampaignMessageDetail = {
  id: number;
  sequence_position: number;
  role: string;
  subject: string;
  headline: string;
  body_paragraphs: string[];
  evidence_ids: string[];
  status: string;
  scheduled_at: string | null;
  sent_at: string | null;
  opened_at: string | null;
  delivery_events: DeliveryEventDetail[];
  engagement_events: EngagementEventDetail[];
};

export type CampaignDetail = {
  id: number;
  status: string;
  presentation_profile: Record<string, string> | null;
  prompt_version: string | null;
  created_at: string | null;
  generation_attempts: GenerationAttemptDetail[];
  messages: CampaignMessageDetail[];
};

export type AuditDetail = {
  id: number;
  captured_at: string | null;
  landing_identifier: string;
  fingerprint: string | null;
  session_duration_seconds: number | null;
  attribution: Record<string, string> | null;
  device: Record<string, string> | null;
  age_group: string;
  sex: string;
  sub_interest: string;
  trigger: string;
  concern: string;
  visitor: AuditVisitor & { created_at: string | null };
  angle: {
    id: number;
    name: string;
    slug: string;
    landing_identifier: string | null;
    brand: { id: number; name: string };
  };
  landing_events: {
    id: number;
    landed_at: string | null;
    landing_identifier: string;
    attribution: Record<string, string> | null;
    device: Record<string, string> | null;
  }[];
  consent_records: {
    channel: string;
    email: string;
    source: string;
    policy_version: string;
    consented_at: string | null;
  }[];
  suppressions: {
    channel: string;
    reason: string;
    source: string;
    suppressed_at: string | null;
  }[];
  campaigns: CampaignDetail[];
};

export type AuditDetailResponse = {
  data: AuditDetail;
};

export type EmailPreviewResponse = {
  data: {
    subject: string;
    html: string;
  };
};

export type EmailPreviewPayload = {
  label: string;
  subject: string;
  html: string;
  violations: string[] | null;
};
