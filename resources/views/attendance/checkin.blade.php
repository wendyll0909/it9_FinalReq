<div id="attendance-checkin-section">
    <h2>Check In</h2>
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
                <h4>Record Check-In</h4>
                <div class="card p-3 mb-3" hx-on::after-request="if (event.detail.xhr.status >= 400) {
                    const response = JSON.parse(event.detail.xhr.responseText || '{}');
                    document.getElementById('error-message').textContent = response.error || 'An error occurred';
                    document.getElementById('error-container').style.display = 'block';
                    document.getElementById('loading').style.display = 'none';
                    setTimeout(() => document.getElementById('error-container').style.display = 'none', 5000);
                }">
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
    console.log('DOM fully loaded and parsed');
    const video = document.getElementById('qrVideo');
    const canvas = document.getElementById('qrCanvas');
    const context = canvas.getContext('2d');
    const startCameraButton = document.getElementById('startCamera');
    const qrUpload = document.getElementById('qrUpload');
    const manualEmployee = document.getElementById('manualEmployee');
    const submitCheckin = document.getElementById('submitCheckin');
    const errorContainer = document.getElementById('error-container');
    const errorMessage = document.getElementById('error-message');
    const loading = document.getElementById('loading');
    let stream = null;

    // Verify CSRF token exists
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token meta tag not found!');
        showError('System configuration error. Please reload the page.');
    }

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
        loading.style.display = 'none';
    }

    function showSuccess(message) {
        console.log('Success:', message);
        const successContainer = document.createElement('div');
        successContainer.className = 'alert alert-success alert-dismissible';
        successContainer.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            ${message}
        `;
        document.getElementById('attendance-checkin-section').prepend(successContainer);
        setTimeout(() => {
            successContainer.remove();
        }, 5000);
        loading.style.display = 'none';
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
            video.style.display = 'none';
            stream = null;
        }
    }

    startCameraButton.addEventListener('click', async () => {
        console.log('Start camera button clicked');
        try {
            console.log('Requesting camera access...');
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            console.log('Camera access granted');
            
            video.srcObject = stream;
            video.style.display = 'block';
            canvas.style.display = 'none';
            
            video.play().then(() => {
                console.log('Video playback started');
                scanQR();
            }).catch(err => {
                console.error('Video play failed:', err);
                showError('Failed to start camera: ' + err.message);
            });
            
        } catch (err) {
            console.error('Camera access error:', err);
            showError('Failed to access camera: ' + err.message);
        }
    });

    function scanQR() {
        if (!video.videoWidth || !video.videoHeight) {
            console.log('Waiting for video dimensions...');
            requestAnimationFrame(scanQR);
            return;
        }

        console.log('Scanning QR code...');
        try {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'attemptBoth'
            });
            
            if (code) {
                console.log('QR code detected:', code.data);
                submitCheckinHandler(code.data, 'qr_camera');
                stopCamera();
            } else {
                requestAnimationFrame(scanQR);
            }
        } catch (err) {
            console.error('QR scanning error:', err);
            showError('QR scanning error: ' + err.message);
            stopCamera();
        }
    }

    qrUpload.addEventListener('change', (event) => {
        console.log('QR upload changed');
        const file = event.target.files[0];
        if (!file) {
            console.log('No file selected');
            return;
        }

        if (!file.type.startsWith('image/')) {
            showError('Please upload a valid image file.');
            return;
        }

        console.log('Processing file:', file.name, 'Type:', file.type, 'Size:', file.size);

        const reader = new FileReader();

        reader.onload = (e) => {
            console.log('File read successfully');
            const img = new Image();

            img.onload = () => {
                console.log('Image loaded successfully. Dimensions:', img.width, 'x', img.height);

                // Resize image if too large to improve QR detection
                const maxSize = 800; // Maximum width/height
                let width = img.width;
                let height = img.height;

                if (width > height && width > maxSize) {
                    height = Math.round((height * maxSize) / width);
                    width = maxSize;
                } else if (height > maxSize) {
                    width = Math.round((width * maxSize) / height);
                    height = maxSize;
                }

                canvas.width = width;
                canvas.height = height;
                context.drawImage(img, 0, 0, width, height);

                try {
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    console.log('Scanning for QR code...');
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'attemptBoth' // Improve detection
                    });

                    if (code) {
                        console.log('QR code found:', code.data);
                        submitCheckinHandler(code.data, 'qr_upload');
                    } else {
                        console.log('No QR code found in image');
                        showError('No QR code found in the uploaded image.');
                    }
                } catch (err) {
                    console.error('Image processing error:', err);
                    showError('Error processing image: ' + err.message);
                }
            };

            img.onerror = () => {
                console.error('Failed to load image');
                showError('The selected file is not a valid image.');
            };

            img.src = e.target.result;
        };

        reader.onerror = () => {
            console.error('File read error:', reader.error);
            showError('Failed to read file: ' + reader.error.message);
        };

        console.log('Starting file read...');
        reader.readAsDataURL(file);
    });

    submitCheckin.addEventListener('click', () => {
        submitCheckin.disabled = true;
        submitCheckinHandler(null, 'manual');
        setTimeout(() => {
            submitCheckin.disabled = false;
        }, 2000); // Re-enable after 2 seconds
    });

    function submitCheckinHandler(qrData, method) {
        const employeeId = manualEmployee.value;

        if (!employeeId && !qrData) {
            showError('Please select an employee or scan a QR code.');
            submitCheckin.disabled = false;
            return;
        }

        // Prepare data for submission
        const formData = new FormData();
        if (employeeId) {
            formData.append('employee_id', employeeId);
        }
        if (qrData) {
            formData.append('qr_code', qrData);
        }
        formData.append('method', method);

        console.log('Submitting check-in:', {
            employee_id: employeeId,
            qr_code: qrData,
            method: method
        });

        loading.style.display = 'block';

        // Make the HTMX request
        htmx.ajax('POST', '{{ route("attendance.checkin.store") }}', {
            target: '#attendance-checkin-section',
            swap: 'innerHTML',
            values: Object.fromEntries(formData),
            headers: {
                'X-CSRF-TOKEN': csrfToken.content
            },
            onBeforeRequest: () => {
                console.log('Sending check-in request...');
            },
            onSuccess: () => {
                console.log('Check-in response received');
                showSuccess('Check-in recorded successfully.');
            },
            onError: (error) => {
                console.error('Check-in request failed:', error);
                showError('Failed to record check-in: ' + error.message);
            }
        });
    }
});
</script>