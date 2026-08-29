<?php

namespace App\Http\Requests;

use App\Support\BrandTermNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageBackoffice() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $presence = $this->isMethod('POST') ? 'required' : 'sometimes';
        $termLimit = (int) config('brands.limits.max_terms', 20);
        $termLength = (int) config('brands.limits.max_term_length', 60);

        return [
            'name' => [$presence, 'string', 'max:120'],
            'tone_preset' => [$presence, 'string', Rule::in(array_keys(config('brands.presets.tone', [])))],
            'flow_preset' => [$presence, 'string', Rule::in(array_keys(config('brands.presets.flow', [])))],
            'tense_preset' => [$presence, 'string', Rule::in(array_keys(config('brands.presets.tense', [])))],
            'reading_level_preset' => [$presence, 'string', Rule::in(array_keys(config('brands.presets.reading_level', [])))],
            'preferred_terms' => [$presence, 'array', "max:{$termLimit}"],
            'preferred_terms.*' => ['string', "max:{$termLength}"],
            'avoided_terms' => [$presence, 'array', "max:{$termLimit}"],
            'avoided_terms.*' => ['string', "max:{$termLength}"],
            'primary_color' => [$presence, 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => [$presence, 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'heading_font' => [$presence, 'string', Rule::in(array_keys(config('brands.fonts', [])))],
            'body_font' => [$presence, 'string', Rule::in(array_keys(config('brands.fonts', [])))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if ($this->isMethod('POST')) {
            $input = array_merge([
                'tone_preset' => config('brands.defaults.tone_preset'),
                'flow_preset' => config('brands.defaults.flow_preset'),
                'tense_preset' => config('brands.defaults.tense_preset'),
                'reading_level_preset' => config('brands.defaults.reading_level_preset'),
                'preferred_terms' => [],
                'avoided_terms' => [],
                'primary_color' => config('brands.defaults.primary_color'),
                'secondary_color' => config('brands.defaults.secondary_color'),
                'heading_font' => config('brands.defaults.heading_font'),
                'body_font' => config('brands.defaults.body_font'),
            ], $input);
        }

        foreach (['preferred_terms', 'avoided_terms'] as $field) {
            if (! array_key_exists($field, $input) || ! is_array($input[$field])) {
                continue;
            }

            $allStrings = count(array_filter($input[$field], 'is_string')) === count($input[$field]);

            if ($allStrings) {
                $input[$field] = BrandTermNormalizer::normalize($input[$field]);
            }
        }

        foreach (['primary_color', 'secondary_color'] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = strtoupper($input[$field]);
            }
        }

        $this->replace($input);
    }
}
