<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Report</title>
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
    .late { background: #fef9c3; color: #a16207; }
    .ok   { background: #dcfce7; color: #166534; }
</style>
</head>
<body>
    <h1>Attendance Report</h1>
    <p class="sub">Generated: {{ now()->format('M d, Y h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours</th>
                <th>OT Hours</th>
                <th>Late</th>
                <th>Undertime</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $rec)
                <tr>
                    <td>{{ $rec->employee?->full_name ?: $rec->employee?->user?->username }}</td>
                    <td>{{ $rec->work_date->format('M d, Y') }}</td>
                    <td>{{ $rec->time_in?->format('h:i A') ?? '—' }}</td>
                    <td>{{ $rec->time_out?->format('h:i A') ?? '—' }}</td>
                    <td>{{ $rec->total_hours > 0 ? $rec->total_hours.'h' : '—' }}</td>
                    <td>{{ $rec->overtime_hours > 0 ? $rec->overtime_hours.'h' : '—' }}</td>
                    <td><span class="badge {{ $rec->is_late ? 'late' : 'ok' }}">{{ $rec->is_late ? 'Yes' : 'No' }}</span></td>
                    <td><span class="badge {{ $rec->is_undertime ? 'late' : 'ok' }}">{{ $rec->is_undertime ? 'Yes' : 'No' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center; color:#94a3b8; padding:20px">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
