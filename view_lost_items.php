<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

$email = $_SESSION['socialWorkerEmail'];
$name = $_SESSION['user_name'];
$school = $_SESSION['school'];
$school_id = $_SESSION['school_id'];

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Modernized: Get theme settings securely
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE email = ?";
$result = db_query($conn, $sql, "s", [$email]);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}

// Get lost items count for statistics
$lostItemsCount = 0;
$registeredItemsCount = 0;
$foundItemsCount = 0;

try {
    $sql_total = "SELECT COUNT(*) as total FROM new_lost_items WHERE school_id = ?";
    $result_total = db_query($conn, $sql_total, "i", [$school_id]);
    if ($result_total && $result_total->num_rows > 0) {
        $row = $result_total->fetch_assoc();
        $lostItemsCount = $row['total'];
    }
    
    $sql_registered = "SELECT COUNT(*) as total FROM new_lost_items WHERE tracking_status = 'lost' AND school_id = ?";
    $result_registered = db_query($conn, $sql_registered, "i", [$school_id]);
    if ($result_registered && $result_registered->num_rows > 0) {
        $row = $result_registered->fetch_assoc();
        $registeredItemsCount = $row['total'];
    }
    
    $sql_found = "SELECT COUNT(*) as total FROM new_lost_items WHERE tracking_status IN ('found', 'collected') AND school_id = ?";
    $result_found = db_query($conn, $sql_found, "i", [$school_id]);
    if ($result_found && $result_found->num_rows > 0) {
        $row = $result_found->fetch_assoc();
        $foundItemsCount = $row['total'];
    }
} catch (Exception $e) {
    // Handle errors gracefully
}

// Modernized: Get lost items data
$lostItems = [];
$sql = "SELECT li.*, c.first_name, c.surname, u.email as parent_email 
        FROM new_lost_items li
        LEFT JOIN children c ON li.child_id = c.id
        LEFT JOIN users u ON c.parent_id = u.id
        WHERE li.school_id = ? 
        ORDER BY li.date_lost DESC";
$result = db_query($conn, $sql, "i", [$school_id]);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Map old column names for backward compatibility
        $row['child_name'] = trim($row['first_name'] . ' ' . $row['surname']);
        $row['item'] = $row['item_name'];
        $row['unique_id'] = $row['unique_report_id'];
        
        // Map new statuses to old display names
        $statusMap = [
            'lost' => 'Registered',
            'found' => 'Found',
            'collected' => 'Collected'
        ];
        $row['tracking_status'] = $statusMap[$row['tracking_status']] ?? 'Registered';
        
        $lostItems[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Lost Items - <?php echo $school; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include 'pwa-head.php'; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="shortcut icon" href="img/f-icon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'header_css.php'; ?>
    <style>
        .stats-container {
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, <?php echo $themeColor; ?>, <?php echo $themeColor; ?>dd);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .table-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .table-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: <?php echo $themeColor; ?>;
            color: white;
            border: none;
            padding: 15px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .table td {
            padding: 15px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-registered {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-found {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-collected {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .search-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: <?php echo $themeColor; ?>;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            background-color: <?php echo $themeColor; ?>;
            border-color: <?php echo $themeColor; ?>;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: <?php echo $themeColor; ?>;
            border-color: <?php echo $themeColor; ?>;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .date-formatted {
            font-weight: 500;
            color: #495057;
        }
        .child-name {
            font-weight: 600;
            color: #212529;
        }
        .item-name {
            font-weight: 500;
            color: #495057;
        }
        .description-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .email-text {
            font-family: monospace;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .unique-id {
            font-family: monospace;
            font-weight: 600;
            color: <?php echo $themeColor; ?>;
            background-color: rgba(0, 123, 255, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'main_header.php'; ?>
        <div class="page-content d-flex align-items-stretch">
            <?php include 'school_sidebar.php'; ?>
            <div class="content-inner w-100">
                <header class="bg-white shadow-sm px-4 py-3 z-index-20">
                    <div class="container-fluid px-0">
                        <h2 class="mb-0 p-1"><i class="fas fa-search me-2"></i>Lost Items Management</h2>
                        <div class="mt-2">
                            <a href="user_dashboard" class="btn btn-sm btn-primary">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                            <a href="request_item" class="btn btn-sm btn-info">
                                <i class="fas fa-plus me-1"></i>Report Lost Item
                            </a>
                        </div>
                    </div>
                </header>
                <div class="container-fluid">
                    <!-- Statistics Cards -->
                    <div class="stats-container">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-search fa-2x"></i>
                                        </div>
                                        <div>
                                            <h3><?php echo $lostItemsCount; ?></h3>
                                            <p>Total Lost Items</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                        <div>
                                            <h3><?php echo $registeredItemsCount; ?></h3>
                                            <p>Registered Items</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                        <div>
                                            <h3><?php echo $foundItemsCount; ?></h3>
                                            <p>Found Items</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="search-container">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search by child name, item, or description...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="Registered">Registered</option>
                                    <option value="Found">Found</option>
                                    <option value="Collected">Collected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary" onclick="exportToCSV()">
                                    <i class="fas fa-download me-2"></i>Export CSV
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lost Items Table -->
                    <div class="table-container">
                        <div class="table-header">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lost Items List</h5>
                            <p class="text-muted mb-0">Showing all lost items for <?php echo $school; ?></p>
                        </div>
                        
                        <?php if (empty($lostItems)): ?>
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <h4>No Lost Items Found</h4>
                                <p>There are currently no lost items reported for this school.</p>
                                <a href="request_item" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Report First Lost Item
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="lostItemsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Child Name</th>
                                            <th>Item</th>
                                            <th>Description</th>
                                            <th>Lost Date</th>
                                            <th>Parent Email</th>
                                            <th>Unique ID</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lostItems as $index => $item): ?>
                                            <tr>
                                                <td><strong><?php echo $index + 1; ?></strong></td>
                                                <td>
                                                    <div class="child-name"><?php echo htmlspecialchars($item['child_name']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="item-name"><?php echo htmlspecialchars($item['item']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="description-text" title="<?php echo htmlspecialchars($item['description']); ?>">
                                                        <?php echo htmlspecialchars($item['description']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="date-formatted">
                                                        <?php echo date('M d, Y', strtotime($item['date_lost'])); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="email-text"><?php echo htmlspecialchars($item['parent_email']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="unique-id"><?php echo htmlspecialchars($item['unique_id']); ?></div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'status-registered';
                                                    if ($item['tracking_status'] == 'Found') {
                                                        $statusClass = 'status-found';
                                                    } elseif ($item['tracking_status'] == 'Collected') {
                                                        $statusClass = 'status-collected';
                                                    }
                                                    ?>
                                                    <span class="status-badge <?php echo $statusClass; ?>">
                                                        <?php echo htmlspecialchars($item['tracking_status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="js/front.js"></script>
    <script>
        function injectSvgSprite(path) {
            var ajax = new XMLHttpRequest();
            ajax.open("GET", path, true);
            ajax.send();
            ajax.onload = function(e) {
                var div = document.createElement("div");
                div.className = 'd-none';
                div.innerHTML = ajax.responseText;
                document.body.insertBefore(div, document.body.childNodes[0]);
            }
        }
        // SVG sprite removed - external resource no longer available
        
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('lostItemsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const childName = cells[1].textContent.toLowerCase();
                const item = cells[2].textContent.toLowerCase();
                const description = cells[3].textContent.toLowerCase();
                const status = cells[7].textContent.trim();
                
                const matchesSearch = childName.includes(searchTerm) || 
                                    item.includes(searchTerm) || 
                                    description.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        // Export to CSV functionality
        function exportToCSV() {
            const table = document.getElementById('lostItemsTable');
            const rows = table.getElementsByTagName('tr');
            let csv = [];
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cols = row.querySelectorAll('td, th');
                let rowData = [];
                
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].textContent.trim();
                    // Remove extra whitespace and newlines
                    text = text.replace(/\s+/g, ' ');
                    rowData.push('"' + text + '"');
                }
                
                csv.push(rowData.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'lost_items_<?php echo date('Y-m-d'); ?>.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

