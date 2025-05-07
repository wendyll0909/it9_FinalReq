<div id="attendance-checkin-section">
    <h2>Check In</h2>
    <div class="mb-3">
        <div id="error-container" class="alert alert-danger alert-dismissible" style="display: none;">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <span id="error-message"></span>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4>Record Check-In</h4>
                <div class="card p-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label">QR Code Scan</label>
                        <div class="d-flex">
                            <button id="startCamera" class="btn btn-primary me-2">Start Camera</button>
                            <input type="file" id="qrUpload" accept="image/*" class="form-control" style="width: auto;">
                        </div>
                        <video id="qrVideo" style="display: none; width: 100%; max-height: 300px;" autoplay></video>
                        <canvas id="qrCanvas" style="display: none;"></canvas>
                    </div>
                    <div class="mb-3">
                        <label for="manualEmployee" class="form-label">Manual Check-In</label>
                        <select id="manualEmployee" class="form-control">
                            <option value="">Select Employee</option>
                            @foreach (App\Models\Employee::where('status', 'active')->get() as $employee)
                                <option value="{{ $employee->employee_id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="submitCheckin" class="btn btn-primary">Submit Check-In</button>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Today's Check-Ins</h4>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        {{ session('error') }}
                    </div>
                @endif
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Check-In Time</th>
                                <th>Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checkins as $checkin)
                                <tr>
                                    <td>{{ $checkin->employee ? ($checkin->employee->fname . ' ' . $checkin->employee->lname) : 'Unknown' }}</td>
                                    <td>{{ $checkin->check_in_time }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $checkin->check_in_method)) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-attendance" data-id="{{ $checkin->attendance_id }}">Edit</button>
                                        <form hx-delete="{{ route('attendance.destroy', $checkin->attendance_id) }}"
                                              hx-target="#attendance-checkin-section"
                                              hx-swap="innerHTML"
                                              hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this check-in?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No check-ins today</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('qrVideo');
    const canvas = document.getElementById('qrCanvas');
    const context = canvas.getContext('2d');
    const startCameraButton = document.getElementById('startCamera');
    const qrUpload = document.getElementById('qrUpload');
    const manualEmployee = document.getElementById('manualEmployee');
    const submitCheckin = document.getElementById('submitCheckin');
    const errorContainer = document.getElementById('error-container');
    const errorMessage = document.getElementById('error-message');
    let stream = null;

    function showError(message) {
        console.error('Error:', message);
        if (errorContainer && errorMessage) {
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 5000);
        } else {
            alert(message);
        }
    }

    startCameraButton.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            video.style.display = 'block';
            canvas.style.display = 'none';
            video.play();
            console.log('Camera started');
            scanQR();
        } catch (err) {
            showError('Failed to access camera: ' + err.message);
        }
    });

    function scanQR() {
        if (!video.videoWidth) {
            requestAnimationFrame(scanQR);
            return;
        }
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        if (code) {
            console.log('QR code scanned:', code.data);
            submitCheckinHandler(code.data, 'qr_camera');
            stopCamera();
        } else {
            requestAnimationFrame(scanQR);
        }
    }

    qrUpload.addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (file) {
        console.log('File selected:', file.name);
        const reader = new FileReader();
        reader.onload = (e) => {
            console.log('File read successfully');
            const img = new Image();
            img.onload = () => {
                console.log('Image loaded, dimensions:', img.width, 'x', img.height);
                canvas.width = img.width;
                canvas.height = img.height;
                context.drawImage(img, 0, 0);
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                console.log('Scanning for QR code...');
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                if (code) {
                    console.log('QR code detected:', code.data);
                    submitCheckinHandler(code.data, 'qr_upload');
                } else {
                    console.log('No QR code found in image');
                    showError('No QR code found in the image.');
                }
            };
            img.onerror = () => {
                console.error('Failed to load image');
                showError('Failed to load image.');
            };
            img.src = e.target.result;
        };
        reader.onerror = () => {
            console.error('Failed to read file');
            showError('Failed to read file.');
        };
        reader.readAsDataURL(file);
    }
});

    submitCheckin.addEventListener('click', () => {
        if (manualEmployee.value) {
            console.log('Manual check-in selected, employee ID:', manualEmployee.value);
            submitCheckinHandler(null, 'manual', manualEmployee.value);
        } else {
            showError('Please select an employee for manual check-in or scan a QR code.');
        }
    });

    function submitCheckinHandler(qrCode, method, employeeId = null) {
    const formData = new FormData();
    if (qrCode) {
        formData.append('qr_code', qrCode);
    } else if (employeeId) {
        formData.append('employee_id', employeeId);
    }
    formData.append('method', method);
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    formData.append('_token', csrfToken);

    console.log('Submitting check-in:', { qrCode, method, employeeId, csrfToken });

    fetch('{{ route("attendance.checkin.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'text/html', // Ensure we expect HTML response
            'X-CSRF-TOKEN': csrfToken // Also include in headers for good measure
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
            });
        }
        return response.text();
    })
    .then(html => {
        console.log('Received HTML:', html.substring(0, 100) + '...'); // Log first 100 chars
        document.getElementById('attendance-checkin-section').innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to record check-in: ' + error.message);
    });
}

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.style.display = 'none';
            console.log('Camera stopped');
        }
    }

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-attendance')) {
            const attendanceId = e.target.getAttribute('data-id');
            console.log('Edit attendance clicked, ID:', attendanceId);
            htmx.ajax('GET', `/dashboard/attendance/${attendanceId}/edit`, {
                target: '#editAttendanceModal .modal-body',
                swap: 'innerHTML'
            }).then(() => {
                const editModal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
                editModal.show();
            });
        }
    });
});
</script>