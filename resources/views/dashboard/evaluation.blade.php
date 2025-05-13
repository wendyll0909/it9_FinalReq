<div class="card-body">
    @if ($employee)
        <p><strong>{{ $employee->fname ?? 'No Name' }} {{ $employee->lname ?? 'No Name' }}</strong></p>
        <p>Performance: {{ number_format($employee->performance_score ?? 0, 1) }}/5</p>
        <a href="{{ route('employees.show', $employee->id ?? 1) }}" class="btn btn-primary btn-sm">View Report</a>
    @else
        <p class="text-danger">No evaluation data available.</p>
    @endif
</div>