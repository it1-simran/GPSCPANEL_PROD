<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle" style="font-size: 14px;">
        <thead class="table-primary">
            <tr>
                <th>Sr. No.</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>User Type</th>
                <th>Device Category</th>
                <th>Device IP</th>
                <th>Device Port</th>
                <th>Resend Count</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @forelse($requests as $request)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $request->name }}</td>
                    <td>{{ $request->email }}</td>
                    <td>{{ $request->phone }}</td>
                    <td>{{ ucfirst($request->userType) }}</td>
                    <td>{{ $request->deviceCategory }}</td>
                    <td>{{ $request->deviceIp }}</td>
                    <td>{{ $request->devicePort }}</td>
                    <td>{{ $request->resend_count }}</td>
                    <td>
                        <span class="badge 
                            @if($request->status === 'approved') bg-success
                            @elseif($request->status === 'supportApproved') bg-info
                            @elseif($request->status === 'pendingApproval') bg-warning text-dark
                            @elseif($request->status === 'reject') bg-danger
                            @else bg-secondary
                            @endif">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                    <td>
                        {{-- You can keep your same buttons and modals here --}}
                        ...
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
