@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">All Users</h4>
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>
            Add New User
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $user->address }}</small>
                                    </div>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone_number }}</td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#userModal{{ $user->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if($user->is_active)
                                            <button type="button" class="btn btn-outline-warning">
                                                <i class="bi bi-pause"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-success">
                                                <i class="bi bi-play"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- User Detail Modal -->
                            <div class="modal fade" id="userModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">User Details - {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Full Name:</strong></div>
                                                <div class="col-sm-8">{{ $user->name }}</div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Username:</strong></div>
                                                <div class="col-sm-8">{{ $user->username }}</div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Email:</strong></div>
                                                <div class="col-sm-8">{{ $user->email }}</div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Phone:</strong></div>
                                                <div class="col-sm-8">{{ $user->phone_number }}</div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Address:</strong></div>
                                                <div class="col-sm-8">{{ $user->address }}</div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Account Number:</strong></div>
                                                <div class="col-sm-8">{{ $user->account_number }}</div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Status:</strong></div>
                                                <div class="col-sm-8">
                                                    @if($user->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Registered:</strong></div>
                                                <div class="col-sm-8">{{ $user->created_at->format('d/m/Y H:i:s') }}</div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Last Updated:</strong></div>
                                                <div class="col-sm-8">{{ $user->updated_at->format('d/m/Y H:i:s') }}</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-1"></i>
                                        <p class="mt-2">No users found</p>
                                        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
                                            <i class="bi bi-person-plus me-2"></i>
                                            Add First User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $users->total() }}</h4>
                    <p class="mb-0">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $users->where('is_active', true)->count() }}</h4>
                    <p class="mb-0">Active Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $users->where('is_active', false)->count() }}</h4>
                    <p class="mb-0">Inactive Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $users->where('created_at', '>=', now()->startOfMonth())->count() }}</h4>
                    <p class="mb-0">This Month</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Add any additional JavaScript for user management here
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Add confirmation for status changes
        document.querySelectorAll('.btn-outline-warning, .btn-outline-success').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const isActivating = this.classList.contains('btn-outline-success');
                const action = isActivating ? 'activate' : 'deactivate';
                
                if (confirm(`Are you sure you want to ${action} this user?`)) {
                    // Here you would implement the actual status change
                    console.log(`User ${action} requested`);
                }
            });
        });
    });
</script>
@endpush