<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require_once "config.php";

$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if (empty($email_err) && empty($password_err)) {
        // Update SQL to select tenant_id
        $sql = "SELECT id, tenant_id, username, password, role FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind tenant_id to a variable
                    mysqli_stmt_bind_result($stmt, $id, $tenant_id, $username, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION["loggedin"]  = true;
                            $_SESSION["id"]        = $id;
                            $_SESSION["username"]  = $username;
                            $_SESSION["role"]      = $role;
                            $_SESSION["tenant_id"] = $tenant_id ?? 0;

                            // Log the login event
                            $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
                            $ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
                            $tid = (int)($tenant_id ?? 0);
                            $log_sql = "INSERT INTO access_logs (user_id, tenant_id, ip_address, user_agent, action) VALUES (?, ?, ?, ?, 'login')";
                            if ($log_stmt = mysqli_prepare($link, $log_sql)) {
                                mysqli_stmt_bind_param($log_stmt, 'iiss', $id, $tid, $ip, $ua);
                                mysqli_stmt_execute($log_stmt);
                                mysqli_stmt_close($log_stmt);
                            }

                            // Redirect to the main dashboard
                            header("location: index.php");
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - StudyFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center mb-6">Login to StudyFlow</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-purple-500 <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $email; ?>">
                <span class="text-red-500 text-sm"><?php echo $email_err; ?></span>
            </div>    
            <div class="mb-6">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-purple-500 <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>">
                <span class="text-red-500 text-sm"><?php echo $password_err; ?></span>
            </div>
            <?php 
            if(!empty($login_err)){
                echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-4">' . $login_err . '</div>';
            }        
            ?>
            <div class="mb-4">
                <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700">Login</button>
            </div>
            <p class="text-center text-sm">Don't have an account? <a href="register.php" class="text-purple-600 hover:underline">Sign up now</a>.</p>
        </form>
    </div>
</body>
</html>