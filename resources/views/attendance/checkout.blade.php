<div id="attendance-checkout-section">
    <h2>Check Out</h2>
    <div class="mb-3">
        <div id="error-container" class="alert alert-danger alert-dismissible" style="display: none;">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <span id="error-message"></span>
        </div>
        <div id="loading" style="display: none;" class="text-center my-3">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
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
                                    <td>{{ $checkout->employee ? ($checkout->employee->fname . ' ' . $checkout->employee->lname) : 'Unknown' }}</td>
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

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('qrVideo');
    const canvasElement = document.getElementById('qrCanvas');
    const canvas = canvasElement ? canvasElement.getContext('2d') : null;
    const startCameraButton = document.getElementById('startCamera');
    const qrUploadInput = document.getElementById('qrUpload');
    const submitCheckoutButton = document.getElementById('submitCheckout');
    const manualEmployeeSelect = document.getElementById('manualEmployee');
    const loading = document.getElementById('loading');
    let stream = null;

    // Start Camera for QR Scanning
    if (startCameraButton && video && canvas) {
        startCameraButton.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                video.style.display = 'block';
                video.play();
                requestAnimationFrame(tick);
            } catch (error) {
                console.error('Error accessing camera:', error);
                showError('Failed to access camera: ' + error.message);
            }
        });
    }

    // Stop Camera when Modal Closes (if applicable)
    const stopCamera = () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.style.display = 'none';
            stream = null;
        }
    };

    // Process QR Code from Camera
    function tick() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvasElement.height = video.videoHeight;
            canvasElement.width = video.videoWidth;
            canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
            const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code) {
                console.log('QR Code detected:', code.data);
                stopCamera();
                submitCheckout(code.data, 'camera');
            } else {
                requestAnimationFrame(tick);
            }
        } else {
            requestAnimationFrame(tick);
        }
    }

    // Process QR Code from Uploaded Image
    if (qrUploadInput && canvas) {
        qrUploadInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        canvasElement.width = img.width;
                        canvasElement.height = img.height;
                        canvas.drawImage(img, 0, 0);
                        const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                        const code = jsQR(imageData.data, imageData.width, imageData.height);
                        if (code) {
                            console.log('QR Code detected from upload:', code.data);
                            submitCheckout(code.data, 'upload');
                        } else {
                            showError('No QR code detected in the uploaded image.');
                        }
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Submit Check-Out
    if (submitCheckoutButton) {
        submitCheckoutButton.addEventListener('click', () => {
            const employeeId = manualEmployeeSelect ? manualEmployeeSelect.value : '';
            if (employeeId) {
                submitCheckout(employeeId, 'manual');
            } else {
                showError('Please select an employee for manual check-out.');
            }
        });
    }

    // Helper Function to Submit Check-Out
    function submitCheckout(identifier, method) {
        if (loading) loading.style.display = 'block';

        const data = method === 'manual' ? { employee_id: identifier } : { qr_code: identifier, method: method === 'camera' ? 'camera' : 'upload' };

        fetch('{{ route("attendance.checkout.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.error || 'Unknown error'); });
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('attendance-checkout-section').innerHTML = html;
            if (loading) loading.style.display = 'none';
        })
        .catch(error => {
            console.error('Check-out submission failed:', error);
            showError(error.message);
            if (loading) loading.style.display = 'none';
        });
    }

    // Helper Function to Show Errors
    function showError(message) {
        const errorContainer = document.getElementById('error-container');
        const errorMessage = document.getElementById('error-message');
        if (errorContainer && errorMessage) {
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 5000);
        }
    }

    // Handle Edit Attendance
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