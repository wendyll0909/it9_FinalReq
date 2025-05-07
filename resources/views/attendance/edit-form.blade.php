<form id="editAttendanceForm"
      hx-put="{{ route('attendance.update', $attendance->attendance_id) }}"
      hx-target="#attendance-{{ $attendance->check_out_time ? 'checkout' : 'checkin' }}-section"
      hx-swap="innerHTML"
      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="check_in_time" class="form-label">Check-In Time</label>
        <input type="time" class="form-control" id="check_in_time" name="check_in_time" value="{{ $attendance->check_in_time }}">
    </div>
    <div class="mb-3">
        <label for="check_out_time" class="form-label">Check-Out Time</label>
        <input type="time" class="form-control" id="check_out_time" name="check_out_time" value="{{ $attendance->check_out_time }}">
    </div>
    <div class="mb-3">
        <label for="check_in_method" class="form-label">Check-In Method</label>
        <select class="form-control" id="check_in_method" name="check_in_method">
            <option value="">None</option>
            <option value="qr_camera" {{ $attendance->check_in_method == 'qr_camera' ? 'selected' : '' }}>QR Camera</option>
            <option value="qr_upload" {{ $attendance->check_in_method == 'qr_upload' ? 'selected' : '' }}>QR Upload</option>
            <option value="manual" {{ $attendance->check_in_method == 'manual' ? 'selected' : '' }}>Manual</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="check_out_method" class="form-label">Check-Out Method</label>
        <select class="form-control" id="check_out_method" name="check_out_method">
            <option value="">None</option>
            <option value="qr_camera" {{ $attendance->check_out_method == 'qr_camera' ? 'selected' : '' }}>QR Camera</option>
            <option value="qr_upload" {{ $attendance->check_out_method == 'qr_upload' ? 'selected' : '' }}>QR Upload</option>
            <option value="manual" {{ $attendance->check_out_method == 'manual' ? 'selected' : '' }}>Manual</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update Attendance</button>
</form>