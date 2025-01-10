<?php
session_start();
require_once "../config/Database.php";
require_once "../models/Theme.php";
require_once "../models/Tag.php";
require_once "../models/User.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$theme = new Theme($db);
$tag = new Tag($db);

$themes = $theme->read();
$tags = $tag->read();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_theme'])) {
            $theme->name = $_POST['theme_name'];
            if (!$theme->create()) {
                throw new Exception("Failed to create theme");
            }
        } elseif (isset($_POST['add_tag'])) {
            $tag->name = $_POST['tag_name'];
            if (!$tag->create()) {
                throw new Exception("Failed to create tag");
            }
        } elseif (isset($_POST['delete_theme'])) {
            $theme->id = $_POST['theme_id'];
            if (!$theme->delete()) {
                throw new Exception("Failed to delete theme");
            }
        } elseif (isset($_POST['delete_tag'])) {
            $tag->id = $_POST['tag_id'];
            if (!$tag->delete()) {
                throw new Exception("Failed to delete tag");
            }
        }

        // Refresh the page to show updated data
        header("Location: dashboard.php");
        exit();
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Drive & Loc Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#191C24] text-gray-200">
    <header class="bg-[#1C1E2D] text-white p-4 border-b border-gray-700">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold text-[#FD5D14]">Admin Dashboard</h1>
        </div>
    </header>

    <main class="container mx-auto mt-8 px-4">
        <?php if (isset($error)): ?>
            <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <section class="bg-[#1C1E2D] rounded-lg border border-gray-700 p-6">
                <h2 class="text-2xl font-semibold text-[#FD5D14] mb-6">Manage Themes</h2>
                <form action="" method="POST" class="mb-6">
                    <div class="flex">
                        <input type="text" name="theme_name" placeholder="New Theme Name" required 
                               class="flex-grow bg-[#2A2D3E] text-gray-200 border border-gray-700 rounded-l-lg px-4 py-2 focus:outline-none focus:border-[#FD5D14]">
                        <button type="submit" name="add_theme" 
                                class="bg-[#FD5D14] text-white px-4 py-2 rounded-r-lg hover:bg-[#E54D14] transition-colors">
                            Add Theme
                        </button>
                    </div>
                </form>
                <ul class="space-y-4">
                    <?php while ($row = $themes->fetch(PDO::FETCH_ASSOC)): ?>
                        <li class="flex justify-between items-center bg-[#2A2D3E] rounded-lg p-4">
                            <span class="text-gray-300"><?php echo htmlspecialchars($row['name']); ?></span>
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="theme_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_theme" 
                                        class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700 transition-colors"
                                        onclick="return confirm('Are you sure you want to delete this theme?');">
                                    Delete
                                </button>
                            </form>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </section>

            <section class="bg-[#1C1E2D] rounded-lg border border-gray-700 p-6">
                <h2 class="text-2xl font-semibold text-[#FD5D14] mb-6">Manage Tags</h2>
                <form action="" method="POST" class="mb-6">
                    <div class="flex">
                        <input type="text" name="tag_name" placeholder="New Tag Name" required 
                               class="flex-grow bg-[#2A2D3E] text-gray-200 border border-gray-700 rounded-l-lg px-4 py-2 focus:outline-none focus:border-[#FD5D14]">
                        <button type="submit" name="add_tag" 
                                class="bg-[#FD5D14] text-white px-4 py-2 rounded-r-lg hover:bg-[#E54D14] transition-colors">
                            Add Tag
                        </button>
                    </div>
                </form>
                <ul class="space-y-4">
                    <?php while ($row = $tags->fetch(PDO::FETCH_ASSOC)): ?>
                        <li class="flex justify-between items-center bg-[#2A2D3E] rounded-lg p-4">
                            <span class="text-gray-300"><?php echo htmlspecialchars($row['name']); ?></span>
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="tag_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_tag" 
                                        class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700 transition-colors"
                                        onclick="return confirm('Are you sure you want to delete this tag?');">
                                    Delete
                                </button>
                            </form>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </section>
        </div>
    </main>

    <footer class="bg-[#1C1E2D] text-gray-400 py-8 mt-16 border-t border-gray-700">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Drive & Loc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

