<?php

namespace App\Imports;

use App\Models\Driver;
use App\Services\DriverService;
use App\Support\WhatsAppNormalizer;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DriverImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use Importable, SkipsFailures;

    public function __construct(private readonly DriverService $driverService)
    {
    }

    public function model(array $row)
    {
        $name = trim((string) ($row['name'] ?? ''));
        $transport = trim((string) ($row['transport_type'] ?? ''));
        $whatsapp = WhatsAppNormalizer::normalize($row['whatsapp'] ?? null);
        $status = isset($row['status']) && $row['status'] !== '' ? (int) $row['status'] : 1;
        if ($whatsapp === '') {
            return null;
        }

        // Upsert by branch + whatsapp (simple, avoids duplicates per branch)
        return Driver::updateOrCreate(
            [
                'branch_id' => $this->driverService->branch(),
                'whatsapp'  => $whatsapp,
            ],
            [
                'name'           => $name,
                'transport_type' => $transport !== '' ? $transport : null,
                'status'         => $status,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'transport_type' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:32'],
            'status' => ['nullable', 'numeric'],
        ];
    }
}


