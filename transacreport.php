<?php
session_start();
include 'asset/connect.php';

// Redirect if the user is not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Set the number of rows per page
$rowsPerPage = 10;

// Get the current page from the query parameter (default to 1)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure the page is at least 1

// Calculate the OFFSET for SQL query
$offset = ($page - 1) * $rowsPerPage;

// Fetch transaction data for the current page
$stmt = $pdo->prepare("SELECT 
        o.order_id,
        DATE_FORMAT(o.created_at, '%b %e, %Y') AS formatted_date,
        o.total_amount AS total,
        o.status,
        GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') AS products
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
    LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $rowsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of rows for pagination
$totalRows = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalPages = ceil($totalRows / $rowsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/transacreport.css">
    <title>Transaction Reports</title>
</head>
<body>
    <!-- Header Section -->
    <div class="top-section">
        <div class="header">
            <img class="logo" src="icon/logo.jpeg" alt="Logo">
            <nav>
                <div class="dashboard-welcome">
                    <h1>Welcome To The Transaction Report</h1>
                </div>
            </nav>
            <a href="adprofileedit.php">
                <img class="profile" src="icon/profilerb.png" alt="Profile">
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
                <li><a href="monthly_sales.php">Monthly Sales</a></li>
                <li><a href="transacreport.php" class="active">Transaction Reports</a></li>
            </ul>
        </div>

        <!-- Transaction Reports Table -->
        <div class="container">
    <h1>Transaction Reports</h1>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Products</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['formatted_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['products']); ?></td>
                    <td>RM <?php echo number_format($transaction['total'], 2); ?></td>
                    <td class="<?php echo strtolower($transaction['status']); ?>">
                        <?php echo htmlspecialchars($transaction['status']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-container">
        <form method="GET" action="">
            <button type="submit" name="page" value="<?php echo $page - 1; ?>" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                &#171; Previous
            </button>
            <select name="page" onchange="this.form.submit()">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i === $page ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
            <button type="submit" name="page" value="<?php echo $page + 1; ?>" <?php echo $page >= $totalPages ? 'disabled' : ''; ?>>
                Next &#187;
            </button>
        </form>
    </div>
</div>

</body>
</html>
