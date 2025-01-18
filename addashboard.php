<?php
session_start();
include 'asset/connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch data for the dashboard
try {
    // Total orders
    $totalOrdersStmt = $pdo->query("SELECT COUNT(*) AS total_orders FROM orders");
    $totalOrders = $totalOrdersStmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    // Customer growth (total customers)
    $customerGrowthStmt = $pdo->query("SELECT COUNT(*) AS total_customers FROM users WHERE role = 'customer'");
    $totalCustomers = $customerGrowthStmt->fetch(PDO::FETCH_ASSOC)['total_customers'];

    // Total revenue for the current week
    $totalRevenueStmt = $pdo->prepare("
        SELECT SUM(total_amount) AS total_revenue 
        FROM orders 
        WHERE WEEK(created_at) = WEEK(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $totalRevenueStmt->execute();
    $totalRevenue = $totalRevenueStmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];

    // Weekly revenue by day (for line chart)
    $weeklyRevenueStmt = $pdo->query("
        SELECT DATE(created_at) AS day, SUM(total_amount) AS revenue
        FROM orders
        WHERE WEEK(created_at) = WEEK(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at)
    ");
    $weeklyRevenue = $weeklyRevenueStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// Targets
$targets = [
    'orders' => 100,
    'customers' => 120,
    'revenue' => 300
];

// Pass data to JavaScript
$data = [
    'totalOrders' => $totalOrders,
    'totalCustomers' => $totalCustomers,
    'totalRevenue' => $totalRevenue ?? 0,
    'weeklyRevenue' => $weeklyRevenue,
    'targets' => $targets
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/addashboard.css">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="top-section">
        <div class="header">
           <img class="logo" src="icon/logo.jpeg" alt="Logo">
           <div class="dashboard-welcome">
               <h1>Welcome To The Admin Dashboard</h1>
           </div>
           <a href="adprofileedit.php">
                <img class="profile" src="icon/profilerb.png" alt="Profile">
            </a>
        </div>
    </div>

    <div class="bottom-section">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="addashboard.php" class="active">Dashboard</a></li>
                <li><a href="amp/amp.php">Manage Products</a></li>
                <li><a href="monthly_sales.php">Monthly Sales</a></li>
                <li><a href="transacreport.php">Transaction Reports</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Dashboard</h1>

            <div class="stats-container">
                <div class="stat-card">
                    <canvas id="orderPieChart"></canvas>
                    <p>Total Orders: <?php echo $totalOrders; ?> (<?php echo round(($totalOrders / $targets['orders']) * 100, 2); ?>% of target)</p>
                </div>
                <div class="stat-card">
                    <canvas id="customerPieChart"></canvas>
                    <p>Total Customers: <?php echo $totalCustomers; ?> (<?php echo round(($totalCustomers / $targets['customers']) * 100, 2); ?>% of target)</p>
                </div>
                <div class="stat-card">
                    <canvas id="revenuePieChart"></canvas>
                    <p>Total Weekly Revenue: RM <?php echo number_format($totalRevenue, 2); ?> (<?php echo round(($totalRevenue / $targets['revenue']) * 100, 2); ?>% of target)</p>
                </div>
            </div>

            <h2 class="titleTR">Weekly Revenue</h2>
            <canvas id="revenueLineChart"></canvas>
        </div>
    </div>

    <script>
        // PHP data passed to JavaScript
        const data = <?php echo json_encode($data); ?>;

        // Pie Chart for Total Orders
        const orderPieChart = new Chart(document.getElementById('orderPieChart'), {
            type: 'pie',
            data: {
                labels: ['Completed Orders', 'Remaining'],
                datasets: [{
                    data: [data.totalOrders, data.targets.orders - data.totalOrders],
                    backgroundColor: ['#ff9999', '#ddd'],
                }]
            }
        });

        // Pie Chart for Customer Growth
        const customerPieChart = new Chart(document.getElementById('customerPieChart'), {
            type: 'pie',
            data: {
                labels: ['Customers', 'Remaining'],
                datasets: [{
                    data: [data.totalCustomers, data.targets.customers - data.totalCustomers],
                    backgroundColor: ['#ffcc99', '#ddd'],
                }]
            }
        });

        // Pie Chart for Total Revenue
        const revenuePieChart = new Chart(document.getElementById('revenuePieChart'), {
            type: 'pie',
            data: {
                labels: ['Achieved Revenue', 'Remaining'],
                datasets: [{
                    data: [data.totalRevenue, data.targets.revenue - data.totalRevenue],
                    backgroundColor: ['#99ff99', '#ddd'],
                }]
            }
        });

        // Line Chart for Weekly Revenue
        const weeklyLabels = data.weeklyRevenue.map(item => item.day);
        const weeklyData = data.weeklyRevenue.map(item => item.revenue);
        const revenueLineChart = new Chart(document.getElementById('revenueLineChart'), {
            type: 'line',
            data: {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Revenue',
                    data: weeklyData,
                    borderColor: '#4CAF50',
                    fill: false,
                }]
            }
        });
    </script>
</body>
</html>
