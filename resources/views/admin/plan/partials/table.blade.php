<div class="table-responsive">
    <table class="table table-striped" width="100%">
        <thead>
            <tr>
                <th>Plan Name</th>
                <th>Customer Type</th>
                <th>No. of Logins</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date of Creation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($plans as $plan)
                <tr>
                    <td>{{ $plan->plan_name }}</td>
                    <td>{{ \App\Models\Plan::getType()[$plan->type] ?? $plan->type }}</td>
                    <td>{{ $plan->no_of_user }}</td>
                    <td>{{ $plan->price }}</td>
                    <td>{{ \App\Models\Plan::getStatus()[$plan->status] ?? $plan->status }}</td>
                    <td>{{ \Carbon\Carbon::parse($plan->created_at)->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.plan.edit', $plan->id) }}" class="btn-rfq  btn-rfq-secondary btn-sm">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No data found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-paginationwithlength :paginator="$plans" />