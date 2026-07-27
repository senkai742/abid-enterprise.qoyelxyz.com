@extends('admin.layouts.admin',["content-header"=>"Dashboard"])
@section('content-header')
@section('content')
<div class="container-fluid">
   <div class="row">

       <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-danger">
            <div class="inner">
               <h3>{{config('settings.currency_symbol')}} {{number_format(($payment_customer_today-$payment_supplier_today), 2)}}</h3>
               <p>{{ __('Today Cash') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('admin.expense.sales-details') }}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>

      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-info">
            <div class="inner">
               <h3>{{config('settings.currency_symbol')}} {{number_format($today_sales, 2)}}</h3>
               <p>{{ __('Today Sales') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-bag"></i>
            </div>
            <a href="{{route('admin.sales.index')}}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-success">
            <div class="inner">
               <h3>{{config('settings.currency_symbol')}} {{number_format($today_purchase, 2)}}</h3>
               <p>{{ __('Today Purchase') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{route('admin.purchases.index')}}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->

      <!-- ./col -->
      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-pink" style="background-color: #e83e8c !important; color: #fff;">
            <div class="inner">
               <h3>{{config('settings.currency_symbol')}} {{number_format($today_purchase_due, 2)}}</h3>
               <p>Today Purchase Due</p>
            </div>
            <div class="icon">
               <i class="fa fa-exclamation-circle"></i>
            </div>
            <a href="{{ route('admin.expense.purchase-details') }}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
   </div>
</div>
<div class="container-fluid">
   <div class="row">

        <div class="col-6 my-4">
         <h3>Best Selling Products</h3>
         <section class="content">
            <div class="card product-list">
               <div class="card-body" style="height:350px;overflow-y:scroll">
                  <table class="table">
                     <thead>
                        <tr>
                           <th>ID</th>
                           <th>Name</th>
                           <th>Image</th>

                           <th>Price</th>
                           <th>Quantity</th>

                           <th>Updated At</th>
                           <!-- <th>Actions</th> -->
                        </tr>
                     </thead>
                     <tbody >
                        @foreach ($best_selling_products as $product)
                        <tr>
                           <td>{{$product->id}}</td>
                           <td>{{$product->name}}</td>
                           <td><img class="product-img" src="{{ Storage::url($product->image) }}" alt=""></td>

                           <td>{{$product->sell_price}}</td>
                           <td>{{$product->total_sold ?? 0}}</td>

                           <td>{{$product->updated_at}}</td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
         </section>
      </div>

      <div class="col-6 my-4">
         <h3>Low Stock Product</h3>
         <section class="content">
            <div class="card product-list">
               <div class="card-body" style="height:350px;overflow-y:scroll">
                  <table class="table">
                     <thead>
                        <tr>
                           <th>ID</th>
                           <th>Name</th>
                           <th>Image</th>
                           <th>Barcode</th>
                           <th>Price</th>
                           <th>Quantity</th>
                           <th>Status</th>
                           <th>Updated At</th>
                           <!-- <th>Actions</th> -->
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($low_stock_products as $product)
                        <tr>
                           <td>{{$product->id}}</td>
                           <td>{{$product->name}}</td>
                           <td><img class="product-img" src="{{ Storage::url($product->image) }}" alt=""></td>
                           <td>{{$product->barcode}}</td>
                           <td>{{$product->sell_price}}</td>
                           <td class="text-danger">{{$product->quantity}}</td>
                           <td>
                              <span class="right badge badge-{{ $product->status ? 'success' : 'danger' }}">{{$product->status ? __('common.Active') : __('common.Inactive') }}</span>
                           </td>
                           <td>{{$product->updated_at}}</td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
         </section>
      </div>



   </div>
</div>
@endsection
