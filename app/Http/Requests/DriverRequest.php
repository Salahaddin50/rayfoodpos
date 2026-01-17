<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:190'],
            'transport_type' => ['nullable', 'string', 'max:190'],
            'whatsapp'        => ['required', 'string', 'max:32'],
            'status'         => ['nullable', 'numeric'],
        ];
    }
}


