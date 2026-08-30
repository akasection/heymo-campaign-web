type BrandPresetOption = {
  value: string;
  label: string;
  description: string;
  instruction?: string;
  stack?: string;
};

type BrandPresetGroup = {
  tone: BrandPresetOption[];
  flow: BrandPresetOption[];
  tense: BrandPresetOption[];
  reading_level: BrandPresetOption[];
};

type BrandDraft = {
  name: string;
  tone_preset: string;
  flow_preset: string;
  tense_preset: string;
  reading_level_preset: string;
  preferred_terms: string[];
  avoided_terms: string[];
  primary_color: string;
  secondary_color: string;
  heading_font: string;
  body_font: string;
};

type BrandPayload = BrandDraft;

type BrandDefaults = Partial<BrandDraft>;

type BrandOptions = {
  presets: BrandPresetGroup;
  fonts: BrandPresetOption[];
  defaults: BrandDefaults;
  limits: {
    max_terms: number;
    max_term_length: number;
  };
};

type BrandPromptDimension = {
  value: string;
  label: string;
  description: string;
  instruction: string;
};

type BrandPromptProfile = {
  version: string;
  brand_name: string;
  voice: {
    tone: BrandPromptDimension;
    flow: BrandPromptDimension;
    tense: BrandPromptDimension;
    reading_level: BrandPromptDimension;
  };
  preferred_terms: string[];
  avoided_terms: string[];
  prompt_skeleton: string;
};

type Brand = BrandDraft & {
  id: number;
  slug: string;
  heading_font_label: string;
  heading_font_stack: string;
  body_font_label: string;
  body_font_stack: string;
  logo_url: string | null;
  archived_at: string | null;
  created_at: string | null;
  updated_at: string | null;
  prompt_profile: BrandPromptProfile;
};

function relativeLuminance(color: string): number | null {
  const match = /^#[0-9a-f]{6}$/i.exec(color);

  if (!match) {
    return null;
  }

  const channels = [0, 2, 4].map(offset => Number.parseInt(color.slice(offset + 1, offset + 3), 16) / 255);
  const linearChannels = channels.map(channel => (channel <= 0.03928 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4));

  return 0.2126 * linearChannels[0] + 0.7152 * linearChannels[1] + 0.0722 * linearChannels[2];
}

function contrastColor(color: string): string {
  const luminance = relativeLuminance(color);
  const darkLuminance = relativeLuminance("#17233D") ?? 0;

  if (luminance === null) {
    return "#17233D";
  }

  const whiteContrast = (1 + 0.05) / (luminance + 0.05);
  const darkContrast = (Math.max(luminance, darkLuminance) + 0.05) / (Math.min(luminance, darkLuminance) + 0.05);

  return whiteContrast >= darkContrast ? "#FFFFFF" : "#17233D";
}

function brandThemeStyle(brand: Pick<Brand, "primary_color" | "secondary_color">): Record<string, string> {
  return {
    "--color-primary": brand.primary_color,
    "--color-primary-content": contrastColor(brand.primary_color),
    "--color-secondary": brand.secondary_color,
    "--color-secondary-content": contrastColor(brand.secondary_color),
  };
}

type BrandCollectionResponse = {
  data: Brand[];
};

type BrandResourceResponse = {
  data: Brand;
  message?: string;
};

function normalizeBrandTerms(terms: string[]): string[] {
  const seen = new Set<string>();
  const normalized: string[] = [];

  for (const rawTerm of terms) {
    const term = rawTerm.replace(/\s+/gu, " ").trim();

    if (term) {
      const key = term.toLocaleLowerCase();

      if (!seen.has(key)) {
        seen.add(key);
        normalized.push(term);
      }
    }
  }

  return normalized;
}

export { brandThemeStyle, contrastColor, normalizeBrandTerms };
export type {
  Brand,
  BrandCollectionResponse,
  BrandDefaults,
  BrandDraft,
  BrandOptions,
  BrandPayload,
  BrandPresetGroup,
  BrandPresetOption,
  BrandPromptDimension,
  BrandPromptProfile,
  BrandResourceResponse,
};
