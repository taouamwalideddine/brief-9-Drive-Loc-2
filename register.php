<?php
session_start();

require_once "config/Database.php";
require_once "models/Registration.php";

$error = null;
$success = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();

    $registration = new Registration($db);

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $result = $registration->registerUser($name, $email, $password);

        if ($result === true) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Royal Cars</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Rubik&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #F77D0A;
            --secondary: #2B2E4A;
            --light: #F4F5F8;
            --dark: #1C1E32;
        }
        .register-container {
            background-color: var(--light);
            border-radius: 5px;
            padding: 30px;
            margin-top: 90px;
            margin-bottom: 90px;
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: #e67609;
            border-color: #e67609;
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="display-4 text-uppercase text-center mb-5">Register</h1>
                <div class="contact-form bg-light p-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="form-group">
                            <input type="text" name="name" class="form-control p-4" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control p-4" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control p-4" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="confirm_password" class="form-control p-4" placeholder="Confirm Password" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">I agree to the Terms and Conditions</label>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-block py-3" type="submit">Register</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <span>Already have an account? </span>
                        <a href="login.php" class="text-primary">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
