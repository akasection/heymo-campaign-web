<?php

namespace App\Http\Requests;

use App\Support\CaptureMetadata;
use App\Support\LandingPageRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CaptureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $identifier = is_string($this->input('landing_identifier')) ? $this->input('landing_identifier') : '';
        $registry = app(LandingPageRegistry::class);

        return [
            'brand_id' => ['required', 'integer', 'min:1'],
            'landing_identifier' => ['required', 'string', Rule::in($registry->identifiers())],
            'preferred_name' => ['required', 'string', 'max:'.config('capture.limits.preferred_name', 80)],
            'age_group' => ['required', 'string', Rule::in(array_keys(config('capture.age_groups', [])))],
            'sex' => ['required', 'string', Rule::in(['female', 'male', 'intersex', 'prefer_not_to_say'])],
            'sub_interest' => ['required', 'string', 'max:255', Rule::in($registry->choiceValues($identifier, 'sub_interest'))],
            'trigger' => ['required', 'string', 'max:255', Rule::in($registry->choiceValues($identifier, 'trigger'))],
            'concern' => ['required', 'string', 'max:'.config('capture.limits.concern', 1000)],
            'email' => ['required', 'email:rfc', 'max:255'],
            'consent' => ['accepted'],
            'fingerprint' => ['nullable', 'uuid'],
            'session_duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'attribution' => ['nullable', 'array'],
            'attribution.utm_source' => ['nullable', 'string', 'max:255'],
            'attribution.utm_medium' => ['nullable', 'string', 'max:255'],
            'attribution.utm_campaign' => ['nullable', 'string', 'max:255'],
            'attribution.utm_term' => ['nullable', 'string', 'max:255'],
            'attribution.utm_content' => ['nullable', 'string', 'max:255'],
            'attribution.referrer' => ['nullable', 'string', 'max:1000'],
            'device' => ['nullable', 'array'],
            'device.type' => ['nullable', 'string', 'max:32'],
            'device.os' => ['nullable', 'string', 'max:64'],
            'device.browser' => ['nullable', 'string', 'max:64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        foreach (['landing_identifier', 'preferred_name', 'sex', 'sub_interest', 'trigger', 'concern'] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = preg_replace('/\\s+/u', ' ', trim($input[$field])) ?? '';
            }
        }

        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = mb_strtolower(trim($input['email']));
        }

        if (array_key_exists('attribution', $input)) {
            $input['attribution'] = CaptureMetadata::sanitizeAttribution($input['attribution'] ?? null);
        }

        if (array_key_exists('device', $input)) {
            $input['device'] = CaptureMetadata::sanitizeDevice($input['device'] ?? null);
        }

        $this->replace($input);
    }
}
