<?php
namespace App\Classes;

use NumberFormatter;
use Illuminate\Support\Facades\DB;

class CustomReport
{

    public function filter($request)
    {
        $query = DB::table('sales')
            ->select('sales.*', 'products.name as product', 'users.name as user')
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->join('users', 'users.id', '=', 'sales.user_id');

            if($request->has('user') && !empty($request->user))
            {
                $query->where('users.id', $request->user);
            }

            if($request->has('from') && !empty($request->from) && $request->has('to') && !empty($request->to))
            {
                $query->whereBetween('sales.created_at', array($request->from, $request->to));
            }

            $sum = DB::table('sales')
            ->selectRaw('sum(sales.amount) as total')
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.created_at', array($request->from, $request->to));

        $sum->first();
        $inWords = new NumberFormatter("En", NumberFormatter::SPELLOUT);
        $words = $inWords->format($sum->first()->total);
            return  [
             'filter' =>  $query->get(),
             'words' => $words,
             'sum' => $sum->first()->total
            ];

    }
}
