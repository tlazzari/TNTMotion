<?php
/*
Plugin Name: Invoice Editor
Description: A custom plugin to list, edit, export invoices, and manage invoice items with detailed views and reports.
Version: 1.8
Author: Your Name
*/




defined('ABSPATH') or die('No script kiddies please!');

// ────────────────
// 1) print invoice CSS
// ────────────────

add_action('wp_head', function(){
  if ( isset($_GET['view'], $_GET['invoice_id']) && $_GET['view']==='report' ) {
    ?>
    <style media="print">
  @page { size: A4 portrait; margin:0 }
  html, body { margin:0; padding:0; height:100% }
  body * { visibility:hidden !important }
  .no-print { display:none !important }
  .print-page, .print-page * { visibility:visible !important }
  body { position: relative }

  .print-page {
    position: absolute !important;
    left: 0; 
    width: 100%;
    margin-left:  10 !important;
    margin-right: 10 !important;
    box-sizing: border-box !important;
    padding: 15mm !important;
    border: 1px solid #ccc !important;
    background: white !important;
    page-break-inside: avoid !important;
  }

  /* Slide sheet 1 up by 5 mm and stretch it to 285 mm tall */
  .page-1 {
    top: -45mm !important;
    height: 335mm !important;
    page-break-after: always !important;
  }

  /* Push sheet 2 down by one full A4 (297 mm) plus 5 mm gap */
  .page-2 {
    top: 332mm !important;  /* 297 + 5 = 302 */
    height: auto !important;
    page-break-after: avoid !important;
  }

  /* Avoid phantom third page */
  .print-page:last-child { page-break-after: avoid !important }

  .print-page::after {
    content: attr(data-page);
    position: absolute;
    bottom: 5mm; right: 5mm;
    font-size: 12px;
  }
</style>

    <?php
  }
});



// ────────────────
// 1) AJAX HANDLER
// ────────────────
add_action( 'wp_ajax_update_rfq_totals',      'tnt_update_rfq_totals' );
add_action( 'wp_ajax_nopriv_update_rfq_totals','tnt_update_rfq_totals' );

function tnt_update_rfq_totals() {
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'invoice_editor_nonce') ) {
        wp_send_json_error('Invalid nonce');
    }
    $price    = isset($_POST['price_to_client']) ? floatval($_POST['price_to_client']) : 0;
    $id       = isset($_POST['rfq_product_id'])  ? intval($_POST['rfq_product_id'])  : 0;
    $tax      = isset($_POST['tax_to_client'])   ? floatval($_POST['tax_to_client'])   : 0;
    $tot      = isset($_POST['total_to_client']) ? floatval($_POST['total_to_client']) : 0;
    $currency = isset($_POST['currency_id'])     ? intval($_POST['currency_id'])       : 0;

    if (!$id) {
        wp_send_json_error('Missing RFQ ID');
    }

    $conn = get_invoice_editor_db_connection();
    if (!$conn) {
        wp_send_json_error('Database connection error');
    }

    $stmt = $conn->prepare("
        UPDATE rfq_product 
        SET price_to_client = ?, 
            tax_to_client = ?, 
            total_to_client = ?, 
            currency_id = ? 
            WHERE rfq_product_id = ?");
    if (!$stmt) {
        wp_send_json_error('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("dddii", $price, $tax, $tot, $currency, $id);

    if ($stmt->execute()) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Execute failed: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}

// Inline quantity update for add rfq product  list
add_action( 'wp_ajax_update_rfq_inline',      'tnt_update_rfq_inline' );
add_action( 'wp_ajax_nopriv_update_rfq_inline','tnt_update_rfq_inline' );
function tnt_update_rfq_inline() {
    if ( empty($_POST['nonce'])
      || ! wp_verify_nonce($_POST['nonce'],'invoice_editor_nonce')
    ) {
      wp_send_json_error('Invalid nonce');
    }

    $id    = intval( $_POST['rfq_product_id'] );
    $field = preg_replace('/[^a-z_]/','', $_POST['field'] );
    $value = $_POST['value'];

    // only allow updating quantity here
    if ( $field !== 'quantity' ) {
      wp_send_json_error('Invalid field');
    }

    $conn = get_invoice_editor_db_connection();
    if ( ! $conn ) {
      wp_send_json_error('DB error');
    }

    $stmt = $conn->prepare(
      "UPDATE rfq_product
         SET quantity = ?
       WHERE rfq_product_id = ?"
    );
    $stmt->bind_param( 'ii', $value, $id );
    if ( $stmt->execute() ) {
      wp_send_json_success();
    } else {
      wp_send_json_error( $stmt->error );
    }
}


// Inline update for main invoices list
add_action( 'wp_ajax_update_invoice_inline',      'tnt_update_invoice_inline' );
add_action( 'wp_ajax_nopriv_update_invoice_inline','tnt_update_invoice_inline' );
function tnt_update_invoice_inline() {
    if (
      empty($_POST['nonce'])
      || ! wp_verify_nonce( $_POST['nonce'], 'invoice_editor_nonce' )
    ) {
      wp_send_json_error('Invalid nonce');
    }

    $invoice_id = intval( $_POST['invoice_id'] );
    // sanitize field name
    $field = preg_replace('/[^a-z_]/', '', $_POST['field'] );
    // whitelist columns you want editable
    $allowed = [
      'invoice_number','invoice_date','invoice_descrpition',
      'entity_id','client_id','total','tax','grand_total',
      'currency_id','invoice_type','amount_payable'
    ];
    if ( ! in_array( $field, $allowed, true ) ) {
      wp_send_json_error('Invalid field');
    }

    $value = $_POST['value'];

    $conn = get_invoice_editor_db_connection();
    if ( ! $conn ) {
      wp_send_json_error('DB connection error');
    }

    // Build a parameterized query with the proper type
    // invoice_date, invoice_type, invoice_descrpition are strings, the rest numeric
    $types = in_array( $field, ['invoice_date','invoice_type','invoice_descrpition'], true )
           ? 'si' // string + int
           : 'di'; // double + int

    $stmt = $conn->prepare(
      "UPDATE TNT_invoices SET {$field} = ? WHERE invoice_id = ?"
    );
    $stmt->bind_param( $types, $value, $invoice_id );

    if ( $stmt->execute() ) {
      wp_send_json_success();
    } else {
      wp_send_json_error( $stmt->error );
    }
}


// ─────────────────────────────────────────────────────────────────
// NEW: AJAX endpoint to rebuild grand_total from items + RFQs
// ─────────────────────────────────────────────────────────────────
add_action( 'wp_ajax_recalc_invoice_total', 'tnt_recalc_invoice_total' );
add_action( 'wp_ajax_nopriv_recalc_invoice_total', 'tnt_recalc_invoice_total' );
function tnt_recalc_invoice_total() {
    if ( empty($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'invoice_editor_nonce') ) {
        wp_send_json_error('Invalid nonce');
    }
    $invoice_id = intval( $_POST['invoice_id'] );
    $conn = get_invoice_editor_db_connection();
    if ( ! $conn ) {
        wp_send_json_error('DB connection failed');
    }

    // 1) total (including tax)
    $stmt = $conn->prepare(
      "SELECT 
          COALESCE(SUM(quantity * total),0) 
         FROM invoices_item 
        WHERE invoice_id=?"
    );
    $stmt->bind_param('i',$invoice_id);
    $stmt->execute();
    $stmt->bind_result($items_total);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
      "SELECT 
          COALESCE(SUM(quantity * price_to_client),0) 
         FROM rfq_product 
        WHERE invoice_id=?"
    );
    $stmt->bind_param('i',$invoice_id);
    $stmt->execute();
    $stmt->bind_result($rfq_total);
    $stmt->fetch();
    $stmt->close();

    $total_sum = $items_total + $rfq_total;

    // 2) tax
    $stmt = $conn->prepare(
      "SELECT 
          COALESCE(SUM(quantity * tax),0) 
         FROM invoices_item 
        WHERE invoice_id=?"
    );
    $stmt->bind_param('i',$invoice_id);
    $stmt->execute();
    $stmt->bind_result($items_tax);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
      "SELECT 
          COALESCE(SUM(quantity * tax_to_client),0) 
         FROM rfq_product 
        WHERE invoice_id=?"
    );
    $stmt->bind_param('i',$invoice_id);
    $stmt->execute();
    $stmt->bind_result($rfq_tax);
    $stmt->fetch();
    $stmt->close();

    $tax_sum = $items_tax + $rfq_tax;

    // 3) Write back grand_total
    // calculate the values
$total_sum = $items_total + $rfq_total;
$tax_sum   = $items_tax   + $rfq_tax;
$grand_sum = $total_sum   + $tax_sum;

// now bind only variables
$upd = $conn->prepare(
  "UPDATE TNT_invoices 
      SET total      = ?, 
          tax        = ?, 
          grand_total= ? 
    WHERE invoice_id = ?"
);
$upd->bind_param(
    'dddi',
    $total_sum,
    $tax_sum,
    $grand_sum,
    $invoice_id
);

    $ok = $upd->execute();
    $upd->close();
    $conn->close();

    if ( $ok ) {
        wp_send_json_success([
          'total'       => round($total_sum,2),
          'tax'         => round($tax_sum,2),
          'grand_total' => round($total_sum + $tax_sum,2),
        ]);
    } else {
        wp_send_json_error('DB update failed');
    }
}

// ────────────────
// 2) SHORTCODE
// ────────────────
add_shortcode('tnt_invoice_editor','tnt_invoice_editor_shortcode');
function tnt_invoice_editor_shortcode() {
    ob_start();
    $conn = get_invoice_editor_db_connection();



    if (!$conn) {
    echo '<p>Database connection failed.</p>';
        return ob_get_clean();
    }

    // right after ob_start() and before any echo of HTML
echo '
<style>
/* — Card style for the “Add Invoice” form — */
.invoice-add-card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  padding: 1.5rem;
  margin-bottom: 2rem;
}
.invoice-add-card h2 {
  margin-top: 0;
  font-size: 1.5rem;
}

/* — Sticky, scrollable invoices table container — */
.invoice-table-container {
  max-height: 60vh;
  overflow: auto;
  position: relative;
  border: 1px solid #ddd;
  border-radius: 0.5rem;
}

/* — Make headers and first column sticky — */
.invoice-table-container thead th {
  position: sticky;
  top: 0;
  background: #fafafa;
  z-index: 2;
}
.invoice-table-container tbody td:first-child,
.invoice-table-container thead th:first-child {
  position: sticky;
  left: 0;
  background: #fafafa;
  z-index: 1;
}

/* — Row selection highlight — */
.invoice-table-container tbody tr.selected {
  background: #e0f7fa;
}

/* — Top‐of‐table action buttons — */
#invoice-actions {
  margin-bottom: 1rem;
}
#invoice-actions button {
  margin-right: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.25rem;
  background: #0073aa;
  color: #fff;
  cursor: pointer;
}
#invoice-actions button:disabled {
  background: #ccc;
  cursor: not-allowed;
}
/* Make label and control sit side by side */
.ie-field {
  display: inline-flex;
  align-items: center;
  margin-right: 1rem;
}
.ie-label {
  margin-right: .5rem;
  white-space: nowrap;
}

</style>
';


// ✅ Moved here
    $entities   = fetch_lookup($conn,'entity','entity_id','entity_name');
    $clients    = fetch_lookup($conn,'client','client_id','client_name');
    $currencies = fetch_lookup($conn,'currency','currency_id','currency_description');




    // ────────────────
    // 0) “Add Product” PAGE
    // ────────────────
?>
    <script>
    const ajaxurl = '<?php echo esc_js( admin_url("admin-ajax.php") ); ?>';
    const nonce   = '<?php echo wp_create_nonce("invoice_editor_nonce"); ?>';
    </script>
<?PHP

    if ( isset($_GET['add_product'],$_GET['invoice_id']) && $_GET['add_product']==='1' ) {
        $invoice_id = intval($_GET['invoice_id']);

        // fetch client_id
        $res = $conn->query("SELECT client_id FROM TNT_invoices WHERE invoice_id=$invoice_id");
        if (!$res) {
            echo '<p style="color:red;">DB Error: '.esc_html($conn->error).'</p>';
            return ob_get_clean();
        }
        $inv = $res->fetch_assoc();
        if (!$inv) {
            echo '<p>No such invoice #'.esc_html($invoice_id).'</p>';
            return ob_get_clean();
        }
        $client_id = intval($inv['client_id']);

        // link to report
        echo '<p><a href="'.esc_url(add_query_arg(
            ['view'=>'report','invoice_id'=>$invoice_id],
            get_permalink()
        )).'" target="_blank">View Invoice Report</a></p>';

        // global Tax %
        echo '<p>Tax %: <input type="number" id="global_tax_pct" step="0.01" value="0">
           <button type="button" id="calc_tax">Calculate</button></p>';

        // Attached RFQ lines (editable)
        echo '<h2>合同编号 Invoice No.'.esc_html($invoice_id).' – Attached RFQ Lines</h2>';
        echo '<table border="1" cellpadding="5"><tr>'
           . '<th>ID</th><th>RFQ Client ID</th><th>产品种类 Family</th><th>产品名称 Product</th>'
           . '<th>数量 Qty</th><th>税前单价 Unit Price Before Tax</th><th>税额 Unit Tax</th>'
           . '<th>税后单价 Unit Price Afer Tax</th><th>货币 Currency</th><th>Actions</th>'
           . '</tr>';
        $attached = $conn->query(
          "SELECT rp.rfq_product_id, rc.rfq_client_id,
                  pf.product_family_name, p.product_name,
                  rp.quantity, rp.price_to_client,
                  rp.tax_to_client, rp.total_to_client,
                  rp.currency_id,
                  cur.currency_description
             FROM rfq_product rp
            JOIN rfq_client rc  ON rc.rfq_client_id    = rp.rfq_client_id
            JOIN product_family pf ON pf.product_family_id = rp.product_family_id
            JOIN products p     ON p.product_id         = rp.product_id
            LEFT JOIN currency cur ON cur.currency_id    = rp.currency_id
            WHERE rp.invoice_id=$invoice_id"
        );
            while ( $r = $attached->fetch_assoc() ) {
                echo '<tr class="rfq-row">'
                   . '<td>' . intval( $r['rfq_product_id'] ) . '</td>'
                   . '<td>' . intval( $r['rfq_client_id'] ) . '</td>'
                   . '<td>' . esc_html( $r['product_family_name'] ) . '</td>'
                   . '<td>' . esc_html( $r['product_name'] ) . '</td>'
                   . '<td><input type="number" step="1" min="0" 
                            class="qty_input" 
                            value="' . esc_attr( $r['quantity'] ) . '">' 
                    .'</td>'
                   // price input
                   . '<td><input type="number" step="0.01" '
                   .   'class="price_to_client" '
                   .   'value="' . esc_attr( $r['price_to_client'] ) . '">'
                   . '</td>'
                   // tax input
                   . '<td><input type="number" step="0.01" class="tax_to_client" '
                   .   'value="' . esc_attr( $r['tax_to_client'] ) . '"></td>'
                   // total input
                   . '<td><input type="number" step="0.01" class="total_to_client" '
                   .   'value="' . esc_attr( $r['total_to_client'] ) . '"></td>';
               echo '<td><select class="currency_id">';
                    foreach ($currencies as $cid => $cname) {
                        $sel = (intval($cid) === intval($r['currency_id'])) ? ' selected' : '';
                        echo '<option value="'.$cid.'"'.$sel.'>'.esc_html($cname).'</option>';
                    }
                    echo '</select></td>';

               echo '<td>'
               . '<a href="'.esc_url(add_query_arg(
                   ['add_product'=>1,'invoice_id'=>$invoice_id,'detach'=>$r['rfq_product_id']],
                   get_permalink()
                 )).'" onclick="return confirm(\'Remove this line?\')">'
               . 'Remove</a>'
               . '</td>'
               . '</tr>';
        }
        echo '</table>';

        // ------------------------
        // CALCULATE TAX AND TOTALS
        // ------------------------

        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
          const ajaxurl = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';
          const nonce   = '<?php echo wp_create_nonce("invoice_editor_nonce"); ?>';

          document.getElementById('calc_tax').addEventListener('click', function(){
            const ratePct = parseFloat(document.getElementById('global_tax_pct').value);
            if (isNaN(ratePct)) return;
            const rate = ratePct / 100;

            document.querySelectorAll('tr.rfq-row').forEach(row => {
              const priceEl   = row.querySelector('input.price_to_client');
              const taxEl     = row.querySelector('input.tax_to_client');
              const totEl     = row.querySelector('input.total_to_client');
              const currency  = row.querySelector('select.currency_id').value;
              const id        = row.querySelector('td:first-child').textContent.trim();

              const price = parseFloat(priceEl.value);
              const tax   = parseFloat(taxEl.value);
              const total = parseFloat(totEl.value);

              // do your three‐way calculate…
              if (!isNaN(total) && !isNaN(rate) && (isNaN(price) || priceEl.value==='')) {
                const p = total/(1+rate);
                priceEl.value = p.toFixed(2);
                taxEl.value   = (total - p).toFixed(2);
                totEl.value   = total.toFixed(2);
              } else if (!isNaN(price) && !isNaN(rate)) {
                const t = price*rate;
                taxEl.value = t.toFixed(2);
                totEl.value = (price + t).toFixed(2);
              } else if (!isNaN(price) && !isNaN(tax)) {
                totEl.value = (price + tax).toFixed(2);
              }

              // now persist via AJAX
              const form = new FormData();
              form.append('action',         'update_rfq_totals');
              form.append('nonce',          nonce);
              form.append('rfq_product_id', id);
              form.append('price_to_client', priceEl.value);
              form.append('tax_to_client',   taxEl.value);
              form.append('total_to_client', totEl.value);
              form.append('currency_id',     currency);

              fetch(ajaxurl, { method:'POST', body: form })
                .then(r => r.json())
                .then(json => {
                  if (!json.success) {
                    console.error('Failed to save line '+id, json.data);
                    row.style.background = '#fdd';
                  }
                });
            });
          });
        });

        // SAVES CHANGES IN THE ADD PRODUCT PAGE ON.BLUR
       
        document.addEventListener('DOMContentLoaded', () => {
          const ajaxurl = '<?php echo esc_js( admin_url("admin-ajax.php") ); ?>';
          const nonce   = '<?php echo wp_create_nonce("invoice_editor_nonce"); ?>';

          // helper: grab the 4 fields from a row and send AJAX
          function saveRow(row) {
            const id        = row.querySelector('td:first-child').textContent.trim();
            const priceEl   = row.querySelector('input.price_to_client');
            const taxEl     = row.querySelector('input.tax_to_client');
            const totEl     = row.querySelector('input.total_to_client');
            const currency  = row.querySelector('select.currency_id').value;

            const form = new FormData();
            form.append('action',          'update_rfq_totals');
            form.append('nonce',           nonce);
            form.append('rfq_product_id',  id);
            form.append('price_to_client', priceEl.value);
            form.append('tax_to_client',   taxEl.value);
            form.append('total_to_client', totEl.value);
            form.append('currency_id',     currency);

            fetch(ajaxurl, { method:'POST', body: form })
              .then(r=>r.json())
              .then(json => {
                if (!json.success) {
                  row.style.background = '#fdd';
                  console.error('save failed for row', id, json);
                } else {
                  row.style.background = '#dfd';
                  setTimeout(()=> row.style.background = '', 300);
                }
              });
          }

          // bind manual edits
          document.querySelectorAll('tr.rfq-row').forEach(row => {
            // inputs: price, tax, total
            row.querySelectorAll('input.price_to_client, input.tax_to_client, input.total_to_client')
               .forEach(input => input.addEventListener('blur', () => saveRow(row)));

            // currency select
            row.querySelector('select.currency_id')
               .addEventListener('change', () => saveRow(row));
          });
        });

        // BLOCKS Calculte button if all the fields are filled
        document.addEventListener('DOMContentLoaded', () => {
          const calcBtn = document.getElementById('calc_tax');

          // scan all rows; if every row has price, tax AND total filled, disable the button
          function updateCalcBtnState() {
            const rows = document.querySelectorAll('tr.rfq-row');
            let allRowsComplete = true;
            rows.forEach(row => {
              const price = row.querySelector('input.price_to_client').value.trim();
              const tax   = row.querySelector('input.tax_to_client').value.trim();
              const tot   = row.querySelector('input.total_to_client').value.trim();
              if (!price || !tax || !tot) {
                allRowsComplete = false;
              }
            });
            calcBtn.disabled = allRowsComplete;
          }

          // after any manual edit, re-check
          document.querySelectorAll('tr.rfq-row input.price_to_client, tr.rfq-row input.tax_to_client, tr.rfq-row input.total_to_client')
            .forEach(input => input.addEventListener('input', updateCalcBtnState));

          document.querySelectorAll('tr.rfq-row select.currency_id')
            .forEach(sel => sel.addEventListener('change', updateCalcBtnState));

          // initial state
          updateCalcBtnState();
        });

        // update quantity
        document.addEventListener('DOMContentLoaded', function(){
          const ajaxurl = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';
          const nonce   = '<?php echo wp_create_nonce("invoice_editor_nonce"); ?>';

          document.querySelectorAll('input.qty_input').forEach(input=>{
            input.addEventListener('blur', function(){
              const row = this.closest('tr.rfq-row');
              const id  = row.querySelector('td:first-child').innerText;
              const qty = this.value;

              const data = new FormData();
              data.append('action',         'update_rfq_inline');
              data.append('nonce',          nonce);
              data.append('rfq_product_id', id);
              data.append('field',          'quantity');
              data.append('value',          qty);

              fetch(ajaxurl, { method:'POST', body: data })
                .then(res => res.json())
                .then(json => {
                  if (json.success) {
                    // flash green on success
                    row.style.backgroundColor = '#dfd';
                    setTimeout(()=> row.style.backgroundColor = '', 300);
                  } else {
                    alert('Save failed: ' + json.data);
                  }
                })
                .catch(err=>{
                  alert('Request error: '+err);
                });
            });
          });
        });



        </script>

        

        <?php



        // detach
        if ( isset($_GET['detach']) ) {
            $pid = intval($_GET['detach']);
            $conn->query("UPDATE rfq_product SET invoice_id=NULL WHERE rfq_product_id=$pid");
            wp_redirect( esc_url_raw( add_query_arg(
                ['add_product'=>1,'invoice_id'=>$invoice_id],
                get_permalink()
            )) );
            exit;
        }

        // Available RFQ lines (STATIC)
        echo '<h2>Available RFQ Lines for Client #'.esc_html($client_id).'</h2>';
        echo '<table border="1" cellpadding="5"><tr>'
           . '<th>ID</th><th>RFQ Client ID</th><th>产品 Product</th><th>数量 Qty</th>'
           . '<th>税前单价 Unit Price Before Tax</th><th>Tax %</th><th>Unit Tax</th>'
           . '<th>税后单价 Unit Price After Tax</th><th>Currency</th><th>Status</th><th>Action</th>'
           . '</tr>';
        $available = $conn->query(
          "SELECT rp.rfq_product_id, rc.rfq_client_id,
                  p.product_name, rp.quantity,
                  rp.price_to_client, rp.tax_to_client,
                  rp.total_to_client,
                  cur.currency_description AS currency_name,
                  st.status_name
             FROM rfq_product rp
            JOIN rfq_client rc ON rc.rfq_client_id      = rp.rfq_client_id
            JOIN products p   ON p.product_id           = rp.product_id
            JOIN status st    ON st.status_id           = rp.status_rfq_product
            LEFT JOIN currency cur ON cur.currency_id   = rp.currency_id
            WHERE (rp.invoice_id IS NULL OR rp.invoice_id=0)
              AND rp.status_rfq_product IN (20,22,11)
              AND rc.client_id=$client_id"
        );
        while ($r = $available->fetch_assoc()) {
            $pct = $r['price_to_client']
                 ? round(100 * $r['tax_to_client'] / $r['price_to_client'],2)
                 : 0;
            echo '<tr>'
               . '<td>'.intval($r['rfq_product_id']).'</td>'
               . '<td>'.intval($r['rfq_client_id']).'</td>'
               . '<td>'.esc_html($r['product_name']).'</td>'
               . '<td>'.esc_html($r['quantity']).'</td>'
               . '<td>'.esc_html($r['price_to_client']).'</td>'
               . '<td>'.esc_html($pct).' %</td>'
               . '<td>'.esc_html($r['tax_to_client']).'</td>'
               . '<td>'.esc_html($r['total_to_client']).'</td>'
               . '<td>'.esc_html($r['currency_name']).'</td>'
               . '<td>'.esc_html($r['status_name']).'</td>'
               . '<td><a href="'.esc_url(add_query_arg(
                   ['add_product'=>1,'invoice_id'=>$invoice_id,'attach'=>$r['rfq_product_id']],
                   get_permalink()
                 )).'">Add to Invoice</a></td>'
               . '</tr>';
        }
        echo '</table>';

        // attach
        if ( isset($_GET['attach']) ) {
            $pid = intval($_GET['attach']);
            $conn->query("UPDATE rfq_product SET invoice_id=$invoice_id WHERE rfq_product_id=$pid");
            wp_redirect( esc_url_raw( add_query_arg(
                ['add_product'=>1,'invoice_id'=>$invoice_id],
                get_permalink()
            )) );
            exit;
        }

        // back link
        echo '<p><a href="'.esc_url(get_permalink()).'">← Back to Invoices</a></p>';

     

        return ob_get_clean();
    }


    // ────────────────
    // 1) ADD INVOICE
    // ────────────────
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_invoice'])) {
        $num  = intval($_POST['invoice_number']);
        $date = $conn->real_escape_string($_POST['invoice_date']);
        $desc = $conn->real_escape_string($_POST['invoice_descrpition']);
        $ent  = intval($_POST['entity_id']);
        $cli  = intval($_POST['client_id']);
        $tax  = floatval($_POST['tax']);
        $tot  = floatval($_POST['total']);
        $cur  = intval($_POST['currency_id']);
        $type = $conn->real_escape_string($_POST['invoice_type']);
        $amt  = floatval($_POST['amount_payable']);
        $conn->query(
            "INSERT INTO TNT_invoices
             (invoice_number,invoice_date,invoice_descrpition,entity_id,client_id,
              tax,total,currency_id,invoice_type,amount_payable)
             VALUES
             ($num,'$date','$desc',$ent,$cli,$tax,$tot,$cur,'$type',$amt)"
        );
    }

    // ────────────────
    // 2) TERMS & CONDITIONS EDIT FORM
    // ────────────────
    if (isset($_GET['view_tc'],$_GET['invoice_id'])) {
        $id = intval($_GET['invoice_id']);
        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_tc'])) {
            $tc = $conn->real_escape_string($_POST['terms_conditions']);
            $pt = $conn->real_escape_string($_POST['payment_terms']);
            $dt = $conn->real_escape_string($_POST['delivery_terms']);
            $conn->query(
                "UPDATE TNT_invoices
                 SET terms_conditions='$tc',payment_terms='$pt',delivery_terms='$dt'
                 WHERE invoice_id=$id"
            );
            echo '<div style="padding:10px;background:#dfd;">Saved!</div>';
        }
        $res = $conn->query("SELECT terms_conditions,payment_terms,delivery_terms FROM TNT_invoices WHERE invoice_id=$id");
        if (!$res) {
            echo '<p style="color:red;">DB Error: '.esc_html($conn->error).'</p>';
            return ob_get_clean();
        }
        $row = $res->fetch_assoc();
        $defaults = [

        // defaults
        'tc'=>"
一、技术标准，质量要求: ISO国际或GB国标质量体系标准。  
二、验收标准及方法: ISO国际或GB国标质量体系标准方法。  
三、包装方式: 供方以纸制或木制包装到需方，包装物不回收。  
四、解决合同纠纷办法: 双方应尽量协商解决。若不能协商，应递交上海国际仲裁中心进行仲裁。此合同由中国法律管辖。 
五、违约责任: 按合同执行，产品在使用过程中出现质量问题，供方无偿退货或换货。供方最大责任仅限于该合同价值。如果是使用不当使产品出现质量问题，供方概不负责。  
六、其他约定事项: 双方不能单向终止合同。

1. Technical standards and quality requirements: ISO international or GB quality system standards.  
2. Acceptance criteria and methods: ISO international or GB quality system standard methods.  
3. Packaging method: The supplier will deliver the product to the buyer in paper or wooden packaging, and the packaging will not be recycled.  
4. Solution to contract disputes: Both parties negotiate to resolve. Unsettled disputes shall be submit to arbitration administered by the Shanghai International Arbitration Center （SHIAC). This agreement shall be governed by the laws of the People's Republic of China.
5. Liability for breach of contract: According to the contract, if the product has quality problems during use, the supplier will return or exchange the product free of charge. In any case, the supplier's maximum liability shall not exceed the value of the current invoice. If the bearing has quality problems due to improper use, the supplier will not be responsible.  
6. Other agreed matters: The parties cannot terminate the contract unilaterally.",

        'pt' => '100% 发货， EXW',
            'dt' => '工厂， EXW',
        ];

        echo '<h2>Edit T&C for Invoice #'.$id.'</h2><form method="post">';
        echo '<p><label>Terms & Conditions</label><br><textarea name="terms_conditions" rows="8" style="width:100%;">'
             . esc_textarea($row['terms_conditions']?:$defaults['tc']) .'</textarea></p>';
        echo '<p><label>Payment Terms</label><br><textarea name="payment_terms" rows="3" style="width:100%;">'
             . esc_textarea($row['payment_terms']?:$defaults['pt']) .'</textarea></p>';
        echo '<p><label>Delivery Terms</label><br><textarea name="delivery_terms" rows="3" style="width:100%;">'
             . esc_textarea($row['delivery_terms']?:$defaults['dt']) .'</textarea></p>';
        echo '<p><button name="save_tc">Save T&C</button> '
             . '<a href="'.esc_url(remove_query_arg(['view_tc','invoice_id'])).'">← Back</a></p>';
        echo '</form>';
        return ob_get_clean();
    }


    // ────────────────
    // 3) EXPORT CSV
    // ────────────────
    if (isset($_GET['export']) && $_GET['export']==='1') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="invoices.csv"');
        $out = fopen('php://output','w');
        fputcsv($out,['ID','Number','Date','Description','Entity','Client','Tax','Total','Currency','Type','Amt Due']);
        $res = $conn->query("
            SELECT inv.invoice_id,inv.invoice_number,inv.invoice_date,inv.invoice_descrpition,
                   ent.entity_name,cli.client_name,inv.tax,inv.total,cur.currency_description,
                   inv.invoice_type,inv.amount_payable
              FROM TNT_invoices inv
         LEFT JOIN entity ent ON ent.entity_id=inv.entity_id
         LEFT JOIN client cli ON cli.client_id=inv.client_id
         LEFT JOIN currency cur ON cur.currency_id=inv.currency_id
         ORDER BY inv.invoice_id
        ");
        while($row=$res->fetch_row()) {
            fputcsv($out,$row);
        }
        fclose($out);
        exit;
    }



    //------------------
    //------------------
    // ────────────────
    // 4) REPORT VIEW
    // ────────────────
    //------------------
if ( isset($_GET['view'],$_GET['invoice_id']) && $_GET['view']==='report' ) {
    $id = intval($_GET['invoice_id']);

    




// print button
echo '<div class="no-print" style="margin:1cm;">
        <button onclick="window.print()">Print PDF</button>
      </div>';

// page 1
echo '<div class="print-page page-1" data-page="Page 1 of 2">';





        // re-declare defaults
        $default_tc = <<<EOD
一、技术标准，质量要求: ISO国际或GB国标质量体系标准。  
二、验收标准及方法: ISO国际或GB国标质量体系标准方法。  
三、包装方式: 供方以纸制或木制包装到需方，包装物不回收。  
四、解决合同纠纷办法: 双方应尽量协商解决。若不能协商，应递交上海国际仲裁中心进行仲裁。此合同由中国法律管辖。 
五、违约责任: 按合同执行，产品在使用过程中出现质量问题，供方无偿退货或换货。供方最大责任仅限于该合同价值。如果是使用不当使产品出现质量问题，供方概不负责。  
六、其他约定事项: 双方不能单向终止合同。

1. Technical standards and quality requirements: ISO international or GB quality system standards.  
2. Acceptance criteria and methods: ISO international or GB quality system standard methods.  
3. Packaging method: The supplier will deliver the product to the buyer in paper or wooden packaging, and the packaging will not be recycled.  
4. Solution to contract disputes: Both parties negotiate to resolve. Unsettled disputes shall be submit to arbitration administered by the Shanghai International Arbitration Center （SHIAC). This agreement shall be governed by the laws of the People's Republic of China.
5. Liability for breach of contract: According to the contract, if the product has quality problems during use, the supplier will return or exchange the product free of charge. In any case, the supplier's maximum liability shall not exceed the value of the current invoice. If the bearing has quality problems due to improper use, the supplier will not be responsible.  
6. Other agreed matters: The parties cannot terminate the contract unilaterally.
EOD;
        $default_pt = '合同签订后需方3天内支付货款 Buyer to complete the payment of the outstanding amount within 3 days of receiving this invoice';
        $default_dt = '供方送到需方收货地址 DDP';

        // fetch all fields including T&C
        $stmt = $conn->prepare("
            SELECT
            inv.entity_id,
            inv.invoice_number,
            inv.invoice_date,
            inv.invoice_type,
            inv.invoice_descrpition,
            inv.tax,
            inv.total,
            inv.grand_total,
            cur.currency_description   AS currency_description,
            inv.amount_payable,
            inv.terms_conditions,
            inv.payment_terms,
            inv.delivery_terms,
            ent.entity_name,
            ent.entity_address,
            ent.bank_details,
            ent.bank_account_name,
            ent.entity_tax_number,
            ent.account_number,
            ent.swift,
            cli.client_name,
            cli.address               AS client_address,
            cli.vat_number
        FROM TNT_invoices inv
        LEFT JOIN currency cur ON cur.currency_id = inv.currency_id
        LEFT JOIN entity  ent ON ent.entity_id  = inv.entity_id
        LEFT JOIN client  cli ON cli.client_id  = inv.client_id
        WHERE inv.invoice_id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $inv = $stmt->get_result()->fetch_assoc();
    $stmt->close();

        if ($inv) {

            // header
            echo '<div style="display:flex; justify-content:space-between; margin-bottom:40px;">';

            // Entity panel
            echo '<div style="flex:1; padding-right:1rem;">'
               .   '<h2 style="font-size:24px; margin:0 0 20px;">'
               .     esc_html( $inv['entity_name'] )
               .   '</h2>'
               .   '<p>'
               .     esc_html( $inv['entity_address'] )
               .     '<br>Tax#: ' . esc_html( $inv['entity_tax_number'] )
               .   '</p>'
               . '</div>';

            // Client panel
            echo '<div style="flex:1; padding-left:1rem;">'
               .   '<h2 style="font-size:24px; margin:0 0 20px;">'
               .     esc_html( $inv['client_name'] )
               .   '</h2>'
               .   '<p>'
               .     esc_html( $inv['client_address'] )
               .     '<br>VAT#: ' . esc_html( $inv['vat_number'] )
               .   '</p>'
               . '</div>';

            echo '</div>'; // .header-wrapper

            // details
// Invoice Details table
        // Invoice Details table (narrower)
echo '<h3>Invoice Details</h3>';
echo '<table border="1" cellpadding="4" cellspacing="0" style="width:100%; border-collapse:collapse; margin:20px 0;">';
echo '  <thead>';
echo '    <tr>';
echo '      <th colspan="2" style="background:#f0f0f0; text-align:left; padding:4px;">Invoice Details</th>';
echo '    </tr>';
echo '  </thead>';
echo '  <tbody>';
echo '    <tr>';
echo '      <th style="width:30%; text-align:left; padding:4px;">合同编号 Contract No.:</th>';
echo '      <td style="padding:4px;">' . esc_html( $inv['invoice_number'] ) . '</td>';
echo '    </tr>';
echo '    <tr>';
echo '      <th style="text-align:left; padding:4px;">发票号 Invoice No.:</th>';
echo '      <td style="padding:4px;">' . esc_html( $id ) . '</td>';
echo '    </tr>';
echo '    <tr>';
echo '      <th style="text-align:left; padding:4px;">日期 Date:</th>';
echo '      <td style="padding:4px;">' . esc_html( $inv['invoice_date'] ) . '</td>';
echo '    </tr>';
echo '    <tr>';
echo '      <th style="text-align:left; padding:4px;">合同类型 Invoice Type:</th>';
echo '      <td style="padding:4px;">' . esc_html( $inv['invoice_type'] ) . '</td>';
echo '    </tr>';
echo '    <tr>';
echo '      <th style="text-align:left; padding:4px;">内容 Description:</th>';
echo '      <td style="padding:4px;">' . esc_html( $inv['invoice_descrpition'] ) . '</td>';
echo '    </tr>';
echo '  </tbody>';
echo '</table>';




            // ▶ Inject previously-added RFQ table
        $attached = $conn->query(
          "SELECT rp.rfq_product_id, rc.rfq_client_id, pf.product_family_name,
                  p.product_name, rp.quantity, rp.price_to_client,
                  rp.tax_to_client, rp.total_to_client,
                  cur.currency_description, rp.currency_id
           FROM rfq_product rp
           JOIN rfq_client rc ON rc.rfq_client_id = rp.rfq_client_id
           JOIN product_family pf ON pf.product_family_id = rp.product_family_id
           JOIN products p ON p.product_id = rp.product_id
           LEFT JOIN currency cur ON cur.currency_id = rp.currency_id
           WHERE rp.invoice_id = $id"
        );
        if ($attached && $attached->num_rows) {
            echo '<h2>产品明细 Products Details And Description</h2>';
            echo '<table border="1" cellpadding="8" style="width:100%;border-collapse:collapse;table-layout:fixed;">'
               . '<th style="width:5%">ID</th>
               <th style="width:10%">RFQ Client ID</th>
               <th style="width:20%">Family</th>
               <th style="width:20%">Product</th>'
               . '<th style="width:5%">Qty</th>
               <th style="width:10%">Price</th>
               <th style="width:10%">Tax</th>'
               . '<th style="width:10%">Total</th>
               <th style="width:10%">Currency</th></tr></thead><tbody>';
            while($r=$attached->fetch_assoc()){
                echo '<tr>'
                
                   . '<td>'.intval($r['rfq_product_id']).'</td>'
                   . '<td>'.intval($r['rfq_client_id']).'</td>'
                   . '<td>'.esc_html($r['product_family_name']).'</td>'
                   . '<td>'.esc_html($r['product_name']).'</td>'
                   . '<td>'.esc_html($r['quantity']).'</td>'
                   . '<td>'.esc_html($r['price_to_client']).'</td>'
                   . '<td>'.esc_html($r['tax_to_client']).'</td>'
                   . '<td>'.esc_html($r['total_to_client']).'</td>'
                   . '<td>' . esc_html($r['currency_description']) . '</td>'

                   . '</tr>';
            }
            echo '</tbody></table>';
            echo '<div style="margin:30px 0;"></div>';
        }
    // <<< end insertion




            // items

            $rit = $conn->prepare("
            SELECT itm.item_id,
                   itm.item_description,
                   itm.notes,
                   itm.quantity,
                   itm.price,
                   itm.tax,
                   itm.total,
                   cur.currency_description
              FROM invoices_item itm
         LEFT JOIN currency cur ON cur.currency_id = itm.currency_id
             WHERE itm.invoice_id = ?
        ");
        $rit->bind_param( 'i', $id );
        $rit->execute();
        $resit = $rit->get_result();

        // Only render if at least one item exists
        if ( $resit->num_rows > 0 ) {

            echo '<h3>其他 Additional Items</h3>';
            echo '<table border="1" cellpadding="8" '
               . 'style="width:100%; border-collapse:collapse; table-layout:fixed;">';

            // Header
            echo '<thead><tr>'
               .   '<th style="width:5%">ID</th>'
               .   '<th style="width:30%">Description</th>'
               .   '<th style="width:20%">Notes</th>'
               .   '<th style="width:5%">Qty</th>'
               .   '<th style="width:10%">Price</th>'
               .   '<th style="width:10%">Tax</th>'
               .   '<th style="width:10%">Total</th>'
               .   '<th style="width:10%">Currency</th>'
               . '</tr></thead>';

            echo '<tbody>';
            while ( $row = $resit->fetch_row() ) {
                echo '<tr>'
                   .   '<td>' . esc_html( $row[0] ) . '</td>'
                   .   '<td>' . esc_html( $row[1] ) . '</td>'
                   .   '<td>' . esc_html( $row[2] ) . '</td>'
                   .   '<td>' . esc_html( $row[3] ) . '</td>'
                   .   '<td>' . esc_html( $row[4] ) . '</td>'
                   .   '<td>' . esc_html( $row[5] ) . '</td>'
                   .   '<td>' . esc_html( $row[6] ) . '</td>'
                   .   '<td>' . esc_html( $row[7] ) . '</td>'
                   . '</tr>';
            }
            echo '</tbody></table>';
        }

        $rit->close();
 

            echo '<div style="margin-top:40px; clear:both;"></div>';

            // totals
            echo '<div style="float:right; width:40%; margin-top:20px;">';
            echo '<h3 style="margin-bottom:10px; text-align:left;">TOTAL</h3>';
            echo '<table border="1" cellpadding="8" 
            style="width:100%;border-collapse:collapse;table-layout:fixed;">';
            echo '<thead><tr>'
               . '<th style="width:10%"> </th>'   // empty spacer
               . '<th style="width:20%">税额 Tax</th>'
               . '<th style="width:20%">总金额 Grand Total</th>'
               . '<th style="width:20%">货币 Currency</th>'
               . '</tr></thead><tbody>';
            echo '<tr>'
               . '<td></td>'
               . '<td>'. number_format((float)$inv['tax'], 2) .'</td>'
               . '<td>'. number_format((float)$inv['grand_total'], 2) .'</td>'
               . '<td>'.esc_html($inv['currency_description']).'</td>'
               . '</tr>';
            echo '</tbody></table>';
            echo '</div>';

            // — ensure content below clears float —
            echo '<div style="clear:both; margin-bottom:30px;"></div>';

            // amount due
            echo '<div style="text-align:right; margin-bottom:30px;"><strong>应付金额 Amount Due:</strong> ' . esc_html($inv['amount_payable']) . '</div>';

            echo '</div>';  // CLOSE THE FIRST PAGE 


            //------------------------------
            // SECOND PAGE
            
 echo '<div class="print-page page-2" data-page="Page 2 of 2">';
         

            // T&C, with fallback
            $tc = trim( $inv['terms_conditions'] ) !== '' ? $inv['terms_conditions'] : $default_tc;
            echo '<h3>合同条款 Terms &amp; Conditions</h3>';
            echo '<div style="white-space:pre-wrap;">' . esc_html( $tc ) . '</div>';

            // Mrgin
            echo '<div style="margin-top:40px; clear:both;"></div>';

            // payment terms fallback
            $pt = trim( $inv['payment_terms'] ) !== '' ? $inv['payment_terms'] : $default_pt;
            echo '<h3>结算方式 Payment Terms</h3>';
            echo '<div style="white-space:pre-wrap;">' . esc_html( $pt ) . '</div>';

            // delivery terms fallback
            $dt = trim( $inv['delivery_terms'] ) !== '' ? $inv['delivery_terms'] : $default_dt;
            echo '<h3>交货方式 Delivery Terms</h3>';
            echo '<div style="white-space:pre-wrap;">' . esc_html( $dt ) . '</div>';

            echo '<div style="clear:both; margin-bottom:30px;"></div>';



            // footer
            // pay to
            echo '<p><strong>支付方式 Please pay to:</strong> ' . esc_html($inv['entity_name']) . '</p>';
            echo '<table border="1" cellpadding="4" cellspacing="0" '
               . 'style="border-collapse:collapse; table-layout:fixed; width:50%;">'
               .   '<colgroup>'
               .     '<col style="width:20%;">'
               .     '<col style="width:30%;">'
               .   '</colgroup>'
               .   '<thead>'
               .     '<tr>'
               .       '<th>银行名字 Account Name：</th>'
               .       '<th>' . esc_html( $inv['bank_account_name'] ) . '</th>'
               .     '</tr>'
               .   '</thead>'
               .   '<tbody>'
               .     '<tr>'
               .       '<td>银行帐号 Bank Account Number：</td>'
               .       '<td>' . esc_html( $inv['account_number'] ) . '</td>'
               .     '</tr>'
               .     '<tr>'
               .       '<td>银行名称 Bank Name：</td>'
               .       '<td>' . esc_html( $inv['bank_details'] ) . '</td>'
               .     '</tr>'
               .     '<tr>'
               .       '<td>Swift</td>'
               .       '<td>' . esc_html( $inv['swift'] ) . '</td>'
               .     '</tr>'
               .   '</tbody>'
               . '</table>';


            echo '<div style="position:absolute;bottom:6cm;right:2cm;font-style:italic;">需方盖章 Please stamp to confirm</div>';


            // reminders
            echo '<div style="margin-top:40px;line-height:1.6;">';
            echo   '<p>备注：银行转账费用由支付方承担 Reminder: Banking transfer expenses have to be covered by the client</p>';
            echo   '<p>This is an electronically generated invoice, it does not need to be signed or stamped.</p>';
            echo '</div>';

            // ────────────────────────────────────────────
            //  conditional stamp in bottom‐left
            // ─────────────────────────────────────────────
            if ($inv['entity_id'] === '1' || $inv['entity_id'] === 1) {
                // TianNuo stamp
                echo '<div style="position:absolute;bottom:2cm;left:2cm;">'
                   .  '<img src="https://tntbearings.com/wp-content/uploads/2025/05/TianNuoStamp.png" '
                   .       'style="width:4cm;height:4cm;" alt="TianNuo Stamp">'
                   .'</div>';
            }
            elseif ($inv['entity_id'] === '5' || $inv['entity_id'] === 5) {
                // Saida stamp
                echo '<div style="position:absolute;bottom:2cm;left:2cm;">'
                   .  '<img src="https://tntbearings.com/wp-content/uploads/2025/05/SaidaStamp.png" '
                   .       'style="width:4cm;height:4cm;" alt="Saida Stamp">'
                   .'</div>';
            }

            } else {

            echo '<p>Invoice not found.</p>';
        }

        echo '</div>'; // end printable
        return ob_get_clean();
    }

    // ────────────────
// 4) Detail (items) VIEW & CRUD - THIS IS THE ADD ITEM PAGE
// ────────────────


if ( ! empty( $_GET['invoice_id'] ) ) {
    $detail_id = intval( $_GET['invoice_id'] );
    $stmt = $conn->prepare( "SELECT invoice_number, invoice_date, invoice_type FROM TNT_invoices WHERE invoice_id = ?" );
    $stmt->bind_param( 'i', $detail_id );
    $stmt->execute();
    $inv = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo '<h2>Items for Invoice ' . esc_html( $detail_id ) . '</h2>';
    if ( $inv ) {
        echo '<p>Invoice #: ' . esc_html( $inv['invoice_number'] )
           . ' | Date: '    . esc_html( $inv['invoice_date'] )
           . ' | Type: '    . esc_html( $inv['invoice_type'] )
           . '</p>';
    }

    // Handle add/update/delete
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        if ( isset( $_POST['add_item'] ) ) {
            $desc   = $conn->real_escape_string( $_POST['item_description'] );
            $notes  = $conn->real_escape_string( $_POST['notes'] );
            $qty    = floatval( $_POST['quantity'] );
            $price  = floatval( $_POST['price'] );
            $tax    = floatval( $_POST['tax'] );
            $total  = floatval( $_POST['total'] );
            $curid  = intval( $_POST['item_currency_id'] );
            if ( $desc && $qty > 0 ) {
                $conn->query(
                    "INSERT INTO invoices_item
                        (item_description,notes,quantity,price,tax,total,currency_id,invoice_id)
                     VALUES
                        ('$desc','$notes',$qty,$price,$tax,$total,$curid,$detail_id)"
                );
            }
        }
        if ( isset( $_POST['update_item'] ) ) {
            $iid    = intval( $_POST['item_id'] );
            $desc   = $conn->real_escape_string( $_POST['item_description'] );
            $notes  = $conn->real_escape_string( $_POST['notes'] );
            $qty    = floatval( $_POST['quantity'] );
            $price  = floatval( $_POST['price'] );
            $tax    = floatval( $_POST['tax'] );
            $total  = floatval( $_POST['total'] );
            $curid  = intval( $_POST['item_currency_id'] );
            $conn->query(
                "UPDATE invoices_item
                    SET item_description='$desc',
                        notes='$notes',
                        quantity=$qty,
                        price=$price,
                        tax=$tax,
                        total=$total,
                        currency_id=$curid
                  WHERE item_id=$iid"
            );
        }
        if ( isset( $_POST['delete_item'] ) ) {
            $iid = intval( $_POST['item_id'] );
            $conn->query( "DELETE FROM invoices_item WHERE item_id=$iid" );
        }
    }

    // table header
    echo '<table border="1" cellpadding="5" style="width:100%;border-collapse:collapse;table-layout:fixed;">';
    echo '<thead><tr>'
       . '<th style="width:5%">ID</th>'
       . '<th style="width:30%">Description</th>'
       . '<th style="width:20%">Notes</th>'
       . '<th style="width:8%">Qty</th>'
       . '<th style="width:10%">Price</th>'
       . '<th style="width:10%">Tax</th>'
       . '<th style="width:10%">Total</th>'
       . '<th style="width:7%">Curr</th>'
       . '<th style="width:10%">Actions</th>'
       . '</tr></thead><tbody>';

    // existing items
    $rit = $conn->query( "
        SELECT item_id,item_description,notes,quantity,price,tax,total,currency_id
          FROM invoices_item
         WHERE invoice_id=$detail_id
         ORDER BY item_id
    " );
    while ( $it = $rit->fetch_assoc() ) {
        echo '<tr>';
        echo '<td>' . $it['item_id'] . '</td>';
        echo '<form method="post">';
        // hidden
        echo '<input type="hidden" name="item_id" value="' . $it['item_id'] . '">';
        echo '<td><input type="text"   name="item_description" value="' . esc_attr( $it['item_description'] ) . '"></td>';
        echo '<td><input type="text"   name="notes"            value="' . esc_attr( $it['notes'] ) . '"></td>';
        echo '<td><input type="number" step="any" name="quantity" value="' . esc_attr( $it['quantity'] ) . '"></td>';
        echo '<td><input type="number" step="any" name="price"    value="' . esc_attr( $it['price'] ) . '"></td>';
        echo '<td><input type="number" step="any" name="tax"      value="' . esc_attr( $it['tax'] ) . '"></td>';
        echo '<td><input type="number" step="any" name="total"    value="' . esc_attr( $it['total'] ) . '"></td>';
        echo '<td><select name="item_currency_id">';
        foreach ( $currencies as $cid => $cname ) {
            $sel = $cid == $it['currency_id'] ? ' selected' : '';
            echo '<option value="' . $cid . '"' . $sel . '>' . esc_html( $cname ) . '</option>';
        }
        echo '</select></td>';
        echo '<td>
                <button name="update_item">Update</button>
                <button name="delete_item" onclick="return confirm(\'Delete?\');">Delete</button>
              </td>';
        echo '</form>';
        echo '</tr>';
    }

    // add-new row
    echo '<tr>';
    echo '<td>New</td>';
    echo '<form method="post">';
    echo '<td><input type="text"   name="item_description" required></td>';
    echo '<td><input type="text"   name="notes"></td>';
    echo '<td><input type="number" step="any" name="quantity" required></td>';
    echo '<td><input type="number" step="any" name="price"    required></td>';
    echo '<td><input type="number" step="any" name="tax"      required></td>';
    echo '<td><input type="number" step="any" name="total"    required></td>';
    echo '<td><select name="item_currency_id">';
    foreach ( $currencies as $cid => $cname ) {
        echo '<option value="' . $cid . '">' . esc_html( $cname ) . '</option>';
    }
    echo '</select></td>';
    echo '<td><button name="add_item">Add Item</button></td>';
    echo '</form>';
    echo '</tr>';

    echo '</tbody></table>';
    return ob_get_clean();
}


    // ────────────────
    // 5) MAIN LIST & EDIT FORM
    // ────────────────
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_invoice'])) {
        $id    = intval($_POST['invoice_id']);
        $num   = intval($_POST['invoice_number']);
        $date  = $conn->real_escape_string($_POST['invoice_date']);
        $desc  = $conn->real_escape_string($_POST['invoice_descrpition']);
        $ent   = intval($_POST['entity_id']);
        $cli   = intval($_POST['client_id']);
        $tax   = floatval($_POST['tax']);
        $tot   = floatval($_POST['total']);
        $cur   = intval($_POST['currency_id']);
        $type  = $conn->real_escape_string($_POST['invoice_type']);
        $amt   = floatval($_POST['amount_payable']);
        $grand = floatval($_POST['grand_total']);

        $conn->query("
            UPDATE TNT_invoices
            SET invoice_number=$num,
                invoice_date='$date',
                invoice_descrpition='$desc',
                entity_id=$ent,
                client_id=$cli,
                tax=$tax,
                total=$tot,
                grand_total=$grand,
                currency_id=$cur,
                invoice_type='$type',
                amount_payable=$amt
            WHERE invoice_id=$id
        ");
    }

    // ────────────────
// Add Invoice Form (revised)
// ────────────────
// ────────────────
// Add Invoice Form (2-column)
// ────────────────
echo '
<style>
  .invoice-add-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 1.5rem;
    margin-bottom: 2rem;
  }
  .invoice-add-card h2 {
    margin-top: 0;
    font-size: 1.5rem;
  }
  .invoice-add-form {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem 2rem;
  }
  .invoice-add-form .form-row {
    display: flex;
    align-items: center;
  }
  .invoice-add-form .form-row label {
    flex: 0 0 110px;
    margin-right: 0.5rem;
    font-weight: 600;
  }
  .invoice-add-form .form-row input,
  .invoice-add-form .form-row select {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 0.25rem;
  }
  .invoice-add-form .form-actions {
    grid-column: 1 / -1;
    text-align: right;
    margin-top: 1rem;
  }
  .invoice-add-form .form-actions button {
    padding: 0.75rem 1.5rem;
    background: #0073aa;
    color: #fff;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
  }
</style>

<div class="invoice-add-card">
  <h2>Add Invoice</h2>
  <form method="post" class="invoice-add-form">
    <div class="form-row">
      <label for="entity_id">Entity</label>
      <select id="entity_id" name="entity_id">';
        foreach ( $entities as $eid => $en ) {
          echo '<option value="'. esc_attr($eid) .'">'. esc_html($en) .'</option>';
        }
echo   '</select>
    </div>
    <div class="form-row">
      <label for="client_id">Client</label>
      <select id="client_id" name="client_id">';
        foreach ( $clients as $cid => $cn ) {
          echo '<option value="'. esc_attr($cid) .'">'. esc_html($cn) .'</option>';
        }
echo   '</select>
    </div>
    <div class="form-row">
      <label for="invoice_number">Number</label>
      <input id="invoice_number" name="invoice_number" required>
    </div>
    <div class="form-row">
      <label for="invoice_date">Date</label>
      <input id="invoice_date" type="date" name="invoice_date" required>
    </div>
    <div class="form-row">
      <label for="invoice_descrpition">Description</label>
      <input id="invoice_descrpition" name="invoice_descrpition">
    </div>
    <div class="form-row">
      <label for="invoice_type">Type</label>
      <input id="invoice_type" name="invoice_type">
    </div>
    <div class="form-row">
      <label for="tax">Tax</label>
      <input id="tax" type="number" step="any" name="tax">
    </div>
    <div class="form-row">
      <label for="total">Total</label>
      <input id="total" type="number" step="any" name="total">
    </div>
    <div class="form-row">
      <label for="currency_id">Currency</label>
      <select id="currency_id" name="currency_id">';
        foreach ( $currencies as $cid => $cname ) {
          echo '<option value="'. esc_attr($cid) .'">'. esc_html($cname) .'</option>';
        }
echo   '</select>
    </div>
    <div class="form-row">
      <label for="amount_payable">Amt Due</label>
      <input id="amount_payable" type="number" step="any" name="amount_payable">
    </div>
    <div class="form-actions">
      <button name="add_invoice">Add Invoice</button>
    </div>
  </form>
</div>
';



    // export link
    echo '<p><a href="?export=1">Export to Excel</a></p>';

    // ────────────────
// Action Buttons
// ────────────────
echo '<div id="invoice-actions">
        <button id="btn-items" disabled>Items</button>
        <button id="btn-report" disabled>Report</button>
        <button id="btn-add-product" disabled>Add Product</button>
        <button id="btn-recalc" disabled>Recalc Total</button>
      </div>';
    // ────────────────
// Invoices List
// ────────────────
echo '<h2>Invoices</h2>';
echo '<div class="invoice-table-container">';
echo '<table border="1" cellpadding="5" style="width:100%;border-collapse:collapse;">';
echo   '<thead><tr>'
     .   '<th>ID</th><th>#</th><th>Date</th><th>Desc</th>'
     .   '<th>Entity</th><th>Client</th><th>Total</th><th>Tax</th>'
     .   '<th>Grand Total</th><th>Curr</th><th>Type</th><th>AmtDue</th>'
     .   '<th>T&C</th>'
     . '</tr></thead>';
echo   '<tbody>';

$res = $conn->query("SELECT * FROM TNT_invoices ORDER BY invoice_id ASC");
if ( ! $res || $res->num_rows === 0 ) {
    echo '<tr><td colspan="14">'
         . ( ! $res ? 'Error: '. esc_html($conn->error) : 'No invoices found.' )
         . '</td></tr>';
} else {
    while ( $inv = $res->fetch_assoc() ) {
        $id = intval($inv['invoice_id']);
        // add data-invoice-id to the TR
        echo '<tr data-invoice-id="'. esc_attr($id) .'">';
          // hidden ID is no longer necessary—JS can read the data-* attribute
          echo '<td>'. $id .'</td>';
          echo '<td><input name="invoice_number" value="'. esc_attr($inv['invoice_number']) .'"></td>';
          echo '<td><input type="date" name="invoice_date" value="'. esc_attr($inv['invoice_date']) .'"></td>';
          echo '<td><input name="invoice_descrpition" value="'. esc_attr($inv['invoice_descrpition']) .'"></td>';

          // Entity dropdown
          echo '<td><select name="entity_id">';
            foreach ($entities as $eid => $en) {
              $sel = $eid == $inv['entity_id'] ? ' selected' : '';
              echo '<option value="'. esc_attr($eid) .'"'. $sel .'>'. esc_html($en) .'</option>';
            }
          echo '</select></td>';

          // Client dropdown
          echo '<td><select name="client_id">';
            foreach ($clients as $cid => $cn) {
              $sel = $cid == $inv['client_id'] ? ' selected' : '';
              echo '<option value="'. esc_attr($cid) .'"'. $sel .'>'. esc_html($cn) .'</option>';
            }
          echo '</select></td>';

          echo '<td><input type="number" step="any" name="total" value="'. esc_attr($inv['total']) .'"></td>';
          echo '<td><input type="number" step="any" name="tax" value="'. esc_attr($inv['tax']) .'"></td>';
          echo '<td><input type="number" step="any" name="grand_total" value="'. esc_attr($inv['grand_total']) .'"></td>';

          // Currency dropdown
          echo '<td><select name="currency_id">';
            foreach ($currencies as $cid => $cname) {
              $sel = $cid == $inv['currency_id'] ? ' selected' : '';
              echo '<option value="'. esc_attr($cid) .'"'. $sel .'>'. esc_html($cname) .'</option>';
            }
          echo '</select></td>';

          echo '<td><input name="invoice_type" value="'. esc_attr($inv['invoice_type']) .'"></td>';
          echo '<td><input type="number" step="any" name="amount_payable" value="'. esc_attr($inv['amount_payable']) .'"></td>';

          // T&C link
          echo '<td><a href="?view_tc=1&invoice_id='. $id .'" style="text-decoration:underline">Edit</a></td>';



        echo '</tr>';
    }
}

echo   '</tbody>';
echo '</table>';
echo '</div>';


     ?>

 <script>
document.addEventListener('DOMContentLoaded', function(){
  const ajaxurl = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';
  const nonce   = '<?php echo wp_create_nonce("invoice_editor_nonce"); ?>';
  let selectedRow = null;

  // 1) Inline‐save
  function saveInvoiceRow(row, field, value) {
    const data = new FormData();
    data.append('action',     'update_invoice_inline');
    data.append('nonce',      nonce);
    data.append('invoice_id', row.dataset.invoiceId);
    data.append('field',      field);
    data.append('value',      value);
    fetch(ajaxurl, { method:'POST', body: data })
      .then(r => r.json())
      .then(json => {
        row.style.backgroundColor = json.success ? '#dfd' : '#fdd';
        setTimeout(()=> row.style.backgroundColor = '', 300);
      })
      .catch(console.error);
  }

  // bind inline‐save to every input/select
  document.querySelectorAll('.invoice-table-container tbody tr').forEach(row => {
    row.querySelectorAll('input[name], select[name]').forEach(el => {
      const evt = el.tagName === 'SELECT' ? 'change' : 'blur';
      el.addEventListener(evt, () => saveInvoiceRow(row, el.name, el.value));
    });
  });

  // 2) Round any non‐editable number cells
  document.querySelectorAll('.invoice-table-container tbody tr').forEach(row => {
    row.querySelectorAll('td').forEach((td,i) => {
      if ([6,7,8].includes(i) && !td.querySelector('input')) {
        td.textContent = (parseFloat(td.textContent) || 0).toFixed(2);
      }
    });
  });

  // 3) Row‐click selection + enable toolbar
  document.querySelectorAll('.invoice-table-container tbody tr').forEach(row => {
    row.addEventListener('click', () => {
      if (selectedRow) selectedRow.classList.remove('selected');
      row.classList.add('selected');
      selectedRow = row;
      document.querySelectorAll('#invoice-actions button')
              .forEach(btn => btn.disabled = false);
    });
  });

  // 4) Toolbar buttons
  document.getElementById('btn-items').addEventListener('click', () => {
    if (!selectedRow) return;
    window.location.href = `?invoice_id=${selectedRow.dataset.invoiceId}`;
  });
  document.getElementById('btn-report').addEventListener('click', () => {
    if (!selectedRow) return;
    window.open(`?view=report&invoice_id=${selectedRow.dataset.invoiceId}`, '_blank');
  });
  document.getElementById('btn-add-product').addEventListener('click', () => {
    if (!selectedRow) return;
    window.location.href = `?add_product=1&invoice_id=${selectedRow.dataset.invoiceId}`;
  });
 document.getElementById('btn-recalc').addEventListener('click', () => {
  if (!selectedRow) return;
  fetch(ajaxurl, {
    method: 'POST',
    body: new URLSearchParams({
      action:     'recalc_invoice_total',
      nonce:      nonce,
      invoice_id: selectedRow.dataset.invoiceId
    })
  })
  .then(r => r.json())
  .then(json => {
    if (!json.success) return console.error(json.data);
    // update the three inputs
    const totalInput       = selectedRow.querySelector('input[name="total"]');
    const taxInput         = selectedRow.querySelector('input[name="tax"]');
    const grandTotalInput  = selectedRow.querySelector('input[name="grand_total"]');
    totalInput.value      = parseFloat(json.data.total).toFixed(2);
    taxInput.value        = parseFloat(json.data.tax).toFixed(2);
    grandTotalInput.value = parseFloat(json.data.grand_total).toFixed(2);
    // flash green
    selectedRow.style.backgroundColor = '#dfd';
    setTimeout(() => selectedRow.style.backgroundColor = '', 300);
  })
  .catch(console.error);
});

});
</script>




<?PHP

    return ob_get_clean();
}

?>



<?php
// ────────────────
// Helper functions
// ────────────────
function fetch_lookup($conn,$table,$idcol,$namecol){
    $out=[];
    $res=$conn->query("SELECT $idcol,$namecol FROM $table ORDER BY $namecol");
    while($r=$res->fetch_assoc()){
        $out[intval($r[$idcol])] = esc_html($r[$namecol]);
    }
    return $out;
}

function get_invoice_editor_db_connection(){
    $conn=new mysqli('localhost','Tom1977','TNT2024@!','TNT_Db');
    if($conn->connect_error){ error_log('DB Error:'.$conn->connect_error); return false; }
    $conn->set_charset('utf8mb4');
    return $conn;
}
