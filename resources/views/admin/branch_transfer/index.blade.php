@extends('admin.layouts.admin')

@section('title', 'Branch Transfer History')
@section('content-header', 'Branch Transfer History')

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('admin.branch-transfer.create') }}" class="btn btn-primary mb-3">New Transfer</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>From Branch</th>
                    <th>To Branch</th>
                    <th>Quantity</th>
                    <th>Transferred By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->id }}</td>
                        <td>{{ $transfer->product->name ?? 'N/A' }}</td>
                        <td>{{ $transfer->fromBranch->branch_name ?? 'N/A' }}</td>
                        <td>{{ $transfer->toBranch->branch_name ?? 'N/A' }}</td>
                        <td>{{ $transfer->quantity }}</td>
                        <td>
                            @if($transfer->admin)
                                {{ $transfer->admin->getFullname() }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $transfer->created_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No transfers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $transfers->links() }}
        </div>
    </div>
</div>
@endsection
