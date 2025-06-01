jQuery(document).ready(function ($) {
    let selectedRFQProduct = null; // Store selected RFQ product globally
    let selectedSupplier = null;   // Store selected supplier globally

    function fetchRFQProducts() {
        $.ajax({
            url: rfqSupplierAjax.ajax_url,
            method: "POST",
            data: { action: "fetch_rfq_products", nonce: rfqSupplierAjax.nonce },
            success: function (response) {
                if (response.success) {
                    populateRFQProductTable(response.data.products);
                } else {
                    $("#rfq-product-table tbody").html(
                        `<tr><td colspan="7">${response.data.message}</td></tr>`
                    );
                }
            },
            error: function () {
                $("#rfq-product-table tbody").html(
                    `<tr><td colspan="7">An error occurred while fetching RFQ products.</td></tr>`
                );
            },
        });
    }

    function updateSelectedRecords() {
        const rfqProductSectionTop = $("#supplier-section #selected-rfq-product");
        const supplierSectionTop = $("#supplier-section #selected-supplier");

        const rfqProductSectionForm = $("#rfq-supplier-form-section #selected-rfq-product");
        const supplierSectionForm = $("#rfq-supplier-form-section #selected-supplier");

        rfqProductSectionTop.html(
            selectedRFQProduct
                ? `<strong>RFQ Product:</strong> ${selectedRFQProduct.product_name} (Quantity: ${selectedRFQProduct.quantity})`
                : "No RFQ Product selected."
        );

        rfqProductSectionForm.html(
            selectedRFQProduct
                ? `<strong>RFQ Product:</strong> ${selectedRFQProduct.product_name} (Quantity: ${selectedRFQProduct.quantity})`
                : "No RFQ Product selected."
        );

        supplierSectionTop.html(
            selectedSupplier
                ? `<strong>Supplier:</strong> ${selectedSupplier.supplier_name}`
                : "No Supplier selected."
        );

        supplierSectionForm.html(
            selectedSupplier
                ? `<strong>Supplier:</strong> ${selectedSupplier.supplier_name}`
                : "No Supplier selected."
        );
    }

    function populateRFQProductTable(products) {
        const tbody = $("#rfq-product-table tbody");
        tbody.empty();

        if (products.length > 0) {
            products.forEach((product) => {
                const row = `
                    <tr>
                        <td>${product.rfq_product_id}</td>
                        <td>${product.product_name}</td>
                        <td>${product.quantity}</td>
                        <td>${product.specifications}</td>
                        <td>${product.notes}</td>
                        <td>${product.client_name}</td>
                        <td>
                            <button class="select-rfq-product" 
                                data-rfq-product-id="${product.rfq_product_id}" 
                                data-product-name="${product.product_name}" 
                                data-quantity="${product.quantity}">
                                Select
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            $(".select-rfq-product").on("click", function () {
                selectedRFQProduct = {
                    rfq_product_id: $(this).data("rfq-product-id"),
                    product_name: $(this).data("product-name"),
                    quantity: $(this).data("quantity"),
                };

                $("#rfq-product-section").hide();
                $("#supplier-section").show();

                updateSelectedRecords();
                fetchSuppliers();
            });
        } else {
            tbody.html(`<tr><td colspan="7">No RFQ products found.</td></tr>`);
        }
    }

    function fetchSuppliers() {
        $.ajax({
            url: rfqSupplierAjax.ajax_url,
            method: "POST",
            data: { action: "fetch_suppliers", nonce: rfqSupplierAjax.nonce },
            success: function (response) {
                if (response.success) {
                    populateSupplierTable(response.data.suppliers);
                } else {
                    $("#supplier-table tbody").html(
                        `<tr><td colspan="4">${response.data.message}</td></tr>`
                    );
                }
            },
            error: function () {
                $("#supplier-table tbody").html(
                    `<tr><td colspan="4">An error occurred while fetching suppliers.</td></tr>`
                );
            },
        });
    }

function populateSupplierTable(suppliers) {
    const tbody = $("#supplier-table tbody");
    tbody.empty();

    if (suppliers.length > 0) {
        suppliers.forEach((supplier) => {
            const row = `
                <tr>
                    <td>${supplier.supplier_id}</td>
                    <td>${supplier.supplier_name || "N/A"}</td>
                    <td>${supplier.notes || "N/A"}</td>
                    <td>${supplier.primary_product || "N/A"}</td>
                    <td>${supplier.database_ranking || "N/A"}</td>
                    <td>
                        <button class="select-supplier" 
                            data-supplier-id="${supplier.supplier_id}" 
                            data-supplier-name="${supplier.supplier_name || "N/A"}">
                            Select
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        $(".select-supplier").on("click", function () {
            selectedSupplier = {
                supplier_id: $(this).data("supplier-id"),
                supplier_name: $(this).data("supplier-name"),
            };

            updateSelectedRecords();

            $("#selected-supplier-id").val(selectedSupplier.supplier_id);
            $("#selected-rfq-product-id").val(selectedRFQProduct.rfq_product_id);

            $("#supplier-section").hide();
            $("#rfq-supplier-form-section").show();
        });
    } else {
        tbody.html(`<tr><td colspan="6">No suppliers found.</td></tr>`);
    }
}


    $("#rfq-supplier-form").on("submit", function (e) {
        e.preventDefault();

        const notes = $("#notes").val();

        $.ajax({
            url: rfqSupplierAjax.ajax_url,
            method: "POST",
            data: {
                action: "submit_rfq_supplier",
                nonce: rfqSupplierAjax.nonce,
                rfq_product_id: selectedRFQProduct.rfq_product_id,
                supplier_id: selectedSupplier.supplier_id,
                notes: notes,
            },
            success: function (response) {
                $("#response-message").text(response.data.message).show();
            },
            error: function () {
                $("#response-message").text("Error submitting RFQ Supplier.").show();
            },
        });
    });

    fetchRFQProducts();
});
