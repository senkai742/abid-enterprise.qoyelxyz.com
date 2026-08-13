@extends('admin.layouts.admin')

@section('title', __('settings.Update_Settings'))
@section('content-header', __('settings.Update_Settings'))

@section('content')
<div class="row">
    <div class="col-sm-10">
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link {{request()->tab==''?'active':''}}" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Home</a>
                        <a class="nav-item nav-link {{request()->tab=='user'?'active':''}}" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">User list</a>
                        <a class="nav-item nav-link {{request()->tab=='branch'?'active':''}}" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Branch List</a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane border p-5 fade {{request()->tab==''?'active show':''}} " id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">

                        <form action="{{ route('admin.settings.store') }}" method="post" enctype="multipart/form-data">
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

                            <div class="form-group">
                                <label class="form-label">POS Features</label>
                                <small class="text-muted d-block mb-2">Enable/disable individual features for the Point of Sale interface.</small>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="feature_barcode_scanner" id="feature_barcode_scanner" value="1" {{ old('feature_barcode_scanner',  $settings->where("key","feature_barcode_scanner")->first()?->value ?? '0') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feature_barcode_scanner">
                                        <strong>Barcode Scanner</strong> 
                                        
                                        <br><small class="text-muted">Allow scanning product barcodes for quick entry</small>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="feature_salesman_selection" id="feature_salesman_selection" value="1" {{ old('feature_salesman_selection', $settings->where("key","feature_salesman_selection")->first()?->value ?? '0') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feature_salesman_selection">
                                        <strong>Salesman Selection</strong>
                                        <br><small class="text-muted">Allow selecting a salesman for each sale</small>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="feature_installment_plans" id="feature_installment_plans" value="1" {{ old('feature_installment_plans', $settings->where("key","feature_installment_plans")->first()?->value ?? '0') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feature_installment_plans">
                                        <strong>Installment Plans</strong>
                                        <br><small class="text-muted">Allow customers to pay in installments</small>
                                    </label>
                                </div>

                            </div>

                            <div class="form-group border-top pt-3">
                                <label for="invoice_logo_file"><strong>Invoice Logo</strong></label>
                                @php
                                    $logoSetting = $settings->where('key','invoice_logo')->first();
                                    $logoPath = $logoSetting ? $logoSetting->value : null;
                                    $logoFileExists = $logoPath && file_exists(public_path($logoPath));
                                @endphp

                                {{-- Current saved logo status --}}
                                <div class="mb-2 p-3" style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:6px;">
                                    @if($logoFileExists)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('public/' . $logoPath) }}" alt="Current Logo"
                                                style="max-height:80px; max-width:220px; border:1px solid #dee2e6; border-radius:4px; padding:4px; background:#fff; object-fit:contain;">
                                            <div class="ml-3">
                                                <span class="badge badge-success d-block mb-1" style="font-size:0.85rem;">✓ Logo Active</span>
                                                <small class="text-muted">{{ basename($logoPath) }}</small>
                                            </div>
                                        </div>
                                    @elseif($logoPath && !$logoFileExists)
                                        <div class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Logo path saved but file is missing at public/{{ $logoPath }}.</strong><br>
                                            <small class="text-muted">Please select an image file below and click Save Settings to upload it.</small>
                                        </div>
                                    @else
                                        <div class="text-muted text-center py-2">
                                            <i class="fas fa-image fa-2x mb-1" style="opacity:0.3;"></i><br>
                                            <small>No logo uploaded yet. Choose an image below and click Save Settings.</small>
                                        </div>
                                    @endif
                                </div>

                                {{-- Live preview area --}}
                                <div id="logoPreviewBox" style="display:none; margin-bottom:10px;">
                                    <small class="text-primary font-weight-bold d-block mb-1">Selected File Preview:</small>
                                    <img id="logoPreviewImg" src="" alt="Preview"
                                        style="max-height:80px; max-width:220px; border:2px dashed #007bff; border-radius:6px; padding:6px; background:#f8f9ff;">
                                    <small id="logoFileName" class="d-block text-primary mt-1 font-weight-bold"></small>
                                </div>

                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="invoice_logo_file" name="invoice_logo" accept="image/*">
                                    <label class="custom-file-label" for="invoice_logo_file" id="logoFileLabel">Choose new logo image…</label>
                                </div>
                                <small class="text-muted d-block mt-1">Recommended size: 300×100px (PNG, JPG, GIF, SVG). Max 2MB.</small>
                            </div>

                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">{{ __('settings.Change_Setting') }}</button>
                            </div>
                        </form>

                        <script>
                        document.getElementById('invoice_logo_file').addEventListener('change', function() {
                            var file = this.files[0];
                            if (file) {
                                document.getElementById('logoFileLabel').textContent = file.name;
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    document.getElementById('logoPreviewImg').src = e.target.result;
                                    document.getElementById('logoFileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                                    document.getElementById('logoPreviewBox').style.display = 'block';
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                        </script>
                    </div><!---------- end tab-one ------------->

                    <div class="tab-pane fade border p-5 {{request()->tab=='user'?'active show':''}}" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                           <form action="{{ route('admin.user.store') }}" method="post">
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
                                        <th><a href="#" class="btn btn-success btn-edit-user"
    data-id="{{$user->id}}"
    data-email="{{$user->email}}"
    data-first_name="{{$user->first_name}}"
    data-last_name="{{$user->last_name}}"
    data-role="{{$user->role}}"
    data-branch="{{$user->branch->branch_name}}"
>Edit</a></th>
                                        <th>
                                            <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </th>
                                    </tr>
                                @endforeach
                            @endif

                        </table>

                    </div><!---------- end tab-two ------------->
                    <div class="tab-pane fade border p-5 {{request()->tab=='branch'?'active show':''}}" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                        <form action="{{ route('admin.branch.store') }}" method="post">
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
                                        <th><a href="#" class="btn btn-success btn-edit-branch"
    data-id="{{$branch->id}}"
    data-branch_name="{{$branch->branch_name}}"
    data-address="{{$branch->address}}"
    data-mobile="{{$branch->mobile}}"
>Edit</a></th>
                                        <th>
                                            <form action="{{ route('admin.branch.delete', $branch->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this branch?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </th>
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

@section('model')
<!-- User Edit Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editUserForm" onsubmit="return false;">
          <input type="hidden" id="edit_user_id">
          <div class="form-group">
            <label for="edit_email">Email</label>
            <input type="email" class="form-control" id="edit_email">
            <div class="text-danger edit-error" id="edit_error_email"></div>
          </div>
          <div class="form-group">
            <label for="edit_first_name">First Name</label>
            <input type="text" class="form-control" id="edit_first_name">
            <div class="text-danger edit-error" id="edit_error_first_name"></div>
          </div>
          <div class="form-group">
            <label for="edit_last_name">Last Name</label>
            <input type="text" class="form-control" id="edit_last_name">
            <div class="text-danger edit-error" id="edit_error_last_name"></div>
          </div>
          <div class="form-group">
            <label for="edit_role">Role</label>
            <input type="text" class="form-control" id="edit_role">
            <div class="text-danger edit-error" id="edit_error_role"></div>
          </div>
          <div class="form-group">
            <label for="edit_branch">Branch</label>
            <select class="form-control" id="edit_branch"></select>
            <div class="text-danger edit-error" id="edit_error_branch_id"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveUserChanges">Save changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Branch Edit Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" role="dialog" aria-labelledby="editBranchModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editBranchForm" onsubmit="return false;">
          <input type="hidden" id="edit_branch_id">
          <div class="form-group">
            <label for="edit_branch_name">Branch Name</label>
            <input type="text" class="form-control" id="edit_branch_name">
            <div class="text-danger edit-branch-error" id="edit_error_branch_name"></div>
          </div>
          <div class="form-group">
            <label for="edit_branch_address">Address</label>
            <input type="text" class="form-control" id="edit_branch_address">
            <div class="text-danger edit-branch-error" id="edit_error_address"></div>
          </div>
          <div class="form-group">
            <label for="edit_branch_mobile">Mobile</label>
            <input type="text" class="form-control" id="edit_branch_mobile">
            <div class="text-danger edit-branch-error" id="edit_error_mobile"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveBranchChanges">Save changes</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit-user');
    editButtons.forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('edit_user_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_email').value = btn.getAttribute('data-email');
        document.getElementById('edit_first_name').value = btn.getAttribute('data-first_name');
        document.getElementById('edit_last_name').value = btn.getAttribute('data-last_name');
        document.getElementById('edit_role').value = btn.getAttribute('data-role');
        // Load branches dynamically
        fetch('/admin/branches', { headers: { 'Accept': 'application/json' } })
          .then(res => res.json())
          .then(branches => {
            const branchSelect = document.getElementById('edit_branch');
            branchSelect.innerHTML = '';
            branches.forEach(branch => {
              const option = document.createElement('option');
              option.value = branch.id;
              option.textContent = branch.branch_name;
              if (branch.branch_name === btn.getAttribute('data-branch')) {
                option.selected = true;
              }
              branchSelect.appendChild(option);
            });
          });
        $('#editUserModal').modal('show');
      });
    });

    // Handle save changes
    document.querySelector('#saveUserChanges').addEventListener('click', function() {
      const userId = document.getElementById('edit_user_id').value;
      const firstName = document.getElementById('edit_first_name').value;
      const lastName = document.getElementById('edit_last_name').value;
      const role = document.getElementById('edit_role').value;
      const email = document.getElementById('edit_email').value;
      const branchId = document.getElementById('edit_branch').value;
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      // Clear previous errors
      document.querySelectorAll('.edit-error').forEach(e => e.textContent = '');
      fetch(`/admin/user/update/${userId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          first_name: firstName,
          last_name: lastName,
          role: role,
          email: email,
          branch_id: branchId
        })
      })
      .then(async response => {
        if (response.ok) {
          return response.json();
        } else if (response.status === 422) {
          const data = await response.json();
          if (data.errors) {
            Object.keys(data.errors).forEach(function(key) {
              const errorElem = document.getElementById('edit_error_' + key);
              if (errorElem) errorElem.textContent = data.errors[key][0];
            });
          }
          throw new Error('Validation failed');
        } else {
          throw new Error('Unknown error');
        }
      })
      .then(data => {
        $('#editUserModal').modal('hide');
        location.reload();
      })
      .catch(err => {
        // Optionally show a toast or alert
      });
    });

    // Branch edit modal logic
    const branchEditButtons = document.querySelectorAll('.btn-edit-branch');
    branchEditButtons.forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('edit_branch_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_branch_name').value = btn.getAttribute('data-branch_name');
        document.getElementById('edit_branch_address').value = btn.getAttribute('data-address');
        document.getElementById('edit_branch_mobile').value = btn.getAttribute('data-mobile');
        // Clear previous errors
        document.querySelectorAll('.edit-branch-error').forEach(e => e.textContent = '');
        $('#editBranchModal').modal('show');
      });
    });
    
     document.querySelector('#feature_barcode_scanner').addEventListener('click', function() {
         if(document.getElementById('feature_barcode_scanner').checked){
             document.getElementById('feature_barcode_scanner').value = 1;
         }else{
             document.getElementById('feature_barcode_scanner').value = "false";
         }
         console.log(document.getElementById('feature_barcode_scanner').checked);
     });
    // Save changes for branch
    document.querySelector('#saveBranchChanges').addEventListener('click', function() {
      const branchId = document.getElementById('edit_branch_id').value;
      const branchName = document.getElementById('edit_branch_name').value;
      const address = document.getElementById('edit_branch_address').value;
      const mobile = document.getElementById('edit_branch_mobile').value;
      const feature_barcode_scanner = document.getElementById('feature_barcode_scanner').checked;
      const feature_salesman_selection = document.getElementById('feature_salesman_selection').checked;
      const feature_installment_plans = document.getElementById('feature_installment_plans').checked;
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      console.log("document.getElementById('feature_barcode_scanner').checked", feature_barcode_scanner);
      // Clear previous errors
      document.querySelectorAll('.edit-branch-error').forEach(e => e.textContent = '');
      fetch(`/admin/branch/update/${branchId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          branch_name: branchName,
          address: address,
          mobile: mobile,
          feature_barcode_scanner,
          feature_salesman_selection,
          feature_installment_plans
        })
      })
      .then(async response => {
        if (response.ok) {
          return response.json();
        } else if (response.status === 422) {
          const data = await response.json();
          if (data.errors) {
            Object.keys(data.errors).forEach(function(key) {
              const errorElem = document.getElementById('edit_error_' + key);
              if (errorElem) errorElem.textContent = data.errors[key][0];
            });
          }
          throw new Error('Validation failed');
        } else {
          throw new Error('Unknown error');
        }
      })
      .then(data => {
        $('#editBranchModal').modal('hide');
        location.reload();
      })
      .catch(err => {
        // Optionally show a toast or alert
      });
    });

    // Branch delete via AJAX
    document.querySelectorAll('form[action*="branch/delete"]').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this branch?')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(form.action, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
          },
        })
        .then(async response => {
          const data = await response.json();
          if (response.ok && data.success) {
            location.reload();
          } else {
            alert(data.message || 'Failed to delete branch.');
          }
        })
        .catch(() => alert('Failed to delete branch.'));
      });
    });

    // User delete via AJAX
    document.querySelectorAll('form[action*="user/delete"]').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this user?')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(form.action, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
          },
        })
        .then(async response => {
          const data = await response.json();
          if (response.ok && data.success) {
            location.reload();
          } else {
            alert(data.message || 'Failed to delete user.');
          }
        })
        .catch(() => alert('Failed to delete user.'));
      });
    });
  });
</script>
@endsection
