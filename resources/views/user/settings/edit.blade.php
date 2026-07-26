@extends('layouts.admin')

@section('title', __('settings.Update_Settings'))
@section('content-header', __('settings.Update_Settings'))

@section('content')
<div class="row">
    <div class="col-sm-10">
        <div class="card">
            <div class="card-body">
 
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link {{request()->tab==''?'active':''}}" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Home</a>
                        <a class="nav-item nav-link {{request()->tab=='user'?'active':''}}" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">User list</a>
                        <a class="nav-item nav-link {{request()->tab=='branch'?'active':''}}" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Branch List</a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane border p-5 fade {{request()->tab==''?'active show':''}} " id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        
                        <form action="{{ route('settings.store') }}" method="post">
                            @csrf

                            <div class="form-group">
                                <label for="app_name">{{ __('settings.app_name') }}</label>
                                <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" id="app_name" placeholder="{{ __('settings.App_name') }}" value="{{ old('app_name', config('settings.app_name')) }}">
                                @error('app_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="app_description">{{ __('settings.app_description') }}</label>
                                <textarea name="app_description" class="form-control @error('app_description') is-invalid @enderror" id="app_description" placeholder="{{ __('settings.app_description') }}">{{ old('app_description', config('settings.app_description')) }}</textarea>
                                @error('app_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="currency_symbol">{{ __('settings.Currency_symbol') }}</label>
                                <input type="text" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" id="currency_symbol" placeholder="{{ __('settings.Currency_symbol') }}" value="{{ old('currency_symbol', config('settings.currency_symbol')) }}">
                                @error('currency_symbol')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="warning_quantity">{{ __('settings.warning_quantity') }}</label>
                                <input type="text" name="warning_quantity" class="form-control @error('warning_quantity') is-invalid @enderror" id="warning_quantity" placeholder="{{ __('settings.warning_quantity') }}" value="{{ old('warning_quantity', config('settings.warning_quantity')) }}">
                                @error('warning_quantity')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">{{ __('settings.Change_Setting') }}</button>
                            </div>
                        </form>
                    </div><!---------- end tab-one ------------->

                    <div class="tab-pane fade border p-5 {{request()->tab=='user'?'active show':''}}" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                           <form action="{{ route('user.store') }}" method="post">
                            @csrf
                                <div class="row">
                                     <div class="form-group col-sm-2">
                                        <label for="branch_id">Branch Code</label>
                                        <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror" id="branch_id" placeholder="Branch Name" value="{{ old('branch_id' ) }}" required>
                                            <option value="">Select a branch</option>
                                            @foreach ($branch_list as $branch_data )
                                                <option value="{{$branch_data->id}}">{{$branch_data->branch_name}}</option>                                                
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-sm-2">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Email" value="{{ old('email') }}" required autocomplete="off">
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-2">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Password" value="{{ old('password') }}" required autocomplete="new-password">
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-3">
                                        <label for="first_name">First Name</label>
                                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" id="first_name" placeholder="Full Name" value="{{ old('first_name') }}" required>
                                        @error('first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-3">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" id="last_name" placeholder="Full Name" value="{{ old('last_name') }}" required>
                                        @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
 
                                
                                    <div class="form-group col-sm-9 text-right"></div>
                                    <div class="form-group col-sm-3 text-right">
                                        <label for="address"> </label><br>
                                        <button type="submit" class="btn btn-primary">Save User</button>
                                    </div>
                                </div>
                               
 
                        </form>

                         <table class="border table table-border mt-5">
                            <tr>
                                <th>Sr</th>
                                <th>User Email</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Branch</th>
                                <th class="text-center">Action</th>
                            </tr>

                            @if($user_list)
                                @foreach ($user_list as $index => $user)
                                    <tr>
                                        <th>{{$index+1}}</th>
                                        <th>{{$user->email}}</th>
                                        <th>{{$user->first_name .' '.$user->last_name}}</th>
                                        <th>{{$user->role}}</th>
                                        <th>{{$user->branch->branch_name}}</th>
                                        <th><a href="#" class="btn btn-success">Edit</a></th>
                                        <th><a href="#" class="btn btn-danger">Delete</a></th>
                                    </tr>
                                @endforeach
                            @endif

                        </table>

                    </div><!---------- end tab-two ------------->
                    <div class="tab-pane fade border p-5 {{request()->tab=='branch'?'active show':''}}" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                        <form action="{{ route('branch.store') }}" method="post">
                            @csrf
                                <div class="row">
                                     <div class="form-group col-sm-3">
                                        <label for="branch_name">Branch Code</label>
                                        <input type="text" name="branch_name" class="form-control @error('branch_name') is-invalid @enderror" id="branch_name" placeholder="Branch Name" value="{{ old('branch_name') }}" required>
                                        @error('branch_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                     <div class="form-group col-sm-3">
                                        <label for="address">Address</label>
                                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" id="address" placeholder="Address" value="{{ old('address' ) }}" required>
                                        @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                     <div class="form-group col-sm-3">
                                        <label for="mobile">Mobile</label>
                                        <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" id="mobile" placeholder="Mobile" value="{{ old('mobile') }}" required>
                                        @error('mobile')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                      
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">Save Branch</button>
                                </div>
 
                        </form>

                         <table class="border table table-border mt-5">
                            <tr>
                                <th>Sr.</th>
                                <th>Branch Code</th>
                                <th>Address</th>
                                <th>Mobile</th>
                                <th class="text-center">Action</th>
                            </tr>
                            @if($branch_list)
                                @foreach ($branch_list as $index => $branch)
                                    <tr>
                                        <th>{{$index+1}}</th>
                                        <th>{{$branch->branch_name}}</th>
                                        <th>{{$branch->address}}</th>
                                        <th>{{$branch->mobile}}</th>
                                        <th><a href="#" class="btn btn-success">Edit</a></th>
                                        <th><a href="#" class="btn btn-danger">Delete</a></th>
                                    </tr>
                                @endforeach
                            @endif

                        </table>
                    </div><!---------- end tab-three ---------->
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
