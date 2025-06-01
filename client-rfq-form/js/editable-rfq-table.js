jQuery(document).ready(function($) {
    let currentOrderBy = 'rfq_supplier.rfq_supplier_id';

    function fetchTableData() {
        $.ajax({
            url: editableRFQTable.ajax_url,
            method: "POST",
            data: {
                action: "fetch_rfq_table_data",
                nonce:  editableRFQTable.nonce,
                orderby: currentOrderBy
            },
            success: function (response) {
                if (response.success && Array.isArray(response.data.rows)) {
                    if (response.data.rows.length === 0) {
                        $("#editable-rfq-table tbody")
                          .html("<tr><td colspan='18'>No data available.</td></tr>");
                    } else {
                        populateTable(response.data.rows);
                    }
                } else {
                    console.error("Invalid response structure:", response);
                    $("#editable-rfq-table tbody")
                      .html("<tr><td colspan='18'>Failed to load data.</td></tr>");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching table data:", status, error);
                $("#editable-rfq-table tbody")
                  .html("<tr><td colspan='18'>Error loading data.</td></tr>");
            }
        });
    }

    function populateTable(rows) {
        const tbody = $("#editable-rfq-table tbody");
        tbody.empty();

        rows.forEach(row => {
            // build dropdowns
            const currencySelect       = buildCurrencyDropdown(row.currency_id||'');
            const clientStatusSelect   = buildStatusDropdown(row.client_status_id||'');
            const supplierStatusSelect = buildStatusDropdown(row.supplier_status_id||'');

            const rowHTML = `
<tr
  data-rfq_supplier_id="${row.rfq_supplier_id||''}"
  data-rfq_product_id="${row.rfq_product_id||''}"
  data-supplier_id="${row.supplier_id||''}"
>
  <td>
    <button class="close-line-button">✓</button>
    <button class="create-order-button">Make Order</button>
  </td>
  <td>${row.client_name||''}</td>
  <td>${row.rfq_supplier_id||''}</td>
  <td>${row.rfq_product_id||''}</td>
  <td>${row.supplier_name||''}</td>
  <td>${row.product_name||''}</td>
  <td><input type="text" class="quantity-input" value="${row.quantity||''}"></td>

  <!-- NEW: target_price -->
  <td><input type="text" class="target-price-input" value="${row.target_price||''}"></td>
  <!-- NEW: price_to_client -->
  <td><input type="text" class="price-to-client-input" value="${row.price_to_client||''}"></td>

  <td>${currencySelect}</td>
  <td><textarea class="supplier-notes-textarea">${row.supplier_notes||''}</textarea></td>
  <td><textarea class="product-notes-textarea">${row.product_notes||''}</textarea></td>
  <td><input type="text" class="specifications-input" value="${row.specifications||''}"></td>
  <td><textarea class="measurements-textarea">${row.measurements||''}</textarea></td>
  <td><input type="date" class="promised-delivery-date-input" value="${row.promised_delivery_dates ? row.promised_delivery_dates.split(', ')[0] : ''}"></td>
  <td>${clientStatusSelect}</td>
  <td>${supplierStatusSelect}</td>
  <td><button class="save-button">Save</button></td>
</tr>`;
            tbody.append(rowHTML);
        });

        // re-bind handlers
        $(".close-line-button").off("click").on("click", handleCloseLineClick);
        $(".create-order-button").off("click").on("click", handleCreateOrderClick);
        $(".save-button").off("click").on("click", handleSaveClick);
    }

    function buildCurrencyDropdown(selectedId) {
        if (!window.availableCurrencies) {
            return `<select class="currency-select"><option value="">No Currencies</option></select>`;
        }
        let html = '<select class="currency-select">';
        window.availableCurrencies.forEach(c => {
            const sel = (String(c.currency_id)===String(selectedId))?' selected':'';
            html += `<option value="${c.currency_id}"${sel}>${c.currency_description}</option>`;
        });
        html += '</select>';
        return html;
    }

    function buildStatusDropdown(selectedId) {
        if (!window.availableStatuses) {
            return `<select class="status-select"><option value="">No Statuses</option></select>`;
        }
        let html = '<select class="status-select">';
        window.availableStatuses.forEach(s => {
            const sel = (String(s.status_id)===String(selectedId))?' selected':'';
            html += `<option value="${s.status_id}"${sel}>${s.status_name}</option>`;
        });
        html += '</select>';
        return html;
    }


    // When user clicks the "Close" (tick) button in the leftmost column
    function handleCloseLineClick(e) {
        const $row = $(e.target).closest("tr");
        const rfqSupplierId = $row.data("rfq_supplier_id");
        const rfqProductId  = $row.data("rfq_product_id");

        if (!confirm("Are you sure you want to close this line? It will disappear.")) {
            return;
        }

        $.ajax({
            url: editableRFQTable.ajax_url,
            method: "POST",
            data: {
                action: "close_quotation_line",
                nonce: editableRFQTable.nonce,
                rfq_supplier_id: rfqSupplierId,
                rfq_product_id:  rfqProductId
            },
            success: function(resp) {
                if (resp.success) {
                    alert("Line closed successfully!");
                    // Option 1: remove from DOM
                    $row.remove();
                    // Or Option 2: reload table entirely
                    // fetchTableData();
                } else {
                    alert("Failed to close line: " + (resp.data.message || "Unknown error"));
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error closing line:", status, error);
                alert("AJAX error closing line.");
            }
        });
    }

    function handleCreateOrderClick(e) {
        const $row = $(e.target).closest("tr");

        // Read them from data-* and the input fields
        const rfqSupplierId = $row.data("rfq_supplier_id");
        const rfqProductId  = $row.data("rfq_product_id");
        const supplierId    = $row.data("supplier_id");   // Now it won't be undefined

        const price         = $row.find(".price-input").val() || 0;
        const currencyId    = $row.find(".currency-select").val() || 1;

        const measurements  = $row.find(".measurements-textarea").val() || "";
        const specifications= $row.find(".specifications-input").val() || "";
        const promisedDelivery = $row.find(".promised-delivery-date-input").val() || null;

        // Then pass them along:
        makeOrderRequest(
            rfqSupplierId, 
            rfqProductId, 
            supplierId,       // <--- now we have it
            price, 
            currencyId,
            measurements, 
            specifications, 
            promisedDelivery,
            0 // force=0 the first time
        );
    }

    function makeOrderRequest(
        rfqSupplierId,
        rfqProductId,
        supplierId,
        price,
        currencyId,
        measurements,
        specifications,
        promisedDelivery,
        force
    ) {
        $.ajax({
            url: editableRFQTable.ajax_url,
            method: "POST",
            data: {
                action: "create_supplier_order",
                nonce: editableRFQTable.nonce,
                rfq_supplier_id: rfqSupplierId,
                rfq_product_id:  rfqProductId,
                supplier_id:     supplierId,
                price:           price,
                currency_id:     currencyId,
                measurements:    measurements,
                specifications:  specifications,
                promised_delivery_day: promisedDelivery,
                force:           force
            },
            success: function(resp) {
                if (resp.success) {
                    alert("Order created successfully!");
                    fetchTableData(); // Refresh table to show new order
                } else {
                    // Already exists logic:
                    if (resp.data && resp.data.already_exists) {
                        // Show your custom modal or confirm
                        if (confirm("Order already exists. Override?")) {
                            makeOrderRequest(
                                rfqSupplierId,
                                rfqProductId,
                                supplierId,
                                price,
                                currencyId,
                                measurements,
                                specifications,
                                promisedDelivery,
                                1 // now force=1
                            );
                        } else {
                            alert("Canceled.");
                        }
                    } else {
                        alert("Failed to create order: " + (resp.data.message || "Unknown error"));
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error: Could not create order:", status, error);
                alert("AJAX error: Could not create order.");
            }
        });
    }

 
    function handleSaveClick(e) {
        const $row = $(e.target).closest("tr");
        const rfqSupplierId = $row.data("rfq_supplier_id");

        const data = {
            action: "update_rfq_table_data",
            nonce:  editableRFQTable.nonce,
            rfq_supplier_id: rfqSupplierId,

            quantity:               $row.find(".quantity-input").val(),
            // REMOVED old price:
            // price:                  $row.find(".price-input").val(),
            target_price:           $row.find(".target-price-input").val(),
            price_to_client:        $row.find(".price-to-client-input").val(),
            currency_id:            $row.find(".currency-select").val(),
            supplier_notes:         $row.find(".supplier-notes-textarea").val(),
            product_notes:          $row.find(".product-notes-textarea").val(),
            specifications:         $row.find(".specifications-input").val(),
            measurements:           $row.find(".measurements-textarea").val(),
            promised_delivery_date: $row.find(".promised-delivery-date-input").val(),

            client_status_id:       $row.find("td:nth-child(16) .status-select").val(),
            rfq_supplier_status_id: $row.find("td:nth-child(17) .status-select").val()
        };

        $.ajax({
            url: editableRFQTable.ajax_url,
            method: "POST",
            data: data,
            success: function(resp) {
                if (resp.success) {
                    alert("Data updated successfully!");
                    fetchTableData();
                } else {
                    alert("Failed to update: " + (resp.data.message || "Unknown error"));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error updating data via AJAX:", status, error);
                alert("Error updating data via AJAX.");
            }
        });
    }

    $("#create-rfq-button").on("click", function() {
        $("#create-rfq-form").slideToggle();
    });

    $("#apply-sort").on("click", function() {
        currentOrderBy = $("#sort-by").val();
        fetchTableData();
    });

    /**
     * Download Table as Excel (CSV) Functionality
     */
    $("#download-excel-button").on("click", function() {
        const table = document.getElementById("editable-rfq-table");
        let csv = [];

        // Extract headers
        const headers = [];
        table.querySelectorAll("thead th").forEach(th => {
            headers.push(th.innerText.trim());
        });
        csv.push(headers.join(","));

        // Extract rows
        table.querySelectorAll("tbody tr").forEach(tr => {
            const row = [];
            tr.querySelectorAll("td").forEach((td, index) => {
                if (index === 1) { // Client Name
                    row.push(`"${td.innerText.trim()}"`);
                } else if (index === 7) { // Price (aggregated)
                    row.push(`"${td.querySelector('.price-input').value.trim()}"`);
                } else if (index === 12) { // Promised Delivery Date
                    row.push(`"${td.querySelector('.promised-delivery-date-input').value.trim()}"`);
                } else if (index === 14 || index === 15) { // Status dropdowns
                    row.push(`"${td.querySelector('.status-select').value.trim()}"`);
                } else {
                    row.push(`"${td.innerText.trim()}"`);
                }
            });
            csv.push(row.join(","));
        });

        // Convert to Blob
        const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });

        // Create download link
        const downloadLink = document.createElement("a");
        downloadLink.download = "rfq_supplier_orders.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });

    // Initialize table on page load
    fetchTableData();
});
