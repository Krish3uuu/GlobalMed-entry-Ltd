<?php
/**
 * GlobalMed Entry - Post Editor (Create & Edit)
 * 
 * Supports Articles, News, Events, Resources,
 * with full control over Homepage placement.
 */
require_once __DIR__ . '/auth.php';
check_auth();

$db = get_db_connection();
$user = get_current_user_data();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$post = null;

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) {
        set_flash('error', 'Post not found.');
        header("Location: index.php");
        exit;
    }
}

$errors = [];

// Helper to create clean URL slug
function create_slug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug ?: 'post-' . time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Security token expired. Please try submitting again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $post_type = $_POST['post_type'] ?? 'article';
        $category = trim($_POST['category'] ?? 'General');
        $read_time = trim($_POST['read_time'] ?? '5 min read');
        $author = trim($_POST['author'] ?? 'Admissions Directorate');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $featured_image = trim($_POST['featured_image'] ?? '');
        $status = $_POST['status'] ?? 'published';
        $show_on_home = isset($_POST['show_on_home']) ? 1 : 0;
        $home_section = $_POST['home_section'] ?? 'articles';
        $home_order = (int)($_POST['home_order'] ?? 0);

        if (empty($title)) {
            $errors[] = 'Please provide a post title.';
        }

        if (empty($slug)) {
            $slug = create_slug($title);
        } else {
            $slug = create_slug($slug);
        }

        // Check unique slug
        $slugCheckSql = $isEdit ? "SELECT id FROM posts WHERE slug = ? AND id != ?" : "SELECT id FROM posts WHERE slug = ?";
        $slugParams = $isEdit ? [$slug, $id] : [$slug];
        $slugStmt = $db->prepare($slugCheckSql);
        $slugStmt->execute($slugParams);
        if ($slugStmt->fetch()) {
            $slug .= '-' . time();
        }

        // Handle Image Upload if provided
        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image_upload'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);

            if (in_array($mime, $allowedTypes)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetDir = __DIR__ . '/../uploads';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $targetPath = $targetDir . '/' . $newFilename;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $featured_image = '../uploads/' . $newFilename;
                }
            } else {
                $errors[] = 'Uploaded image must be JPG, PNG, or WebP format.';
            }
        }

        if (empty($errors)) {
            if ($isEdit) {
                $updateSql = "UPDATE posts SET 
                    title = ?, slug = ?, post_type = ?, category = ?, read_time = ?, author = ?, 
                    excerpt = ?, content = ?, featured_image = ?, status = ?, show_on_home = ?, 
                    home_section = ?, home_order = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
                $stmt = $db->prepare($updateSql);
                $stmt->execute([
                    $title, $slug, $post_type, $category, $read_time, $author,
                    $excerpt, $content, $featured_image, $status, $show_on_home,
                    $home_section, $home_order, $id
                ]);
                set_flash('success', "Post '{$title}' updated successfully.");
            } else {
                $insertSql = "INSERT INTO posts 
                    (title, slug, post_type, category, read_time, author, excerpt, content, featured_image, status, show_on_home, home_section, home_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($insertSql);
                $stmt->execute([
                    $title, $slug, $post_type, $category, $read_time, $author,
                    $excerpt, $content, $featured_image, $status, $show_on_home,
                    $home_section, $home_order
                ]);
                $newId = $db->lastInsertId();
                set_flash('success', "New post '{$title}' created successfully.");
            }

            header("Location: index.php");
            exit;
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $isEdit ? 'Edit Post' : 'Create New Post' ?> | GlobalMed Entry Editorial</title>
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

    <!-- Top Admin Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="index.php" class="p-2 rounded-[5px] text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="Back to Posts">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <h1 class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight">
                    <?= $isEdit ? 'Edit Post: ' . htmlspecialchars($post['title']) : 'Create New Post' ?>
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($isEdit && !empty($post['slug'])): ?>
                    <a href="../blog/post.php?slug=<?= urlencode($post['slug']) ?>" target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[5px] bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[15px]">open_in_new</span>
                        <span>Preview Live</span>
                    </a>
                <?php endif; ?>
                <button type="submit" form="postForm"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-[5px] bg-primary hover:bg-primary-dark text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span>Save Post</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 rounded-[5px] bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium space-y-1">
                <?php foreach ($errors as $err): ?>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-rose-600">error</span>
                        <span><?= htmlspecialchars($err) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="postForm" method="POST" action="" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" />

            <!-- Left Col (8 cols): Title, Excerpt, Content -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Title & Slug -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-6 shadow-xs space-y-4">
                    <div>
                        <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Post Title <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" required
                            value="<?= htmlspecialchars($_POST['title'] ?? $post['title'] ?? '') ?>"
                            onkeyup="autoGenerateSlug(this.value)"
                            placeholder="e.g. Complete Guide to European Medical University Entrance Exams"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary text-base font-bold placeholder-slate-400" />
                    </div>

                    <div>
                        <label for="slug" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                            URL Slug (Identifier)
                        </label>
                        <div class="flex items-center">
                            <span class="px-3 py-2 bg-slate-50 border border-r-0 border-slate-200 text-slate-400 text-xs font-mono rounded-l-[5px]">
                                /blog/post.php?slug=
                            </span>
                            <input type="text" id="slug" name="slug"
                                value="<?= htmlspecialchars($_POST['slug'] ?? $post['slug'] ?? '') ?>"
                                placeholder="guide-to-entrance-exams"
                                class="flex-1 px-3 py-2 rounded-r-[5px] border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary text-xs font-mono text-slate-700" />
                        </div>
                    </div>
                </div>

                <!-- Short Excerpt -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-6 shadow-xs space-y-2">
                    <label for="excerpt" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Short Excerpt (Summary)
                    </label>
                    <p class="text-xs text-slate-500">Brief 1–2 sentence summary displayed on card listings, search results, and client homepage cards.</p>
                    <textarea id="excerpt" name="excerpt" rows="3"
                        placeholder="Compare entry criteria, tuition fees, clinical hospital partnerships, and living costs..."
                        class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary text-sm text-slate-700 leading-relaxed"><?= htmlspecialchars($_POST['excerpt'] ?? $post['excerpt'] ?? '') ?></textarea>
                </div>

                <!-- Full Content Editor -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-6 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="content" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Article Body Content (HTML supported)
                        </label>
                        <!-- Quick format toolbar -->
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="insertTag('<h3>', '</h3>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-bold text-slate-700" title="Heading 3">H3</button>
                            <button type="button" onclick="insertTag('<p>', '</p>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-bold text-slate-700" title="Paragraph">&para;</button>
                            <button type="button" onclick="insertTag('<strong>', '</strong>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-bold text-slate-700" title="Bold">B</button>
                            <button type="button" onclick="insertTag('<em>', '</em>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs italic font-bold text-slate-700" title="Italic">I</button>
                            <button type="button" onclick="insertTag('<blockquote>', '</blockquote>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-bold text-slate-700" title="Quote">&ldquo;</button>
                            <button type="button" onclick="insertTag('<ul>\n  <li>', '</li>\n</ul>')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-bold text-slate-700" title="Bullet List">&bull; List</button>
                        </div>
                    </div>
                    <textarea id="content" name="content" rows="18"
                        placeholder="Write or paste your article content here..."
                        class="w-full px-4 py-3 rounded-[5px] border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono text-slate-800 leading-relaxed"><?= htmlspecialchars($_POST['content'] ?? $post['content'] ?? '') ?></textarea>
                    <p class="text-[11px] text-slate-400">
                        Tip: You can paste formatted HTML directly. Standard paragraph tags (&lt;p&gt;), headings (&lt;h3&gt;), lists (&lt;ul&gt;&lt;li&gt;), and links will render with the website's executive typography.
                    </p>
                </div>
            </div>

            <!-- Right Col (4 cols): Meta, Homepage Placement, Featured Image -->
            <div class="lg:col-span-4 space-y-6">

                <!-- 1. HOMEPAGE PLACEMENT CARD (Highlighted) -->
                <div class="bg-gradient-to-br from-blue-50/80 via-white to-blue-50/40 rounded-[5px] border-2 border-primary/40 p-5 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[18px]">home</span>
                        <span>Homepage Curation</span>
                    </div>

                    <?php
                    $curShowHome = isset($_POST['show_on_home']) ? 1 : ($post['show_on_home'] ?? 0);
                    $curHomeSection = $_POST['home_section'] ?? ($post['home_section'] ?? 'articles');
                    $curHomeOrder = $_POST['home_order'] ?? ($post['home_order'] ?? 1);
                    ?>

                    <label class="flex items-start gap-3 cursor-pointer pt-1">
                        <input type="checkbox" name="show_on_home" value="1" <?= $curShowHome ? 'checked' : '' ?>
                            class="rounded-[4px] border-slate-300 text-primary focus:ring-primary w-4 h-4 mt-0.5" />
                        <div>
                            <span class="text-sm font-bold text-slate-900 block leading-tight">Display on Homepage</span>
                            <span class="text-xs text-slate-500">Feature this item on the client homepage</span>
                        </div>
                    </label>

                    <div class="space-y-3 pt-2 border-t border-blue-100">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Homepage Section
                            </label>
                            <select name="home_section"
                                class="w-full text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-2 text-slate-800 font-medium">
                                <option value="articles" <?= $curHomeSection === 'articles' ? 'selected' : '' ?>>
                                    Articles Grid (Top 3 Navy Cards)
                                </option>
                                <option value="news" <?= $curHomeSection === 'news' ? 'selected' : '' ?>>
                                    Latest News &amp; Updates Column (Right Sidebar)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Slot Display Order
                            </label>
                            <input type="number" name="home_order" min="1" max="10" value="<?= (int)$curHomeOrder ?>"
                                class="w-full text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-2 text-slate-800" />
                            <span class="text-[11px] text-slate-400 mt-0.5 block">1 appears first, 2 second, etc.</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Publishing Status & Post Type -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-5 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Publication Settings
                    </h3>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Post Type</label>
                        <?php $curType = $_POST['post_type'] ?? ($post['post_type'] ?? 'article'); ?>
                        <select name="post_type"
                            class="w-full text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-2 text-slate-800 font-semibold">
                            <option value="article" <?= $curType === 'article' ? 'selected' : '' ?>>Article (Study in Europe)</option>
                            <option value="news" <?= $curType === 'news' ? 'selected' : '' ?>>News &amp; Regulatory Update</option>
                            <option value="event" <?= $curType === 'event' ? 'selected' : '' ?>>Event &amp; Webinar</option>
                            <option value="resource" <?= $curType === 'resource' ? 'selected' : '' ?>>Resource &amp; Policy Pack</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Category Badge</label>
                        <input type="text" name="category" list="categoryList"
                            value="<?= htmlspecialchars($_POST['category'] ?? ($post['category'] ?? 'Admissions Guide')) ?>"
                            class="w-full text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-2 text-slate-800" />
                        <datalist id="categoryList">
                            <option value="Admissions Guide">
                            <option value="Tuition & Finance">
                            <option value="Exam Strategy">
                            <option value="Regulatory Update">
                            <option value="University Comparison">
                            <option value="Dental Education">
                            <option value="Clinical Skills">
                            <option value="Campus Life">
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Status</label>
                        <?php $curStatus = $_POST['status'] ?? ($post['status'] ?? 'published'); ?>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2 rounded-[5px] border cursor-pointer <?= $curStatus === 'published' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 font-bold' : 'border-slate-200 text-slate-600' ?>">
                                <input type="radio" name="status" value="published" <?= $curStatus === 'published' ? 'checked' : '' ?> class="text-emerald-600 focus:ring-emerald-500" />
                                <span class="text-xs">Published</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-[5px] border cursor-pointer <?= $curStatus === 'draft' ? 'border-amber-500 bg-amber-50 text-amber-900 font-bold' : 'border-slate-200 text-slate-600' ?>">
                                <input type="radio" name="status" value="draft" <?= $curStatus === 'draft' ? 'checked' : '' ?> class="text-amber-600 focus:ring-amber-500" />
                                <span class="text-xs">Draft</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Reading Time</label>
                        <input type="text" name="read_time"
                            value="<?= htmlspecialchars($_POST['read_time'] ?? ($post['read_time'] ?? '5 min read')) ?>"
                            placeholder="5 min read"
                            class="w-full text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-2 text-slate-800" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Author / Council</label>
                        <input type="text" name="author"
                            value="<?= htmlspecialchars($_POST['author'] ?? ($post['author'] ?? 'Admissions Directorate')) ?>"
                            placeholder="European Admissions Directorate"
                            class="w-full text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-2 text-slate-800" />
                    </div>
                </div>

                <!-- 3. Featured Image Card -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-5 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                        Featured Image
                    </h3>

                    <?php
                    $curImg = $_POST['featured_image'] ?? ($post['featured_image'] ?? '');
                    if (!empty($curImg)):
                    ?>
                        <div class="aspect-video rounded-[4px] overflow-hidden border border-slate-200 relative group">
                            <img src="<?= htmlspecialchars($curImg) ?>" alt="" class="w-full h-full object-cover" id="imgPreview" />
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Image URL</label>
                        <input type="url" name="featured_image" id="featured_image"
                            value="<?= htmlspecialchars($curImg) ?>"
                            placeholder="https://images.unsplash.com/..."
                            class="w-full text-xs rounded-[5px] border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary py-2 text-slate-800" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Or Upload File</label>
                        <input type="file" name="image_upload" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-[4px] file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-primary hover:file:bg-blue-100 cursor-pointer" />
                    </div>
                </div>

                <!-- Save Action Buttons -->
                <div class="space-y-2">
                    <button type="submit"
                        class="w-full py-3 rounded-[5px] bg-[#12bda9] hover:bg-[#0d9686] text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">publish</span>
                        <span><?= $isEdit ? 'Save Changes' : 'Publish Article' ?></span>
                    </button>
                    <a href="index.php" class="w-full py-2.5 rounded-[5px] bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs text-center block transition-colors">
                        Cancel &amp; Return
                    </a>
                </div>

            </div>
        </form>
    </main>

    <script>
        function autoGenerateSlug(title) {
            // Only auto-generate if slug is empty or user is creating new post
            const slugInput = document.getElementById('slug');
            <?php if (!$isEdit): ?>
                slugInput.value = title.toLowerCase()
                    .replace(/[^a-z0-9 -]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            <?php endif; ?>
        }

        function insertTag(openTag, closeTag) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selectedText = text.substring(start, end);
            const replacement = openTag + (selectedText || 'text') + closeTag;
            textarea.value = text.substring(0, start) + replacement + text.substring(end);
            textarea.focus();
            textarea.selectionStart = start + openTag.length;
            textarea.selectionEnd = start + openTag.length + (selectedText.length || 4);
        }
    </script>
</body>
</html>
