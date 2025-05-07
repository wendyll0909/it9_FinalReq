<div id="attendance-checkout-section">
    <h2>Check Out</h2>
    <div class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <h4>Record Check-Out</h4>
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
                        <label for="manualEmployee" class="form-label">Manual Check-Out</label>
                        <select id="manualEmployee" class="form-control">
                            <option value="">Select Employee</option>
                            @foreach (App\Models\Employee::where('status', 'active')->get() as $employee)
                                <option value="{{ $employee->employee_id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="submitCheckout" class="btn btn-primary">Submit Check-Out</button>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Today's Check-Outs</h4>
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
                                <th>Check-Out Time</th>
                                <th>Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checkouts as $checkout)
                                <tr>
                                    <td>{{ $checkout->employee->fname }} {{ $checkout->employee->lname }}</td>
                                    <td>{{ $checkout->check_in_time }}</td>
                                    <td>{{ $checkout->check_out_time }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $checkout->check_out_method)) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-attendance" data-id="{{ $checkout->attendance_id }}">Edit</button>
                                        <form hx-delete="{{ route('attendance.destroy', $checkout->attendance_id) }}"
                                              hx-target="#attendance-checkout-section"
                                              hx-swap="innerHTML"
                                              hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this check-out?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No check-outs today</td>
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
    const submitCheckout = document.getElementById('submitCheckout');
    let stream = null;

    startCameraButton.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            video.style.display = 'block';
            canvas.style.display = 'none';
            video.play();
            scanQR();
        } catch (err) {
            console.error('Camera access failed:', err);
            alert('Failed to access camera. Please use file upload or manual check-out.');
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
            submitCheckoutHandler(code.data, 'qr_camera');
            stopCamera();
        } else {
            requestAnimationFrame(scanQR);
        }
    }

    qrUpload.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    context.drawImage(img, 0, 0);
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    if (code) {
                        submitCheckoutHandler(code.data, 'qr_upload');
                    } else {
                        alert('No QR code found in the image.');
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    submitCheckout.addEventListener('click', () => {
        if (manualEmployee.value) {
            submitCheckoutHandler(null, 'manual', manualEmployee.value);
        } else {
            alert('Please select an employee for manual check-out or scan a QR code.');
        }
    });

    function submitCheckoutHandler(qrCode, method, employeeId = null) {
        const formData = new FormData();
        if (qrCode) {
            formData.append('qr_code', qrCode);
        } else if (employeeId) {
            formData.append('employee_id', employeeId);
        }
        formData.append('method', method);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('{{ route("attendance.checkout.store") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('attendance-checkout-section').innerHTML = html;
        })
        .catch(error => {
            console.error('Check-out failed:', error);
            alert('Failed to record check-out.');
        });
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.style.display = 'none';
        }
    }

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-attendance')) {
            const attendanceId = e.target.getAttribute('data-id');
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