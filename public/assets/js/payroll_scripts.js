// Utility function to fetch deductions (unchanged)
function fetchDeductions(employeeId) {
    fetch(`/payroll66/payroll/generate_payroll.php?employee_id=${employeeId}`, {
        method: "POST"
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");
        const newDeductions = doc.querySelectorAll("#deduction_id option:not([value=''])");
        const select = document.getElementById("deduction_id");
        if (select) {
            while (select.options.length > 1) {
                select.remove(1);
            }
            newDeductions.forEach(option => select.add(new Option(option.text, option.value)));
        }
    })
    .catch(error => console.error("Error fetching deductions:", error));
}

// Edit button listeners for payroll reports
function attachEditListeners() {
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const payrollId = this.getAttribute('data-payroll-id');

            // Fetch latest payroll data from the server
            fetch('/payroll66/payroll/payroll_reports.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `payroll_id=${encodeURIComponent(payrollId)}&action=fetch_single`
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const payroll = data.payroll;
                    // Populate modal with server-fetched data only
                    document.getElementById('modal_payroll_id').value = payroll.payroll_id || '';
                    document.getElementById('modal_employee_id').innerText = payroll.employee_id || '';
                    document.getElementById('modal_full_name').innerText = payroll.full_name || '';
                    document.getElementById('modal_days_worked').value = payroll.days_worked || '0';
                    document.getElementById('modal_overtime_hours').value = payroll.overtime_hours || '0.00';
                    document.getElementById('modal_gross_salary').value = payroll.gross_salary || '0.00';
                    document.getElementById('modal_total_deduction').value = payroll.total_deduction || '0.00';
                    document.getElementById('modal_net_pay').value = payroll.net_pay || '0.00';
                    document.getElementById('modal_start_date').value = payroll.start_date || '';
                    document.getElementById('modal_end_date').value = payroll.end_date || '';
                    document.getElementById('modal_status').value = payroll.status || 'Pending';

                    const deductionSelect = document.getElementById('modal_deduction_id');
                    deductionSelect.innerHTML = '<option value="">No Deduction</option>';
                    if (payroll.deductions) {
                        payroll.deductions.forEach(ded => {
                            const opt = new Option(`Deduction ID: ${ded.deduction_id} - Amount: ${parseFloat(ded.total_deduction).toFixed(2)}`, ded.deduction_id);
                            if (ded.deduction_id == payroll.deduction_id) opt.selected = true;
                            deductionSelect.add(opt);
                        });
                    }
                } else {
                    throw new Error(data.message || 'Failed to fetch payroll data');
                }
            })
            .catch(error => {
                console.error('Error fetching payroll data:', error);
                document.getElementById('messageContainerModal').innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
        });
    });
}

// Print button listeners for payroll reports
function attachPrintListeners() {
    document.querySelectorAll('.print-btn').forEach(button => {
        button.addEventListener('click', function() {
            const employeeId = this.getAttribute('data-employee-id');
            const payrollId = this.closest('tr').dataset.payrollId;
            const startDate = this.getAttribute('data-start-date');
            const year = new Date(startDate).getFullYear();
            const payslipNumber = `PS-${year}-${String(payrollId).padStart(4, '0')}`;

            fetch('/payroll66/payroll/payslipview.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `payroll_id=${encodeURIComponent(payrollId)}`
            })
            .then(response => {
                if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const payslipContent = doc.querySelector('.card')?.outerHTML || '<p>No payslip data available.</p>';
                console.log('Payslip HTML:', html); // Debug: Check raw response

                let modifiedContent = payslipContent.replace(
                    /<p class="card-text">Payslip Number:.*?<\/p>/i,
                    `<p class="card-text">Payslip Number: ${payslipNumber}</p>`
                );
                
                if (!/<p class="card-text">Payslip Number:/i.test(payslipContent)) {
                    modifiedContent = payslipContent.replace(
                        '<div class="card-body">',
                        `<div class="card-body"><p class="card-text">Payslip Number: ${payslipNumber}</p>`
                    );
                }

                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Payslip</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            @media print { .card { width: 100%; margin: 0; } body { padding: 20px; } }
                        </style>
                    </head>
                    <body onload="window.print(); window.close();">
                        <div class="container">
                            ${modifiedContent}
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
            })
            .catch(error => {
                console.error('Error fetching payslip:', error);
                document.getElementById("messageContainerModal").innerHTML = `<div class="alert alert-danger">Error fetching payslip data: ${error.message}</div>`;
            });
        });
    });
}