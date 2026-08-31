<?php

namespace App\Http\Requests;

use App\Support\AngleOfferValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AngleRequest extends FormRequest
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
        $nameLimit = (int) config('angles.limits.name', 120);
        $textLimit = (int) config('angles.limits.text', 2000);
        $landingPage = Rule::unique('angles', 'landing_identifier')
            ->where(fn ($query) => $query
                ->where('brand_id', (int) $this->route('brand'))
                ->whereNull('deleted_at'));

        if (is_numeric($this->route('angle'))) {
            $landingPage->ignore((int) $this->route('angle'));
        }

        return [
            'landing_identifier' => ['nullable', 'string', Rule::in(array_keys(config('landing-pages', []))), $landingPage],
            'name' => [$presence, 'string', "max:{$nameLimit}"],
            'audience' => [$presence, 'string', "max:{$textLimit}"],
            'trigger_moment' => [$presence, 'string', "max:{$textLimit}"],
            'primary_job' => [$presence, 'string', "max:{$textLimit}"],
            'tension' => [$presence, 'string', "max:{$textLimit}"],
            'desired_outcome' => [$presence, 'string', "max:{$textLimit}"],
            'single_promise' => [$presence, 'string', "max:{$textLimit}"],
            'proof' => [$presence, 'string', "max:{$textLimit}"],
            'objection' => [$presence, 'string', "max:{$textLimit}"],
            'offer' => [$presence, 'string', "max:{$textLimit}"],
            'tone' => [$presence, 'string', Rule::in(array_keys(config('angles.tones', [])))],
            'next_step' => [$presence, 'string', "max:{$textLimit}"],
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        foreach ([
            'landing_identifier',
            'name',
            'audience',
            'trigger_moment',
            'primary_job',
            'tension',
            'desired_outcome',
            'single_promise',
            'proof',
            'objection',
            'offer',
            'tone',
            'next_step',
        ] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = preg_replace('/\\s+/u', ' ', trim($input[$field])) ?? '';
            }
        }

        $this->replace($input);
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $offer = $this->input('offer');
            $nextStep = $this->input('next_step');

            if (! is_string($offer) || ! is_string($nextStep)) {
                return;
            }

            foreach (app(AngleOfferValidator::class)->validate($offer, $nextStep) as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
