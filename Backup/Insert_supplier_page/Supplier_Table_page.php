add_shortcode('List_Suppliers', function() {
    ob_start(); ?>
    <form style="width: 100%" method="GET">
        <label for="search_query">Search:</label>
        <input type="text" name="search_query" id="search_query" placeholder="Enter search term" value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>">
        <button type="submit">Search</button>

        <label for="sort_column">Sort by:</label>
        <select name="sort_column" id="sort_column">
            <option value="supplier_id">Supplier ID</option>
            <option value="supplier_name">Supplier Name</option>
            <option value="last_audited">Last Audited</option>
            <option value="database_ranking">DB Ranking</option>
        </select>
        <label for="sort_order">Order:</label>
        <select name="sort_order" id="sort_order">
            <option value="ASC">Ascending</option>
            <option value="DESC">Descending</option>
        </select>
        <button type="submit">Sort</button>
            <div class="success-message" id="success-message"></div>
    <div class="error-message" id="error-message"></div>
    </form>

    <style>
        #new_row td {
            min-height: 40px;
            height: 40px;
            vertical-align: middle;
        }
        #new_row td:first-child {
            background-color: #f0f0f0;
            pointer-events: none;
            user-select: none;
            color: gray;
        }
        .success-message, .error-message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            font-size: 13px; /* Adjust font size */
        }
        td {
            word-wrap: break-word; /* Break words if necessary */
 
        }
        th {
            word-wrap: break-word; /* Break words if necessary */
 
        }
        .column-small {
            width: 50px; /* Set width */
        }

        .column-medium {
             width: 150px; /* Adjust as needed */
        </style>




    <table border="1" id="editable_table">
        <tr>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;width: 50px">Supplier ID</th>
                    <th style="width: 50px; position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Supplier Name</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Supplier Address</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Last Audited</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Main Contact</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Last Update</th>

                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Notes</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Brand</th>

                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Main Product</th>

                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Factory Size</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Clients</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Equipment</th>
                    <th class="column-small" style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;width: 50px">Website</th>
                     <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">Source</th>
                    <th style="position: sticky; top: 0; background: #f2f2f2; z-index: 999;">DB Ranking</th>
        </tr>

        <?php
        $host = 'localhost';
        $dbname = 'TNT_Db';
        $user = 'Tom1977';
        $password = 'TNT2024@!';
        $port = 3306;

        $sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'supplier_id';
        $sort_order = isset($_GET['sort_order']) && in_array($_GET['sort_order'], ['ASC', 'DESC']) ? $_GET['sort_order'] : 'ASC';
        $search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

        $valid_columns = ['supplier_id', 'supplier_name', 'last_audited', 'database_ranking'];
        if (!in_array($sort_column, $valid_columns)) {
            $sort_column = 'supplier_id';
        }

        $mysqli = new mysqli($host, $user, $password, $dbname, $port);

        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        if (!$mysqli->set_charset("utf8mb4")) {
            die("Error loading character set utf8mb4: " . $mysqli->error);
        }

        $query = "SELECT supplier_id, supplier_name, supplier_address, last_audited, 
                         supplier_main_contact, last_update, notes, brand, primary_product, 
                         factory_size, clients, equipment, website, source, database_ranking 
                  FROM suppliers";

        if (!empty($search_query)) {
            $query .= " WHERE supplier_name LIKE ? OR supplier_address LIKE ? OR notes LIKE ? OR brand LIKE ? 
                        OR primary_product LIKE ? OR clients LIKE ? OR source LIKE ?";
        }

        $query .= " ORDER BY $sort_column $sort_order LIMIT 100";

        $stmt = $mysqli->prepare($query);

        if (!empty($search_query)) {
            $search_param = "%$search_query%";
            $stmt->bind_param('sssssss', $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            echo "<tr><td colspan='15'>Query Error: {$mysqli->error}</td></tr>";
        } elseif ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                 echo "<td><a href='/supplier-report?supplier_id=" . htmlspecialchars($row['supplier_id']) . "'>" . htmlspecialchars($row['supplier_id']) . "</a></td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['supplier_name']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['supplier_address']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['last_audited']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['supplier_main_contact']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['last_update']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['notes']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['brand']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['primary_product']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['factory_size']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['clients']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['equipment']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['website']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['source']) . "</td>";
                echo "<td contenteditable='true'>" . htmlspecialchars($row['database_ranking']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='15'>No suppliers found</td></tr>";
        }

        echo "<tr id='new_row'>";
        echo "<td>AUTO</td>";
        for ($i = 1; $i < 15; $i++) {
            echo "<td contenteditable='true'></td>";
        }
        echo "</tr>";

        $stmt->close();
        $mysqli->close();
        ?>
    </table>
    <button id="add_supplier">Add Supplier Entry</button>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('editable_table');
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');

            table.addEventListener('input', function (event) {
                if (event.target.tagName === 'TD' && event.target.parentElement.id !== 'new_row') {
                    const updatedValue = event.target.textContent;
                    const columnIndex = event.target.cellIndex;
                    const row = event.target.parentElement;
                    const id = row.firstChild.textContent;

                    fetch('/wp-content/uploads/save_supplier.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update_supplier', id, columnIndex, updatedValue })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successMessage.textContent = data.message;
                            successMessage.style.display = 'none';
                            setTimeout(() => successMessage.style.display = 'none', 3000);
                        } else {
                            errorMessage.textContent = data.message;
                            errorMessage.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        errorMessage.textContent = 'Fetch error: ' + error.message;
                        errorMessage.style.display = 'block';
                    });
                }
            });

            const addSupplierButton = document.getElementById('add_supplier');
            const newRow = document.getElementById('new_row');

            addSupplierButton.addEventListener('click', function () {
                const cells = newRow.querySelectorAll('td');
                const supplierData = Array.from(cells).slice(1).map(cell => cell.textContent);

                fetch('/wp-content/upgrade/save_supplier.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_supplier', data: supplierData })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        successMessage.textContent = data.message;
                        successMessage.style.display = 'block';
                        location.reload();
                    } else {
                        errorMessage.textContent = data.message;
                        errorMessage.style.display = 'block';
                    }
                })
                .catch(error => {
                    errorMessage.textContent = 'Fetch error: ' + error.message;
                    errorMessage.style.display = 'block';
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
});