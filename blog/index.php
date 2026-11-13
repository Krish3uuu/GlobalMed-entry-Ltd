<?php
/**
 * GlobalMed Entry - Public Blog & Knowledge Base Listing
 * 
 * Displays Articles, News, Events, and Resources.
 * Clean, modern layout matching the GlobalMed Entry design language.
 */
require_once __DIR__ . '/db.php';

$db = get_db_connection();

$type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$q = trim($_GET['q'] ?? '');

$where = ["status = 'published'"];
$params = [];

if ($type !== 'all' && in_array($type, ['article', 'news', 'event', 'resource'])) {
    $where[] = "post_type = ?";
    $params[] = $type;
}

if ($category !== 'all') {
    $where[] = "category = ?";
    $params[] = $category;
}

if (!empty($q)) {
    $where[] = "(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $term = "%{$q}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$whereSql = "WHERE " . implode(" AND ", $where);
$stmt = $db->prepare("SELECT * FROM posts {$whereSql} ORDER BY created_at DESC");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Counts for filter pills
$countAll = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$countArticles = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published' AND post_type = 'article'")->fetchColumn();
$countNews = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published' AND post_type = 'news'")->fetchColumn();
$countEvents = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published' AND post_type = 'event'")->fetchColumn();
$countResources = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published' AND post_type = 'resource'")->fetchColumn();

// Hero featured post (first article)
$featuredPost = null;
if ($type === 'all' && empty($q) && !empty($posts)) {
    $featuredPost = $posts[0];
    $remainingPosts = array_slice($posts, 1);
} else {
    $remainingPosts = $posts;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Medical Education Blog, News &amp; Resources | GlobalMed Entry Ltd</title>
    <meta name="description" content="Official articles, entrance examination strategies, regulatory guidelines, and news for UK and international students studying medicine in Europe." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Karla:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,700&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css" />
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
<body class="bg-white text-slate-800 font-['Karla',sans-serif] antialiased min-h-screen flex flex-col">

    <!-- Global Dynamic Header Anchor -->
    <header id="site-header"></header>
    <div id="site-mobile-drawer"></div>

    <main class="w-full pt-[96px] sm:pt-[108px] lg:pt-[130px] flex-1">

        <!-- 1. HERO SECTION -->
        <section class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#172554] text-white py-14 sm:py-20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-[#12bda9]/15 blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 relative z-10 text-center space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-[5px] bg-blue-500/10 border border-blue-400/20 text-blue-200 text-xs font-semibold uppercase tracking-wider">
                    <span class="material-symbols-outlined text-[15px] text-[#12bda9]">menu_book</span>
                    Knowledge Base &amp; Editorial Directorate
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    European Medical Insights &amp; Updates
                </h1>
                <p class="text-sm sm:text-base md:text-lg text-blue-100/90 max-w-2xl mx-auto leading-relaxed">
                    Authoritative guidance on entrance examinations, GMC &amp; UKMLA registration frameworks, university life, and tuition breakdowns for European medical studies.
                </p>

                <!-- Search Bar inside Hero -->
                <div class="pt-4 max-w-xl mx-auto">
                    <form method="GET" action="index.php" class="relative flex items-center">
                        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>" />
                        <span class="absolute left-4 text-slate-400 pointer-events-none flex items-center">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                            placeholder="Search articles by keyword, university, or exam..."
                            class="w-full pl-11 pr-28 py-3.5 rounded-[5px] bg-white/95 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#12bda9] shadow-lg border-0" />
                        <button type="submit"
                            class="absolute right-1.5 top-1.5 bottom-1.5 px-4 rounded-[4px] bg-primary hover:bg-primary-dark text-white font-bold text-xs uppercase tracking-wider transition-colors">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- 2. FILTER TABS & MAIN LISTING -->
        <section class="py-10 sm:py-14 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 space-y-10">

                <!-- Filter Pills -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-200">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="index.php?type=all<?= !empty($q) ? '&q=' . urlencode($q) : '' ?>"
                            class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs <?= $type === 'all' ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-primary' ?>">
                            All Publications (<?= $countAll ?>)
                        </a>
                        <a href="index.php?type=article<?= !empty($q) ? '&q=' . urlencode($q) : '' ?>"
                            class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs <?= $type === 'article' ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-primary' ?>">
                            Articles (<?= $countArticles ?>)
                        </a>
                        <a href="index.php?type=news<?= !empty($q) ? '&q=' . urlencode($q) : '' ?>"
                            class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs <?= $type === 'news' ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-primary' ?>">
                            News &amp; Updates (<?= $countNews ?>)
                        </a>
                        <a href="index.php?type=event<?= !empty($q) ? '&q=' . urlencode($q) : '' ?>"
                            class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs <?= $type === 'event' ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-primary' ?>">
                            Events &amp; Webinars (<?= $countEvents ?>)
                        </a>
                        <a href="index.php?type=resource<?= !empty($q) ? '&q=' . urlencode($q) : '' ?>"
                            class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs <?= $type === 'resource' ? 'bg-primary text-white' : 'bg-white border border-slate-200 text-slate-700 hover:border-primary' ?>">
                            Resources &amp; Packs (<?= $countResources ?>)
                        </a>
                    </div>

                    <?php if (!empty($q)): ?>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span>Showing results for: <strong class="text-slate-900">"<?= htmlspecialchars($q) ?>"</strong></span>
                            <a href="index.php" class="text-primary hover:underline font-bold">Clear</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Featured Post Card (If on All tab and no search) -->
                <?php if ($featuredPost): ?>
                    <div class="bg-white rounded-[5px] border border-slate-200 overflow-hidden shadow-executive group hover:shadow-xl transition-all duration-300">
                        <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch">
                            <div class="lg:col-span-7 relative aspect-video lg:aspect-auto overflow-hidden">
                                <img src="<?= htmlspecialchars($featuredPost['featured_image']) ?>" alt="<?= htmlspecialchars($featuredPost['title']) ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <span class="absolute top-4 left-4 bg-primary text-white font-bold text-xs uppercase tracking-wider px-3 py-1 rounded-[3px] shadow-md">
                                    Featured Editorial
                                </span>
                            </div>
                            <div class="lg:col-span-5 p-7 sm:p-9 flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span class="font-bold text-primary uppercase tracking-wider"><?= htmlspecialchars($featuredPost['category']) ?></span>
                                        <span>&bull;</span>
                                        <span><?= htmlspecialchars($featuredPost['read_time']) ?></span>
                                        <span>&bull;</span>
                                        <span><?= date('d M Y', strtotime($featuredPost['created_at'])) ?></span>
                                    </div>
                                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-950 group-hover:text-primary transition-colors leading-tight">
                                        <a href="post.php?slug=<?= urlencode($featuredPost['slug']) ?>">
                                            <?= htmlspecialchars($featuredPost['title']) ?>
                                        </a>
                                    </h2>
                                    <p class="text-sm text-slate-600 leading-relaxed">
                                        <?= htmlspecialchars($featuredPost['excerpt']) ?>
                                    </p>
                                </div>
                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-slate-500"><?= htmlspecialchars($featuredPost['author']) ?></span>
                                    <a href="post.php?slug=<?= urlencode($featuredPost['slug']) ?>"
                                        class="inline-flex items-center gap-1.5 text-xs font-extrabold text-primary hover:text-primary-dark uppercase tracking-wider group-hover:translate-x-1 transition-transform">
                                        <span>Read Article</span>
                                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Remaining Posts Grid -->
                <?php if (empty($remainingPosts)): ?>
                    <div class="py-16 text-center text-slate-500 bg-white rounded-[5px] border border-slate-200 p-8">
                        <span class="material-symbols-outlined text-5xl text-slate-300 block mb-3">feed</span>
                        <h3 class="text-lg font-bold text-slate-800">No publications found</h3>
                        <p class="text-sm text-slate-500 mt-1">Try adjusting your category filter or search keywords.</p>
                        <a href="index.php" class="inline-block mt-4 px-4 py-2 rounded-[5px] bg-primary text-white font-bold text-xs uppercase tracking-wider">
                            View All Publications
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 items-stretch">
                        <?php foreach ($remainingPosts as $p): ?>
                            <article class="bg-white rounded-[5px] border border-slate-200 overflow-hidden shadow-xs hover:shadow-executive hover:border-primary/40 transition-all duration-300 flex flex-col justify-between group">
                                <div>
                                    <div class="relative aspect-video overflow-hidden">
                                        <img src="<?= htmlspecialchars($p['featured_image'] ?: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=600&q=80') ?>"
                                            alt="<?= htmlspecialchars($p['title']) ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                        <span class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-xs text-white text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-[3px]">
                                            <?= htmlspecialchars($p['category']) ?>
                                        </span>
                                    </div>
                                    <div class="p-6 space-y-2.5">
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 font-semibold">
                                            <span><?= date('d M Y', strtotime($p['created_at'])) ?></span>
                                            <span>&bull;</span>
                                            <span><?= htmlspecialchars($p['read_time']) ?></span>
                                        </div>
                                        <h3 class="font-extrabold text-slate-950 text-base group-hover:text-primary transition-colors leading-snug">
                                            <a href="post.php?slug=<?= urlencode($p['slug']) ?>">
                                                <?= htmlspecialchars($p['title']) ?>
                                            </a>
                                        </h3>
                                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-3">
                                            <?= htmlspecialchars($p['excerpt']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="p-6 pt-0 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-400 font-medium truncate max-w-[150px]"><?= htmlspecialchars($p['author']) ?></span>
                                    <a href="post.php?slug=<?= urlencode($p['slug']) ?>"
                                        class="font-extrabold text-primary hover:text-primary-dark inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                        <span>Read</span>
                                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </section>

        <!-- 3. PRE-FOOTER CTA -->
        <section class="py-16 sm:py-20 bg-[#172554] text-white text-center relative overflow-hidden">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 space-y-4 relative z-10">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-300 block">Admissions Planning</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Have Questions Regarding European Medical Admissions?
                </h2>
                <p class="text-sm sm:text-base text-blue-100/90 max-w-2xl mx-auto">
                    Speak directly with our clinical advisors to review your academic background, entrance examination timelines, and university choices.
                </p>
                <div class="pt-3">
                    <a href="../contact.html#consultation-form"
                        class="inline-block px-7 py-3.5 rounded-[5px] bg-[#12bda9] hover:bg-[#0d9686] text-white font-extrabold text-xs uppercase tracking-wider transition-all shadow-md">
                        Book Free Clinical Consultation
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Global Dynamic Footer Anchor -->
    <footer id="site-footer"></footer>

    <!-- Centralized Layout Loader -->
    <script src="../layout.js"></script>
</body>
</html>
