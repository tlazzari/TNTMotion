jQuery(document).ready(function($){
  // 1) Which column to sort by
  var currentOrderBy = 'rfq_supplier.rfq_supplier_id';

  // 2) Fetch + render
  function fetchTableData(){
    $.post(editableRFQTable.ajax_url, {
      action:  'fetch_rfq_table_data',
      nonce:   editableRFQTable.nonce,
      orderby: currentOrderBy
    }, function(resp){
      if (!resp.success || !Array.isArray(resp.data.rows)) {
        return $('#editable-rfq-table tbody')
          .html('<tr><td colspan="7">Error loading data.</td></tr>');
      }
      var rows = resp.data.rows;
      if (!rows.length) {
        return $('#editable-rfq-table tbody')
          .html('<tr><td colspan="7">No data.</td></tr>');
      }
      // — your existing populateTable(rows) goes here —
      populateTable(rows);
    }, 'json')
    .fail(function(){
      $('#editable-rfq-table tbody')
        .html('<tr><td colspan="7">Error loading data.</td></tr>');
    });
  }

  // 3) When any <th data-orderby> is clicked…
  $('#editable-rfq-table thead').on('click','th[data-orderby]',function(){
    var col = $(this).data('orderby');
    if (!col) return;
    currentOrderBy = col;
    // highlight
    $('#editable-rfq-table thead th').removeClass('sorted');
    $(this).addClass('sorted');
    fetchTableData();
  })


  // 4) wire up the rest of your buttons as before...
  //    (close-line-button, create-order-button, save-button, etc.)
  //    — unchanged —

  // 5) initial load
  fetchTableData();


    function populateTable(rows) {
        const tbody = $("#editable-rfq-table tbody");
        tbody.empty();

        rows.forEach(row => {
            try {
                const currencySelect = buildCurrencyDropdown(row.currency_id || '');
                const rawDate = row.promised_delivery_dates
                 ? row.promised_delivery_dates.split(', ')[0]
                 : '';
                const dateValue = (rawDate === '0000-00-00') ? '' : rawDate;
                const rowHTML = `
<tr 
  data-rfq_supplier_id="${row.rfq_supplier_id}" 
  data-rfq_product_id ="${row.rfq_product_id}" 
  data-quotation_id   ="${row.quotation_id || ''}"
  data-supplier_id    ="${row.supplier_id}"
>
  <td>
    <button class="close-line-button">✓</button>
    <button class="create-order-button">Make Order</button>
  </td>
  <td>${row.client_name || ''}</td>
  <td>${row.rfq_supplier_id}</td>
  <td>
    <a href="https://tntbearings.com/clients-and-rfq/?rfq_product_id=${row.rfq_product_id}"
       target="_blank" rel="noopener">
      ${row.rfq_product_id}
    </a>
  </td>
  <td>${row.supplier_name || ''}</td>
  <td>${row.product_name || ''}</td>
  <!-- make these narrower via inline style or a class -->
  <td style="width:60px">
    <input type="text" class="quantity-input" value="${row.quantity || ''}">
  </td>
  <td style="width:80px">
    <input type="number" step="0.01" class="price-input" value="${row.price || ''}">
  </td>
  <td>${currencySelect}</td>
  <td><textarea class="supplier-notes-textarea">${row.supplier_notes || ''}</textarea></td>
  <td><textarea class="product-notes-textarea">${row.product_notes || ''}</textarea></td>
  <td><input type="text" class="specifications-input" value="${row.specifications || ''}"></td>
  <td><textarea class="measurements-textarea">${row.measurements || ''}</textarea></td>
  <td>
    <input type="date" class="promised-delivery-date-input" value="${dateValue}">
  </td>
</tr>
            `;
                tbody.append(rowHTML);

            } catch (err) {
                console.error("Error building row HTML:", row, err);
            }
        });

        // No need to attach event listeners here
    }

    // Build currency dropdown from window.availableCurrencies
    function buildCurrencyDropdown(selectedId) {
        if (!window.availableCurrencies || window.availableCurrencies.length === 0) {
            return `<select class="currency-select"><option value="">No Currencies</option></select>`;
        }
        let html = '<select class="currency-select">';
        window.availableCurrencies.forEach(c => {
            const sel = (parseInt(c.currency_id) === parseInt(selectedId)) ? 'selected' : '';
            html += `<option value="${c.currency_id}" ${sel}>${c.currency_description}</option>`;
        });
        html += '</select>';
        return html;
    }

    // Build status dropdown from window.availableStatuses
    function buildStatusDropdown(selectedId) {
        if (!window.availableStatuses || window.availableStatuses.length === 0) {
            return `<select class="status-select"><option value="">No Statuses</option></select>`;
        }
        let html = '<select class="status-select">';
        window.availableStatuses.forEach(s => {
            const sel = (parseInt(s.status_id) === parseInt(selectedId)) ? 'selected' : '';
            html += `<option value="${s.status_id}" ${sel}>${s.status_name}</option>`;
        });
        html += '</select>';
        return html;
    }

    // Event delegation for dynamically added elements
    $("#editable-rfq-table").on("click", ".close-line-button", handleCloseLineClick);
    $("#editable-rfq-table").on("click", ".create-order-button", handleCreateOrderClick);

      // auto-save on blur or change of any editable cell
  $("#editable-rfq-table").on("blur change",
    ".quantity-input, .price-input, .currency-select, .supplier-notes-textarea," +
    ".product-notes-textarea, .specifications-input, .measurements-textarea," +
    ".promised-delivery-date-input",
    function(){
      const $row = $(this).closest("tr");
      const rfqSupplierId = $row.data("rfq_supplier_id");
      const quotationId = $row.data('quotation-id');

      const payload = {
        action: "update_rfq_table_data",
        nonce:  editableRFQTable.nonce,
        rfq_supplier_id:        rfqSupplierId,
        quotation_id:           quotationId,
        quantity:               $row.find(".quantity-input").val(),
        price:                  $row.find(".price-input").val(),
        currency_id:            $row.find(".currency-select").val(),
        supplier_notes:         $row.find(".supplier-notes-textarea").val(),
        product_notes:          $row.find(".product-notes-textarea").val(),
        specifications:         $row.find(".specifications-input").val(),
        measurements:           $row.find(".measurements-textarea").val(),
        promised_delivery_date: $row.find(".promised-delivery-date-input").val(),
        client_status_id:       $row.find("td:nth-child(15) .status-select").val(),
        rfq_supplier_status_id: $row.find("td:nth-child(16) .status-select").val()
      };

      $.post(editableRFQTable.ajax_url, payload, function(resp){
        if (!resp.success) {
          alert("Save failed: " + (resp.data.message||"Unknown"));
        }
      }, "json");
  });


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
                    // Remove the row from DOM
                    $row.remove();
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

    // Handle "Make Order" button click
    function handleCreateOrderClick(e) {
        const $row = $(e.target).closest("tr");

        // Read data from data-* attributes and input fields
        const rfqSupplierId = $row.data("rfq_supplier_id");
        const rfqProductId  = $row.data("rfq_product_id");
        const supplierId    = $row.data("supplier_id");

        const price         = $row.find(".price-input").val() || 0;
        const currencyId    = $row.find(".currency-select").val() || 1;

        const measurements  = $row.find(".measurements-textarea").val() || "";
        const specifications= $row.find(".specifications-input").val() || "";
        const promisedDelivery = $row.find(".promised-delivery-date-input").val() || null;

        // Then pass them along:
        makeOrderRequest(
            rfqSupplierId, 
            rfqProductId, 
            supplierId,       // Now we have it
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
                        // Confirm override
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


    


    /**
     * Download Table as Excel (CSV) Functionality
     */
    $("#download-excel-button").on("click", function() {
        const table = document.getElementById("editable-rfq-table");
        let csv = [];

        // Extract headers
        const headers = [];
        table.querySelectorAll("thead th").forEach(th => {
            headers.push(`"${th.innerText.trim()}"`);
        });
        csv.push(headers.join(","));

        // Extract rows
        table.querySelectorAll("tbody tr").forEach(tr => {
            const row = [];
            tr.querySelectorAll("td").forEach((td, index) => {
                if (index === 1) { // Client Name
                    row.push(`"${td.innerText.trim()}"`);
                } else if (index === 7) { // Price (aggregated)
                    const priceInput = td.querySelector('.price-input');
                    row.push(`"${priceInput ? priceInput.value.trim() : ''}"`);
                } else if (index === 12) { // Promised Delivery Date
                    const dateInput = td.querySelector('.promised-delivery-date-input');
                    row.push(`"${dateInput ? dateInput.value.trim() : ''}"`);
                } else if (index === 14 || index === 15) { // Status dropdowns
                    const select = td.querySelector('.status-select');
                    row.push(`"${select ? select.options[select.selectedIndex].text.trim() : ''}"`);
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

