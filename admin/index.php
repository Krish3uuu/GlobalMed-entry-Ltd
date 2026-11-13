<?php
/**
 * GlobalMed Entry - Admin Dashboard & Post Management
 * 
 * Provides full control over Articles, News, Events, Resources,
 * and which items appear on the Homepage.
 */
require_once __DIR__ . '/auth.php';
check_auth();

$db = get_db_connection();
$user = get_current_user_data();
$flash = get_flash();

// Filter parameters
$typeFilter = $_GET['type'] ?? 'all';
$homeFilter = $_GET['home'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

// Build query
$whereClauses = [];
$params = [];

if ($typeFilter !== 'all') {
    $whereClauses[] = "post_type = ?";
    $params[] = $typeFilter;
}

if ($homeFilter === '1') {
    $whereClauses[] = "show_on_home = 1";
} elseif ($homeFilter === '0') {
    $whereClauses[] = "show_on_home = 0";
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(title LIKE ? OR category LIKE ? OR excerpt LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Order by: homepage items first, then updated_at DESC
$postsSql = "SELECT * FROM posts {$whereSql} ORDER BY show_on_home DESC, home_order ASC, updated_at DESC";
$stmt = $db->prepare($postsSql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Metrics
$totalPosts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$homeArticlesCount = $db->query("SELECT COUNT(*) FROM posts WHERE show_on_home = 1 AND home_section = 'articles'")->fetchColumn();
$homeNewsCount = $db->query("SELECT COUNT(*) FROM posts WHERE show_on_home = 1 AND home_section = 'news'")->fetchColumn();
$draftsCount = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Post &amp; Homepage Manager | GlobalMed Entry Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700;800&family=Rubik:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#02326f',
                        'primary-dark': '#01214a',
                        accent: '#12bda9',
                        'accent-dark': '#0d9686'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen font-['Karla',sans-serif] text-slate-800 antialiased flex flex-col">

    <!-- Top Admin Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="index.php" class="flex items-center gap-3">
                    <img src="../images/logo.webp" alt="GlobalMed Entry" class="h-10 w-auto object-contain" />
                    <span class="hidden sm:inline-block font-extrabold text-slate-900 text-sm tracking-tight border-l border-slate-200 pl-3">
                        Editorial Manager
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <a href="../blog/index.php" target="_blank"
                    class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[5px] bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                    <span>View Public Blog</span>
                </a>
                <a href="../index.html#articles" target="_blank"
                    class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[5px] bg-blue-50 hover:bg-blue-100 text-primary text-xs font-semibold transition-colors border border-blue-200">
                    <span class="material-symbols-outlined text-[16px]">home</span>
                    <span>View Homepage</span>
                </a>
                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs uppercase">
                        <?= htmlspecialchars(substr($user['username'] ?? 'A', 0, 2)) ?>
                    </div>
                    <span class="text-xs font-bold text-slate-900 hidden sm:inline"><?= htmlspecialchars($user['username'] ?? 'Admin') ?></span>
                </div>
                <a href="logout.php"
                    class="p-2 rounded-[5px] text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                    title="Sign Out">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Flash Notice -->
        <?php if ($flash): ?>
            <div class="p-4 rounded-[5px] <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?> border text-sm font-medium flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] <?= $flash['type'] === 'success' ? 'text-emerald-600' : 'text-rose-600' ?>">
                        <?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?>
                    </span>
                    <span><?= htmlspecialchars($flash['message']) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Header Title & Action Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Articles, News &amp; Events</h1>
                <p class="text-slate-500 text-sm mt-0.5">Manage published content, edit full articles, and control what appears on the client homepage.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="edit.php"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-[5px] bg-[#12bda9] hover:bg-[#0d9686] text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Create New Post</span>
                </a>
            </div>
        </div>

        <!-- 4 Metrics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-[5px] bg-white border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Posts</span>
                    <span class="material-symbols-outlined text-[22px] text-blue-500">article</span>
                </div>
                <div class="text-2xl font-black text-slate-900"><?= $totalPosts ?></div>
                <span class="text-xs text-slate-400 mt-1 block">Articles, News, Events &amp; Resources</span>
            </div>

            <div class="p-5 rounded-[5px] bg-white border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Home Articles Grid</span>
                    <span class="material-symbols-outlined text-[22px] text-emerald-500">featured_video</span>
                </div>
                <div class="text-2xl font-black text-emerald-600"><?= $homeArticlesCount ?> <span class="text-xs font-semibold text-slate-400">/ 3 slots</span></div>
                <span class="text-xs text-slate-400 mt-1 block">Featured cards in navy section</span>
            </div>

            <div class="p-5 rounded-[5px] bg-white border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-primary">Home News Column</span>
                    <span class="material-symbols-outlined text-[22px] text-primary">newspaper</span>
                </div>
                <div class="text-2xl font-black text-primary"><?= $homeNewsCount ?> <span class="text-xs font-semibold text-slate-400">items active</span></div>
                <span class="text-xs text-slate-400 mt-1 block">Latest News &amp; Updates list</span>
            </div>

            <div class="p-5 rounded-[5px] bg-white border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Drafts</span>
                    <span class="material-symbols-outlined text-[22px] text-amber-500">edit_note</span>
                </div>
                <div class="text-2xl font-black text-amber-600"><?= $draftsCount ?></div>
                <span class="text-xs text-slate-400 mt-1 block">Unpublished internal drafts</span>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-[5px] border border-slate-200 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Filter Pills by Type -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
                <a href="index.php?type=all&home=<?= $homeFilter ?>"
                    class="px-3 py-1.5 rounded-[5px] transition-colors <?= $typeFilter === 'all' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    All Types
                </a>
                <a href="index.php?type=article&home=<?= $homeFilter ?>"
                    class="px-3 py-1.5 rounded-[5px] transition-colors <?= $typeFilter === 'article' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Articles
                </a>
                <a href="index.php?type=news&home=<?= $homeFilter ?>"
                    class="px-3 py-1.5 rounded-[5px] transition-colors <?= $typeFilter === 'news' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    News &amp; Updates
                </a>
                <a href="index.php?type=event&home=<?= $homeFilter ?>"
                    class="px-3 py-1.5 rounded-[5px] transition-colors <?= $typeFilter === 'event' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Events &amp; Webinars
                </a>
                <a href="index.php?type=resource&home=<?= $homeFilter ?>"
                    class="px-3 py-1.5 rounded-[5px] transition-colors <?= $typeFilter === 'resource' ? 'bg-primary text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Resources &amp; Packs
                </a>
            </div>

            <!-- Homepage Filter & Search Input -->
            <form method="GET" action="index.php" class="flex items-center gap-2">
                <input type="hidden" name="type" value="<?= htmlspecialchars($typeFilter) ?>" />
                
                <select name="home" onchange="this.form.submit()"
                    class="text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-1.5 pl-2.5 pr-8 text-slate-700">
                    <option value="all" <?= $homeFilter === 'all' ? 'selected' : '' ?>>All Locations</option>
                    <option value="1" <?= $homeFilter === '1' ? 'selected' : '' ?>>Only on Homepage (<?= $homeArticlesCount + $homeNewsCount ?>)</option>
                    <option value="0" <?= $homeFilter === '0' ? 'selected' : '' ?>>Not on Homepage</option>
                </select>

                <div class="relative">
                    <input type="text" name="q" placeholder="Search title or category..." value="<?= htmlspecialchars($searchQuery) ?>"
                        class="text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-1.5 pl-8 pr-3 text-slate-700 placeholder-slate-400 w-48 sm:w-56" />
                    <span class="material-symbols-outlined text-[16px] text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none">search</span>
                </div>
                <?php if (!empty($searchQuery) || $homeFilter !== 'all' || $typeFilter !== 'all'): ?>
                    <a href="index.php" class="p-1.5 text-slate-400 hover:text-slate-600" title="Reset Filters">
                        <span class="material-symbols-outlined text-[18px]">clear_all</span>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Posts Table Card -->
        <div class="bg-white rounded-[5px] border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Post Title &amp; Category</th>
                            <th class="py-3 px-3">Type</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Homepage Display</th>
                            <th class="py-3 px-3">Date</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">post_add</span>
                                    <p class="font-medium">No posts match the current filter.</p>
                                    <a href="edit.php" class="inline-block mt-2 text-primary font-bold hover:underline">Create a new post &rarr;</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $p): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors group">
                                    <!-- Title & Excerpt -->
                                    <td class="py-3.5 px-4 max-w-sm">
                                        <div class="flex items-start gap-3">
                                            <?php if (!empty($p['featured_image'])): ?>
                                                <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt=""
                                                    class="w-12 h-12 rounded-[3px] object-cover shrink-0 border border-slate-200" />
                                            <?php else: ?>
                                                <div class="w-12 h-12 rounded-[3px] bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center shrink-0">
                                                    <span class="material-symbols-outlined text-[20px]">image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <a href="edit.php?id=<?= $p['id'] ?>" class="font-bold text-slate-900 hover:text-primary transition-colors text-sm line-clamp-1">
                                                    <?= htmlspecialchars($p['title']) ?>
                                                </a>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="px-2 py-0.5 rounded-[3px] bg-slate-100 text-slate-600 font-semibold text-[10px]">
                                                        <?= htmlspecialchars($p['category']) ?>
                                                    </span>
                                                    <span class="text-slate-400 text-[11px] truncate max-w-[200px]">/<?= htmlspecialchars($p['slug']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Post Type -->
                                    <td class="py-3.5 px-3">
                                        <?php
                                        $typeStyles = [
                                            'article' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'news' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'event' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'resource' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        ];
                                        $style = $typeStyles[$p['post_type']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                        ?>
                                        <span class="inline-block px-2 py-0.5 rounded-[3px] border font-bold uppercase tracking-wider text-[10px] <?= $style ?>">
                                            <?= htmlspecialchars(strtoupper($p['post_type'])) ?>
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-3">
                                        <?php if ($p['status'] === 'published'): ?>
                                            <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                <span>Live</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-amber-600 font-bold">
                                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                                <span>Draft</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Homepage Management -->
                                    <td class="py-3.5 px-3">
                                        <?php if ($p['show_on_home'] == 1): ?>
                                            <div class="flex items-center gap-2">
                                                <a href="toggle-home.php?id=<?= $p['id'] ?>&csrf=<?= $csrf_token ?>"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold hover:bg-emerald-100 transition-colors"
                                                    title="Click to remove from homepage">
                                                    <span class="material-symbols-outlined text-[15px] text-emerald-600">check_circle</span>
                                                    <span><?= $p['home_section'] === 'articles' ? 'Articles Grid' : 'News Column' ?> (#<?= (int)$p['home_order'] ?>)</span>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <a href="toggle-home.php?id=<?= $p['id'] ?>&csrf=<?= $csrf_token ?>"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[4px] bg-slate-100 text-slate-500 hover:text-slate-700 hover:bg-slate-200 transition-colors text-[11px]"
                                                title="Click to show on homepage">
                                                <span class="material-symbols-outlined text-[14px]">add</span>
                                                <span>Add to Home</span>
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Date -->
                                    <td class="py-3.5 px-3 text-slate-500 text-[11px]">
                                        <?= date('d M Y', strtotime($p['created_at'])) ?>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="../blog/post.php?slug=<?= urlencode($p['slug']) ?>" target="_blank"
                                                class="p-1.5 text-slate-400 hover:text-primary rounded-[3px] hover:bg-slate-100 transition-colors" title="View Public Post">
                                                <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                            </a>
                                            <a href="edit.php?id=<?= $p['id'] ?>"
                                                class="p-1.5 text-slate-400 hover:text-primary rounded-[3px] hover:bg-slate-100 transition-colors" title="Edit Post">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </a>
                                            <button onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['title'])) ?>')"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 rounded-[3px] hover:bg-rose-50 transition-colors" title="Delete Post">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Client Management Guide Callout -->
        <div class="p-5 rounded-[5px] bg-blue-50/70 border border-blue-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary text-2xl shrink-0 mt-0.5">info</span>
                <div class="text-xs text-slate-700 space-y-1">
                    <p class="font-bold text-slate-900 text-sm">How Homepage Curation Works</p>
                    <p>To feature any article or news piece on the homepage, click <strong>"Add to Home"</strong> or check <em>"Display on Homepage"</em> inside the post editor. Choose whether it goes into the <strong>Articles Grid (top 3 featured cards)</strong> or the <strong>Latest News &amp; Updates list</strong> and set the display order.</p>
                </div>
            </div>
            <a href="edit.php" class="px-4 py-2 rounded-[5px] bg-primary text-white font-bold text-xs shrink-0 hover:bg-primary-dark transition-colors">
                Publish New Article
            </a>
        </div>

    </main>

    <!-- Delete Confirmation Modal Form -->
    <form id="deleteForm" method="POST" action="delete.php" style="display:none;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" />
        <input type="hidden" name="id" id="deleteId" value="" />
    </form>

    <script>
        function confirmDelete(id, title) {
            if (confirm("Are you sure you want to permanently delete '" + title + "'?\n\nThis action cannot be undone.")) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
