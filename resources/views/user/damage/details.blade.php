@extends('user.layouts.app')

@section('title', __('Salesreturn'))
@section('content-header', __('Salesreturn#'.$salesreturns[0]->id))
@section('content-actions')
<a href="/user/salesreturn" class="btn btn-primary">{{ __('<< Salesreturn') }}</a>
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <form action="{{route('user.salesreturns.index')}}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control" value="{{request('start_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="{{request('end_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary" type="submit">{{ __('Salesreturn submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr>

        @if($salesreturns)
            <div class="row">
                <div class="col-md-4"><b>Order id: </b> {{$salesreturns[0]->order_id }}</div>
                <div class="col-md-4"><b>Customer: </b> {{$salesreturns[0]->customer->first_name }}</div>
                <div class="col-md-4"><b>Total items:</b> {{number_format($salesreturns[0]->total_qnty,0) }}</div>
                <div class="col-md-4"><b>Total Amount:</b> {{$salesreturns[0]->total_amount }}</div>
                <div class="col-md-4"><b>Return Amount:</b> {{$salesreturns[0]->return_amount }}</div>
                <div class="col-md-4"></div>
            </div>
        @endif

        <hr>

        <table class="table">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Product' }}</th>
                    <th>{{ 'Rate' }}</th>
                    <th>{{ 'Return Qnty.' }}</th>
                    <th>{{ 'Total' }}</th>
                    <th>{{ 'Created' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salesreturns[0]->items as $salesreturn)
                <tr>
                    <td>{{$salesreturn->id}}</td>
                    <td><img class="product-img" src="{{ Storage::url($salesreturn->product->image) }}" alt="" style="width:75px;height:75px"></td>
                    <td>{{$salesreturn->product->name}}</td>
                    <td>{{$salesreturn->sell_price}}</td>
                    <td>{{number_format($salesreturn->qnty)}}</td>
                    <td>{{$salesreturn->total_price}}</td>
                    <td>{{$salesreturn->created_at}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <div class="text-center"></div>
    </div>
</div>
@endsection
