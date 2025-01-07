<?php
session_start();
require_once "config/Database.php";
require_once "models/Article.php";
require_once "models/Comment.php";
require_once "models/Theme.php";

$database = new Database();
$db = $database->getConnection();

$article = new Article($db);
$comment = new Comment($db);
$theme = new Theme($db);

$article_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Fetch article details
$article->id = $article_id;
$article_details = $article->readOne();

// Fetch comments for this article
$comments = $comment->read($article_id);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['user_id'])) {
        $comment->user_id = $_SESSION['user_id'];
        $comment->article_id = $article_id;
        $comment->content = $_POST['comment'];
        
        if ($comment->create()) {
            // Refresh the page to show the new comment
            header("Location: article.php?id=" . $article_id);
            exit();
        }
    } else {
        $error = "You must be logged in to comment.";
    }
}

// Handle adding/removing from favorites
if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $favorite = new Favorite($db);
    $favorite->user_id = $user_id;
    $favorite->article_id = $article_id;

    if ($favorite->exists()) {
        $favorite->remove();
    } else {
        $favorite->add();
    }
    // Refresh the page
    header("Location: article.php?id=" . $article_id);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article_details['title']; ?> - Drive & Loc Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold">Drive & Loc Blog</h1>
        </div>
    </header>

    <main class="container mx-auto mt-8">
        <article class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($article_details['title']); ?></h1>
            <p class="text-gray-600 mb-4">By <?php echo htmlspecialchars($article_details['author']); ?> in <?php echo htmlspecialchars($article_details['theme']); ?></p>
            <div class="prose max-w-none">
                <?php echo nl2br(htmlspecialchars($article_details['content'])); ?>
            </div>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="mt-4">
                    <button type="submit" name="toggle_favorite" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        <?php echo $favorite->exists() ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                    </button>
                </form>
            <?php endif; ?>
        </article>

        <section class="mt-8">
            <h2 class="text-2xl font-semibold mb-4">Comments</h2>
            <?php if (isset($error)): ?>
                <p class="text-red-500"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="" method="POST" class="mb-6">
                    <textarea name="comment" rows="4" class="w-full p-2 border rounded" placeholder="Add a comment..." required></textarea>
                    <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Submit Comment</button>
                </form>
            <?php else: ?>
                <p class="mb-4">Please <a href="login.php" class="text-blue-600 hover:underline">log in</a> to leave a comment.</p>
            <?php endif; ?>

            <?php while ($row = $comments->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="bg-white p-4 rounded-lg shadow-md mb-4">
                    <p class="font-semibold"><?php echo htmlspecialchars($row['user_name']); ?></p>
                    <p class="text-gray-600 text-sm mb-2"><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                        <div class="mt-2">
                            <a href="edit_comment.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline mr-4">Edit</a>
                            <a href="delete_comment.php?id=<?php echo $row['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Drive & Loc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

