<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "config/Database.php";
require_once "models/User.php";

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if($user->login($email, $password)) {
        $user->startSession();

        if($_SESSION['user_role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: booking.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ROYAL CARS - Login</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Rubik&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
        :root {
            --primary: #F77D0A;
            --secondary: #2B2E4A;
            --light: #F4F5F8;
            --dark: #1C1E32;
        }
        
        .bg-secondary { background-color: var(--secondary) !important; }
        .text-primary { color: var(--primary) !important; }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #e67609;
            border-color: #e67609;
        }
        
        .login-container {
            background-color: var(--light);
            border-radius: 5px;
            padding: 30px;
            margin-top: 90px;
            margin-bottom: 90px;
        }
    </style>
</head>

<body>
    <!-- Top Bar Start -->
    <div class="container-fluid bg-dark py-3 px-lg-5 d-none d-lg-block">
        <div class="row">
            <div class="col-md-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body pr-3" href=""><i class="fa fa-phone-alt mr-2"></i>+012 345 6789</a>
                    <span class="text-body">|</span>
                    <a class="text-body px-3" href=""><i class="fa fa-envelope mr-2"></i>info@example.com</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Bar End -->

    <!-- Navigation Bar Start -->
    <div class="container-fluid position-relative nav-bar p-0">
        <div class="position-relative px-lg-5" style="z-index: 9;">
            <nav class="navbar navbar-expand-lg bg-secondary navbar-dark py-3 py-lg-0 pl-3 pl-lg-5">
                <a href="" class="navbar-brand">
                    <h1 class="text-uppercase text-primary mb-1">Royal Cars</h1>
                </a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between px-3" id="navbarCollapse">
                    <div class="navbar-nav ml-auto py-0">
                        <a href="index.php" class="nav-item nav-link">Home</a>
                        <a href="car.php" class="nav-item nav-link">Car Listing</a>
                        <a href="booking.php" class="nav-item nav-link">Car Booking</a>
                        <a href="login.php" class="nav-item nav-link active">Login</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navigation Bar End -->

    <!-- Login Form Start -->
    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="display-4 text-uppercase text-center mb-5">Login</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="contact-form bg-light p-5">
                    <form action="" method="POST">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control p-4" 
                                   placeholder="Your Email" required="required">
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-control p-4" 
                                   placeholder="Your Password" required="required">
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-block py-3" type="submit">Login</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <a href="forgot-password.php" class="text-primary">Forgot Password?</a>
                    </div>
                    <div class="text-center mt-3">
                        <span>Don't have an account? </span>
                        <a href="register.php" class="text-primary">Register Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Login Form End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-secondary py-5 px-sm-3 px-md-5">
        <div class="row pt-5">
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Get In Touch</h4>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-white mr-3"></i>123 Street, New York, USA</p>
                <p class="mb-2"><i class="fa fa-phone-alt text-white mr-3"></i>+012 345 67890</p>
                <p><i class="fa fa-envelope text-white mr-3"></i>info@example.com</p>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Copyright Start -->
    <div class="container-fluid bg-dark py-4 px-sm-3 px-md-5">
        <p class="mb-2 text-center text-body">&copy; <a href="#">Royal Cars</a>. All Rights Reserved.</p>
    </div>
    <!-- Copyright End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>