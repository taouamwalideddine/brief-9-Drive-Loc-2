<?php
session_start();
require_once "config/Database.php";
require_once "models/Theme.php";
require_once "models/Article.php";
require_once "models/comment.php";
$database = new Database();
$db = $database->getConnection();

$theme = new Theme($db);
$article = new Article($db);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 10;

$themes = $theme->read();
$articles = $article->read($page, $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drive & Loc Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

<!-- Template Stylesheet -->
<link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Topbar Start -->
    <div class="container-fluid bg-dark py-3 px-lg-5 d-none d-lg-block">
        <div class="row">
            <div class="col-md-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body pr-3" href=""><i class="fa fa-phone-alt mr-2"></i>+012 345 6789</a>
                    <span class="text-body">|</span>
                    <a class="text-body px-3" href=""><i class="fa fa-envelope mr-2"></i>info@example.com</a>
                </div>
            </div>
            <div class="col-md-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body px-3" href="">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="text-body px-3" href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a class="text-body px-3" href="">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a class="text-body px-3" href="">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="text-body pl-3" href="">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar Start -->
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
                        <a href="index.html" class="nav-item nav-link active">Home</a>

                                <a href="car.html" class="nav-item nav-link ">Car Listing</a>
                                <a href="detail.html" class="nav-item nav-link ">Car Detail</a>
                                <a href="booking.html" class="nav-item nav-link ">Car Booking</a>
                           
                        <a href="login.html" class="nav-item nav-link">login</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navbar End -->

    <main class="container mx-auto mt-8">
        <div class="flex">
            <aside class="w-1/4 pr-8">
                <h2 class="text-xl font-semibold mb-4">Themes</h2>
                <ul>
                    <?php while ($row = $themes->fetch(PDO::FETCH_ASSOC)): ?>
                        <li class="mb-2">
                            <a href="?theme=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </aside>

            <section class="w-3/4">
                <h2 class="text-2xl font-semibold mb-6">Latest Articles</h2>
                <?php while ($row = $articles->fetch(PDO::FETCH_ASSOC)): ?>
                    <article class="bg-white p-6 rounded-lg shadow-md mb-6">
                        <h3 class="text-xl font-semibold mb-2">
                            <a href="article.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-2">By <?php echo htmlspecialchars($row['author']); ?> in <?php echo htmlspecialchars($row['theme']); ?></p>
                        <p class="text-gray-700"><?php echo substr(htmlspecialchars($row['content']), 0, 200) . '...'; ?></p>
                        <a href="article.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">Read more</a>
                    </article>
                <?php endwhile; ?>

                <div class="mt-8">
                    <form action="" method="get" class="flex items-center">
                        <label for="per_page" class="mr-2">Articles per page:</label>
                        <select name="per_page" id="per_page" class="border rounded px-2 py-1" onchange="this.form.submit()">
                            <option value="5" <?php echo $per_page == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="15" <?php echo $per_page == 15 ? 'selected' : ''; ?>>15</option>
                        </select>
                    </form>
                </div>

                <div class="mt-4 flex justify-between">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $per_page; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Previous</a>
                    <?php endif; ?>
                    
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) FROM articles WHERE status = 'published'");
                    $total_articles = $stmt->fetchColumn();
                    $total_pages = ceil($total_articles / $per_page);
                    if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $per_page; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Next</a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <div class="container-fluid bg-secondary py-5 px-sm-3 px-md-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Get In Touch</h4>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-white mr-3"></i>123 Street, New York, USA</p>
                <p class="mb-2"><i class="fa fa-phone-alt text-white mr-3"></i>+012 345 67890</p>
                <p><i class="fa fa-envelope text-white mr-3"></i>info@example.com</p>
                <h6 class="text-uppercase text-white py-2">Follow Us</h6>
                <div class="d-flex justify-content-start">
                    <a class="btn btn-lg btn-dark btn-lg-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-lg btn-dark btn-lg-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-lg btn-dark btn-lg-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a class="btn btn-lg btn-dark btn-lg-square" href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Usefull Links</h4>
                <div class="d-flex flex-column justify-content-start">
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Private Policy</a>
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Term & Conditions</a>
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>New Member Registration</a>
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Affiliate Programme</a>
                    <a class="text-body mb-2" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Return & Refund</a>
                    <a class="text-body" href="#"><i class="fa fa-angle-right text-white mr-2"></i>Help & FQAs</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Car Gallery</h4>
                <div class="row mx-n1">
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="img/gallery-1.jpg" alt=""></a>
                    </div>
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="img/gallery-2.jpg" alt=""></a>
                    </div>
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="img/gallery-3.jpg" alt=""></a>
                    </div>
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="img/gallery-4.jpg" alt=""></a>
                    </div>
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="img/gallery-5.jpg" alt=""></a>
                    </div>
                    <div class="col-4 px-1 mb-2">
                        <a href=""><img class="w-100" src="img/gallery-6.jpg" alt=""></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

