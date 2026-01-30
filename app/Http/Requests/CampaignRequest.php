<?php

namespace App\Http\Requests;

use App\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignRequest extends FormRequest
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
        return [
            'name'               => [
                'required',
                'string',
                'max:190',
                Rule::unique("campaigns", "name")->ignore($this->route('campaign.id'))
            ],
            'description'        => ['nullable', 'string'],
            'type'               => ['required', 'numeric', Rule::in([CampaignType::PERCENTAGE, CampaignType::ITEM])],
            'discount_value'     => ['nullable', 'numeric', 'max:100'],
            'free_item_id'       => ['nullable', 'integer', 'exists:items,id'],
            'required_purchases' => ['nullable', 'integer', 'min:1'],
            'status'             => ['required', 'numeric', 'max:24'],
            'start_date'         => ['nullable', 'string', 'max:190'],
            'end_date'           => ['nullable', 'string', 'max:190'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // For percentage type, discount_value is required
            if (request('type') == CampaignType::PERCENTAGE && !request('discount_value')) {
                $validator->errors()->add('discount_value', 'The discount value field is required for percentage type campaigns.');
            }

            // For item type, required_purchases is required
            if (request('type') == CampaignType::ITEM && !request('required_purchases')) {
                $validator->errors()->add('required_purchases', 'The required purchases field is required for item type campaigns.');
            }

            // Date validation
            if ($this->isNotNull(request('start_date')) && $this->isNotNull(request('end_date'))) {
                if (strtotime(request('end_date')) < strtotime(request('start_date'))) {
                    $validator->errors()->add('end_date', 'End date can\'t be older than Start date.');
                }
            }
        });
    }

    private function isNotNull($value)
    {
        if ($value === 'null' || $value === null) {
            return false;
        }
        return true;
    }
}
