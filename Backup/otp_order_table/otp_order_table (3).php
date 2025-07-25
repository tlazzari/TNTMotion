<?php
/**
 * Plugin Name: Order Document Table Plugin (Inline + Lines + Items + Add)
 * Description: Lists order_document entries (inline-editable), plus “Add Items”, “Add Order Line”, and a form to add a new order_document. Also renders the [tnt_order_details] page.
 * Version:     1.12
 * Author:      Your Name
 * License:     GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** DB connection **/
function otp_get_db_connection() {
    $conn = new mysqli('localhost','Tom1977','TNT2024@!','TNT_Db');
    if ( $conn->connect_error ) {
        error_log('DB Error: '.$conn->connect_error);
        return false;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

/** Enqueue assets **/
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_style('otp-style', plugin_dir_url(__FILE__).'css/otp_order_table.css', [], '1.0');
    wp_enqueue_script('otp-script', plugin_dir_url(__FILE__).'js/otp_order_table.js', ['jquery'], '1.0', true);
    wp_localize_script('otp-script','otpData',[
      'ajax_url'=>admin_url('admin-ajax.php'),
      'nonce'   =>wp_create_nonce('otp_nonce'),
      'base_url'=>home_url('/order_table/')
    ]);
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    // include the default jQuery UI theme (you can bundle your own CSS instead)
    wp_enqueue_style( 'jquery-ui-css',
      'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'
    );
});

/** Main shortcode **/
add_shortcode('otp_order_document_table','otp_order_document_table_shortcode');
function otp_order_document_table_shortcode(){
    $view  = sanitize_text_field($_GET['view']                ?? '');
    $docId = intval(         $_GET['order_document_id'] ?? 0 );


    // … after you grab $view and $docId …
if ( $view === 'edit_terms' && $docId > 0 ) {
    $conn = otp_get_db_connection();
    if ( ! $conn ) {
        return '<p>Database error.</p>';
    }

    // If the form was just POSTed, save the new values:
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_terms']) ) {
        $tc = sanitize_textarea_field( $_POST['terms_conditions'] );
        $pt = sanitize_textarea_field( $_POST['payment_terms'] );
        $dl = sanitize_textarea_field( $_POST['other_description'] );

        $stmt = $conn->prepare(
            "UPDATE order_document
               SET terms_conditions = ?,
                   payment_terms   = ?,
                   other_description  = ?
             WHERE order_document_id = ?"
        );
        $stmt->bind_param( 'sssi', $tc, $pt, $dl, $docId );
        $stmt->execute();
        $stmt->close();

        // redirect back to the main table after saving
        wp_safe_redirect( remove_query_arg([ 'view','order_document_id' ]) );
        exit;
    }

    // 1) Fetch the existing row
    $stmt = $conn->prepare(
        "SELECT terms_conditions, payment_terms, other_description
           FROM order_document
          WHERE order_document_id = ?
          LIMIT 1"
    );
    $stmt->bind_param( 'i', $docId );
    $stmt->execute();
    $stmt->bind_result( $tc_db, $pt_db, $dl_db );
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    // 2) Your defaults if empty
    $defaults = [
      'tc' => "二、运输方式及费用承担：快递、物流、空运，费用由供方承担。\n"
            . "三、技术标准，质量要求：按需方提供的图纸为准，其他质量指标参考国际质量体系标准。\n"
            . "四、验收标准及方法：国际质量体系标准方法。\n"
            . "五、包装方式：供方以纸制或木制包装到需方，包装物不回收。\n"
            . "七、解决合同纠纷办法：双方协商解决。\n"
            . "八、违约责任：按合同执行，产品在使用过程中出现质量问题，供方无偿退货或换货，包括相关物流费用等。\n"
            . "九、其他约定事项：双方不能单向终止合同。\n"
            . "十、合同生效及有效期限：合同原件或者扫描件双方盖章后即生效。",
      'pt' => "30% 订单，70% 收货",
      'dl' => "上海 FOB",
    ];

    // 3) Decide what to show
    $tc = $tc_db ?: $defaults['tc'];
    $pt = $pt_db ?: $defaults['pt'];
    $dl = $dl_db ?: $defaults['dl'];

    // 4) Render the form
    ob_start(); ?>
        <div style="max-width:600px;margin:20px auto;">
      <h2>Edit Terms for Document #<?php echo esc_html( $docId ); ?></h2>
      <form method="post">
        <p>
          <label>Terms &amp; Conditions:<br>
            <textarea 
              name="terms_conditions" 
              rows="15" 
              style="width:100%; min-height:300px; font-family:monospace;"
            ><?php echo esc_textarea( $tc ); ?></textarea>
          </label>
        </p>
        <p>
          <label>Payment Terms:<br>
            <input type="text" name="payment_terms" style="width:100%;"></input>
          </label>
        </p>
        <p>
          <label>Other Description:<br>
            <textarea 
              name="other_description" 
              rows="8" 
              style="width:100%; min-height:200px;"
            ><?php echo esc_textarea( $od ); ?></textarea>
          </label>
        </p>
        <p>
          <button type="submit" name="save_terms">Save Changes</button>
          <a href="<?php echo esc_url( remove_query_arg( [ 'view', 'order_document_id' ] ) ); ?>"
             style="margin-left:1em;">Cancel</a>
        </p>
      </form>
    </div>

    <?php
    return ob_get_clean();
}


    // --- VIEW: order_items ---
    if($view==='order_items' && $docId>0){
        $conn=otp_get_db_connection();
        if(!$conn) return '<p>DB error.</p>';

        // fetch existing items
        $stmt=$conn->prepare("
          SELECT oi.item_id,oi.description,oi.quantity,oi.price,oi.tax,oi.total,c.currency_description,oi.currency_id
          FROM order_item oi
          LEFT JOIN currency c ON c.currency_id=oi.currency_id
          WHERE oi.order_document_id=?
        ");
        $stmt->bind_param('i',$docId);
        $stmt->execute();
        $res=$stmt->get_result();
        $items=[];
        while($r=$res->fetch_assoc()) $items[]=$r;
        $stmt->close();

        // build list
        $out = '<h2>Items for Document #'.esc_html($docId).'</h2>';
        $out .= '<table border="1" cellpadding="6" cellspacing="0" width="100%"><thead>
          <tr><th>Item ID</th><th>Description</th><th>Qty</th><th>Price</th>
              <th>Tax</th><th>Total</th><th>Currency</th></tr></thead><tbody>';
        if(empty($items)){
            $out .= '<tr><td colspan="7" style="text-align:center">No items yet.</td></tr>';
        } else {
            foreach($items as $i){
                $out .= '<tr>'
                  .'<td>'.esc_html($i['item_id']).'</td>'
                  .'<td><span class="editable-item-field" data-item-id="'.esc_attr($i['item_id']).'" data-field="description">'
                     .esc_html($i['description']).'</span></td>'
                  .'<td><span class="editable-item-field" data-item-id="'.esc_attr($i['item_id']).'" data-field="quantity">'
                     .esc_html($i['quantity']).'</span></td>'
                  .'<td><span class="editable-item-field" data-item-id="'.esc_attr($i['item_id']).'" data-field="price">'
                     .esc_html($i['price']).'</span></td>'
                  .'<td><span class="editable-item-field" data-item-id="'.esc_attr($i['item_id']).'" data-field="tax">'
                     .esc_html($i['tax']).'</span></td>'
                  .'<td><span class="editable-item-field" data-item-id="'.esc_attr($i['item_id']).'" data-field="total">'
                     .esc_html($i['total']).'</span></td>'
                  .'<td><span class="editable-item-field" data-item-id="'.esc_attr($i['item_id']).'" data-field="currency_id" data-current-id="'.esc_attr($i['currency_id']).'">'
                     .esc_html($i['currency_description']).'</span></td>'
                  .'</tr>';
            }
        }
        $out .= '</tbody></table>';

        // add-item form
        $out .= '<h3>Add New Item</h3>
        <form id="otp-add-item-form">
          <input type="hidden" name="order_document_id" value="'.esc_attr($docId).'"><br>
          <label>Description:<br><input type="text" name="description" required></label><br>
          <label>Quantity:<br><input type="number" name="quantity" min="1" required></label><br>
          <label>Price:<br><input type="number" step="0.01" name="price" required></label><br>
          <label>Tax:<br><input type="number" step="0.01" name="tax"></label><br>
          <label>Total:<br><input type="number" step="0.01" name="total"></label><br>
          <label>Currency:<br><select name="currency_id" required>
            <option value="">Loading…</option>
          </select></label><br><br>
          <button type="submit">Save Item</button>
        </form>';

        $conn->close();
        return $out;
    }

    // --- VIEW: order_lines ---
    if($view==='order_lines' && $docId>0){
        $conn=otp_get_db_connection();
        if(!$conn) return '<p>DB error.</p>';

        // existing lines
        $stmt=$conn->prepare("
          SELECT so.order_id,p.product_name,so.quantity,so.specifications,
                 so.measurements,so.price,so.tax,so.total,c.currency_description,so.currency_id
          FROM supplier_order so
          LEFT JOIN rfq_product rp ON rp.rfq_product_id=so.rfq_product_id
          LEFT JOIN products p ON p.product_id=rp.product_id
          LEFT JOIN currency c ON c.currency_id=so.currency_id
          WHERE so.order_document_id=?
        ");
        $stmt->bind_param('i',$docId);
        $stmt->execute();
        $r=$stmt->get_result();
        $lines=[];
        while($a=$r->fetch_assoc()) $lines[]=$a;
        $stmt->close();

        // supplier_id of this doc
        $s2=$conn->prepare("SELECT supplier_id FROM order_document WHERE order_document_id=?");
        $s2->bind_param('i',$docId);
        $s2->execute();
        $rr=$s2->get_result();
        $supplier_id = intval($rr->fetch_assoc()['supplier_id'] ?? 0);
        $s2->close();

        // unsubmitted lines for that supplier
        $avail=[];
        if($supplier_id){
            $s3=$conn->prepare("
              SELECT so.order_id,p.product_name,so.quantity,so.price,so.total
              FROM supplier_order so
              LEFT JOIN rfq_supplier rs ON rs.rfq_supplier_id=so.rfq_supplier_id
              LEFT JOIN rfq_product rp ON rp.rfq_product_id=so.rfq_product_id
              LEFT JOIN products p ON p.product_id=rp.product_id
              WHERE rs.supplier_id=? AND so.submitted=0
            ");
            $s3->bind_param('i',$supplier_id);
            $s3->execute();
            $r3=$s3->get_result();
            while($x=$r3->fetch_assoc()) $avail[]=$x;
            $s3->close();
        }

        // build HTML
        $out = '<h2>Order Lines for Document #'.esc_html($docId).'</h2>';

        // existing lines table
        $out .= '<h3>Existing Lines</h3><table border="1" cellpadding="6" cellspacing="0" width="100%">
          <thead><tr>
            <th>Order ID</th><th>Product</th><th>Qty</th>
            <th>Specs</th><th>Meas.</th><th>Price</th>
            <th>Tax</th><th>Total</th><th>Currency</th>
          </tr></thead><tbody>';
        if(empty($lines)){
            $out .= '<tr><td colspan="9" style="text-align:center">None.</td></tr>';
        } else {
            foreach($lines as $l){
                $out .= '<tr>'
                  .'<td>'.esc_html($l['order_id']).'</td>'
                  .'<td>'.esc_html($l['product_name']).'</td>'
                  .'<td><span class="editable-line-field" data-order-id="'.esc_attr($l['order_id']).'" data-field="quantity">'.esc_html($l['quantity']).'</span></td>'
                  .'<td><span class="editable-line-field" data-order-id="'.esc_attr($l['order_id']).'" data-field="specifications">'.esc_html($l['specifications']).'</span></td>'
                  .'<td><span class="editable-line-field" data-order-id="'.esc_attr($l['order_id']).'" data-field="measurements">'.esc_html($l['measurements']).'</span></td>'
                  .'<td><span class="editable-line-field" data-order-id="'.esc_attr($l['order_id']).'" data-field="price">'.esc_html($l['price']).'</span></td>'
                  .'<td><span class="editable-line-field" data-order-id="'.esc_attr($l['order_id']).'" data-field="tax">'.esc_html($l['tax']).'</span></td>'
                  .'<td><span class="editable-line-field" data-order-id="'.esc_attr($l['order_id']).'" data-field="total">'.esc_html($l['total']).'</span></td>'
                  .'<td><span class="editable-line-field" data-order-id="'.esc_attr($l['order_id']).'" data-field="currency_id" data-current-id="'.esc_attr($l['currency_id']).'">'.esc_html($l['currency_description']).'</span></td>'
                  .'</tr>';
            }
        }
        $out .= '</tbody></table>';

        // unsubmitted lines table
        $out .= '<h3>Unsubmitted Lines for Supplier #'.esc_html($supplier_id).'</h3>';
        $out .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $out .= '<thead><tr>
                     <th>Select</th><th>Order ID</th><th>Product</th><th>Qty</th>
                     <th>Price</th><th>Total</th>
                    </tr></thead><tbody>';
        if(empty($avail)){
            $out .= '<tr><td colspan="6" style="text-align:center">None available.</td></tr>';
        } else {
            foreach($avail as $a){
                $out .= '<tr>'
                  .'<td><input type="checkbox" class="select-line-checkbox" data-order-id="'.esc_attr($a['order_id']).'"></td>'
                  .'<td>'.esc_html($a['order_id']).'</td>'
                  .'<td>'.esc_html($a['product_name']).'</td>'
                  .'<td>'.esc_html($a['quantity']).'</td>'
                  .'<td>'.esc_html($a['price']).'</td>'
                  .'<td>'.esc_html($a['total']).'</td>'
                  .'</tr>';
            }
        }
        $out .= '</tbody></table>';
        $out .= '<button id="otp-assign-lines" data-doc-id="'.esc_attr($docId).'">Assign Selected Lines</button>';

        $conn->close();
        return $out;
    }

    // --- DEFAULT VIEW: list + “Add New Document” form ---
    $conn=otp_get_db_connection();
    if(!$conn) return '<p>DB error.</p>';

    // fetch dropdown lists
    $entities=[];  $rE=$conn->query("SELECT entity_id,entity_name FROM entity ORDER BY entity_name");
    while($x=$rE->fetch_assoc()) $entities[]=$x;
    $suppliers=[];$rS=$conn->query("SELECT supplier_id,supplier_name FROM suppliers ORDER BY supplier_name");
    while($x=$rS->fetch_assoc())$suppliers[]=$x;
    $rE->free(); $rS->free();

    // “Add New Document” form (compact two-column layout)
    $out  = '<h2>Add New Order Document</h2>';
    $out .= '<form id="otp-add-doc-form" style="
        display: grid;
        grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
        gap: 10px;
        align-items: end;
    ">
      <div>
        <label>Entity<br>
          <select name="entity_id" required>
            <option value="">-- select entity --</option>';
    foreach($entities as $e){
      $out .= '<option value="'.esc_attr($e['entity_id']).'">'.esc_html($e['entity_name']).'</option>';
    }
    $out .= '</select>
        </label>
      </div>
      <div>
   <label>Supplier<br>
     <input 
       type="text" 
       id="supplier-autocomplete" 
       placeholder="Type to search…" 
       required 
       style="width:100%;box-sizing:border-box;"
     >
     <input type="hidden" name="supplier_id" id="supplier-id">
   </label>
 </div>
      <div>
        <label>Order Date<br>
          <input type="date" name="order_date" required>
        </label>
      </div>
      <div>
        <label>Currency<br>
          <select name="currency_id" required>
            <option value="">Loading…</option>
          </select>
        </label>
      </div>
      <div>
        <label>Tax<br>
          <input type="number" step="0.01" name="tax">
        </label>
      </div>
      <div>
        <label>Total<br>
          <input type="number" step="0.01" name="total">
        </label>
      </div>
      <div>
        <label>Grand Total<br>
          <input type="number" step="0.01" name="grand_total">
        </label>
      </div>
      <div style="grid-column:1 / -1; text-align:right;">
        <button type="submit">Create Document</button>
      </div>
    </form><hr>';

    // fetch docs
    $sql = "
      SELECT 
        od.order_document_id,
        od.entity_id,
        e.entity_name,
        od.supplier_id,
        s.supplier_name,
        od.order_date,
        od.terms_conditions,
        od.tax,
        od.total AS total,
        od.grand_total,
        od.currency_id      AS doc_currency_id,
        c.currency_description AS doc_currency_name
      FROM order_document od
      LEFT JOIN entity    e ON e.entity_id    = od.entity_id
      LEFT JOIN suppliers s ON s.supplier_id  = od.supplier_id
      LEFT JOIN currency  c ON c.currency_id  = od.currency_id
      ORDER BY od.order_date DESC
    ";
    $res=$conn->query($sql);
    if(!$res){
        $err=esc_html($conn->error);
        $conn->close();
        return '<p>Error: '.$err.'</p>';
    }

    // table header
    $out .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">
      <thead><tr>
        <th>Doc ID</th><th>Entity</th><th>Supplier</th><th>Date</th>
        <th>T&amp;C</th><th>Tax</th><th>Total</th><th>Grand Total</th>
        <th>Currency</th><th>Actions</th>
      </tr></thead><tbody>';

    while($d=$res->fetch_assoc()){
        $id   = (int)$d['order_document_id'];
        $eid  = (int)$d['entity_id'];
        $sid  = (int)$d['supplier_id'];
        $en   = esc_html($d['entity_name']);
        $sn   = esc_html($d['supplier_name']);
        $dt   = esc_html($d['order_date']);
        $tc   = esc_html($d['terms_conditions']);
        $tx   = esc_html($d['tax']);
        $tot  = esc_html($d['total']);
        $gr   = esc_html($d['grand_total']);
        $cid  = (int)$d['doc_currency_id'];
        $cc   = esc_html($d['doc_currency_name']);

        $tc_html = '<button class="edit-terms-btn" data-doc-id="' . esc_attr( $id ) . '">'
         . 'Edit T&amp;C'
         . '</button>';

        $out .= '<tr data-doc-id="'.$id.'">
          <td>'.$id.'</td>
          <td>
            <span class="editable-doc-field" data-doc-id="'.$id.'" data-field="entity_id" data-field-type="select" data-current-id="'.$eid.'">'
            .$en.'</span>
          </td>
          <td>
            <span class="editable-doc-field" data-doc-id="'.$id.'" data-field="supplier_id" data-field-type="select" data-current-id="'.$sid.'">'
            .$sn.'</span>
          </td>
          <td>
            <span class="editable-doc-field" data-doc-id="'.$id.'" data-field="order_date">'
            .$dt.'</span>
          </td>
          <td>'.$tc_html.'</td>
          <td>
            <span class="editable-doc-field" data-doc-id="'.$id.'" data-field="tax">'
            .$tx.'</span>
          </td>
          <td>
            <span class="editable-doc-field" data-doc-id="'.$id.'" data-field="total">'
            .$tot.'</span>
          </td>
          <td>
            <span class="editable-doc-field" data-doc-id="'.$id.'" data-field="grand_total">'
            .$gr.'</span>
          </td>
          <td>
            <span class="editable-doc-field" data-doc-id="'.$id.'" data-field="currency_id" data-field-type="select" data-current-id="'.$cid.'">'
            .$cc.'</span>
          </td>
          <td>
            <button class="add-order-line"      data-doc-id="'.$id.'">Add Order Line</button>
            <button class="add-items"           data-doc-id="'.$id.'">Add Items</button>
            <button class="order-document-link" data-doc-id="'.$id.'">Order Document</button>
          </td>
        </tr>';
    }

    $out .= '</tbody></table>';
    $conn->close();
    return $out;
}

/** AJAX: fetch currencies **/
add_action('wp_ajax_otp_get_currencies','otp_get_currencies');
add_action('wp_ajax_nopriv_otp_get_currencies','otp_get_currencies');
function otp_get_currencies(){
    check_ajax_referer('otp_nonce','nonce');
    $conn=otp_get_db_connection(); if(!$conn) wp_send_json_error('DB error');
    $res=$conn->query("SELECT currency_id,currency_description FROM currency");
    $out=[]; while($r=$res->fetch_assoc()) $out[]=$r;
    $conn->close();
    wp_send_json_success($out);
}

/** AJAX: fetch entities **/
add_action('wp_ajax_otp_get_entities','otp_get_entities');
add_action('wp_ajax_nopriv_otp_get_entities','otp_get_entities');
function otp_get_entities(){
    check_ajax_referer('otp_nonce','nonce');
    $conn=otp_get_db_connection(); if(!$conn) wp_send_json_error('DB error');
    $res=$conn->query("SELECT entity_id,entity_name FROM entity ORDER BY entity_name");
    $out=[]; while($r=$res->fetch_assoc()) $out[]=$r;
    $conn->close();
    wp_send_json_success($out);
}

/** AJAX: fetch suppliers **/
add_action('wp_ajax_otp_get_suppliers','otp_get_suppliers');
add_action('wp_ajax_nopriv_otp_get_suppliers','otp_get_suppliers');
function otp_get_suppliers(){
    check_ajax_referer('otp_nonce','nonce');
    $conn=otp_get_db_connection(); if(!$conn) wp_send_json_error('DB error');
    $res=$conn->query("SELECT supplier_id,supplier_name FROM suppliers ORDER BY supplier_name");
    $out=[]; while($r=$res->fetch_assoc()) $out[]=$r;
    $conn->close();
    wp_send_json_success($out);
}

/** AJAX: create new document **/
add_action('wp_ajax_otp_add_order_document','otp_add_order_document');
add_action('wp_ajax_nopriv_otp_add_order_document','otp_add_order_document');
function otp_add_order_document(){
    check_ajax_referer('otp_nonce','nonce');
    $e = intval($_POST['entity_id']              ??0);
    $s = intval($_POST['supplier_id']            ??0);
    $d = sanitize_text_field($_POST['order_date']??'');
    $t = sanitize_textarea_field($_POST['terms_conditions']??'');
    $x = floatval($_POST['tax']                  ??0);
    $v = floatval($_POST['total']                ??0);
    $g = floatval($_POST['grand_total']          ??0);
    $c = intval($_POST['currency_id']            ??0);
    if(!$e||!$s||!$d||!$c) wp_send_json_error('Missing required');
    $conn=otp_get_db_connection(); if(!$conn) wp_send_json_error('DB error');
    $stmt=$conn->prepare("
      INSERT INTO order_document
        (entity_id, supplier_id, order_date, terms_conditions, tax, total, grand_total, currency_id)
      VALUES(?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param('iissdddi',$e,$s,$d,$t,$x,$v,$g,$c);
    if($stmt->execute()){
        $stmt->close(); $conn->close();
        wp_send_json_success('OK');
    } else {
        $err=$stmt->error;
        $stmt->close(); $conn->close();
        wp_send_json_error('Err:'.$err);
    }
}

add_action('wp_ajax_otp_search_suppliers','otp_search_suppliers');
add_action('wp_ajax_nopriv_otp_search_suppliers','otp_search_suppliers');
function otp_search_suppliers(){
    check_ajax_referer('otp_nonce','nonce');
    $term = '%' . $GLOBALS['wpdb']->esc_like( sanitize_text_field($_POST['term'] ?? '') ) . '%';
    $conn = otp_get_db_connection();
    if( ! $conn ) wp_send_json_error('DB error');
    $stmt = $conn->prepare(
      "SELECT supplier_id,supplier_name 
         FROM suppliers 
        WHERE supplier_name LIKE ? 
        ORDER BY supplier_name 
        LIMIT 20"
    );
    $stmt->bind_param('s',$term);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while($r = $res->fetch_assoc()) {
        $out[] = $r;
    }
    $stmt->close();
    $conn->close();
    wp_send_json_success($out);
}


/** AJAX: add order item **/
add_action('wp_ajax_otp_add_order_item','otp_add_order_item');
add_action('wp_ajax_nopriv_otp_add_order_item','otp_add_order_item');
function otp_add_order_item(){
    check_ajax_referer('otp_nonce','nonce');
    $doc = intval($_POST['order_document_id']??0);
    $desc= sanitize_text_field($_POST['description']??'');
    $qty = floatval($_POST['quantity'] ??0);
    $prc = floatval($_POST['price']    ??0);
    $tax = floatval($_POST['tax']      ??0);
    $tot = floatval($_POST['total']    ??0);
    $cid = intval($_POST['currency_id']??0);
    if(!$doc||!$desc||$qty<=0||$prc<=0||!$cid) wp_send_json_error('Missing/invalid');
    $conn=otp_get_db_connection(); if(!$conn) wp_send_json_error('DB err');
    $stmt=$conn->prepare("
      INSERT INTO order_item 
        (order_document_id,description,quantity,price,tax,total,currency_id)
      VALUES(?,?,?,?,?,?,?)
    ");
    $stmt->bind_param('isddddi',$doc,$desc,$qty,$prc,$tax,$tot,$cid);
    if($stmt->execute()){
        $stmt->close(); $conn->close();
        wp_send_json_success('OK');
    } else {
        $err=$stmt->error;
        $stmt->close(); $conn->close();
        wp_send_json_error('Err:'.$err);
    }
}

/** AJAX: generate order-document link **/
add_action('wp_ajax_otp_order_document_link','otp_order_document_link');
add_action('wp_ajax_nopriv_otp_order_document_link','otp_order_document_link');
function otp_order_document_link(){
    check_ajax_referer('otp_nonce','nonce');
    $doc=intval($_POST['order_document_id']??0);
    if(!$doc) wp_send_json_error('Invalid');
    wp_send_json_success(site_url('/tnt-order/?order_id='.$doc));
}

/** AJAX: assign lines **/
add_action('wp_ajax_otp_assign_order_lines','otp_assign_order_lines');
add_action('wp_ajax_nopriv_otp_assign_order_lines','otp_assign_order_lines');
function otp_assign_order_lines(){
    check_ajax_referer('otp_nonce','nonce');
    $doc = intval($_POST['order_document_id'] ??0);
    $ids = $_POST['selected_order_ids'] ?? [];
    if(!$doc||!is_array($ids)||empty($ids)) wp_send_json_error('Missing');
    $conn=otp_get_db_connection(); if(!$conn) wp_send_json_error('DB err');
    $place = implode(',',array_fill(0,count($ids),'?'));
    $sql   = "UPDATE supplier_order SET order_document_id=? WHERE order_id IN($place)";
    $stmt  = $conn->prepare($sql);
    $types = str_repeat('i',count($ids)+1);
    $params = array_merge([$doc],array_map('intval',$ids));
    $stmt->bind_param($types,...$params);
    if($stmt->execute()){
        $stmt->close(); $conn->close();
        wp_send_json_success('OK');
    } else {
        $e=$stmt->error;
        $stmt->close(); $conn->close();
        wp_send_json_error('Err:'.$e);
    }
}

/** AJAX: inline-update doc field **/
add_action('wp_ajax_otp_update_doc_field','otp_update_doc_field');
add_action('wp_ajax_nopriv_otp_update_doc_field','otp_update_doc_field');
function otp_update_doc_field(){
    check_ajax_referer('otp_nonce','nonce');
    $doc   = intval($_POST['doc_id']   ??0);
    $field = sanitize_key($_POST['field']  ??'');
    $value = sanitize_text_field($_POST['value']??'');
    $allowed=['entity_id','supplier_id','order_date','terms_conditions','tax','total','grand_total','currency_id'];
    if(!$doc||!in_array($field,$allowed,true)){
        wp_send_json_error('Invalid');
    }
    if(in_array($field,['tax','total','grand_total'],true)){
        $value=floatval($value);
    } elseif(in_array($field,['entity_id','supplier_id','currency_id'],true)){
        $value=intval($value);
    }
    $conn=otp_get_db_connection(); if(!$conn) wp_send_json_error('DB err');
    $stmt=$conn->prepare("UPDATE order_document SET `$field`=? WHERE order_document_id=?");
    if(in_array($field,['tax','total','grand_total'],true)||
       in_array($field,['entity_id','supplier_id','currency_id'],true)){
        $stmt->bind_param('di',$value,$doc);
    } else {
        $stmt->bind_param('si',$value,$doc);
    }
    if($stmt->execute()){
        $stmt->close(); $conn->close();
        wp_send_json_success('OK');
    } else {
        $e=$stmt->error;
        $stmt->close(); $conn->close();
        wp_send_json_error('Err:'.$e);
    }
}

/** AJAX: inline-update supplier_order field **/
add_action('wp_ajax_otp_update_supplier_order_field','otp_update_supplier_order_field');
add_action('wp_ajax_nopriv_otp_update_supplier_order_field','otp_update_supplier_order_field');
function otp_update_supplier_order_field(){
  check_ajax_referer('otp_nonce','nonce');
  $order_id = intval($_POST['order_id'] ?? 0);
  $field    = sanitize_key($_POST['field']   ?? '');
  $value    = sanitize_text_field($_POST['value'] ?? '');
  $allowed = ['quantity','specifications','measurements','price','tax','total','currency_id'];
  if (!$order_id || ! in_array($field,$allowed,true)) {
    wp_send_json_error('Invalid parameters');
  }
  if (in_array($field,['quantity','currency_id'],true)) {
    $value = intval($value);
  } elseif (in_array($field,['price','tax','total'],true)) {
    $value = floatval($value);
  }
  $conn = otp_get_db_connection();
  if (!$conn) wp_send_json_error('DB error');
  $stmt = $conn->prepare("UPDATE supplier_order SET `$field`=? WHERE order_id=?");
  if (in_array($field,['quantity','currency_id'],true)) {
    $stmt->bind_param('ii',$value,$order_id);
  } elseif (in_array($field,['price','tax','total'],true)) {
    $stmt->bind_param('di',$value,$order_id);
  } else {
    $stmt->bind_param('si',$value,$order_id);
  }
  if ($stmt->execute()) {
    wp_send_json_success();
  } else {
    wp_send_json_error('DB error: '.$stmt->error);
  }
}

/** AJAX: inline-update order_item field **/
add_action('wp_ajax_otp_update_order_item_field','otp_update_order_item_field');
add_action('wp_ajax_nopriv_otp_update_order_item_field','otp_update_order_item_field');
function otp_update_order_item_field(){
  check_ajax_referer('otp_nonce','nonce');
  $item_id = intval($_POST['item_id'] ?? 0);
  $field   = sanitize_key($_POST['field']   ?? '');
  $value   = sanitize_text_field($_POST['value'] ?? '');
  $allowed = ['description','quantity','price','tax','total','currency_id'];
  if (!$item_id || ! in_array($field,$allowed,true)) {
    wp_send_json_error('Invalid parameters');
  }
  if (in_array($field,['quantity','currency_id'],true)) {
    $value = intval($value);
  } elseif (in_array($field,['price','tax','total'],true)) {
    $value = floatval($value);
  }
  $conn = otp_get_db_connection();
  if (!$conn) wp_send_json_error('DB error');
  $stmt = $conn->prepare("UPDATE order_item SET `$field`=? WHERE item_id=?");
  if (in_array($field,['quantity','currency_id'],true)) {
    $stmt->bind_param('ii',$value,$item_id);
  } elseif (in_array($field,['price','tax','total'],true)) {
    $stmt->bind_param('di',$value,$item_id);
  } else {
    $stmt->bind_param('si',$value,$item_id);
  }
  if ($stmt->execute()) {
    wp_send_json_success();
  } else {
    wp_send_json_error('DB error: '.$stmt->error);
  }
}

/**
 * Shortcode: [tnt_order_details]
 * Renders the full order detail page (supplier_order + items) with QR and translations.
 */
/**
 * Shortcode: [tnt_order_details]
 * Renders the full, bilingual order-detail page with inline editing,
 * conditional “Additional Items,” and a Print-PDF button.
 */
function otp_display_order_details() {
    if ( ! isset( $_GET['order_id'] ) ) {
        return '<p>Order ID is missing.</p>';
    }
    $order_id = intval( $_GET['order_id'] );

    $conn = otp_get_db_connection();
    if ( ! $conn ) {
        return '<p>Error connecting to database.</p>';
    }

    // 1) Fetch everything, including entity_id
    $query = "
        SELECT 
            od.order_document_id,
            od.entity_id,
            en.entity_name                     AS entity_name,
            od.order_date,
            od.tax,
            od.grand_total,
            c0.currency_description            AS order_document_currency_name,
            suppliers.supplier_name,
            suppliers.supplier_address,

            so.order_id                        AS supplier_order_id,
            products.product_name,
            so.quantity                        AS supplier_order_quantity,
            so.specifications                  AS supplier_order_specifications,
            so.measurements                    AS supplier_order_measurements,
            so.price                           AS supplier_order_price,
            so.tax                             AS supplier_order_tax,
            so.total                           AS supplier_order_total,
            c2.currency_description            AS supplier_order_currency_name,

            oi.item_id,
            oi.description                     AS order_item_description,
            oi.quantity                        AS order_item_quantity,
            oi.price                           AS order_item_price,
            oi.tax                             AS order_item_tax,
            oi.total                           AS order_item_total,
            c1.currency_description            AS order_item_currency_name,

            od.terms_conditions,
            od.payment_terms,
            od.other_description
        FROM order_document od
        LEFT JOIN entity        en ON en.entity_id        = od.entity_id
        LEFT JOIN currency      c0 ON c0.currency_id      = od.currency_id

        LEFT JOIN supplier_order so ON so.order_document_id = od.order_document_id
        LEFT JOIN suppliers       ON suppliers.supplier_id  = so.supplier_id
        LEFT JOIN rfq_product     rp ON rp.rfq_product_id    = so.rfq_product_id
        LEFT JOIN products        ON products.product_id    = rp.product_id

        LEFT JOIN order_item      oi ON oi.order_document_id = od.order_document_id
        LEFT JOIN currency        c1 ON c1.currency_id      = oi.currency_id
        LEFT JOIN currency        c2 ON c2.currency_id      = so.currency_id

        WHERE od.order_document_id = ?
    ";
    $stmt = $conn->prepare( $query );
    if ( ! $stmt ) {
        $err = esc_html( $conn->error );
        $conn->close();
        return "<p>Query prep failed: {$err}</p>";
    }
    $stmt->bind_param( 'i', $order_id );
    if ( ! $stmt->execute() ) {
        $err = esc_html( $stmt->error );
        $stmt->close();
        $conn->close();
        return "<p>Execute failed: {$err}</p>";
    }
    $result = $stmt->get_result();
    if ( $result->num_rows === 0 ) {
        $stmt->close();
        $conn->close();
        return '<p>No order found for ID ' . esc_html( $order_id ) . '.</p>';
    }

    // 2) Collect rows
    $rowForGeneral  = null;
    $supplierOrders = [];
    $distinctItems  = [];
    while ( $row = $result->fetch_assoc() ) {
        if ( ! $rowForGeneral ) {
            $rowForGeneral = $row;
        }
        $soId = intval( $row['supplier_order_id'] );
        if ( $soId > 0 && ! isset( $supplierOrders[ $soId ] ) ) {
            $supplierOrders[ $soId ] = [
                'product_name'   => esc_html( $row['product_name'] ),
                'quantity'       => esc_html( $row['supplier_order_quantity'] ),
                'specifications' => esc_html( $row['supplier_order_specifications'] ),
                'measurements'   => esc_html( $row['supplier_order_measurements'] ),
                'price'          => esc_html( $row['supplier_order_price'] ),
                'tax'            => esc_html( $row['supplier_order_tax'] ),
                'total'          => esc_html( $row['supplier_order_total'] ),
                'currency'       => esc_html( $row['supplier_order_currency_name'] ),
            ];
        }
        $itId = intval( $row['item_id'] );
        if ( $itId > 0 && ! isset( $distinctItems[ $itId ] ) ) {
            $distinctItems[ $itId ] = [
                'description' => esc_html( $row['order_item_description'] ),
                'quantity'    => esc_html( $row['order_item_quantity'] ),
                'price'       => esc_html( $row['order_item_price'] ),
                'tax'         => esc_html( $row['order_item_tax'] ),
                'total'       => esc_html( $row['order_item_total'] ),
                'currency'    => esc_html( $row['order_item_currency_name'] ),
            ];
        }
    }
    $stmt->close();
    $conn->close();

    // 3) Extract for display
    $entity_name       = esc_html( $rowForGeneral['entity_name'] );
    $entity_id         = intval( $rowForGeneral['entity_id'] );
    $order_document_id = esc_html( $rowForGeneral['order_document_id'] );
    $order_date        = esc_html( $rowForGeneral['order_date'] );
    $supplier_name     = esc_html( $rowForGeneral['supplier_name'] );
    $supplier_address  = esc_html( $rowForGeneral['supplier_address'] );
    $order_tax         = esc_html( $rowForGeneral['tax'] );
    $order_total       = esc_html( $rowForGeneral['grand_total'] );
    $order_currency    = esc_html( $rowForGeneral['order_document_currency_name'] );
    $terms_conditions  = nl2br( esc_html( $rowForGeneral['terms_conditions'] ) );
    $payment_terms     = nl2br( esc_html( $rowForGeneral['payment_terms'] ) );
    $measurements      = nl2br( esc_html( $rowForGeneral['supplier_order_measurements'] ) );
    $other_description = nl2br( esc_html( $rowForGeneral['other_description'] ) );

    // 4) Choose logo by entity_id
    if ( in_array( $entity_id, [2,5,6], true ) ) {
        $logo_url = 'https://tntbearings.com/wp-content/uploads/2025/05/LOGO_FINAL_PNG.png';
        $logo_height = 60;
    } elseif ( $entity_id === 1 ) {
        $logo_url = 'https://tntbearings.com/wp-content/uploads/2025/05/Logo-Final.jpg';
        $logo_height = 180;  // doubled for entity 1
    } else {
        $logo_url = '';
        $logo_height = 60;
    }

    // 5) Render
    ob_start();
    ?>
    <style>
      /* print-only / no-print */
      @media print {
        .no-print, header, footer { display: none !important; }
        body * { visibility: hidden; }
        .printable-area, .printable-area * { visibility: visible; }
        .printable-area { position: absolute; top: 0; left: 0; width: 100%; }
      }
      .printable-area table {
  margin-bottom: 2em;
}
      .no-print { margin-bottom: 1em; }
      .printable-area { max-width: 800px; margin: auto; padding: 1em; }
      .top-section { display: flex; justify-content: space-between; margin-top: 1em; }
      .top-left h3 { margin: 0.2em 0; }
      .top-right p { margin: 0.2em 0; }
      .supplier-name { font-size: 1.2em; font-weight: bold; }
      h2 { margin-bottom: 1em; }
      .footer-note { margin-top: 2em; font-size: 0.9em; }
    </style>

    <div class="no-print">
      <button id="print-document">Print PDF</button>
    </div>

    <div class="printable-area">
      <?php if ( $logo_url ): ?>
  <img src="<?php echo esc_url($logo_url) ?>"
       style="max-height:<?php echo intval($logo_height) ?>px; display:block; margin-bottom:1em;">
      <?php endif; ?>

      <h2>Order Details 订单详情</h2>

      <div class="top-section">
        <div class="top-left">
          <h4>Client Name – 需方：<?php echo $entity_name; ?></h4>
          <h4>Supplier Name – 供方：<?php echo $supplier_name; ?></h4>
          <p><strong>Supplier Address 供方地址：</strong><?php echo $supplier_address; ?></p>
        </div>
        <div class="top-right">
          <p><strong>订单号码 Order Number：</strong><?php echo $order_document_id; ?></p>
          <p><strong>Order Date 日期：</strong><?php echo $order_date; ?></p>
        </div>
      </div>

      <?php if ( ! empty( $supplierOrders ) ) : ?>
        <h3>Products orders 产品</h3>
        <table border="1" cellpadding="4" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>Product</th><th>Qty</th>
              <th>Specs 条款与条件</th>
              <th>测量报告 Measurements</th>
              <th>Price</th><th>Tax</th><th>Total</th><th>Currency</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $supplierOrders as $so ) : ?>
              <tr>
                <td><?php echo $so['product_name']; ?></td>
                <td><?php echo $so['quantity']; ?></td>
                <td><?php echo $so['specifications']; ?></td>
                <td><?php echo $so['measurements']; ?></td>
                <td><?php echo $so['price']; ?></td>
                <td><?php echo $so['tax']; ?></td>
                <td><?php echo $so['total']; ?></td>
                <td><?php echo $so['currency']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ( ! empty( $distinctItems ) ) : ?>
        <h3>Additional Items 附加信息</h3>
        <table border="1" cellpadding="4" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>Item ID</th><th>Description</th><th>Qty</th>
              <th>Price</th><th>Tax</th><th>Total</th><th>Currency</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $distinctItems as $itId => $it ) : ?>
              <tr>
                <td><?php echo $itId; ?></td>
                <td><?php echo $it['description']; ?></td>
                <td><?php echo $it['quantity']; ?></td>
                <td><?php echo $it['price']; ?></td>
                <td><?php echo $it['tax']; ?></td>
                <td><?php echo $it['total']; ?></td>
                <td><?php echo $it['currency']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <h3>Order Summary 订单摘要</h3>
      <table border="1" cellpadding="4" cellspacing="0" width="100%">
        <thead>
          <tr><th>Tax</th><th>Total</th><th>Currency</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><?php echo $order_tax; ?></td>
            <td><?php echo $order_total; ?></td>
            <td><?php echo $order_currency; ?></td>
          </tr>
        </tbody>
      </table>

      <div style="margin-top:1em;">
        <p><strong>条款与条件 Terms & Conditions：</strong><br><?php echo $terms_conditions; ?></p>
        <p><strong>测量报告 Measurements：</strong><br><?php echo $measurements; ?></p>
        <p><strong>付款条款 Payment Terms：</strong><br><?php echo $payment_terms; ?></p>
        <p><strong>其他描述 Other Description：</strong><br><?php echo $other_description; ?></p>
      </div>

      <div class="footer-note">
        <hr>
        <p>This order is generated electronically and it is valid even if not stamped or signed.<br>
        此订单为电子生成，即使未加盖公章或签名，亦视为有效。</p>
      </div>
    </div>
    <script>
      // make sure print button works
      document.getElementById('print-document')
              .addEventListener('click', function(){ window.print(); });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'tnt_order_details', 'otp_display_order_details' );


/** Query vars & rewrite rules **/
function otp_register_query_vars( $vars ) {
    $vars[] = 'order_id'; return $vars;
}
add_filter( 'query_vars', 'otp_register_query_vars' );
function otp_add_rewrite_rules() {
    add_rewrite_rule('^tnt-order/?$','index.php?pagename=tnt-order','top');
}
add_action( 'init', 'otp_add_rewrite_rules' );
function otp_flush_rewrite_rules() {
    otp_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__,'otp_flush_rewrite_rules');
register_deactivation_hook(__FILE__,'flush_rewrite_rules');
add_action( 'wp_loaded', function(){ global $wp_rewrite; error_log( print_r($wp_rewrite->rules,true) ); } );


