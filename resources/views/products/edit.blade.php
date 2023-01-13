@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Products</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('app.products.index') }}">Products</a></li>
                            <li class="breadcrumb-item active">Update Product</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- New User form elements -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Update Product</h3>
                    </div>
                    <!-- /.card-header -->
                    <form action="{{route('app.products.update', $product->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <!-- form start -->
                        <div class="card-body">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" value="{{old('name', isset($product) ? $product->name : '')}}" class="form-control" placeholder="Name">
                            </div>
                            <div class="form-group">
                                <label>Buying Price</label>
                                <input type="number" name="buying_price" value="{{old('name', isset($product) ? $product->buying_price : '')}}" class="form-control" placeholder="Buying Price">
                            </div>
                            <div class="form-group">
                                <label>Selling Price</label>
                                <input type="number" name="selling_price" value="{{old('name', isset($product) ? $product->selling_price : '')}}" class="form-control" placeholder="Selling Price">
                            </div>
                            <div class="form-group">
                                <label>Quantity in Stock</label>
                                <input type="number" name="qty" value="{{old('name', isset($product) ? $product->qty : '')}}" class="form-control" placeholder="Quantity in Stock">
                            </div>
                            <div class="form-group">
                                <label>Expiry Date</label>
                                <input type="date" name="expiry_date" value="{{old('name', isset($product) ? $product->expiry_date : '')}}" class="form-control" placeholder="Expiry Date">
                            </div>
                            
                            <div class="form-group">
                                <label>Store</label>
                                <select name="store_id" class="form-control">
                                    <option selected value="{{$product->store->id}}">{{$product->store->name}}</option>  
                                    @foreach($stores as $store)
                                            <option value="{{$store->id}}">{{$store->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
                <!-- /.card -->
                <!-- /.row (main row) -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
@endsection
