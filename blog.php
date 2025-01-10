<?php
session_start();
require_once "config/Database.php";
require_once "models/Theme.php";
require_once "models/Article.php";
require_once "models/Tag.php";

$database = new Database();
$db = $database->getConnection();

$theme = new Theme($db);
$article = new Article($db);
$tag = new Tag($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$selected_theme = isset($_GET['theme']) ? $_GET['theme'] : null;

$themes = $theme->read();
$articles = $article->read($page, $per_page, $selected_theme);
$tags = $tag->read();

// Get total pages
$stmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE status = 'published'" . ($selected_theme ? " AND theme_id = ?" : ""));
if ($selected_theme) {
    $stmt->execute([$selected_theme]);
} else {
    $stmt->execute();
}
$total_articles = $stmt->fetchColumn();
$total_pages = ceil($total_articles / $per_page);

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    // If it's an AJAX request, only return the articles HTML
    ob_start();
    while ($row = $articles->fetch(PDO::FETCH_ASSOC)):
?>
        <article class="bg-[#1C1E2D] rounded-lg border border-gray-700 overflow-hidden hover:border-[#FD5D14] transition-colors mb-6">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-semibold">
                        <a href="article.php?id=<?php echo $row['id']; ?>" class="text-gray-100 hover:text-[#FD5D14] transition-colors">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </a>
                    </h3>
                    <span class="bg-[#2A2D3E] text-sm text-gray-400 px-3 py-1 rounded-full">
                        <?php echo htmlspecialchars($row['theme']); ?>
                    </span>
                </div>
                <p class="text-gray-400 mb-4">By <?php echo htmlspecialchars($row['author']); ?></p>
                <p class="text-gray-300 mb-4"><?php echo substr(htmlspecialchars($row['content']), 0, 200) . '...'; ?></p>
                
                <?php
                $article_tags = $article->getArticleTags($row['id']);
                if (!empty($article_tags)):
                ?>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php foreach ($article_tags as $tag): ?>
                            <span class="bg-[#2A2D3E] px-3 py-1 rounded-full text-sm text-gray-300">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <a href="article.php?id=<?php echo $row['id']; ?>" class="inline-block text-[#FD5D14] hover:text-[#E54D14] transition-colors">
                    Read more →
                </a>
            </div>
        </article>
<?php
    endwhile;
    $articlesHtml = ob_get_clean();
    
    echo json_encode([
        'articles' => $articlesHtml,
        'currentPage' => $page,
        'totalPages' => $total_pages
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drive & Loc Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#191C24] text-gray-200">
    <header class="bg-[#1C1E2D] text-white p-4 border-b border-gray-700">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold text-[#FD5D14]">Drive & Loc Blog</h1>
        </div>
    </header>

    <main class="container mx-auto mt-8">
        <div class="flex flex-col md:flex-row gap-8">
            <aside class="w-full md:w-1/4">
                <div class="bg-[#1C1E2D] p-6 rounded-lg border border-gray-700 mb-6">
                    <h2 class="text-xl font-semibold mb-4 text-[#FD5D14]">Themes</h2>
                    <ul class="space-y-2">
                        <li>
                            <a href="?theme=" class="text-gray-300 hover:text-[#FD5D14] transition-colors">All Themes</a>
                        </li>
                        <?php while ($row = $themes->fetch(PDO::FETCH_ASSOC)): ?>
                            <li>
                                <a href="?theme=<?php echo $row['id']; ?>" class="text-gray-300 hover:text-[#FD5D14] transition-colors">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="bg-[#1C1E2D] p-6 rounded-lg border border-gray-700">
                    <h2 class="text-xl font-semibold mb-4 text-[#FD5D14]">Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php while ($row = $tags->fetch(PDO::FETCH_ASSOC)): ?>
                            <span class="bg-[#2A2D3E] px-3 py-1 rounded-full text-sm text-gray-300">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </span>
                        <?php endwhile; ?>
                    </div>
                </div>
            </aside>

            <section class="w-full md:w-3/4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-[#FD5D14]">Latest Articles</h2>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="create_article.php" class="bg-[#FD5D14] text-white px-4 py-2 rounded-lg hover:bg-[#E54D14] transition-colors">Create New Article</a>
                    <?php endif; ?>
                </div>

                <div id="articles-container" class="grid gap-6">
                    <?php while ($row = $articles->fetch(PDO::FETCH_ASSOC)): ?>
                        <article class="bg-[#1C1E2D] rounded-lg border border-gray-700 overflow-hidden hover:border-[#FD5D14] transition-colors">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-xl font-semibold">
                                        <a href="article.php?id=<?php echo $row['id']; ?>" class="text-gray-100 hover:text-[#FD5D14] transition-colors">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </a>
                                    </h3>
                                    <span class="bg-[#2A2D3E] text-sm text-gray-400 px-3 py-1 rounded-full">
                                        <?php echo htmlspecialchars($row['theme']); ?>
                                    </span>
                                </div>
                                <p class="text-gray-400 mb-4">By <?php echo htmlspecialchars($row['author']); ?></p>
                                <p class="text-gray-300 mb-4"><?php echo substr(htmlspecialchars($row['content']), 0, 200) . '...'; ?></p>
                                
                                <?php
                                $article_tags = $article->getArticleTags($row['id']);
                                if (!empty($article_tags)):
                                ?>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <?php foreach ($article_tags as $tag): ?>
                                            <span class="bg-[#2A2D3E] px-3 py-1 rounded-full text-sm text-gray-300">
                                                <?php echo htmlspecialchars($tag['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="article.php?id=<?php echo $row['id']; ?>" class="inline-block text-[#FD5D14] hover:text-[#E54D14] transition-colors">
                                    Read more →
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <div class="mt-8 flex items-center justify-between bg-[#1C1E2D] p-4 rounded-lg border border-gray-700">
                    <form action="" method="get" class="flex items-center gap-4">
                        <label for="per_page" class="text-gray-300">Articles per page:</label>
                        <select name="per_page" id="per_page" class="bg-[#2A2D3E] text-gray-300 border border-gray-700 rounded px-2 py-1" onchange="this.form.submit()">
                            <option value="5" <?php echo $per_page == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="15" <?php echo $per_page == 15 ? 'selected' : ''; ?>>15</option>
                        </select>
                        <?php if ($selected_theme): ?>
                            <input type="hidden" name="theme" value="<?php echo $selected_theme; ?>">
                        <?php endif; ?>
                    </form>

                    <div class="flex gap-2">
                        <button id="prev-page" class="bg-[#FD5D14] text-white px-4 py-2 rounded hover:bg-[#E54D14] transition-colors <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                            Previous
                        </button>
                        <button id="next-page" class="bg-[#FD5D14] text-white px-4 py-2 rounded hover:bg-[#E54D14] transition-colors <?php echo $page >= $total_pages ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>
                            Next
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="bg-[#1C1E2D] text-gray-400 py-8 mt-16 border-t border-gray-700">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Drive & Loc. All rights reserved.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = <?php echo $page; ?>;
        const perPage = <?php echo $per_page; ?>;
        const selectedTheme = <?php echo $selected_theme ? $selected_theme : 'null'; ?>;
        const totalPages = <?php echo $total_pages; ?>;

        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        const articlesContainer = document.getElementById('articles-container');

        function updateButtonStates() {
            prevButton.disabled = currentPage <= 1;
            prevButton.classList.toggle('opacity-50', currentPage <= 1);
            prevButton.classList.toggle('cursor-not-allowed', currentPage <= 1);

            nextButton.disabled = currentPage >= totalPages;
            nextButton.classList.toggle('opacity-50', currentPage >= totalPages);
            nextButton.classList.toggle('cursor-not-allowed', currentPage >= totalPages);
        }

        function loadArticles(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                articlesContainer.innerHTML = data.articles;
                currentPage = data.currentPage;
                updateButtonStates();
            })
            .catch(error => console.error('Error:', error));
        }

        prevButton.addEventListener('click', function() {
            if (currentPage > 1) {
                loadArticles(currentPage - 1);
            }
        });

        nextButton.addEventListener('click', function() {
            if (currentPage < totalPages) {
                loadArticles(currentPage + 1);
            }
        });

        // Initial button state update
        updateButtonStates();
    });
    </script>
</body>
</html>

