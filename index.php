<?php
session_start();
include 'asset/connect.php'; // Ensure this file sets up $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login logic
    $email = filter_var(trim($_POST['Email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['Password']);

    header('Content-Type: application/json');

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit();
    }

    try {
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT user_id, password_hash, role FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                session_regenerate_id(true);

                echo json_encode(['success' => true, 'redirect' => $user['role'] === 'admin' ? 'addashboard.php' : 'cusdashboard.php']);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/Login.css">
</head>
<body>
    <div class="left-section">
        <!-- Login Form -->
        <form class="form-position" id="loginForm" method="post">
            <h1>Login</h1>
            <img id="user" src="icon/user.png" alt="IconLogin">

            <!-- Email Input -->
            <label for="Email">Email</label>
            <input type="email" id="Email" name="Email" required placeholder="Enter your email">

            <!-- Password Input -->
            <label for="Password">Password</label>
            <input type="password" id="Password" name="Password" required placeholder="Enter your password">

            <!-- Forgot Password Link -->
            <div class="forgot-password">
                <a href="#" id="forgotPasswordLink">Forgot Password?</a>
            </div>

            <!-- Buttons -->
            <div class="button-container">
                <a href="Signup.php" class="button">Signup</a>
                <button class="button2" type="submit" name="Confirm">Confirm</button>
            </div>
        </form>
    </div>

    <div class="right-section">
        <img id="laksa" src="icon/Laksa1rb.png" alt="laksa in login">
    </div>

    <script>
        // Handle form submission for login
        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault(); // Prevent form submission

            const emailField = document.getElementById('Email');
            const passwordField = document.getElementById('Password');

            const email = emailField.value.trim();
            const password = passwordField.value.trim();

            // Client-side validation
            if (!email) {
                alert('Email is required.');
                emailField.focus();
                return;
            }
            if (!validateEmail(email)) {
                alert('Invalid email format.');
                emailField.focus();
                return;
            }
            if (!password) {
                alert('Password is required.');
                passwordField.focus();
                return;
            }

            // Send data to the server
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ Email: email, Password: password })
                });

                const result = await response.json();

                if (result.success) {
                    // Redirect if login is successful
                    window.location.href = result.redirect;
                } else {
                    // Display error message
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again later.');
            }
        });

        // Handle Forgot Password click
        document.getElementById('forgotPasswordLink').addEventListener('click', function (e) {
            e.preventDefault(); // Prevent the default link behavior

            // Replace this block with actual forgot password logic
            alert('Please check your email to reset your password.');
        });

        // Function to validate email format
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>
</body>
</html>
