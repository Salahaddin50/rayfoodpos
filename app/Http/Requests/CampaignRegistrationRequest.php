<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $campaignId = $this->route('campaign') ? $this->route('campaign')->id : null;
        $registrationId = $this->route('registration') ? $this->route('registration')->id : null;

        return [
            'name'            => ['required', 'string', 'max:190'],
            'email'           => ['nullable', 'email', 'max:190'],
            'phone'           => [
                'required',
                'string',
                'max:20',
                Rule::unique("campaign_registrations", "phone")
                    ->where('campaign_id', $campaignId)
                    ->ignore($registrationId)
            ],
            'status'          => ['nullable', 'numeric', 'max:24'],
            'purchase_count'  => ['nullable', 'integer', 'min:0'],
            'rewards_claimed' => ['nullable', 'integer', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'This phone number is already registered for this campaign.',
        ];
    }
}
