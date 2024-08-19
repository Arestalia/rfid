<?php
include "database/db.php";
session_start();

$database = new Database();
$conn = $database->getConnection();

$message = "";

if (isset($_POST['login'])) {
    if (!isset($_POST['username'], $_POST['password'])) {
        // Could not get the data that should have been sent.
        exit('Please fill both the username and password fields!');
    }

    // Prepare our SQL, preparing the SQL statement will prevent SQL injection.
    if ($stmt = $conn->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
        // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $password);
            $stmt->fetch();
            // Account exists, now we verify the password.
            // Note: remember to use password_hash in your registration file to store the hashed passwords.
            if (password_verify($_POST['password'], $password)) {
                // Verification success! User has logged-in!
                // Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
                session_regenerate_id();
                $_SESSION['loggedin'] = true;
                $_SESSION['name'] = $_POST['username'];
                $_SESSION['id'] = $id;

                $roleStmt = $conn->prepare('SELECT role FROM accounts WHERE id = ?');
                $roleStmt->bind_param('i', $id);
                $roleStmt->execute();
                $roleStmt->bind_result($role);
                $roleStmt->fetch();


                header('Location: admin/index.php');
            } else {
                // Incorrect password
                $message = 'Incorrect username and/or password!';
            }
        } else {
            // Incorrect username
            $message = 'Incorrect username and/or password!';
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Login</h1>
            <form method="post">
                <div class="input-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                    </label>
                    <input type="text" name="username" placeholder="Username" id="username" required>
                </div>
                <div class="input-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                    </label>
                    <input type="password" name="password" placeholder="Password" id="password" required>
                </div>
                <?php if ($message): ?>
                    <p class="error-message"><?= $message ?></p>
                <?php endif; ?>
                <input type="submit" name="login" id="login" value="Login">
            </form>
        </div>
    </div>
</body>

</html>