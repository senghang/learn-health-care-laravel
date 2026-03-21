<?php

namespace App\Imports;

use App\Models\CommuneModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CommuneImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // dd($row);
        return new CommuneModel([
            'code'    => $row['code'],      // Column "Code"
            'name_kh' => $row['name_kh'],   // Column "name_kh"
            'name_en' => $row['name_en'],   // Column "Name_en"
            'latitude' => $row['latitude'], // Column "Latitude"
            'longtitude' => $row['longtitude'], // Column "Longitude" Longtitude
            'district' => $row['district'], // Column "District"
            'province' => $row['province'], // Column "Province"
            'note'    => $row['note'],      // Column "Note"
        ]);
    }
}