type LandingOption = {
  value: string;
  label: string;
};

type QuizDefinition = {
  title: string;
  description: string;
  sub_interest: {
    label: string;
    options: LandingOption[];
  };
  trigger: {
    label: string;
    options: LandingOption[];
  };
  concern_label: string;
  concern_placeholder: string;
};

type BrandPresentation = {
  id: number;
  name: string;
  primary_color: string;
  secondary_color: string;
  heading_font: string;
  body_font: string;
  logo_url: string | null;
};

type CaptureForm = {
  age: string;
  sex: string;
  sub_interest: string;
  trigger: string;
  concern: string;
  email: string;
  consent: boolean;
};

export type { BrandPresentation, CaptureForm, LandingOption, QuizDefinition };
