<?php
include('connection.php');
// session_start(); // Already started in connection.php -> security.php

// Check if user is logged in first
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login");
    exit();
}

// Get session variables with fallbacks
$email = isset($_SESSION['socialWorkerEmail']) ? $_SESSION['socialWorkerEmail'] : '';
$name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$selectedSchool = $_SESSION['school'] ?? '';
$school_id = $_SESSION['school_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// Handle school selection from URL parameter
if (isset($_GET['school_id'])) {
    $selectedSchoolId = intval($_GET['school_id']);
    $_SESSION['school_id'] = $selectedSchoolId;
    $school_id = $selectedSchoolId;
    
    // Fetch school name for session
    $stmt = $conn->prepare("SELECT school_name FROM schools WHERE id = ?");
    $stmt->bind_param("i", $selectedSchoolId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $_SESSION['school'] = $row['school_name'];
        $selectedSchool = $row['school_name'];
    }
}

// If essential session data is missing, redirect to login
if (empty($email) || empty($name) || $school_id <= 0) {
    session_destroy();
    header("Location: login");
    exit();
}

// Get theme settings for the selected school securely
$sql = "SELECT logo_path, theme_color FROM admin_settings WHERE school = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedSchool);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $themeColor = $row["theme_color"];
    $logoPath = $row["logo_path"];
} else {
    $themeColor = "#007bff";
    $logoPath = "img/Find&R.png";
}
$stmt->close();

// Get found items data from new_lost_items table
$foundItems = [];
$totalFoundItems = 0;
$recentlyFoundItems = 0;
$awaitingCollectionItems = 0;

try {
    // Get found items for this school using school_id
    $sql_found = "SELECT nli.*, c.first_name, c.surname, c.grade,
                         u.email as parent_email, u.phone_no as parent_phone
                  FROM new_lost_items nli
                  LEFT JOIN children c ON nli.child_id = c.id
                  LEFT JOIN users u ON c.parent_id = u.id
                  WHERE nli.school_id = ?
                    AND nli.tracking_status IN ('found', 'collected')
                  ORDER BY nli.created_at DESC";
    $result_found = db_query($conn, $sql_found, "i", [$school_id]);
    if ($result_found && $result_found->num_rows > 0) {
        while ($row = $result_found->fetch_assoc()) {
            $foundItems[] = $row;
        }
    }

    // Statistics
    $r1 = db_query($conn,
        "SELECT COUNT(*) as total FROM new_lost_items WHERE school_id = ? AND tracking_status IN ('found','collected')",
        "i", [$school_id]);
    if ($r1) $totalFoundItems = $r1->fetch_assoc()['total'];

    $r2 = db_query($conn,
        "SELECT COUNT(*) as total FROM new_lost_items WHERE school_id = ? AND tracking_status IN ('found','collected') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
        "i", [$school_id]);
    if ($r2) $recentlyFoundItems = $r2->fetch_assoc()['total'];

    $r3 = db_query($conn,
        "SELECT COUNT(*) as total FROM new_lost_items WHERE school_id = ? AND tracking_status = 'found'",
        "i", [$school_id]);
    if ($r3) $awaitingCollectionItems = $r3->fetch_assoc()['total'];

} catch (Exception $e) {
    // handle gracefully
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Found Items - <?php echo $selectedSchool; ?></title>
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
        .found-items-container {
            max-width: 1400px;
            margin: 20px auto;
        }
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
        .child-name {
            font-weight: 600;
            color: #333;
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
        .date-formatted {
            font-weight: 500;
            color: #495057;
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
                        <h2 class="mb-0 p-1"><i class="fas fa-hand-holding-heart me-2"></i>Found Items</h2>
                        <div class="mt-2">
                            <?php if ($_SESSION['role'] == 'parent'): ?>
                                <a href="parent_dashboard" class="btn btn-sm btn-info">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            <?php else: ?>
                                <a href="index2" class="btn btn-sm btn-primary">
                                    <i class="fas fa-qrcode me-1"></i>Scan New Item
                                </a>
                                <a href="user_dashboard" class="btn btn-sm btn-info">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>
                <!-- Breadcrumb-->
                <div class="bg-white">
                    <div class="container-fluid">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 py-3">
                                <?php if ($_SESSION['role'] == 'parent'): ?>
                                    <li class="breadcrumb-item"><a class="fw-light" href="parent_dashboard">Home</a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item"><a class="fw-light" href="user_dashboard">Dashboard</a></li>
                                <?php endif; ?>
                                <li class="breadcrumb-item active fw-light" aria-current="page">Found Items</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="found-items-container">
                        <!-- Statistics Cards -->
                        <div class="stats-container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-hand-holding-heart fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3><?php echo $totalFoundItems; ?></h3>
                                                <p>Total Found Items</p>
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
                                                <h3><?php echo $recentlyFoundItems; ?></h3>
                                                <p>Recently Found (7 days)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="fas fa-user-clock fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3><?php echo $awaitingCollectionItems; ?></h3>
                                                <p>Awaiting Collection</p>
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
                                        <option value="Item found">Found</option>
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

                        <!-- Found Items Table -->
                        <div class="table-container">
                            <div class="table-header">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Found Items List</h5>
                                <p class="text-muted mb-0">Showing found items for <?php echo $selectedSchool; ?></p>
                            </div>
                            
                            <?php if (empty($foundItems)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-hand-holding-heart"></i>
                                    <h4>No Found Items</h4>
                                    <p>There are currently no found items for this school.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="foundItemsTable">
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
                                            <?php foreach ($foundItems as $index => $item): ?>
                                                <tr>
                                                    <td><strong><?php echo $index + 1; ?></strong></td>
                                                    <td>
                                                        <div class="child-name"><?php echo htmlspecialchars(($item['first_name'] ?? '') . ' ' . ($item['surname'] ?? '')); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="item-name"><?php echo htmlspecialchars($item['item_name'] ?? ''); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="description-text" title="<?php echo htmlspecialchars($item['description'] ?? ''); ?>">
                                                            <?php echo htmlspecialchars($item['description'] ?? ''); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="date-formatted">
                                                            <?php echo date('M d, Y', strtotime($item['created_at'] ?? date('Y-m-d'))); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="email-text"><?php echo htmlspecialchars($item['parent_email'] ?? ''); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="unique-id"><?php echo htmlspecialchars($item['unique_report_id'] ?? ''); ?></div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusClass = ($item['tracking_status'] ?? '') === 'collected' ? 'status-collected' : 'status-found';
                                                        ?>
                                                        <span class="status-badge <?php echo $statusClass; ?>">
                                                            <?php echo ucfirst(htmlspecialchars($item['tracking_status'] ?? 'found')); ?>
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
            const table = document.getElementById('foundItemsTable');
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
            const table = document.getElementById('foundItemsTable');
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
            link.setAttribute('download', 'found_items_<?php echo date('Y-m-d'); ?>.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    <?php include 'pwa-footer.php'; ?>
</body>
</html>

