<?php
session_start();
include 'asset/connect.php';

// Redirect if the user is not authenticated
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch sales data from the database
try {
    $stmt = $pdo->query("
        SELECT 
            MONTH(created_at) AS month, 
            SUM(total_amount) AS revenue 
        FROM orders 
        WHERE status = 'completed' 
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at)
    ");
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for the chart
    $months = [
        1 => "January", 2 => "February", 3 => "March", 4 => "April",
        5 => "May", 6 => "June", 7 => "July", 8 => "August",
        9 => "September", 10 => "October", 11 => "November", 12 => "December"
    ];

    $chartData = [
        'labels' => [],
        'data' => []
    ];

    foreach ($salesData as $row) {
        $chartData['labels'][] = $months[$row['month']];
        $chartData['data'][] = $row['revenue'];
    }
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/monthly_sales.css">
    <title>Monthly Sales</title>
</head>
<body>
    <!-- Header Section -->
    <div class="top-section">
        <div class="header">
            <img class="logo" src="icon/logo.jpeg" alt="Logo">
            <div class="dashboard-welcome">
                <h1>Welcome To The Monthly Sales Report</h1>
            </div>
            <a href="profile.html">
                <img class="profile" src="../icon/profilerb.png" alt="Profile">
            </a>
        </div>
    </div>

    <!-- Sidebar Section -->
    <div class="bottom-section">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="addashboard.php">Dashboard</a></li>
                <li><a href="amp/amp.php">Manage Products</a></li>
                <li><a href="monthly_sales.php" class="active">Monthly Sales</a></li>
                <li><a href="transacreport.php">Transaction Reports</a></li>
            </ul>
        </div>

        <!-- Main Content Section -->
        <div class="main-content">
            <h1>Monthly Sales Report</h1>
            <div style="display: flex">
                <div class="chart">
                    <canvas id="chart" width="900" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PHP data passed to JavaScript
        const chartData = <?php echo json_encode($chartData); ?>;

        // Render the chart using Chart.js
        new Chart("chart", {
            type: "bar",
            data: {
                labels: chartData.labels,
                datasets: [{
                    backgroundColor: "#9bbee1",
                    data: chartData.data
                }]
            },
            options: {
                legend: { display: false },
                title: {
                    display: true,
                    text: "Monthly Sales Revenue"
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return 'RM ' + value.toLocaleString();
                            }
                        }
                    }]
                }
            }
        });
    </script>
</body>
</html>
