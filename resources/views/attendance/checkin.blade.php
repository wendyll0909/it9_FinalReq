<script>
console.log('Initial script running: Confirming JavaScript execution');
</script>

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
            <div class="col-md-4">
                <h4>Check-In via Camera</h4>
                <div class="card p-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label">QR Code Scan</label>
                        <button id="startCamera" class="btn btn-primary w-100">Start Camera</button>
                        <button id="stopCamera" class="btn btn-danger w-100 mt-2" style="display: none;">Stop Camera</button>
                        <video id="qrVideo" style="display: none; width: 100%; max-height: 200px;" autoplay playsinline></video>
                        <canvas id="qrCanvas" style="display: none;"></canvas>
                        <div id="qrResult" class="mt-2" style="display: none;"></div>
                    </div>
                    <button id="submitCameraCheckin" class="btn btn-primary w-100" disabled>Submit Camera Check-In</button>
                </div>
            </div>
            <div class="col-md-4">
                <h4>Check-In via Image Upload</h4>
                <div class="card p-3 mb-3">
                    <div class="mb-3">
                        <label for="qrUpload" class="form-label">Upload QR Code Image</label>
                        <input type="file" id="qrUpload" accept="image/*" class="form-control">
                        <div id="uploadPreview" class="mt-2 text-center"></div>
                    </div>
                    <button id="submitUploadCheckin" class="btn btn-primary w-100" disabled>Submit Upload Check-In</button>
                </div>
            </div>
            <div class="col-md-4">
                <h4>Manual Check-In</h4>
                <div class="card p-3 mb-3">
                    <div class="mb-3">
                        <label for="manualEmployee" class="form-label">Select Employee</label>
                        <select id="manualEmployee" class="form-control">
                            <option value="">Select Employee</option>
                            @foreach (App\Models\Employee::where('status', 'active')->get() as $employee)
                                <option value="{{ $employee->employee_id }}">{{ $employee->fname }} {{ $employee->lname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="submitManualCheckin" class="btn btn-primary w-100">Submit Manual Check-In</button>
                </div>
            </div>
            <div class="col-12">
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
            </div>        </div>
    </div>
</div>

<script>
(function() {
    console.log('Main script running');
    
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('CSRF token not found');
        alert('CSRF token missing. Please refresh the page.');
    }

    // Camera Elements
    const video = document.getElementById('qrVideo');
    const canvas = document.getElementById('qrCanvas');
    const startCameraBtn = document.getElementById('startCamera');
    const stopCameraBtn = document.getElementById('stopCamera');
    const submitCameraBtn = document.getElementById('submitCameraCheckin');
    let stream = null;
    let qrCode = null;

    // Upload Elements
    const qrUpload = document.getElementById('qrUpload');
    const submitUploadBtn = document.getElementById('submitUploadCheckin');
    const uploadPreview = document.getElementById('uploadPreview');

    // Manual Check-In Elements
    const manualEmployeeSelect = document.getElementById('manualEmployee');
    const submitManualBtn = document.getElementById('submitManualCheckin');

    // Error Handling
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

    // Camera Functionality
    startCameraBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            video.srcObject = stream;
            video.style.display = 'block';
            startCameraBtn.style.display = 'none';
            stopCameraBtn.style.display = 'block';
            
            // Start QR scanning
            scanQRCode();
        } catch (err) {
            showError('Could not access camera: ' + err.message);
        }
    });

    stopCameraBtn.addEventListener('click', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
            video.style.display = 'none';
            startCameraBtn.style.display = 'block';
            stopCameraBtn.style.display = 'none';
            submitCameraBtn.disabled = true;
            qrCode = null;
        }
    });

    function scanQRCode() {
        if (!stream) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        
        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });
                
                if (code) {
                    qrCode = code.data;
                    document.getElementById('qrResult').textContent = `Detected: ${qrCode}`;
                    document.getElementById('qrResult').style.display = 'block';
                    submitCameraBtn.disabled = false;
                } else {
                    submitCameraBtn.disabled = true;
                }
            }
            requestAnimationFrame(tick);
        }
        tick();
    }

    submitCameraBtn.addEventListener('click', () => {
        if (qrCode) {
            submitCheckin(qrCode, 'qr_camera');
        } else {
            showError('No QR code detected');
        }
    });

    // Upload Functionality
    qrUpload.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        try {
            // Display preview
            const reader = new FileReader();
            reader.onload = (event) => {
                uploadPreview.innerHTML = `<img src="${event.target.result}" style="max-width: 100%; max-height: 200px;">`;
            };
            reader.readAsDataURL(file);

            // Process QR code
            const image = new Image();
            image.src = URL.createObjectURL(file);
            
            image.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = image.width;
                canvas.height = image.height;
                const context = canvas.getContext('2d');
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });
                
                if (code) {
                    qrCode = code.data;
                    submitUploadBtn.disabled = false;
                } else {
                    showError('No QR code found in the image');
                    submitUploadBtn.disabled = true;
                }
            };
        } catch (err) {
            showError('Error processing image: ' + err.message);
            submitUploadBtn.disabled = true;
        }
    });

    submitUploadBtn.addEventListener('click', () => {
        if (qrCode) {
            submitCheckin(qrCode, 'qr_upload');
        } else {
            showError('No QR code detected');
        }
    });

    // Manual Check-In Functionality
    submitManualBtn.addEventListener('click', () => {
        const employeeId = manualEmployeeSelect.value;
        if (employeeId) {
            submitCheckin(employeeId, 'manual');
        } else {
            showError('Please select an employee for manual check-in.');
        }
    });

    // Helper Function to Submit Check-In
    function submitCheckin(employeeIdOrCode, method) {
        const loading = document.getElementById('loading');
        if (loading) loading.style.display = 'block';

        let data = {};
        if (method === 'manual') {
            data = { employee_id: employeeIdOrCode };
        } else {
            // QR code methods
            if (!employeeIdOrCode.startsWith('EMP-')) {
                showError('Invalid QR code format');
                if (loading) loading.style.display = 'none';
                return;
            }
            data = { qr_code: employeeIdOrCode, method: method === 'qr_camera' ? 'camera' : 'upload' };
        }

        fetch('/dashboard/attendance/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/html,application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 419) {
                    throw new Error('CSRF token mismatch. Please refresh the page.');
                }
                return response.text().then(text => {
                    throw new Error(`Server error: ${response.status} ${text}`);
                });
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('attendance-checkin-section').innerHTML = html;
            if (loading) loading.style.display = 'none';
        })
        .catch(error => {
            console.error('Check-in submission failed:', error);
            showError(error.message || 'Submission failed. Check console for details.');
            if (loading) loading.style.display = 'none';
        });
    }

    // Global error handler
    window.onerror = function(message, source, lineno, colno, error) {
        console.error('Global error:', message, 'at', source, 'line', lineno, 'column', colno, 'Error object:', error);
    };
})();
</script>