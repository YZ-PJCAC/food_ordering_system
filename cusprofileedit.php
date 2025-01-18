<?php
session_start();
include 'asset/connect.php'; // Database connection

$user_id = $_SESSION['user_id'];
$message = ""; // Initialize message

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT username, email, phone, gender, birthday FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Update user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];

    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = :name, email = :email, phone = :phone, gender = :gender, birthday = :birthday 
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'gender' => $gender,
            'birthday' => $birthday,
            'user_id' => $user_id
        ]);
        $message = "Profile updated successfully!";
    } catch (PDOException $e) {
        $message = "Error updating profile: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Edit</title>
    <link rel="stylesheet" href="css/profile.css">
    <style>
        .home-btn {
            background-color: #fff;
            color: black;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 50px;
            border: 2px solid red;
            cursor: pointer;
            transition: 0.3s ease;
            align-items: center;
            margin-bottom: 15px;
            margin-left: 5px;
        }

        .home-btn:hover {
            background-color: #ff9999;
        }

        .popup-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #d4edda;
            color: #155724;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-weight: bold;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- JavaScript Message Popup -->
    <div id="popup-message" class="popup-message"></div>

    <!-- Navigation Section -->
    <div class="header">
        <img class="logo" src="icon/logo.jpeg" alt="Logo">
        <div class="dashboard-welcome">
            <h1>Welcome To The Profile</h1>
        </div>
        <a href="adprofileedit.php">
            <img class="profile" src="icon/profilerb.png" alt="Profile">
        </a>
    </div>

    <!-- Main Profile Edit Section -->
    <div class="profile-section">
        <div class="profile-sidebar">
            <h1>Profile</h1>
            <img src="icon/user.png" alt="User Icon" class="user-icon">
            <div class="profilebtn">
                <button class="home-btn" onclick="location.href='cusdashboard.php'">Home</button><br>
                <button class="logout-btn" onclick="location.href='index.php'">Logout</button>
            </div>
        </div>
        <div class="profile-form">
            <form action="" method="POST">
                <label for="name">NAME</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required>

                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label for="phone">PHONE NO</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

                <label for="gender">GENDER</label>
                <input type="text" id="gender" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>">

                <label for="birthday">BIRTHDAY</label>
                <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>">

                <button type="submit" class="confirm-btn">Confirm</button>
            </form>
        </div>
        <div class="edit-icon">
            <img src="icon/pencil.png" alt="Edit Icon">
        </div>
    </div>

    <script>
        // Display the success or error message using JavaScript
        const message = "<?php echo $message; ?>";
        if (message) {
            const popup = document.getElementById('popup-message');
            popup.textContent = message;
            popup.style.display = 'block';

            // Hide the popup after 3 seconds
            setTimeout(() => {
                popup.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
