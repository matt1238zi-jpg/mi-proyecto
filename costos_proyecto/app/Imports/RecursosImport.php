<?php

namespace App\Imports;

use Maatwebsite\Excel\Facades\Excel;   // ✅ ESTA es la buena

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class RecursosImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // NO guardamos aquí. Solo devolvemos la colección (el Controller la procesa).
        return $rows;
    }
}
