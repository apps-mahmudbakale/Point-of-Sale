<?php

namespace App\Imports;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection,  WithHeadingRow
{
    public function collection(Collection $rows)
    {
        //dd($rows);
        foreach ($rows as $row) {
            Product::updateOrCreate(
                ['name' => ucfirst($row['product'])],
                [
                    'buying_price' => $row['cost'],
                    'selling_price' => $row['cost'] * 1.5,
                    'qty' => DB::raw('qty + ' . $row['quantity']),
                    'expiry_date' => $row['expiry'],
                ]
            );
        }
    }
}
