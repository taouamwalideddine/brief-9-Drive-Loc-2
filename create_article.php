<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Session user_id: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
var_dump($_SESSION);

require_once "config/Database.php";
require_once "models/Article.php";
require_once "models/Theme.php";
require_once "models/Tag.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$article = new Article($db);
$theme = new Theme($db);
$tag = new Tag($db);

$themes = $theme->read();
$tags = $tag->read();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article->user_id = $_SESSION['user_id'];
    $article->theme_id = $_POST['theme_id'];
    $article->title = $_POST['title'];
    $article->content = $_POST['content'];

    $article_id = $article->create();

    if ($article_id) {
        // Add tags to the article
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag_id) {
                $article->addTag($article_id, $tag_id);
            }
        }
        $success_message = "Your article has been submitted for approval.";
        header("Location: article.php?id=" . $article_id);
        exit();
    } else {
        $error_message = "Failed to create article. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Article - Drive & Loc Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#191C24] text-gray-200">
    <header class="bg-[#1C1E2D] text-white p-4 border-b border-gray-700">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold text-[#FD5D14]">Create Article</h1>
        </div>
    </header>

    <main class="container mx-auto mt-8 px-4">
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>
        <form action="" method="POST" class="max-w-3xl mx-auto">
            <div class="bg-[#1C1E2D] rounded-lg border border-gray-700 overflow-hidden">
                <div class="p-6 space-y-6">
                    <div>
                        <label for="title" class="block text-gray-200 font-semibold mb-2">Title</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               required 
                               class="w-full px-4 py-3 bg-[#2A2D3E] border border-gray-700 rounded-lg text-gray-200 focus:border-[#FD5D14] focus:ring-1 focus:ring-[#FD5D14] transition-colors"
                               placeholder="Enter article title">
                    </div>

                    <div>
                        <label for="theme_id" class="block text-gray-200 font-semibold mb-2">Theme</label>
                        <select id="theme_id" 
                                name="theme_id" 
                                required 
                                class="w-full px-4 py-3 bg-[#2A2D3E] border border-gray-700 rounded-lg text-gray-200 focus:border-[#FD5D14] focus:ring-1 focus:ring-[#FD5D14] transition-colors">
                            <option value="">Select a theme</option>
                            <?php while ($row = $themes->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label for="content" class="block text-gray-200 font-semibold mb-2">Content</label>
                        <textarea id="content" 
                                  name="content" 
                                  required 
                                  class="w-full px-4 py-3 bg-[#2A2D3E] border border-gray-700 rounded-lg text-gray-200 focus:border-[#FD5D14] focus:ring-1 focus:ring-[#FD5D14] transition-colors" 
                                  rows="12"
                                  placeholder="Write your article content here"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-200 font-semibold mb-4">Tags</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php while ($row = $tags->fetch(PDO::FETCH_ASSOC)): ?>
                                <label class="flex items-center space-x-3 p-3 bg-[#2A2D3E] rounded-lg border border-gray-700 cursor-pointer hover:border-[#FD5D14] transition-colors">
                                    <input type="checkbox" 
                                           name="tags[]" 
                                           value="<?php echo $row['id']; ?>" 
                                           class="form-checkbox h-5 w-5 text-[#FD5D14] border-gray-700 rounded focus:ring-[#FD5D14]">
                                    <span class="text-gray-300"><?php echo htmlspecialchars($row['name']); ?></span>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-[#2A2D3E] border-t border-gray-700 flex justify-end space-x-4">
                    <a href="blog.php" 
                       class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-[#FD5D14] text-white rounded-lg hover:bg-[#E54D14] transition-colors">
                        Submit for Approval
                    </button>
                </div>
            </div>
        </form>
    </main>

    <footer class="bg-[#1C1E2D] text-gray-400 py-8 mt-16 border-t border-gray-700">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Drive & Loc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

