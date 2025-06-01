/**
 * rfq-script.js
 *
 * Comprehensive JavaScript for RFQ Form Functionality
 * — plus two new editable columns: target_price & price_to_client
 */

function debounce(func, delay) {
  let debounceTimer;
  return function() {
    const context = this, args = arguments;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => func.apply(context,args), delay);
  };
}

document.addEventListener('DOMContentLoaded', function() {
    // if this page doesn’t have the RFQ form, do nothing
     if (! document.getElementById('rfq_form') ) return;
  console.log("Initializing RFQ Form...");

    // PART A: FORM ELEMENTS INITIALIZATION
    const rfqForm = document.getElementById('rfq_form');
    const rfqOptionRadios = document.querySelectorAll('input[name="rfq_option"]');

    const newClientSection = document.getElementById('new_client_section');
    const existingClientSection = document.getElementById('existing_client_section');
    const cancelExistingClientBtn = document.getElementById('cancel_existing_client');

    const existingRfqsTableContainer = document.getElementById('existing_rfqs_table_container');
    const existingRfqsTableBody = document.querySelector('#existing_rfqs_table tbody');
    const selectedRfqClientIdInput = document.getElementById('selected_rfq_client_id');

    const productsContainer = document.getElementById('products_container');
    const initialProductEntry = document.querySelector('.product-entry'); // Updated to 'product-entry'
    const addAnotherProductBtn = document.getElementById('add_another_product_btn');
    let productCount = 1; // Initialize count based on existing product entries

    /**
     * Shows the New Client section and hides others.
     */
    function showNewClientSection(){
        console.log("Showing New Client Section");
        newClientSection.style.display = 'block';
        existingClientSection.style.display = 'none';
        existingRfqsTableContainer.style.display = 'none';
        existingRfqsTableBody.innerHTML = '';
        selectedRfqClientIdInput.value = '';
    }

    /**
     * Shows the Existing Client section and hides others.
     */
    function showExistingClientSection(){
        console.log("Showing Existing Client Section");
        newClientSection.style.display = 'none';
        existingClientSection.style.display = 'block';
        existingRfqsTableContainer.style.display = 'none';
        existingRfqsTableBody.innerHTML = '';
        selectedRfqClientIdInput.value = '';
    }

    /**
     * Initializes the form by showing the New Client section and setting up suggestions.
     */
    function initializeForm(){
        showNewClientSection();
        initializeAllSuggestions();
    }

    /**
     * Sets up event listeners for RFQ option radio buttons.
     */
    rfqOptionRadios.forEach(function(radio){
        radio.addEventListener('change', function(){
            console.log(`RFQ Option changed to: ${this.value}`);
            if(this.value === 'new'){ showNewClientSection(); }
            else if(this.value === 'existing'){ showExistingClientSection(); }
        });
    });

    /**
     * Sets up the Cancel button in the Existing Client section.
     */
    if(cancelExistingClientBtn){
        cancelExistingClientBtn.addEventListener('click', function(){
            console.log("Canceling Existing Client Selection");
            showNewClientSection();
            clearFields(['existing_client_name','existing_client_id']);
        });
    }

    /**
     * Clears specified form fields.
     * @param {Array} fieldIds - Array of field IDs to clear.
     */
    function clearFields(fieldIds){
        fieldIds.forEach(function(id){
            const fld = document.getElementById(id);
            if(fld) fld.value = '';
        });
    }

    /**
     * Sets up the Add Another Product button functionality.
     */
    addAnotherProductBtn.addEventListener('click', function(){
        console.log("Adding Another Product");
        productCount++;
        const newSection = initialProductEntry.cloneNode(true);

        // Update IDs and names to ensure uniqueness
        newSection.querySelectorAll('input, textarea').forEach(field => {
            if(field.id){
                const originalId = field.id;
                // Extract the base ID without the trailing number
                const baseId = originalId.substring(0, originalId.lastIndexOf('_') + 1);
                const newId = `${baseId}${productCount}`;
                field.id = newId;

                // Update corresponding label's 'for' attribute
                const lbl = newSection.querySelector(`label[for="${originalId}"]`);
                if(lbl){
                    lbl.setAttribute('for', newId);
                }
            }
            // Clear the value
            field.value = '';
        });

        // Append the cloned section to the products container
        productsContainer.appendChild(newSection);
        initializeAllSuggestions(); // Initialize suggestions for the new fields
    });

    // PART B: SUGGESTIONS INITIALIZATION

    /**
     * Initializes all suggestion functionalities for client names and product fields.
     */
    function initializeAllSuggestions(){
        console.log("Initializing All Suggestions");
        const productEntries = productsContainer.querySelectorAll('.product-entry');
        productEntries.forEach(entry => {
            const familyInput = entry.querySelector('[id^="product_family_name_"]');
            const familyId = entry.querySelector('[id^="product_family_id_"]');
            const familySug = entry.querySelector('.product_family_suggestions');
            if(familyInput && familyId && familySug){
                handleSuggestionsWithNew({
                    inputField: familyInput,
                    hiddenField: familyId,
                    suggestionsContainer: familySug,
                    actionParam: 'rfq_get_product_families',
                    createAction: 'rfq_create_new_product_family',
                    queryParam: 'product_family_name' // Specify the AJAX query parameter
                });
            }
            const productInput = entry.querySelector('[id^="product_name_"]');
            const productId = entry.querySelector('[id^="product_id_"]');
            const productSug = entry.querySelector('.product_suggestions');
            if(productInput && productId && productSug){
                handleSuggestionsWithNew({
                    inputField: productInput,
                    hiddenField: productId,
                    suggestionsContainer: productSug,
                    actionParam: 'rfq_get_products',
                    createAction: 'rfq_create_new_product',
                    queryParam: 'product_name' // Specify the AJAX query parameter
                });
            }
        });

        // Initialize suggestions for New and Existing Client sections
        const newClientNameInput = document.getElementById('new_client_name');
        const newClientIdInput = document.getElementById('new_client_id');
        const newClientSuggestions = document.getElementById('new_client_suggestions');
        if(newClientNameInput && newClientIdInput && newClientSuggestions){
            handleNewClientSuggestions(newClientNameInput, newClientIdInput, newClientSuggestions);
        }

        const existingClientNameInput = document.getElementById('existing_client_name');
        const existingClientIdInput = document.getElementById('existing_client_id');
        const existingClientSuggestions = document.getElementById('existing_client_suggestions');
        if(existingClientNameInput && existingClientIdInput && existingClientSuggestions){
            handleExistingClientSuggestions(existingClientNameInput, existingClientIdInput, existingClientSuggestions);
        }
    }

    /**
     * Handles suggestions with the ability to create new entries.
     * @param {Object} config - Configuration object.
     * @param {HTMLElement} config.inputField - The input field element.
     * @param {HTMLElement} config.hiddenField - The hidden input field to store the selected ID.
     * @param {HTMLElement} config.suggestionsContainer - The container to display suggestions.
     * @param {string} config.actionParam - The AJAX action parameter for fetching suggestions.
     * @param {string} config.createAction - The AJAX action parameter for creating new entries.
     * @param {string} config.queryParam - The parameter name to use in the AJAX request.
     */
    function handleSuggestionsWithNew(config) {
        const { inputField, hiddenField, suggestionsContainer, actionParam, createAction, queryParam } = config;
        console.log(`Setting up suggestions for input: ${inputField.id}`);

        inputField.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            if (query.length < 1) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
                return;
            }
            console.log(`Fetching suggestions for ${actionParam}: "${query}"`);
            const data = new FormData();
            data.append('action', actionParam);
            data.append('nonce', rfq_ajax_object.nonce);
            data.append(queryParam, query); // Use the specified query parameter

            fetch(rfq_ajax_object.ajax_url, { method: 'POST', body: data })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(resp => {
                    console.log(`Received response for action "${actionParam}":`, resp);
                    if (!resp.success) {
                        console.error(`Error fetching suggestions for ${actionParam}:`, resp);
                        suggestionsContainer.innerHTML = '<div class="suggestion-item">Error fetching suggestions.</div>';
                        suggestionsContainer.style.display = 'block';
                        return;
                    }
                    const results = resp.data.results || [];
                    suggestionsContainer.innerHTML = '';
                    suggestionsContainer.style.display = 'block';

                    if (results.length === 0) {
                        // Option to create a new entry
                        const createOption = document.createElement('div');
                        createOption.classList.add('suggestion-item');
                        createOption.textContent = `Create new "${query}"`;
                        createOption.style.fontStyle = 'italic';
                        createOption.addEventListener('click', () => {
                            createNewEntry(query, createAction, inputField, hiddenField, suggestionsContainer);
                        });
                        suggestionsContainer.appendChild(createOption);
                        return;
                    }

                    results.forEach(item => {
                      const suggestion = document.createElement('div');
                      suggestion.classList.add('suggestion-item');
                      suggestion.textContent = item.name;             // use the "name" field
                      suggestion.addEventListener('click', (e) => {
                        e.stopPropagation();
                        inputField.value          = item.name;
                        hiddenField.value         = item.id;
                        suggestionsContainer.innerHTML = '';
                        suggestionsContainer.style.display = 'none';
                        // ↓← **no** fetchRfqsForClient here
                      });
                      suggestionsContainer.appendChild(suggestion);
                    });
                })
                .catch(err => {
                    console.error(`Error fetching suggestions for ${actionParam}:`, err);
                    suggestionsContainer.innerHTML = '<div class="suggestion-item">Error fetching suggestions.</div>';
                    suggestionsContainer.style.display = 'block';
                });
        }, 300)); // Debounce delay of 300ms

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!suggestionsContainer.contains(e.target) && e.target !== inputField){
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    /**
     * Handles suggestions for new client namesYY.
     * @param {HTMLElement} inputField - The client name input field.
     * @param {HTMLElement} hiddenField - The hidden client ID field.
     * @param {HTMLElement} suggestionsContainer - The container for suggestions.
     */
    function handleNewClientSuggestions(inputField, hiddenField, suggestionsContainer){
        inputField.addEventListener('input', debounce(function(){
            const query = this.value.trim();
            if(query.length < 1){
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
                return;
            }
            console.log(`Fetching new client suggestions for query: "${query}"`);
            const data = new FormData();
            data.append('action', 'rfq_get_new_client_suggestions'); // Updated action name
            data.append('nonce', rfq_ajax_object.nonce);
            data.append('client_name', query);

            fetch(rfq_ajax_object.ajax_url, {method: 'POST', body: data})
            .then(response => response.json())
            .then(resp => {
                console.log(`Received response for action "rfq_get_new_client_suggestions":`, resp);
                if(!resp.success){
                    console.error('Error fetching new client suggestions:', resp);
                    suggestionsContainer.innerHTML = '<div class="suggestion-item">Error fetching suggestions.</div>';
                    suggestionsContainer.style.display = 'block';
                    return;
                }
                const results = resp.data.results || [];
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'block';
                if(results.length === 0){
                    suggestionsContainer.innerHTML = '<div class="suggestion-item">No suggestions found.</div>';
                    return;
                }
                results.forEach(item => {
                    const suggestion = document.createElement('div');
                    suggestion.classList.add('suggestion-item');
                    suggestion.textContent = item.client_name;
                    suggestion.setAttribute('tabindex', '0'); // Make it focusable
                    suggestion.addEventListener('click', (e) => {
                        e.stopPropagation();
                        inputField.value = item.client_name;
                        hiddenField.value = item.client_id;
                        suggestionsContainer.innerHTML = '';
                        suggestionsContainer.style.display = 'none';
                    });
                    suggestionsContainer.appendChild(suggestion);
                });
            })
            .catch(err => {
                console.error('Error fetching new client suggestions:', err);
                suggestionsContainer.innerHTML = '<div class="suggestion-item">Error fetching suggestions.</div>';
                suggestionsContainer.style.display = 'block';
            });
        }, 300)); // Debounce delay of 300ms

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if(!suggestionsContainer.contains(e.target) && e.target !== inputField){
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    /**
     * Handles suggestions for existing client names.
     * @param {HTMLElement} inputField - The existing client name input field.
     * @param {HTMLElement} hiddenField - The hidden client ID field.
     * @param {HTMLElement} suggestionsContainer - The container for suggestions.
     */
    function handleExistingClientSuggestions(inputField, hiddenField, suggestionsContainer){
  inputField.addEventListener('input', debounce(function(){
    const query = this.value.trim();
    if(query.length < 1){
      suggestionsContainer.innerHTML = '';
      suggestionsContainer.style.display = 'none';
      return;
    }

    console.log(`Fetching existing client suggestions for query: "${query}"`);
    const data = new FormData();
    data.append('action', 'rfq_get_existing_client_suggestions');
    data.append('nonce', rfq_ajax_object.nonce);
    data.append('search_term', query);

    fetch(rfq_ajax_object.ajax_url, { method: 'POST', body: data })
      .then(response => response.json())
      .then(resp => {
        console.log(`Received response for action "rfq_get_existing_client_suggestions":`, resp);
        if(!resp.success){
          console.error('Error fetching existing client suggestions:', resp);
          suggestionsContainer.innerHTML = '<div class="suggestion-item">Error fetching suggestions.</div>';
          suggestionsContainer.style.display = 'block';
          return;
        }

        const results = resp.data.results || [];
        suggestionsContainer.innerHTML = '';
        suggestionsContainer.style.display = 'block';

        if(results.length === 0){
          suggestionsContainer.innerHTML = '<div class="suggestion-item">No suggestions found.</div>';
          return;
        }

        results.forEach(item => {
          const ids = item.rfq_ids || [];
          const suggestion = document.createElement('div');
          suggestion.classList.add('suggestion-item');
          suggestion.textContent = 
            `Client: ${item.client_name} (RFQ ID${ids.length>1?'s':''}: ${ids.join(', ')})`;
          suggestion.setAttribute('tabindex', '0');
          suggestion.addEventListener('click', (e) => {
            e.stopPropagation();
            inputField.value = item.client_name;
            hiddenField.value = item.client_id;
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.style.display = 'none';
            // Now fetch and display their RFQs:
            fetchRfqsForClient(item.client_id);
          });
          suggestionsContainer.appendChild(suggestion);
        });
      })
      .catch(err => {
        console.error('Error fetching existing client suggestions:', err);
        suggestionsContainer.innerHTML = '<div class="suggestion-item">Error fetching suggestions.</div>';
        suggestionsContainer.style.display = 'block';
      });
  }, 300)); // Debounce delay of 300ms

  // Hide suggestions when clicking outside
  document.addEventListener('click', function(e){
    if(!suggestionsContainer.contains(e.target) && e.target !== inputField){
      suggestionsContainer.innerHTML = '';
      suggestionsContainer.style.display = 'none';
    }
  });
}

/**
 * Fetch and display RFQs for a given client.
 * @param {number} clientId – The ID of the selected client.
 */
function fetchRfqsForClient(clientId) {
  console.log(`Fetching RFQs for Client ID: ${clientId}`);
  const data = new FormData();
  data.append('action', 'rfq_get_rfqs_for_client_v2');
  data.append('nonce', rfq_ajax_object.nonce);
  data.append('client_id', clientId);

  fetch(rfq_ajax_object.ajax_url, { method: 'POST', body: data })
    .then(r => r.json())
    .then(resp => {
      console.log(`Received response for "rfq_get_rfqs_for_client_v2":`, resp);
      if (!resp.success) {
        console.error('Error fetching RFQs for client:', resp);
        alert("Error fetching RFQs for the selected client.");
        return;
      }
      const rfqs = resp.data.rfqs || [];
      if (rfqs.length === 0) {
        alert("No RFQs found for the selected client.");
        return;
      }
      console.log(`${rfqs.length} RFQs found. Displaying in table.`);
      // store selected RFQ IDs if needed
      selectedRfqClientIdInput.value = rfqs.map(r => r.rfq_client_id).join(',');
      populateExistingRfqsTable(rfqs);
      existingRfqsTableContainer.style.display = 'block';
    })
    .catch(err => {
      console.error("Error fetching RFQs for client:", err);
      alert("Error fetching RFQs for the selected client.");
    });
}



    /**
     * Handles suggestions with the ability to create new entries.
     * @param {Object} config - Configuration object.
     * @param {HTMLElement} config.inputField - The input field element.
     * @param {HTMLElement} config.hiddenField - The hidden input field to store the selected ID.
     * @param {HTMLElement} config.suggestionsContainer - The container to display suggestions.
     * @param {string} config.actionParam - The AJAX action parameter for fetching suggestions.
     * @param {string} config.createAction - The AJAX action parameter for creating new entries.
     * @param {string} config.queryParam - The parameter name to use in the AJAX request.
     */
    
    /**
     * Creates a new entry (Product Family or Product) via AJAX and updates the form fields.
     * @param {string} name - The name of the new entry to create.
     * @param {string} createAction - The AJAX action parameter for creating the entry.
     * @param {HTMLElement} inputField - The input field element.
     * @param {HTMLElement} hiddenField - The hidden input field to store the created ID.
     * @param {HTMLElement} suggestionsContainer - The container to display suggestions.
     */
function createNewEntry(name, createAction, inputField, hiddenField, suggestionsContainer) {
    console.log(`Creating new entry: ${name} via action: ${createAction}`);

    const data = new FormData();
    data.append('action', createAction);
    data.append('nonce', rfq_ajax_object.nonce);
    data.append('name', name);

    // Log the FormData entries
    for (const [key, value] of data.entries()) {
        console.log(`${key}: ${value}`);
    }

    fetch(rfq_ajax_object.ajax_url, { method: 'POST', body: data })
        .then(response => {
            console.log("AJAX Response Status:", response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(resp => {
            console.log('AJAX Response Data:', resp);
            if (!resp.success) {
                console.error(`Error creating new entry for ${createAction}:`, resp);
                alert(`Failed to create new entry: ${resp.message || 'Unknown error'}`);
                return;
            }

            inputField.value = resp.data.product_name;
            hiddenField.value = resp.data.product_id;
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.style.display = 'none';
            alert(`"${name}" has been created successfully.`);
        })
        .catch(err => {
            console.error(`Error creating new entry for ${createAction}:`, err);
            alert("Error creating new entry.");
        });
}

    /**
     * Fetches RFQs for a specific client and handles the display of existing RFQs.
     * @param {number} clientId - The ID of the client.
     * @param {number} rfqClientId - The selected RFQ Client ID.
     */
function populateExistingRfqsTable(rfqs){
  existingRfqsTableBody.innerHTML = '';
  rfqs.forEach(rfq => {
    // loop each product/quantity pair
    rfq.products.forEach((prodName, i) => {
      const qty = rfq.quantities[i] || '';
      const tr = document.createElement('tr');

      // 1) Select radio
      const tdSel = document.createElement('td');
      const radio = document.createElement('input');
      radio.type = 'radio';
      radio.name = 'selected_existing_rfq';
      radio.value = rfq.rfq_client_id;
      radio.addEventListener('click', () => {
        selectedRfqClientIdInput.value = rfq.rfq_client_id;
      });
      tdSel.appendChild(radio);
      tr.appendChild(tdSel);

      // 2) RFQ Client ID
      const tdId = document.createElement('td');
      tdId.textContent = rfq.rfq_client_id;
      tr.appendChild(tdId);

      // 3) Product Name
      const tdProd = document.createElement('td');
      tdProd.textContent = prodName;
      tr.appendChild(tdProd);

      // 4) Quantity
      const tdQty = document.createElement('td');
      tdQty.textContent = qty;
      tr.appendChild(tdQty);

      existingRfqsTableBody.appendChild(tr);
    });
  });
  existingRfqsTableContainer.style.display = 'block';
}

    /**
     * Populates the Existing RFQs table with the provided RFQs.
     * @param {Array} rfqs - Array of RFQ objects.
     */
    function populateExistingRfqsTable(rfqs){
        existingRfqsTableBody.innerHTML = '';
        rfqs.forEach(rfq => {
            const tr = document.createElement('tr');

            // Selection Radio
            const tdSel = document.createElement('td');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'selected_existing_rfq';
            radio.value = rfq.rfq_client_id;
            radio.addEventListener('click', () => {
                selectedRfqClientIdInput.value = rfq.rfq_client_id;
            });
            tdSel.appendChild(radio);

            // RFQ Client ID
            const tdId = document.createElement('td');
            tdId.textContent = rfq.rfq_client_id;

            // Notes
            const tdNotes = document.createElement('td');
            tdNotes.textContent = rfq.notes;

            // Status
            const tdStatus = document.createElement('td');
            tdStatus.textContent = rfq.status;

            // Products
            const tdProds = document.createElement('td');
            tdProds.textContent = (rfq.products || []).join(', ');

            // Quantities
            const tdQty = document.createElement('td');
            tdQty.textContent = (rfq.quantities || []).join(', ');

            tr.appendChild(tdSel);
            tr.appendChild(tdId);
            tr.appendChild(tdNotes);
            tr.appendChild(tdStatus);
            tr.appendChild(tdProds);
            tr.appendChild(tdQty);
            existingRfqsTableBody.appendChild(tr);
        });
        existingRfqsTableContainer.style.display = 'block';
    }

    // PART C: SINGLE EDITABLE DATA TABLE
    const nonce_table = rfq_ajax_object.nonce;
    const ajaxUrl_table = rfq_ajax_object.ajax_url;
    let suppliersList = [];
    let statusList = [];
    let tableInstance = null;


    // —————————————————————————————
    //  Custom filter: hide statuses 7, 8 & 19
    // —————————————————————————————
    const hiddenStatuses = [7,8,19];
    let filterActive    = true;

    // after your initDataTable() and once tableInstance is set:
    jQuery.fn.dataTable.ext.search.push(function(settings, _unused, dataIndex) {
      // only apply to this one table
      if (settings.nTable !== tableInstance.table().node()) {
        return true;
      }
      if (!filterActive) return true;

      // grab the full row object
      const row = tableInstance.row(dataIndex).data();
      return !hiddenStatuses.includes(row.status_rfq_product);
    });






    /**
     * Loads the list of suppliers via AJAX.
     * @returns {Promise} - AJAX Promise.
     */
    function loadSuppliers(){
        console.log("Loading Suppliers...");
        return jQuery.ajax({
            url: ajaxUrl_table,
            method: "POST",
            data: {
                action: "srfq_fetch_suppliers",
                nonce: nonce_table
            }
        });
    }

    /**
     * Loads the list of statuses via AJAX.
     * @returns {Promise} - AJAX Promise.
     */
    function loadStatuses(){
        console.log("Loading Statuses...");
        return jQuery.ajax({
            url: ajaxUrl_table,
            method: "POST",
            data: {
                action: "srfq_fetch_statuses",
                nonce: nonce_table
            }
        });
    }

    /**
     * Initializes the DataTable for RFQ lines.
     */
    function initDataTable(){
  console.log("Initializing DataTable...");
  tableInstance = jQuery('#single_rfq_table').DataTable({
  paging:      false,
  info:        false,
  searching:   false,
  lengthChange:false,
  autoWidth:   false,

    ajax: {
        url: ajaxUrl_table,
        type: "POST",
        data: { action: "srfq_fetch_lines", nonce: nonce_table },
        dataSrc(json) {
        if (!json.success) return [];
        let rows = json.data.rows;
        // only filter when filterActive === true
        if (filterActive) {
          const hidden = [7,8,19];
          rows = rows.filter(r => !hidden.includes(r.status_rfq_product));
        }
        return rows;
        }
      },

  //  Sort ascending on Valid Quotes (quotations_count)
  order: [[5, 'asc']],

  rowCallback(row, data) {
    // coerce to numbers (NaN → 0)
    const price  = parseFloat(data.price_to_client)    || 0;
    const quotes = parseInt(data.quotations_count, 10) || 0;

    if (price === 0 && quotes === 0) {
    jQuery(row)
      .css('background-color', '#f8d7da')      // light red
      .attr('title', 'No price and no valid quotes');
  } else {
    jQuery(row)
      .css('background-color', '')             // clear on redraw
      .removeAttr('title');
  }
},
  

  columns: [
    { data:'rfq_client_id',    title:'RFQ Client ID' },
    { data:'client_name',      title:'Client Name'   },
    { data:'product_name',     title:'Product Name'  },
    {
      data:'quantity',
      title:'Quantity',
      render:(v,t)=> t==='display'
        ? `<input type="number" min="1" step="1" class="edit-quantity" value="${v}">`
        : v
    },
    {
      data:'suppliers_count',
      title:'Suppliers',
      render:(v,t)=> t==='display'
        ? `<span>${v}</span>`
        : v
    },
    {
      data:'quotations_count',
      title:'Valid Quotes',
      render:(v,t)=> t==='display'
        ? `<span>${v}</span>`
        : v
    },
    {
      data:'target_price',
      title:'Target Price',
      render:(v,t)=> t==='display'
        ? `<input type="number" step="0.001" class="edit-target-price" value="${v}">`
        : v
    },
    // ← NEW: Currency dropdown
    {
      data:'currency_description',
      title:'Currency',
      render:(val,type,row)=> {
        if(type!=='display') return val;
        const opts = rfq_ajax_object.currencies.map(c=>
          `<option value="${c.id}"${c.name===val?' selected':''}>${c.name}</option>`
        ).join('');
        return `<select class="currency-select" data-rfq-product-id="${row.rfq_product_id}">${opts}</select>`;
      }
    },
    {
      data:'price_to_client',
      title:'Price to Client',
      render:(v,t)=> t==='display'
        ? `<input type="number" step="0.001" class="edit-price-to-client" value="${v}">`
        : v
    },
    // ← NEW: Paid Percentage
    {
      data:'paid_percentage',
      title:'Paid %',
      render:(v,t)=> t==='display'
        ? `<input type="number" min="0" max="100" step="1" class="edit-paid-percentage" value="${v||''}">`
        : v
    },
    {
      data:'notes',
      title:'Notes',
      render:(v,t)=> t==='display'
        ? `<input type="text" class="edit-notes" value="${v||''}">`
        : v
    },
    {
      data:'status_name',
      title:'Status',
      render:(txt,t,row)=> t==='display'
        ? `<span class="status-cell"
                 data-status-id="${row.status_rfq_product}"
                 data-rfq-product-id="${row.rfq_product_id}">${txt||'(No Status)'}</span>`
        : txt
    }
  ]
});

}

    /**
     * Sets up event listeners for Supplier and Status dropdowns within the DataTable.
     */
 /**
 * Sets up event listeners for Supplier, Currency, Status, inputs, etc.
 */
function setupDataTableEventListeners() {
  console.log("Setting up DataTable Event Listeners...");
  const tableBody = jQuery('#single_rfq_table tbody');

  // Unbind everything in our namespace so we don't double-bind
  tableBody.off('.rfq');

  tableBody
    // 1) Supplier dropdown change
    .on('change.rfq-supplier', 'select.supplier-dropdown', function(e) {
      e.stopPropagation();
      handleSupplierChange(jQuery(this));
    })

    // 2) Currency dropdown change (only update that cell, no reload)
    .on('change.rfq-currency', 'select.currency-select', function(e) {
      e.stopPropagation();
      const $select       = jQuery(this);
      const rfqProductId  = $select.data('rfq-product-id');
      const newCurrencyId = parseInt($select.val(), 10);
      const newCurrencyDesc = $select.find('option:selected').text();

      const data = new FormData();
      data.append('action',         'srfq_save_quotation');
      data.append('nonce',          nonce_table);
      data.append('rfq_product_id', rfqProductId);
      data.append('currency_id',    newCurrencyId);

      fetch(ajaxUrl_table, { method: 'POST', body: data })
        .then(r => r.json())
        .then(resp => {
          if (!resp.success) {
            alert('Failed to save currency: ' + (resp.data?.message || ''));
          } else {
            // Update only that cell in the DOM
            const cell = tableBody
              .find(`select.currency-select[data-rfq-product-id="${rfqProductId}"]`)
              .closest('td');
            cell.text(newCurrencyDesc);

            // Also update the DataTable's internal data so future redraws keep it
            const rowIdx = tableInstance.rows().indexes().filter(idx =>
              tableInstance.row(idx).data().rfq_product_id === rfqProductId
            )[0];
            if (rowIdx !== undefined) {
              const rowData = tableInstance.row(rowIdx).data();
              rowData.currency_id          = newCurrencyId;
              rowData.currency_description = newCurrencyDesc;
            }
          }
        })
        .catch(() => {
          alert('Error saving currency change');
        });
    })

    // 3) Status dropdown change
    .on('change.rfq-status', 'select.status-dropdown', function(e) {
      e.stopPropagation();
      handleStatusChange(jQuery(this));
    })

    // 4) Click on supplier/status cell to open the dropdown
    .on('click.rfq', 'span.supplier-cell, span.status-cell', function(e) {
      e.stopPropagation();
      const $span    = jQuery(this);
      const rowData  = tableInstance.row($span.closest('tr')).data();

      // If already open, bail
      if ($span.find('select').length) return;

      if ($span.hasClass('supplier-cell')) {
        // build supplier <select>…
        const currSupId = parseInt($span.data('supplier-id') || 0, 10);
        let html = '<select class="supplier-dropdown"><option value="0">NEW</option>';
        suppliersList.forEach(sup => {
          html += `<option value="${sup.supplier_id}"${sup.supplier_id === currSupId ? ' selected' : ''}>` +
                  sup.supplier_name +
                  `</option>`;
        });
        html += '</select>';
        $span.html(html).find('select').focus();
      } else {
        // build status <select>…
        const currStatId = parseInt($span.data('status-id') || 0, 10);
        let html = '<select class="status-dropdown">';
        statusList.forEach(st => {
          html += `<option value="${st.id}"${st.id === currStatId ? ' selected' : ''}>` +
                  st.name +
                  `</option>`;
        });
        html += '</select>';
        $span.html(html).find('select').focus();
      }
    })

    // 5) Prevent mousedown on inputs from bubbling up (keeps you in edit mode)
    .on('mousedown.rfq',
      'input.edit-quantity, input.edit-target-price, input.edit-price-to-client, ' +
      'input.edit-paid-percentage, input.edit-notes, input.edit-date',
      function(e) {
        e.stopPropagation();
      }
    )

    // 6) Any input change (quantity, prices, paid%, notes, date) → save
    .on('change.rfq',
  'input.edit-quantity, input.edit-target-price, input.edit-price-to-client, input.edit-paid-percentage, input.edit-notes, input.edit-date',
  function(e) {
    e.stopPropagation();
    const $row         = jQuery(this).closest('tr');
    const rowData      = tableInstance.row($row).data();
    const rfqProductId = rowData.rfq_product_id;
    const updateData   = new FormData();

    // always send EVERY field:
    updateData.append('action',                 'srfq_save_quotation');
    updateData.append('nonce',                  nonce_table);
    updateData.append('rfq_product_id',         rfqProductId);
    updateData.append('quantity',               parseInt($row.find('.edit-quantity').val(), 10) || 0);
    updateData.append('target_price',           parseFloat($row.find('.edit-target-price').val()) || 0);
    updateData.append('price_to_client',        parseFloat($row.find('.edit-price-to-client').val()) || 0);
    updateData.append('paid_percentage',        parseInt($row.find('.edit-paid-percentage').val(), 10) || 0);
    updateData.append('notes',                  $row.find('.edit-notes').val() || '');
    updateData.append('promised_delivery_date', $row.find('.edit-date').val() || '');

    // **NEW**: re-send the currency so it doesn’t get nulled out
    updateData.append('currency_id', rowData.currency_id);

    fetch(ajaxUrl_table, { method: 'POST', body: updateData })
      .then(r => r.json())
      .then(resp => {
        if (!resp.success) {
          alert('Failed to save: ' + (resp.data?.message || ''));
        }
      })
      .catch(() => {
        alert('Error saving quotation fields');
      });
});

}  // ← exactly one brace closes setupDataTableEventListeners()



// -----------------------------------------------------------------------------
// Now the XLSX download button **after** setupDataTableEventListeners()
// -----------------------------------------------------------------------------
jQuery('#download_xlsx_btn').off('click.rfq').on('click.rfq', function(e){
  e.stopPropagation();
  console.log("Downloading XLSX...");
  const dataArr = tableInstance.data().toArray();
  const aoa = [
    ["RFQ Client ID","Client Name","Product Name","Supplier","Target Price","Price to Client","Notes","Promised Delivery Date","Status"]
  ];

  dataArr.forEach(r => {
    aoa.push([
      r.rfq_client_id,
      r.client_name,
      r.product_name,
      r.supplier_name    || '',
      r.target_price     || '',
      r.price_to_client  || '',
      r.notes            || '',
      r.promised_delivery_date || '',
      r.status_name      || ''
    ]);
  });

  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(aoa);
  XLSX.utils.book_append_sheet(wb, ws, "RFQ Export");
  XLSX.writeFile(wb, "rfq_export.xlsx");
});


    /**
     * Handles changes in the Supplier dropdown.
     * @param {jQuery} $select - The jQuery object of the select element.
     */
    function handleSupplierChange($select) {
        const $span = $select.closest('span.supplier-cell');
        const rowData = tableInstance.row($span.closest('tr')).data();
        const rfqProductId = rowData.rfq_product_id;
        const chosenId = parseInt($select.val() || 0);
        const rfqSupplierId = parseInt($span.data('rfq-supplier-id') || 0);

        console.log(`Chosen Supplier ID: ${chosenId} for RFQ Product ID: ${rfqProductId}`);

        if (chosenId === 0) {
            // Handle creating a new supplier
            console.log(`Creating New Supplier for RFQ Product ID: ${rfqProductId}`);
            const newName = prompt("Enter new supplier name:");
            if (!newName || !newName.trim()) {
                console.log("No new supplier name entered. Reverting dropdown.");
                tableInstance.ajax.reload(null, false);
                return;
            }
            const newPrim = prompt("Enter 'primary_product' (optional):") || '';
            const createData = new FormData();
            createData.append('action', 'srfq_save_supplier');
            createData.append('nonce', rfq_ajax_object.nonce);
            createData.append('rfq_product_id', rfqProductId);
            createData.append('rfq_supplier_id', rfqSupplierId);
            createData.append('supplier_id', 0);
            createData.append('is_new_supplier', 1);
            createData.append('new_supplier_name', newName.trim());
            createData.append('new_supplier_main', newPrim);

            fetch(rfq_ajax_object.ajax_url, {method: 'POST', body: createData})
            .then(response => response.json())
            .then(resp => {
                console.log('AJAX Response for creating supplier:', resp);
                if(!resp.success){
                    alert("Failed to create supplier: " + (resp.data?.message || 'Unknown error'));
                    console.error('Failed to create supplier:', resp);
                    tableInstance.ajax.reload(null, false);
                } else {
                    console.log("New supplier created successfully:", resp);
                    // Assuming the PHP response includes 'supplier_id' and 'rfq_supplier_id'
                    const newSupplierId = resp.data.supplier_id;
                    const newRfqSupplierId = resp.data.rfq_supplier_id;
                    $span.data('supplier-id', newSupplierId)
                         .data('rfq-supplier-id', newRfqSupplierId)
                         .text(newName.trim());
                }
            })
            .catch(err => {
                console.error("Error creating new supplier:", err);
                alert("Error creating new supplier.");
                tableInstance.ajax.reload(null, false);
            });
        } else {
            // Handle updating the supplier
            console.log(`Updating Supplier for RFQ Product ID: ${rfqProductId} to Supplier ID: ${chosenId}`);
            const updateData = new FormData();
            updateData.append('action', 'srfq_save_supplier');
            updateData.append('nonce', rfq_ajax_object.nonce);
            updateData.append('rfq_product_id', rfqProductId);
            updateData.append('rfq_supplier_id', rfqSupplierId);
            updateData.append('supplier_id', chosenId);

            fetch(rfq_ajax_object.ajax_url, {method: 'POST', body: updateData})
            .then(response => response.json())
            .then(resp => {
                console.log('AJAX Response for updating supplier:', resp);
                if(!resp.success){
                    alert("Failed to update supplier: " + (resp.data?.message || 'Unknown error'));
                    console.error('Failed to update supplier:', resp);
                    tableInstance.ajax.reload(null, false);
                } else {
                    console.log("Supplier updated successfully:", resp);
                    const selectedSupplier = suppliersList.find(s => s.supplier_id === chosenId);
                    $span.data('supplier-id', chosenId)
                         .text(selectedSupplier ? selectedSupplier.supplier_name : 'UNKNOWN');
                }
            })
            .catch(err => {
                console.error("Error updating supplier:", err);
                alert("Error updating supplier.");
                tableInstance.ajax.reload(null, false);
            });
        }
    }

    /**
     * Handles changes in the Status dropdown.
     * @param {jQuery} $select - The jQuery object of the select element.
     */
    function handleStatusChange($select) {
        const $span = $select.closest('span.status-cell');
        const rowData = tableInstance.row($span.closest('tr')).data();
        const rfqProductId = rowData.rfq_product_id;
        const chosenId = parseInt($select.val() || 0);

        console.log(`Chosen Status ID: ${chosenId} for RFQ Product ID: ${rfqProductId}`);

        // Prepare AJAX request to save status
        const updateData = new FormData();
        updateData.append('action', 'rfq_save_rfq_status');
        updateData.append('nonce', rfq_ajax_object.nonce);
        updateData.append('rfq_product_id', rfqProductId);
        updateData.append('status_id', chosenId);

        console.log(`Sending AJAX request to update status for RFQ Product ID: ${rfqProductId} with Status ID: ${chosenId}`);

        fetch(rfq_ajax_object.ajax_url, {method: 'POST', body: updateData})
        .then(response => response.json())
        .then(resp => {
            console.log('AJAX Response for updating status:', resp);
            if(!resp.success){
                alert("Failed to save status: " + (resp.data?.message || 'Unknown error'));
                console.error('Failed to save status:', resp);
                tableInstance.ajax.reload(null, false);
            } else {
                console.log("Status updated successfully:", resp);
                const newStatus = statusList.find(s => s.id === chosenId)?.name || '(No Status)';
                $span.data('status-id', chosenId)
                     .text(newStatus);
            }
        })
        .catch(err => {
            console.error("Error saving status:", err);
            alert("Error saving status.");
            tableInstance.ajax.reload(null, false);
        });
    }

    /**
     * Loads suppliers and statuses, then initializes the DataTable.
     */
    function loadAndInitializeTable(){
        console.log("Loading Suppliers and Statuses, then Initializing DataTable...");
        jQuery.when(loadSuppliers(), loadStatuses())
        .done(function(respSup, respStat){
            const supResp = respSup[0];
            const statResp = respStat[0];

            if(supResp.success){
                suppliersList = supResp.data.suppliers || [];
                console.log("Suppliers loaded:", suppliersList);
            } else {
                console.warn("Failed to load suppliers:", supResp);
                suppliersList = [];
            }

            if(statResp.success){
                statusList = statResp.data.statuses || [];
                console.log("Statuses loaded:", statusList);
            } else {
                console.warn("Failed to load statuses:", statResp);
                statusList = [];
            }

            initDataTable();
            tableInstance.draw();
            setupDataTableEventListeners();
        })
        .fail(function(e1, e2){
            console.error("Error loading suppliers or statuses:", e1, e2);
            initDataTable();
            tableInstance.draw();
            setupDataTableEventListeners();
        });
    }

    // —————————————————————————————
    //  Insert “View All” next to Download XLSX
    // —————————————————————————————
    jQuery('#download_xlsx_btn').after(
      '<button id="view_all_btn" class="rfq-button" style="margin-left:8px;">View All</button>'
    );
    jQuery('#view_all_btn').on('click', function() {
      filterActive = false;
      tableInstance.ajax.reload(null, false);
      jQuery(this).prop('disabled', true).text('All Rows Shown');
    });

    // Initialize the form and load the DataTable
    initializeForm();
    loadAndInitializeTable();
});
