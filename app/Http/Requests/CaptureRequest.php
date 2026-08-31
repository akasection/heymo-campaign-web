<?php

namespace App\Http\Requests;

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
            'age' => ['required', 'integer', 'between:18,120'],
            'sex' => ['required', 'string', Rule::in(['female', 'male', 'intersex', 'prefer_not_to_say'])],
            'sub_interest' => ['required', 'string', 'max:255', Rule::in($registry->choiceValues($identifier, 'sub_interest'))],
            'trigger' => ['required', 'string', 'max:255', Rule::in($registry->choiceValues($identifier, 'trigger'))],
            'concern' => ['required', 'string', 'max:'.config('capture.limits.concern', 1000)],
            'email' => ['required', 'email:rfc', 'max:255'],
            'consent' => ['accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        foreach (['landing_identifier', 'sex', 'sub_interest', 'trigger', 'concern'] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = preg_replace('/\\s+/u', ' ', trim($input[$field])) ?? '';
            }
        }

        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = mb_strtolower(trim($input['email']));
        }

        $this->replace($input);
    }
}
