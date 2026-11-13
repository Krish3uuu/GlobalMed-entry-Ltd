<?php
/**
 * GlobalMed Entry - Single Post & Article Details Page
 * 
 * Renders complete editorial content with executive formatting,
 * consultation sidebar, social share, and related publications.
 */
require_once __DIR__ . '/db.php';

$db = get_db_connection();

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (!empty($slug)) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE slug = ?");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
} else {
    $post = null;
}

// Check published status (allow admin preview if session active)
session_start();
$isAdmin = isset($_SESSION['admin_user_id']);

if (!$post || ($post['status'] !== 'published' && !$isAdmin)) {
    http_response_code(404);
    $postNotFound = true;
} else {
    $postNotFound = false;

    // Fetch related articles (same category or type, excluding current)
    $relatedStmt = $db->prepare("SELECT * FROM posts WHERE id != ? AND status = 'published' AND (category = ? OR post_type = ?) ORDER BY created_at DESC LIMIT 3");
    $relatedStmt->execute([$post['id'], $post['category'], $post['post_type']]);
    $relatedPosts = $relatedStmt->fetchAll();

    if (count($relatedPosts) < 3) {
        // Fallback to any published
        $fallbackStmt = $db->prepare("SELECT * FROM posts WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
        $fallbackStmt->execute([$post['id']]);
        $relatedPosts = $fallbackStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $postNotFound ? 'Article Not Found' : htmlspecialchars($post['title']) . ' | GlobalMed Entry Ltd' ?></title>
    <?php if (!$postNotFound): ?>
        <meta name="description" content="<?= htmlspecialchars(substr($post['excerpt'] ?? '', 0, 160)) ?>" />
    <?php endif; ?>
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

        <?php if ($postNotFound): ?>
            <div class="py-24 text-center max-w-lg mx-auto px-4">
                <span class="material-symbols-outlined text-6xl text-slate-300 block mb-3">search_off</span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Article Not Found</h1>
                <p class="text-slate-500 text-sm mt-2">The article you requested could not be located or may have been relocated.</p>
                <div class="pt-6">
                    <a href="index.php" class="px-5 py-2.5 rounded-[5px] bg-primary text-white font-bold text-xs uppercase tracking-wider hover:bg-primary-dark transition-colors inline-block">
                        &larr; Return to All Articles
                    </a>
                </div>
            </div>
        <?php else: ?>

            <?php if ($isAdmin && $post['status'] === 'draft'): ?>
                <div class="bg-amber-500 text-slate-950 px-4 py-2 text-center text-xs font-bold tracking-wide flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                    <span>Admin Preview Mode: This post is currently saved as DRAFT and is not publicly visible.</span>
                    <a href="../admin/edit.php?id=<?= $post['id'] ?>" class="underline ml-2">Edit Post</a>
                </div>
            <?php endif; ?>

            <!-- Article Hero Header -->
            <section class="bg-[#F8FAFD] border-b border-slate-200 py-10 sm:py-14">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                    
                    <!-- Breadcrumbs -->
                    <nav class="flex items-center gap-2 text-xs text-slate-400">
                        <a href="../index.html" class="hover:text-primary transition-colors">Home</a>
                        <span>/</span>
                        <a href="index.php" class="hover:text-primary transition-colors">Blog</a>
                        <span>/</span>
                        <a href="index.php?type=<?= urlencode($post['post_type']) ?>" class="hover:text-primary transition-colors uppercase font-bold text-[10px]">
                            <?= htmlspecialchars($post['post_type']) ?>
                        </a>
                    </nav>

                    <!-- Category & Status -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="px-3 py-1 rounded-[4px] bg-blue-50 text-primary border border-blue-200 text-xs font-bold uppercase tracking-wider">
                            <?= htmlspecialchars($post['category']) ?>
                        </span>
                        <?php if ($post['show_on_home'] == 1): ?>
                            <span class="px-2.5 py-1 rounded-[4px] bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold">
                                Featured on Homepage
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Article Title -->
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-950 tracking-tight leading-tight">
                        <?= htmlspecialchars($post['title']) ?>
                    </h1>

                    <!-- Lead Excerpt -->
                    <?php if (!empty($post['excerpt'])): ?>
                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed font-normal">
                            <?= htmlspecialchars($post['excerpt']) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Author & Meta Strip -->
                    <div class="flex flex-wrap items-center gap-4 sm:gap-6 pt-4 text-xs text-slate-500 border-t border-slate-200/80">
                        <div class="flex items-center gap-2 font-semibold text-slate-800">
                            <span class="material-symbols-outlined text-primary text-[18px]">verified</span>
                            <span><?= htmlspecialchars($post['author']) ?></span>
                        </div>
                        <span>&bull;</span>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span>
                            <span>Published: <?= date('d F Y', strtotime($post['created_at'])) ?></span>
                        </div>
                        <span>&bull;</span>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-slate-400">schedule</span>
                            <span><?= htmlspecialchars($post['read_time']) ?></span>
                        </div>
                        <?php if ($isAdmin): ?>
                            <div class="ml-auto">
                                <a href="../admin/edit.php?id=<?= $post['id'] ?>" class="px-3 py-1 bg-white border border-slate-200 text-primary hover:bg-slate-50 font-bold rounded text-xs flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">edit</span>
                                    <span>Edit</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </section>

            <!-- Main Content Grid -->
            <section class="py-12 sm:py-16 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">

                        <!-- Main Article Body (Col 8) -->
                        <article class="lg:col-span-8 space-y-8">
                            
                            <!-- Featured Image -->
                            <?php if (!empty($post['featured_image'])): ?>
                                <div class="rounded-[5px] overflow-hidden border border-slate-200 shadow-executive aspect-video">
                                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
                                        class="w-full h-full object-cover" />
                                </div>
                            <?php endif; ?>

                            <!-- Rich Content Body -->
                            <div class="prose max-w-none text-slate-700 leading-relaxed space-y-5 text-sm sm:text-base">
                                <?= $post['content'] ?>
                            </div>

                            <!-- Social Share Bar -->
                            <div class="pt-8 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Share This Editorial</span>
                                <?php
                                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                                $uri = $_SERVER['REQUEST_URI'] ?? '/blog/post.php?slug=' . urlencode($post['slug']);
                                $shareUrl = urlencode("https://" . $host . $uri);
                                $shareText = urlencode($post['title']);
                                ?>
                                <div class="flex items-center gap-2 text-xs">
                                    <a href="https://wa.me/?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank"
                                        class="px-3 py-1.5 rounded-[4px] bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold flex items-center gap-1.5 transition-colors">
                                        <span>WhatsApp</span>
                                    </a>
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank"
                                        class="px-3 py-1.5 rounded-[4px] bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold flex items-center gap-1.5 transition-colors">
                                        <span>LinkedIn</span>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $shareUrl ?>" target="_blank"
                                        class="px-3 py-1.5 rounded-[4px] bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center gap-1.5 transition-colors">
                                        <span>X / Twitter</span>
                                    </a>
                                    <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');"
                                        class="px-3 py-1.5 rounded-[4px] bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center gap-1.5 transition-colors">
                                        <span class="material-symbols-outlined text-[15px]">link</span>
                                        <span>Copy Link</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Author Card -->
                            <div class="p-6 rounded-[5px] bg-slate-50 border border-slate-200 flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[24px]">verified</span>
                                </div>
                                <div class="space-y-1 text-xs">
                                    <span class="font-bold text-slate-900 text-sm block"><?= htmlspecialchars($post['author']) ?></span>
                                    <p class="text-slate-600 leading-relaxed">
                                        GlobalMed Entry Ltd provides comprehensive academic diagnostic assessments, sworn translation coordination, and clinical curriculum mapping for UK students seeking accredited medical and dental qualifications across Europe.
                                    </p>
                                </div>
                            </div>

                        </article>

                        <!-- Sticky Sidebar (Col 4) -->
                        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-28">

                            <!-- Direct Consultation Card -->
                            <div class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#172554] text-white rounded-[5px] p-6 shadow-executive border border-blue-900/40 text-center space-y-4">
                                <div class="w-12 h-12 rounded-full bg-white/10 text-white flex items-center justify-center mx-auto border border-white/20">
                                    <span class="material-symbols-outlined text-2xl">medical_services</span>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-white text-base tracking-tight">Need Clinical Admissions Guidance?</h3>
                                    <p class="text-xs text-blue-100/90 mt-1 leading-relaxed">
                                        Book a free 1-on-1 assessment with our clinical advisors to review your academic background, university options, and entrance exam syllabus.
                                    </p>
                                </div>
                                <a href="../contact.html#consultation-form"
                                    class="w-full py-2.5 rounded-[5px] bg-[#12bda9] hover:bg-[#0d9686] text-white font-bold text-xs uppercase tracking-wider block transition-colors shadow-sm">
                                    Book Free Consultation
                                </a>
                            </div>

                            <!-- Fast Contact Specs -->
                            <div class="p-5 rounded-[5px] bg-white border border-slate-200 shadow-xs space-y-3">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-primary">contact_support</span>
                                    Direct Admissions Desk
                                </h4>
                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between border-b border-slate-100 pb-1.5">
                                        <span class="text-slate-500">Email Inquiries:</span>
                                        <a href="mailto:info@globalmedentry.com" class="font-bold text-primary hover:underline">info@globalmedentry.com</a>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-100 pb-1.5">
                                        <span class="text-slate-500">UK Direct Line:</span>
                                        <a href="tel:+447345692756" class="font-bold text-slate-800">+44 7345 692756</a>
                                    </div>
                                    <div class="flex justify-between pt-0.5">
                                        <span class="text-slate-500">Academic Intake:</span>
                                        <span class="font-bold text-emerald-600">September 2026</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Return to Blog -->
                            <div class="text-center">
                                <a href="index.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                                    <span>Browse All Articles &amp; News</span>
                                </a>
                            </div>

                        </aside>

                    </div>
                </div>
            </section>

            <!-- Related Articles Row -->
            <?php if (!empty($relatedPosts)): ?>
                <section class="py-14 sm:py-20 bg-slate-50 border-t border-slate-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 space-y-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-widest text-primary block">Related Editorials</span>
                                <h2 class="text-2xl font-extrabold text-slate-950 tracking-tight">You Might Also Find Useful</h2>
                            </div>
                            <a href="index.php" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                <span>View All</span>
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 items-stretch">
                            <?php foreach ($relatedPosts as $rp): ?>
                                <div class="bg-white rounded-[5px] border border-slate-200 overflow-hidden shadow-xs hover:shadow-executive transition-all flex flex-col justify-between group">
                                    <div>
                                        <div class="relative aspect-video overflow-hidden">
                                            <img src="<?= htmlspecialchars($rp['featured_image'] ?: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=600&q=80') ?>"
                                                alt="<?= htmlspecialchars($rp['title']) ?>"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                            <span class="absolute top-2.5 left-2.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-[3px]">
                                                <?= htmlspecialchars($rp['category']) ?>
                                            </span>
                                        </div>
                                        <div class="p-5 space-y-2">
                                            <h3 class="font-bold text-slate-950 text-sm group-hover:text-primary transition-colors line-clamp-2">
                                                <a href="post.php?slug=<?= urlencode($rp['slug']) ?>">
                                                    <?= htmlspecialchars($rp['title']) ?>
                                                </a>
                                            </h3>
                                            <p class="text-xs text-slate-500 line-clamp-2">
                                                <?= htmlspecialchars($rp['excerpt']) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="p-5 pt-0 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <span class="text-slate-400 text-[11px]"><?= date('d M Y', strtotime($rp['created_at'])) ?></span>
                                        <a href="post.php?slug=<?= urlencode($rp['slug']) ?>" class="font-bold text-primary hover:underline">Read &rarr;</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <!-- Global Dynamic Footer Anchor -->
    <footer id="site-footer"></footer>

    <!-- Centralized Layout Loader -->
    <script src="../layout.js"></script>
</body>
</html>
