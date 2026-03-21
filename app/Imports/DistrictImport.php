<?php

namespace App\Imports;

use App\Models\DistrictModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistrictImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // dd($row);
        return new DistrictModel([
            'code'    => $row['code'],      // Column "Code"
            'name_kh' => $row['name_kh'],   // Column "name_kh"
            'name_en' => $row['name_en'],   // Column "Name_en"
            'latitude' => $row['latitude'], // Column "Latitude"
            'longtitude' => $row['longtitude'], // Column "Longitude" Longtitude
            'province' => $row['province'], // Column "Province"
            'postal'  => $row['postal'],    // Column "Postal"
            'note'    => $row['note'],      // Column "Note"
        ]);
    }
}

