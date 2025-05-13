<div class="card-body text-center">
    @if ($topEmployee)
        <img src="{{ $topEmployee->photo ?? asset('assets/img/placeholder.jpg') }}" class="rounded-circle mb-2" style="width: 50px; height: 50px;" alt="Employee">
        <h6>{{ $topEmployee->fname ?? 'No Name' }} {{ $topEmployee->lname ?? 'No Name' }}</h6>
        <p class="text-muted">Employee of the Month</p>
    @else
        <p class="text-danger">No top employee data available.</p>
    @endif
</div>