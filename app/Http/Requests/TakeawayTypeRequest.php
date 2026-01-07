<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Status;
use App\Traits\DefaultAccessModelTrait;

class TakeawayTypeRequest extends FormRequest
{
    use DefaultAccessModelTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Auto-fill branch_id from default access if frontend didn't send it
        $branchId = $this->input('branch_id');
        if ($branchId === null || $branchId === '') {
            $this->merge(['branch_id' => $this->branch()]);
        }

        // Default status to ACTIVE if not provided
        $status = $this->input('status');
        if ($status === null || $status === '') {
            $this->merge(['status' => Status::ACTIVE]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $takeawayTypeId = $this->route('takeawayType');
        $takeawayTypeId = $takeawayTypeId instanceof \App\Models\TakeawayType ? $takeawayTypeId->id : $takeawayTypeId;
        $branchId       = $this->input('branch_id') ?? $this->branch();
        
        return [
            'name'       => [
                'required',
                'string',
                'max:90',
                Rule::unique('takeaway_types', 'name')->where(function ($query) {
                    return $query->where('branch_id', $this->input('branch_id'));
                })->ignore($takeawayTypeId),
            ],
            'branch_id'  => ['required', 'numeric'],
            'status'     => ['required', 'numeric'],
            'sort_order' => ['nullable', 'numeric'],
        ];
    }
}

