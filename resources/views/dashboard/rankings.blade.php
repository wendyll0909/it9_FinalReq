<div class="card-body">
    <ul class="list-group list-group-flush">
        @forelse ($rankings as $employee)
            <li class="list-group-item">{{ $loop->iteration }}. {{ $employee->fname ?? 'No Name' }} {{ $employee->lname ?? 'No Name' }} - {{ number_format($employee->attendance_percentage ?? 0, 1) }}%</li>
        @empty
            <li class="list-group-item text-danger">No rankings available</li>
        @endforelse
    </ul>
</div>