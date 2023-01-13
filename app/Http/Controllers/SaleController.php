<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sales.index');
    }

    public function createRandomPassword()
    {
        $station = auth()->user()->station->name;
        $sum = DB::table('sales')->where('station_id', auth()->user()->station->id)->count() + 1;
        $pass = substr($station, 0,3)."".date('d')."".date('m')."".date('y')."-".sprintf('%04d', $sum);
        return strtoupper($pass);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (empty(session('invoice'))) {
            session()->put('invoice', $this->createRandomPassword());
        }
        $invoice = session()->get('invoice');
        return view('sales.create', compact('invoice'));
    }

    public function searchItem(Request $request)
    {

        $keyword = trim($request->search_keyword, "");

        $products = DB::table('station_products')
            ->join('products', 'products.id', '=', 'station_products.product_id')
            ->where('products.name', 'like', '%' . $keyword . '%')
            ->where('station_products.quantity', '>=', '1')
            ->get();
        echo '<ul class="nav flex-column">';
        foreach ($products as $product) {
            $url = base64_encode($product->product_id . ',' . session()->get('invoice') . ',' . $product->selling_price);
            echo '<li class="nav-item">
                <a href="' . route('app.sales.cart', $url) . '" class="nav-link">
                  <strong>' . $product->name . '</strong>
                  <span class="float-right badge bg-primary">&#8358; ' . number_format($product->selling_price, 2) . '</span>
                </a>
              </li>';
        }
        echo '</ul>';
    }

    public function cart($invoice)
    {
        $data = explode(',', base64_decode($invoice));
        // dd($data);
        $qty = 1;
        $cart = DB::table('sales_order')
            ->updateOrInsert(
                ['product_id' => $data[0], 'invoice' => $data[1]],
                [
                    'quantity' => DB::raw('quantity + '.$qty),
                    'amount' => DB::raw('amount + '.$data[2]),
                    'created_at' => DB::raw('now()'),
                    'updated_at' => DB::raw('now()'),
                ]
            );

        return redirect()->route('app.sales.create');
    }
    public function saveSale($invoice)
    {
        $sales_order = DB::table('sales_order')
                            ->where('invoice', $invoice)
                            ->get();
        foreach ($sales_order as $order){
            $sales = Sale::create([
                'invoice' => $invoice,
                'product_id' => $order->product_id,
                'qty' => $order->quantity,
                'amount' => $order->amount,
                'station_id' => auth()->user()->station->id ? : '0',
                'user_id' => auth()->user()->id
            ]);
            $product = DB::table('station_products')
                            ->where('product_id',  $order->product_id)
                            ->update(['quantity' => DB::raw('quantity - '.$order->quantity)]);
        }
        $invoice = Invoice::create([
            'invoice' => $invoice,
            'created_at' => now(),
        ]);
        DB::table('sales_order')->where('invoice', $invoice)->delete();
        session()->forget('invoice');
        return redirect()->route('app.sales.create')->with('success', 'Sales Saved');
    }
    public function saveSalePrint($invoice)
    {
        $sales_order = DB::table('sales_order')
                            ->where('invoice', $invoice)
                            ->get();
        foreach ($sales_order as $order){
            $sales = Sale::create([
                'invoice' => $invoice,
                'product_id' => $order->product_id,
                'qty' => $order->quantity,
                'amount' => $order->amount,
                'station_id' => auth()->user()->station->id ? : '0',
                'user_id' => auth()->user()->id
            ]);
            $product = DB::table('station_products')
                ->where('product_id',  $order->product_id)
                ->update(['quantity' => DB::raw('quantity - '.$order->quantity)]);
        }
        $invoices = Invoice::create([
            'invoice' => $invoice,
            'created_at' => now(),
        ]);
        DB::table('sales_order')->where('invoice', $invoice)->delete();
        session()->forget('invoice');
        return redirect()->route('app.sales.print', $invoice);
    }

    public function cancelSale($invoice)
    {
        DB::table('sales_order')->where('invoice', $invoice)->delete();
        session()->forget('invoice');
        return redirect()->route('app.sales.create');
    }

    public function printInvoice($invoice)
    {
        $items = DB::table('sales')
            ->select('sales.*','products.name as product', 'products.selling_price')
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->where('sales.invoice', $invoice)
            ->get();
            $sum = DB::table('sales')
            ->select(DB::raw('SUM(amount) as sum'))
            ->where('invoice', $invoice)
            ->first();
            $user = DB::table('sales')
            ->select('users.name')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->where('sales.invoice', $invoice)
            ->first();
        return view('sales.print', compact('items', 'invoice', 'sum', 'user'));
    }

    public function returnShow($invoice)
    {
        
        $items = DB::table('sales')
        ->select('sales.*','products.name as product', 'products.selling_price')
        ->join('products', 'products.id', '=', 'sales.product_id')
        ->where('sales.invoice', $invoice)
        ->get();
        // dd($items);
        return view('sales.return-show', compact('items'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function syncStore(Request $request)
    {
        $products = Product::updateOrCreate(
            ['name' => $request->name, 'store_id' => $request->store_id],
            [
                'buying_price' => $request->buying_price, 
                'selling_price' => $request->selling_price,
                'qty' => $request->qty,
                'expiry_date' => $request->expiry_date
            ]);
          
        if($products){
            return response()->json([
                'success' => true,
                'message' => 'Activity successfully created.',
            ]);
        }
    }


    public function store(Request $request)
    {
        
           $sales = DB::table('sales')->insert([
                'invoice' => $request->invoice,
                'product_id' => $request->product_id,
                'user_id' => $request->user_id,
                'amount'  => $request->amount,
                'qty' => $request->qty,
                'station_id' => $request->station_id,
                'created_at' => now(),
            ]);
        if($sales){
            return response()->json([
                'success' => true,
                'message' => 'Activity successfully created.',
            ]);
        }
    }


    public function synced(Request $request){
        DB::table('sales')
        ->where('invoice', $request->invoice)
        ->where('product_id', $request->product_id)
        ->update(['synced' => true]);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();

        return back()->with('Sale Deleted');
    }
}
