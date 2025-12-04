// DataTable checkbox functionality
function selectAllTable(checkbox) {
    const dataTable = $('#datatable').DataTable();
    const isChecked = $(checkbox).prop('checked');
    
    // Select/deselect all checkboxes in the table
    dataTable.$('input[type="checkbox"]').prop('checked', isChecked);
    
    // Update the selected rows count
    updateSelectedRowsCount();
}

function dataTableRowCheck(rowId) {
    // Check if all checkboxes are selected
    const allCheckboxes = $('#datatable tbody input[type="checkbox"]');
    const checkedCheckboxes = $('#datatable tbody input[type="checkbox"]:checked');
    
    // Update the "select all" checkbox
    $('#select-all-table').prop('checked', allCheckboxes.length === checkedCheckboxes.length);
    
    // Update the selected rows count
    updateSelectedRowsCount();
}

function updateSelectedRowsCount() {
    const checkedCount = $('#datatable tbody input[type="checkbox"]:checked').length;
    const selectedText = checkedCount === 1 ? 'row selected' : 'rows selected';
    
    // Update the count display if it exists
    if ($('#selected-rows-count').length) {
        $('#selected-rows-count').text(`${checkedCount} ${selectedText}`);
    }
    
    // Show/hide bulk actions if they exist
    if ($('#bulk-actions').length) {
        if (checkedCount > 0) {
            $('#bulk-actions').removeClass('d-none');
        } else {
            $('#bulk-actions').addClass('d-none');
        }
    }
}

// Initialize checkbox functionality when document is ready
$(document).ready(function() {
    // Add click handler for individual checkboxes
    $(document).on('click', '.select-table-row', function() {
        const rowId = $(this).val();
        dataTableRowCheck(rowId);
    });
    
    // Add click handler for "select all" checkbox
    $(document).on('click', '#select-all-table', function() {
        selectAllTable(this);
    });
}); 