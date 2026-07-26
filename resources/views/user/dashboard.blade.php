@extends('user.layouts.user')
@section('content-header', '['.auth()->user()->first_name.'] User Dashboard - '. now())
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
            <a href="{{route('user.sales.index')}}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>

      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-info">
            <div class="inner">
               <h3>{{$today_sales}}</h3>
               <p>{{ __('Today Sales') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-bag"></i>
            </div>
            <a href="{{route('user.sales.index')}}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
      {{-- Remove any purchase or purchase return blocks/cards --}}
      <!-- ./col -->

      <!-- ./col -->
      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-warning">
            <div class="inner">
               <h3>{{$today_profit}}</h3>
               <p>{{ __('Today Profit') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-person-add"></i>
            </div>
            <a href="{{ route('user.customers.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
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
               <div class="card-body">
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
                     <tbody>
                        @foreach ($best_selling_products as $product)
                        <tr>
                           <td>{{$product->id}}</td>
                           <td>{{$product->name}}</td>
                           <td><img class="product-img" src="{{ Storage::url($product->image) }}" alt=""></td>

                           <td>{{$product->sell_price}}</td>
                           <td>{{$product->quantity}}</td>

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
               <div class="card-body">
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
