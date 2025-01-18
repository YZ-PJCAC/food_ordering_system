<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Signup.css">
    <title>Sign Up Page</title>
</head>
<body>
    <div class="left-section">
        <form class="form-position" id="signup" method="post">
            <h1>Sign Up</h1>
            <img id="user" src="icon/user.png" alt="IconSignup">

            <label for="email">Email</label>
            <input type="text" id="email" name="Email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="Password" required>
            <p>6-8 characters, one uppercase, one number, one special character, no spaces</p>

            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" name="ConfirmPassword" required>

            <div class="button-container">
                <a href="index.php" class="button">Login</a>
                <button class="button2" type="submit" name="Confirm">Confirm</button>
                <label for="role">Role</label>
            <select id="role" name="Role" required>
                <option value="customer" selected>Customer</option>
                <option value="admin">Admin</option>
            </select>
            </div>
        </form>
    </div>

    <div class="right-section">
        <img id="laksa" src="icon/Laksa1rb.png" alt="Laksa Image">
    </div>

    <script>
        document.getElementById('signup').addEventListener('submit', function (event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Basic password validation
            const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{6,8}$/;

            if (!passwordRegex.test(password)) {
                alert('Password must be 6-8 characters, one uppercase, one number, one special character, and no spaces.');
                event.preventDefault();
                return;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                event.preventDefault();
            }
        });
    </script>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Database connection details
        $servername = "sql209.infinityfree.com";
        $username = "if0_38042508";
        $password = "OPhQ0F3THq6o2C";
        $dbname = "if0_38042508_db_system";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve form data
        $email = $conn->real_escape_string($_POST['Email']);
        $password = $_POST['Password'];
        $confirmPassword = $_POST['ConfirmPassword'];
        $role = $conn->real_escape_string($_POST['Role']);

        // Server-side validation
        if ($password !== $confirmPassword) {
            echo "<script>alert('Passwords do not match.');</script>";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the database
            $sql = "INSERT INTO users (email, password_hash, role) VALUES ('$email', '$hashedPassword', '$role')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Sign up successful!'); window.location.href = 'index.php';</script>";
            } else {
                if ($conn->errno === 1062) {
                    // Handle duplicate entry error
                    echo "<script>alert('Email already exists. Please try another.');</script>";
                } else {
                    echo "<script>alert('Error: " . $conn->error . "');</script>";
                }
            }
        }

        $conn->close();
    }
    ?>
</body>
</html>
