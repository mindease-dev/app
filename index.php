<?php
// --- DEBUGGING MODE ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// --- SERVER DATABASE CONFIGURATION ---
$servername = "sql206.infinityfree.com"; 
$username = "if0_40471712"; 
$password = "cFZlZCXRY9Y"; 
$dbname = "if0_40471712_mindease"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$login_error = ""; 
$signup_error = "";

// --- BACKEND LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- SIGNUP LOGIC ---
    if (isset($_POST['signup'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $pass = $_POST['password'];
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        
        if ($check->num_rows > 0) {
            $signup_error = "Email already exists!";
        } else {
            // Naya user banate waqt attempts 0 honge
            $sql = "INSERT INTO users (full_name, email, password, failed_attempts) VALUES ('$name', '$email', '$hashed_password', 0)";
            if ($conn->query($sql) === TRUE) {
                header("Location: https://mindease.puter.site/");
                exit();
            } else {
                $signup_error = "Error: " . $conn->error;
            }
        }
    }

    // --- LOGIN LOGIC (Delete on 3rd Fail) ---
    if (isset($_POST['signin'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $pass = $_POST['password'];

        $sql = "SELECT id, password, full_name, failed_attempts FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $uid = $row['id'];

            // Password Verify
            if (password_verify($pass, $row['password'])) {
                // SUCCESS: Reset attempts to 0
                $conn->query("UPDATE users SET failed_attempts = 0 WHERE id = '$uid'");

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['full_name'];
                
                $ip = $_SERVER['REMOTE_ADDR'];
                $conn->query("INSERT INTO login_history (user_id, ip_address) VALUES ('$uid', '$ip')");
                
                header("Location: https://mindease.puter.site/");
                exit();
            } else {
                // FAILURE Logic
                $new_attempts = $row['failed_attempts'] + 1;
                
                if ($new_attempts >= 3) {
                    // --- DELETE USER PERMANENTLY ---
                    $conn->query("DELETE FROM users WHERE id = '$uid'");
                    $login_error = "Your account has been removed due to 3 failed attempts. You can signup again with this email.";
                } else {
                    // --- UPDATE ATTEMPTS ---
                    $conn->query("UPDATE users SET failed_attempts = '$new_attempts' WHERE id = '$uid'");
                    $remaining = 3 - $new_attempts;
                    $login_error = "Wrong Password! You have $remaining attempt(s) left before deletion.";
                }
            }
        } else {
            $login_error = "User Not Found or Already Deleted.";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindEase AI - Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #4facfe;
            --secondary-color: #00f2fe;
            --error-color: #e74c3c;
            --bg-gradient: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: var(--bg-gradient);
            display: flex; align-items: center; justify-content: center;
            flex-direction: column; height: 100vh; overflow: hidden;
        }

        .container {
            background-color: #fff; border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            position: relative; overflow: hidden;
            width: 768px; max-width: 100%; min-height: 500px;
            transition: all 0.6s ease-in-out;
        }

        /* PROFESSIONAL ERROR MESSAGE STYLE */
        .error-msg {
            color: var(--error-color);
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
            margin-bottom: 10px;
            background: rgba(231, 76, 60, 0.1);
            padding: 10px;
            border-radius: 6px;
            border-left: 3px solid var(--error-color);
            width: 100%;
            text-align: center;
            animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        /* Rest of Design */
        @keyframes gradientText {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container h1 {
            font-weight: 700; margin-bottom: 15px;
            background: linear-gradient(45deg, #4facfe, #00f2fe, #66a6ff, #89f7fe);
            background-size: 300% 300%;
            -webkit-background-clip: text; background-clip: text; color: transparent;
            animation: gradientText 4s ease infinite;
        }

        .container span { font-size: 12px; color: #666; margin-bottom: 15px; }
        
        .container button {
            background: linear-gradient(to right, #5c6bc0, #512da8);
            color: #fff; font-size: 12px; padding: 12px 50px;
            border: 1px solid transparent; border-radius: 8px;
            font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;
            margin-top: 10px; cursor: pointer; transition: 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .container button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.25); }
        .container button.hidden { background-color: transparent; border-color: #fff; }

        .container form {
            background-color: #fff; display: flex; align-items: center; justify-content: center;
            flex-direction: column; padding: 0 40px; height: 100%;
        }

        .container input {
            background-color: #f0f4f8; border: none; margin: 8px 0; padding: 12px 15px;
            font-size: 13px; border-radius: 8px; width: 100%; outline: none; transition: 0.3s;
        }
        .container input:focus { background-color: #e8eaf6; border-left: 4px solid #512da8; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .container a { color: #333; font-size: 13px; text-decoration: none; margin: 10px 0; transition: 0.3s; }
        .container a:hover { color: #4facfe; }

        /* Desktop Layout */
        .form-container { position: absolute; top: 0; height: 100%; transition: all 0.6s ease-in-out; }
        .sign-in { left: 0; width: 50%; z-index: 2; }
        .container.active .sign-in { transform: translateX(100%); }
        .sign-up { left: 0; width: 50%; opacity: 0; z-index: 1; }
        .container.active .sign-up { transform: translateX(100%); opacity: 1; z-index: 5; animation: move 0.6s; }

        @keyframes move {
            0%, 49.99% { opacity: 0; z-index: 1; }
            50%, 100% { opacity: 1; z-index: 5; }
        }

        .toggle-container {
            position: absolute; top: 0; left: 50%; width: 50%; height: 100%;
            overflow: hidden; transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px; z-index: 1000;
        }
        .container.active .toggle-container { transform: translateX(-100%); border-radius: 0 150px 100px 0; }

        .toggle {
            background: linear-gradient(to right, #5c6bc0, #512da8); color: #fff;
            position: relative; left: -100%; height: 100%; width: 200%;
            transform: translateX(0); transition: all 0.6s ease-in-out;
        }
        .container.active .toggle { transform: translateX(50%); }

        .toggle-panel {
            position: absolute; width: 50%; height: 100%; display: flex;
            align-items: center; justify-content: center; flex-direction: column;
            padding: 0 30px; text-align: center; top: 0; transform: translateX(0); transition: all 0.6s ease-in-out;
        }

        .toggle-left { transform: translateX(-200%); }
        .container.active .toggle-left { transform: translateX(0); }
        .toggle-right { right: 0; transform: translateX(0); }
        .container.active .toggle-right { transform: translateX(200%); }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body { padding: 10px; height: 100vh; } 
            .container { width: 100%; max-width: 420px; height: 90dvh; min-height: 550px; border-radius: 25px; }
            .form-container { width: 100%; height: 65%; top: 0; transition: all 0.5s ease; overflow-y: auto; }
            .container form { padding: 0 25px; }
            .container h1 { font-size: 24px; margin-bottom: 10px; }
            
            .sign-in { top: 0; opacity: 1; z-index: 2; }
            .container.active .sign-in { transform: translateY(-100%); opacity: 0; }
            .sign-up { top: 0; opacity: 0; transform: translateY(100%); }
            .container.active .sign-up { transform: translateY(0); opacity: 1; z-index: 5; animation: none; }

            .toggle-container { top: auto; bottom: 0; left: 0; width: 100%; height: 35%; border-radius: 50px 50px 0 0; transform: none !important; }
            .container.active .toggle-container { transform: none !important; border-radius: 50px 50px 0 0; }
            .toggle { width: 100%; height: 200%; left: 0; transform: translateY(0); background: linear-gradient(to top, #5c6bc0, #512da8); }
            .container.active .toggle { transform: translateY(-50%); }
            
            .toggle-panel { width: 100%; height: 50%; padding: 0 20px; }
            .toggle-right { top: 0; bottom: auto; right: auto; transform: translateY(0) !important; }
            .toggle-left { top: auto; bottom: 0; left: auto; transform: translateY(0) !important; }
            .container.active .toggle-right { transform: translateY(0) !important; }
            .container.active .toggle-left { transform: translateY(0) !important; }
        }
    </style>
</head>
<body>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="" method="POST">
                <h1>Create Account</h1>
                <span>use your email for registration</span>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                
                <?php if(!empty($signup_error)): ?>
                    <div class="error-msg">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $signup_error; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" name="signup">Sign Up</button>
            </form>
        </div>

        <div class="form-container sign-in">
            <form action="" method="POST">
                <h1>Welcome Back</h1>
                <span>Use your email and password</span>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                
                <?php if(!empty($login_error)): ?>
                    <div class="error-msg">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>

                <a href="#">Forgot Your Password?</a>
                <button type="submit" name="signin">Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login.</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details to start journey.</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        <?php if(!empty($signup_error)): ?>
            container.classList.add("active");
        <?php endif; ?>

        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove("active");
        });
    </script>
</body>
</html>