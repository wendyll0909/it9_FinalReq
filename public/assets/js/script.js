document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var employeeId = button.getAttribute('data-employee_id');
            var fname = button.getAttribute('data-fname');
            var mname = button.getAttribute('data-mname');
            var lname = button.getAttribute('data-lname');
            var address = button.getAttribute('data-address');
            var contact = button.getAttribute('data-contact');
            var hireDate = button.getAttribute('data-hire_date');
            var positionId = button.getAttribute('data-position_id');

            document.getElementById('edit_employee_id').value = employeeId;
            document.getElementById('edit_Fname').value = fname;
            document.getElementById('edit_Mname').value = mname;
            document.getElementById('edit_Lname').value = lname;
            document.getElementById('edit_Address').value = address;
            document.getElementById('edit_Contact').value = contact;
            document.getElementById('edit_hire_date').value = hireDate;
            document.getElementById('edit_position_id').value = positionId;
        });
    }
});