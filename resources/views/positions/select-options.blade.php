<option value="">Select Position</option>
@foreach ($positions as $pos)
    <option value="{{ $pos->position_id }}">{{ $pos->position_name }}</option>
@endforeach