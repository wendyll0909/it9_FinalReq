@if ($positions->isEmpty())
    <option value="">No positions available</option>
@else
    <option value="">Select Position</option>
    @foreach ($positions as $pos)
        <option value="{{ $pos->position_id }}">{{ htmlspecialchars($pos->position_name) }}</option>
    @endforeach
@endif