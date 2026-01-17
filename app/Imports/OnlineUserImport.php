<?php

namespace App\Imports;

use App\Models\OnlineUser;
use App\Traits\DefaultAccessModelTrait;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OnlineUserImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use Importable, SkipsFailures, DefaultAccessModelTrait;

    public function model(array $row)
    {
        $whatsapp = trim((string) ($row['whatsapp'] ?? ''));
        $location = isset($row['location']) ? trim((string) $row['location']) : null;

        return OnlineUser::updateOrCreate(
            [
                'branch_id' => $this->branch(),
                'whatsapp'  => $whatsapp,
            ],
            [
                'location' => $location !== '' ? $location : null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'whatsapp' => ['required', 'string', 'max:32'],
            'location' => ['nullable', 'string', 'max:5000'],
        ];
    }
}


