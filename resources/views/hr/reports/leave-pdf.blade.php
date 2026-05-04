<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Leave Report</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
    h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
    p.sub { font-size: 10px; color: #64748b; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #0f172a; color: #fff; }
    th { padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
    td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }
    tr:nth-child(even) td { background: #f8fafc; }
    .badge { display: inline-block; padding: 1px 7px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
    .approved { background: #dcfce7; color: #166534; }
    .pending  { background: #fef9c3; color: #a16207; }
    .rejected { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>
    <h1>Leave Report</h1>
    <p class="sub">Generated: {{ now()->format('M d, Y h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $rec)
                <tr>
                    <td>{{ $rec->employee?->full_name ?: $rec->employee?->user?->username }}</td>
                    <td>{{ $rec->leaveType->name }}</td>
                    <td>{{ $rec->start_date->format('M d, Y') }}</td>
                    <td>{{ $rec->end_date->format('M d, Y') }}</td>
                    <td>{{ $rec->days_requested }}</td>
                    <td>{{ $rec->reason }}</td>
                    <td><span class="badge {{ $rec->status }}">{{ ucfirst($rec->status) }}</span></td>
                    <td>{{ $rec->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center; color:#94a3b8; padding:20px">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
