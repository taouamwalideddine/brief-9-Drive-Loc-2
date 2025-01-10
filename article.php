<?php
session_start();
require_once "config/Database.php";
require_once "models/Article.php";
require_once "models/Comment.php";
require_once "models/Theme.php";
require_once "models/Favorite.php";

$database = new Database();
$db = $database->getConnection();

$article = new Article($db);
$comment = new Comment($db);
$theme = new Theme($db);
$favorite = new Favorite($db);

$article_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

$article->id = $article_id;
$article_details = $article->readOne();

if (!$article_details) {
    die('ERROR: Article not found.');
}

$favorites_count = $article->getFavoritesCount();

$comments = $comment->read($article_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_comment']) && isset($_SESSION['user_id'])) {
        $comment->user_id = $_SESSION['user_id'];
        $comment->article_id = $article_id;
        $comment->content = $_POST['comment'];
        
        if ($comment->create()) {
            header("Location: article.php?id=" . $article_id);
            exit();
        } else {
            $error = "Failed to submit comment.";
        }
    } elseif (isset($_POST['delete_comment']) && isset($_SESSION['user_id'])) {
        $comment->id = $_POST['comment_id'];
        $comment->user_id = $_SESSION['user_id'];
        
        if ($comment->delete()) {
            header("Location: article.php?id=" . $article_id);
            exit();
        } else {
            $error = "Failed to delete comment.";
        }
    } elseif (isset($_POST['delete_article']) && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article_details['user_id']) {
        if ($article->delete()) {
            header("Location: blog.php");
            exit();
        } else {
            $error = "Failed to delete article.";
        }
    } elseif (!isset($_SESSION['user_id'])) {
        $error = "You must be logged in to perform this action.";
    }
}

if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $favorite->user_id = $user_id;
    $favorite->article_id = $article_id;

    if ($favorite->exists()) {
        $favorite->remove();
    } else {
        $favorite->add();
    }
    header("Location: article.php?id=" . $article_id);
    exit();
}

$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $favorite->user_id = $_SESSION['user_id'];
    $favorite->article_id = $article_id;
    $is_favorite = $favorite->exists();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article_details['title']); ?> - Drive & Loc Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#191C24] text-gray-200">
    <header class="bg-[#1C1E2D] text-white p-4 border-b border-gray-700">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold text-[#FD5D14]">Drive & Loc Blog</h1>
        </div>
    </header>

    <main class="container mx-auto mt-8">
        <article class="bg-[#1C1E2D] rounded-lg border border-gray-700 overflow-hidden mb-8">
            <div class="p-8">
                <div class="flex justify-between items-start mb-6">
                    <h1 class="text-3xl font-bold text-gray-100"><?php echo htmlspecialchars($article_details['title']); ?></h1>
                    <span class="bg-[#2A2D3E] text-sm text-gray-400 px-3 py-1 rounded-full">
                        <?php echo htmlspecialchars($article_details['theme']); ?>
                    </span>
                </div>
                <p class="text-gray-400 mb-6">By <?php echo htmlspecialchars($article_details['author']); ?></p>
                <div class="prose max-w-none text-gray-300 mb-6">
                    <?php echo nl2br(htmlspecialchars($article_details['content'])); ?>
                </div>
                
                <div class="flex items-center justify-between border-t border-gray-700 pt-6">
                    <div class="flex items-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FD5D14]" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="ml-2"><?php echo $favorites_count; ?> favorites</span>
                    </div>
                    
                    <div class="flex space-x-4">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST">
                                <button type="submit" name="toggle_favorite" 
                                        class="bg-[#2A2D3E] text-gray-200 px-4 py-2 rounded-lg hover:bg-[#FD5D14] transition-colors">
                                    <?php echo $is_favorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article_details['user_id']): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this article? This action cannot be undone.');">
                                <button type="submit" name="delete_article" 
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                    Delete Article
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>

        <section class="bg-[#1C1E2D] rounded-lg border border-gray-700 p-8">
            <h2 class="text-2xl font-semibold text-[#FD5D14] mb-6">Comments</h2>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-900/50 text-red-200 px-4 py-3 rounded-lg mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="" method="POST" class="mb-8">
                    <textarea name="comment" rows="4" 
                            class="w-full bg-[#2A2D3E] text-gray-200 border border-gray-700 rounded-lg p-4 focus:border-[#FD5D14] focus:ring-1 focus:ring-[#FD5D14] transition-colors" 
                            placeholder="Add a comment..." required></textarea>
                    <button type="submit" name="submit_comment" 
                            class="mt-2 bg-[#FD5D14] text-white px-6 py-2 rounded-lg hover:bg-[#E54D14] transition-colors">
                        Submit Comment
                    </button>
                </form>
            <?php else: ?>
                <p class="mb-8 text-gray-400">Please <a href="login.php" class="text-[#FD5D14] hover:text-[#E54D14] transition-colors">log in</a> to leave a comment.</p>
            <?php endif; ?>

            <div class="space-y-6">
                <?php while ($row = $comments->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="bg-[#2A2D3E] rounded-lg p-6">
                        <div class="flex justify-between items-start mb-2">
                            <p class="font-semibold text-gray-200"><?php echo htmlspecialchars($row['user_name']); ?></p>
                            <p class="text-sm text-gray-400"><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></p>
                        </div>
                        <p class="text-gray-300 mb-4"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                            <div class="flex gap-4">
                                <form action="" method="POST" class="inline">
                                    <input type="hidden" name="comment_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_comment" 
                                            class="text-red-400 hover:text-red-300 transition-colors"
                                            onclick="return confirm('Are you sure you want to delete this comment?');">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

    <footer class="bg-[#1C1E2D] text-gray-400 py-8 mt-16 border-t border-gray-700">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Drive & Loc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

