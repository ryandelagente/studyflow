<?php
require_once "config.php";
session_start();

// If user is already logged in, redirect them.
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

$username = $email = $password = "";
$username_err = $email_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your name.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        // Check if email is already taken
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check input errors before processing
    if (empty($username_err) && empty($email_err) && empty($password_err)) {

        // Use a transaction to ensure atomicity (all or nothing)
        mysqli_begin_transaction($link);

        try {
            // 1. Insert the new user with an 'admin' role for their new tenant
            $sql_user = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
            $stmt_user = mysqli_prepare($link, $sql_user);
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt_user, "sss", $username, $email, $param_password);
            mysqli_stmt_execute($stmt_user);
            $new_user_id = mysqli_insert_id($link);
            mysqli_stmt_close($stmt_user);

            // 2. Create a new tenant owned by this user
            $sql_tenant = "INSERT INTO tenants (name, owner_id) VALUES (?, ?)";
            $stmt_tenant = mysqli_prepare($link, $sql_tenant);
            $tenant_name = $username . "'s Workspace"; // e.g., "John Doe's Workspace"
            mysqli_stmt_bind_param($stmt_tenant, "si", $tenant_name, $new_user_id);
            mysqli_stmt_execute($stmt_tenant);
            $new_tenant_id = mysqli_insert_id($link);
            mysqli_stmt_close($stmt_tenant);

            // 3. Update the user record with their new tenant_id
            $sql_update_user = "UPDATE users SET tenant_id = ? WHERE id = ?";
            $stmt_update_user = mysqli_prepare($link, $sql_update_user);
            mysqli_stmt_bind_param($stmt_update_user, "ii", $new_tenant_id, $new_user_id);
            mysqli_stmt_execute($stmt_update_user);
            mysqli_stmt_close($stmt_update_user);

            // If all queries succeeded, commit the transaction
            mysqli_commit($link);

            // Automatically log the user in and redirect to the dashboard
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $new_user_id;
            $_SESSION["tenant_id"] = $new_tenant_id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = 'admin';

            header("location: index.php");
            exit();

        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($link);
            // In a production app, you would log this error.
            die("Error during registration. Please try again.");
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - StudyFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center mb-6">Create Your StudyFlow Account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700">Full Name</label>
                <input type="text" name="username" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-purple-500 <?php echo (!empty($username_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $username; ?>">
                <span class="text-red-500 text-sm"><?php echo $username_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email Address</label>
                <input type="email" name="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-purple-500 <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $email; ?>">
                <span class="text-red-500 text-sm"><?php echo $email_err; ?></span>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-purple-500 <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>">
                <span class="text-red-500 text-sm"><?php echo $password_err; ?></span>
            </div>
            <div class="mb-4">
                <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700">Sign Up</button>
            </div>
            <p class="text-center text-sm">Already have an account? <a href="login.php" class="text-purple-600 hover:underline">Log in</a>.</p>
        </form>
    </div>
</body>
</html>