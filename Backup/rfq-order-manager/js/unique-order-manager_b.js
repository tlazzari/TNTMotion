// Add this at the top of your JavaScript file



jQuery(document).ready(function($) {
    console.log("Initializing Unique Order Manager...");

    $(document).ready(function() {
    console.log("Document ready");
    console.log("Suggestions containers:", {
        supplier: $('#rfq_supplier_suggestions').length,
        client: $('#rfq_client_suggestions').length
    });
});

    /**
     * Initialize DataTable with AJAX
     */
    const orderTable = $('#unique-order-table').DataTable({
        "ajax": {
            "url": uniqueOrderManager.ajax_url,
            "type": "POST",
            "data": {
                "action": "unique_fetch_past_orders",
                "nonce": uniqueOrderManager.nonce
            },
            "dataSrc": function(json) {
                console.log("Full AJAX response for DataTable:", json); // Debugging log
                if (json.success) {
                    if (Array.isArray(json.data.orders)) {
                        console.log("Received orders:", json.data.orders); // Debugging log
                        return json.data.orders;
                    } else {
                        console.error("Orders data is not an array:", json.data.orders);
                        showToast("Error: Orders data is invalid.");
                        return [];
                    }
                } else {
                    showToast("Error fetching past orders: " + (json.message || "Unknown error"));
                    return [];
                }
            },
            "error": function(xhr, status, error) {
                console.error("AJAX error fetching past orders:", error);
                showToast("AJAX error fetching past orders.");
            }
        },
        "createdRow": function(row, data, dataIndex) {
            $(row).attr('data-order-id', data.order_id);
        },
        "columns": [
            { "data": "order_id" },
            { 
                "data": "price",
                "render": function(data, type, row) {
                    return `<span class="editable-field" data-field="price">${parseFloat(data).toFixed(2)}</span>`;
                }
            },
            { 
                "data": "tax",
                "render": function(data, type, row) {
                    return `<span class="editable-field" data-field="tax">${parseFloat(data).toFixed(2)}</span>`;
                }
            },
            { 
                "data": "vat",
                "render": function(data, type, row) {
                    return `<span class="editable-field" data-field="vat">${parseFloat(data).toFixed(2)}</span>`;
                }
            },
            { 
                "data": "total",
                "render": function(data, type, row) {
                    return `<span class="editable-field" data-field="total">${parseFloat(data).toFixed(2)}</span>`;
                }
            },
            { 
                "data": "quantity",
                "render": function(data, type, row) {
                    return `<span class="editable-field" data-field="quantity">${parseInt(data)}</span>`;
                }
            },
            { "data": "supplier_name" },
            { "data": "client_name" },
            { "data": "product_name" },
            { 
                "data": "order_id",
                "orderable": false,
                "render": function(data, type, row) {
                    return `<button class="save-changes-button btn btn-sm btn-success" style="display:none;">Save</button>
                            <button class="cancel-changes-button btn btn-sm btn-secondary" style="display:none;">Cancel</button>`;
                }
            }
        ],
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "columnDefs": [
            { "orderable": false, "targets": 8 } // Disable ordering on Actions column
        ]
    });

    /**
     * Show Toast Notification
     */
    function showToast(message) {
        $('#order-toast .toast-body').text(message);
        $('#order-toast').toast('show');
    }

    /**
     * Handle Click on Editable Field to Enable Editing
     */
    $(document).on('click', '.editable-field', function () {
        const $cell = $(this);
        const currentValue = $cell.text();
        const fieldName = $cell.data('field');
        const $row = $cell.closest('tr');
        const $saveButton = $row.find('.save-changes-button');
        const $cancelButton = $row.find('.cancel-changes-button');

        // Prevent multiple inputs
        if ($cell.find('input').length > 0) {
            return;
        }

        // Determine input type based on field
        let inputType = 'number';
        let step = '0.01';
        if (fieldName === 'quantity') {
            inputType = 'number';
            step = '1';
        }

        // Replace cell content with input field
        $cell.html(`<input type="${inputType}" step="${step}" class="form-control form-control-sm" value="${currentValue}">`);
        $cell.find('input').focus(); // Auto-focus the input field
        $saveButton.show();
        $cancelButton.show();
    });

    /**
     * Handle Click on Save Changes Button
     */
$(document).on('click', '.save-changes-button', function () {
    const $button = $(this);
    const $row = $button.closest('tr');
    const order_id = $row.data('order-id');
    const $inputs = $row.find('input');

    let updates = {};

    $inputs.each(function () {
        const $input = $(this);
        const field = $input.closest('td').find('.editable-field').data('field');
        const newValue = $input.val();

        updates[field] = newValue;
    });

    // Validate inputs
    for (const field in updates) {
        if (updates.hasOwnProperty(field)) {
            if ((field === 'price' || field === 'tax' || field === 'vat' || field === 'total') && (isNaN(updates[field]) || parseFloat(updates[field]) < 0)) {
                showToast(`Please enter a valid value for ${field.toUpperCase()}.`);
                return;
            }
            if (field === 'quantity' && (!Number.isInteger(parseFloat(updates[field])) || parseInt(updates[field]) < 0)) {
                showToast(`Please enter a valid integer for Quantity.`);
                return;
            }
        }
    }

    // Send a single AJAX request with all updates
    const data = {
        action: "unique_update_order_fields", // Updated to match PHP handler
        nonce: uniqueOrderManager.nonce,
        order_id: order_id,
        updates: updates
    };

    $.ajax({
        url: uniqueOrderManager.ajax_url,
        method: "POST",
        data: data,
        beforeSend: function() {
            // Disable buttons to prevent multiple clicks
            $button.prop('disabled', true);
            $row.find('.cancel-changes-button').prop('disabled', true);
        },
        success: function (response) {
            console.log("Update response:", response); // Debugging log
            if (response.success) {
                showToast("All changes saved successfully.");
                // Refresh the table
                orderTable.ajax.reload(null, false); // Reload without resetting pagination
            } else {
                showToast("Failed to save changes: " + (response.message || "Unknown error"));
            }
        },
        error: function (error) {
            console.error("AJAX error saving changes:", error);
            showToast("AJAX error: Could not save changes.");
        },
        complete: function () {
            // Re-enable buttons
            $button.prop('disabled', false);
            $row.find('.cancel-changes-button').prop('disabled', false);
            // Hide Save and Cancel buttons
            $row.find('.save-changes-button').hide();
            $row.find('.cancel-changes-button').hide();
        }
    });
});


    /**
     * Handle Click on Cancel Changes Button
     */
    $(document).on('click', '.cancel-changes-button', function () {
        const $button = $(this);
        const $row = $button.closest('tr');
        const $inputs = $row.find('input');

        // Restore original values from inputs' data
        $inputs.each(function () {
            const $input = $(this);
            const originalValue = $input.attr('value');
            const fieldName = $input.closest('td').find('.editable-field').data('field');
            $input.closest('td').html(`<span class="editable-field" data-field="${fieldName}">${originalValue}</span>`);
        });

        // Hide Save and Cancel buttons
        $row.find('.save-changes-button').hide();
        $row.find('.cancel-changes-button').hide();
    });

    /**
     * Debounce Function to Limit Autocomplete AJAX Calls
     */
    function debounce(func, delay) {
        let debounceTimer;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        }
    }



    /**
     * Fetch RFQ Suggestions for Autocomplete
     */
 $(document).on('input', '#rfq_supplier_id', function () {
    const searchTerm = $(this).val();  // Ensure that searchTerm is defined here
    const $suggestionsList = $("#rfq_supplier_suggestions .suggestion-list");


    // If input length is less than 2, hide suggestions and return early
    if (searchTerm.length < 2) {
        $suggestionsList.empty().hide();
        return;
    }

    $.ajax({
        url: uniqueOrderManager.ajax_url,
        method: "POST",
        data: {
            action: "unique_fetch_rfq_suggestions",
            nonce: uniqueOrderManager.nonce,
            search_term: searchTerm  // Pass the searchTerm to the backend
        },
        success: function (response) {
            console.log("Suggestions received:", response);

            if (response.success && response.data.suggestions) {
                const suggestions = response.data.suggestions;
                let suggestionHTML = '';

                if (Array.isArray(suggestions) && suggestions.length > 0) {
                    suggestions.forEach(suggestion => {
                        suggestionHTML += `
                            <li class="suggestion-item" 
                                data-supplier-id="${suggestion.rfq_supplier_id}" 
                                data-client-id="${suggestion.rfq_client_id}">
                                <div><strong>Product:</strong> ${suggestion.product_name}</div>
                                <div><strong>Supplier:</strong> ${suggestion.supplier_name || 'N/A'}</div>
                                <div><strong>Client:</strong> ${suggestion.client_name}</div>
                            </li>`;
                    });

                    // Update the suggestions list and show it
                    $suggestionsList.html(suggestionHTML).show();
                    console.log("Suggestions added to DOM.");
                } else {
                    $suggestionsList.html('<li>No suggestions found.</li>').show();
                    console.log("No suggestions found.");
                }
            } else {
                $suggestionsList.html('<li>Error fetching suggestions.</li>').show();
            }
        },
        error: function (error) {
            console.error("AJAX error:", error);
            $("#rfq_supplier_suggestions .suggestion-list").html('<li>Error fetching suggestions.</li>').show();
        }
    });
});



// Click handler for suggestions
$(document).on('click', '.suggestion-item', function() {
    const $this = $(this);
    const supplierId = $this.data('supplier-id');
    const clientId = $this.data('client-id');
    
    // Update the input fields
    if ($this.closest('#rfq_supplier_suggestions').length) {
        $('#rfq_supplier_id').val(supplierId);
        $('#rfq_client_id').val(clientId);
    } else {
        $('#rfq_client_id').val(clientId);
        $('#rfq_supplier_id').val(supplierId);
    }
    
    // Hide suggestions
    $('.suggestion-list').empty().removeClass('active');
});

// Close suggestions when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('.form-group').length) {
        $('.suggestion-list').empty().removeClass('active');
    }
});
    /**
     * Handle Order Form Submission
     */
    $("#unique-order-form").on('submit', function (e) {
        e.preventDefault();

        // Retrieve form values
        const price = $("#price").val();
        const quantity = $("#quantity").val();
        const total = $("#total").val();
        const rfq_supplier_id = $("#rfq_supplier_id").val();
        const rfq_client_id = $("#rfq_client_id").val();

        // Check for missing fields
        let missingFields = [];
        if (price === "") missingFields.push("Price");
        if (quantity === "") missingFields.push("Quantity");
        if (total === "") missingFields.push("Total");
        if (rfq_supplier_id === "") missingFields.push("RFQ Supplier ID");
        if (rfq_client_id === "") missingFields.push("RFQ Client ID");

        if (missingFields.length > 0) {
            showToast(`Missing fields: ${missingFields.join(", ")}. Please fill them out.`);
            return;
        }

        // Parse and validate numerical inputs
        const parsedPrice = parseFloat(price);
        const parsedQuantity = parseInt(quantity);
        const parsedTotal = parseFloat(total);
        const parsedRfqSupplierId = parseInt(rfq_supplier_id);
        const parsedRfqClientId = parseInt(rfq_client_id);

        let invalidFields = [];
        if (isNaN(parsedPrice) || parsedPrice <= 0) invalidFields.push("Price");
        if (isNaN(parsedQuantity) || parsedQuantity < 0) invalidFields.push("Quantity");
        if (isNaN(parsedTotal) || parsedTotal < 0) invalidFields.push("Total");
        if (isNaN(parsedRfqSupplierId) || parsedRfqSupplierId <= 0) invalidFields.push("RFQ Supplier ID");
        if (isNaN(parsedRfqClientId) || parsedRfqClientId <= 0) invalidFields.push("RFQ Client ID");

        if (invalidFields.length > 0) {
            showToast(`Invalid values for: ${invalidFields.join(", ")}. Please correct them.`);
            return;
        }

        const data = {
            action: "unique_insert_order",
            nonce: uniqueOrderManager.nonce,
            price: parsedPrice,
            tax: parseFloat($("#tax").val()) || 0.0,
            vat: parseFloat($("#vat").val()) || 0.0,
            total: parsedTotal,
            quantity: parsedQuantity,
            rfq_supplier_id: parsedRfqSupplierId,
            rfq_client_id: parsedRfqClientId,
            notes: $("#notes").val()
        };

        $.ajax({
            url: uniqueOrderManager.ajax_url,
            method: "POST",
            data: data,
            success: function (response) {
                console.log("Insert response:", response); // Debugging log
                if (response.success) {
                    showToast("Order inserted successfully.");
                    $("#unique-order-form")[0].reset();
                    orderTable.ajax.reload(null, false); // Reload without resetting pagination
                } else {
                    console.error("Error inserting order:", response.message);
                    showToast("Failed to insert order: " + (response.message || "Unknown error"));
                }
            },
            error: function (error) {
                console.error("AJAX error inserting order:", error);
                showToast("AJAX error: Could not insert order.");
            },
        });
    });
});
