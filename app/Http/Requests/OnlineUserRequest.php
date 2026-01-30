<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnlineUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'whatsapp'    => ['required', 'string', 'max:32'],
            'location'    => ['nullable', 'string', 'max:5000'],
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
        ];
    }
}


