type AngleToneOption = {
  value: string;
  label: string;
  description: string;
};

type AngleDraft = {
  landing_identifier: string | null;
  name: string;
  audience: string;
  trigger_moment: string;
  primary_job: string;
  tension: string;
  desired_outcome: string;
  single_promise: string;
  proof: string;
  objection: string;
  offer: string;
  tone: string;
  next_step: string;
  next_step_url: string;
};

type AnglePayload = AngleDraft;

type Angle = AngleDraft & {
  id: number;
  slug: string;
  brand: {
    id: number;
    name: string;
    slug: string;
  };
  landing_page: {
    identifier: string;
    label: string;
    eyebrow: string | null;
    url: string;
  } | null;
  tone_label: string;
  tone_description: string | null;
  proof_state: "configured" | "missing";
  archived_at: string | null;
  created_at: string | null;
  updated_at: string | null;
};

type AngleOptions = {
  tones: AngleToneOption[];
  landing_pages: {
    value: string;
    label: string;
    description: string | null;
    url: string;
    assigned_angle_id: number | null;
    assigned_angle_name: string | null;
  }[];
  defaults: Partial<AngleDraft>;
  limits: {
    name: number;
    text: number;
  };
};

type AngleCollectionResponse = {
  data: Angle[];
};

type AngleResourceResponse = {
  data: Angle;
  message?: string;
};

export type { Angle, AngleCollectionResponse, AngleDraft, AngleOptions, AnglePayload, AngleResourceResponse, AngleToneOption };
