<?php
session_start();
include 'asset/connect.php'; // Database connection

$user_id = $_SESSION['user_id'];

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT username, email, phone, gender, birthday FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
    exit();
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
        echo "<script>alert('Profile updated successfully!');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error updating profile: " . $e->getMessage() . "');</script>";
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

    </style>

</head>
<body>
    <!-- Navigation Section -->
    <div class="header">
        <img class="logo" src="icon/logo.jpeg" alt="Logo">
        <div class="dashboard-welcome">
               <h1>Welcome To The Profile </h1>
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
            <button class="home-btn" onclick="location.href='addashboard.php'">Home</button><br>
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

                <label for="birthday">
                <div class="birthday-display">Your Birthday: <?php echo htmlspecialchars($user['birthday']); ?></div></label>
                <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>">
                  
                <button type="submit" class="confirm-btn">Confirm</button>
            </form>
        </div>
        <div class="edit-icon">
            <img src="icon/pencil.png" alt="Edit Icon">
        </div>
    </div>
</body>
</html>
