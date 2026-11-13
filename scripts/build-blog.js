const fs = require('fs');
const path = require('path');

const postsDataRaw = fs.readFileSync(path.join(__dirname, '../blog/posts-data.js'), 'utf8');

// 1. Build blog/index.html
const indexHtml = `<!DOCTYPE html>
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
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-slate-400 pointer-events-none flex items-center">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input type="text" id="blogSearchInput"
                            placeholder="Search articles by keyword, university, or exam..."
                            class="w-full pl-11 pr-28 py-3.5 rounded-[5px] bg-white/95 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#12bda9] shadow-lg border-0"
                            oninput="renderBlogList()" />
                        <button type="button" onclick="renderBlogList()"
                            class="absolute right-1.5 top-1.5 bottom-1.5 px-4 rounded-[4px] bg-primary hover:bg-primary-dark text-white font-bold text-xs uppercase tracking-wider transition-colors">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. FILTER TABS & MAIN LISTING -->
        <section class="py-10 sm:py-14 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 space-y-10">

                <!-- Filter Pills -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-200">
                    <div class="flex flex-wrap items-center gap-2" id="filterPillsContainer">
                        <button onclick="setBlogFilter('all')" data-type="all"
                            class="blog-tab-btn px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs bg-primary text-white">
                            All Publications (<span id="countAll">0</span>)
                        </button>
                        <button onclick="setBlogFilter('article')" data-type="article"
                            class="blog-tab-btn px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs bg-white border border-slate-200 text-slate-700 hover:border-primary">
                            Articles (<span id="countArticles">0</span>)
                        </button>
                        <button onclick="setBlogFilter('news')" data-type="news"
                            class="blog-tab-btn px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs bg-white border border-slate-200 text-slate-700 hover:border-primary">
                            News &amp; Updates (<span id="countNews">0</span>)
                        </button>
                        <button onclick="setBlogFilter('event')" data-type="event"
                            class="blog-tab-btn px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs bg-white border border-slate-200 text-slate-700 hover:border-primary">
                            Events &amp; Webinars (<span id="countEvents">0</span>)
                        </button>
                        <button onclick="setBlogFilter('resource')" data-type="resource"
                            class="blog-tab-btn px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs bg-white border border-slate-200 text-slate-700 hover:border-primary">
                            Resources &amp; Packs (<span id="countResources">0</span>)
                        </button>
                    </div>

                    <div id="searchIndicator" class="hidden items-center gap-2 text-xs text-slate-500">
                        <span>Showing results for: <strong id="searchQueryLabel" class="text-slate-900"></strong></span>
                        <button onclick="clearSearch()" class="text-primary hover:underline font-bold">Clear</button>
                    </div>
                </div>

                <!-- Featured Post Card Mount Point -->
                <div id="featuredPostMount"></div>

                <!-- Main Posts Grid -->
                <div id="postsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Cards will be populated by renderBlogList() -->
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="hidden text-center py-16 bg-white rounded-[5px] border border-slate-200 p-8 space-y-4">
                    <span class="material-symbols-outlined text-5xl text-slate-300">search_off</span>
                    <h3 class="text-xl font-bold text-slate-900">No Publications Found</h3>
                    <p class="text-slate-500 text-sm max-w-md mx-auto">
                        No articles match your current search query or category filter. Try clearing your filters.
                    </p>
                    <button onclick="clearSearch(); setBlogFilter('all');" class="px-5 py-2.5 rounded-[5px] bg-primary text-white text-xs font-bold uppercase tracking-wider">
                        View All Publications
                    </button>
                </div>

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
                    Speak directly with our admissions advisors to review your academic background, entrance examination timelines, and university choices.
                </p>
                <div class="pt-3">
                    <a href="../contact.html#consultation-form"
                        class="inline-block px-7 py-3.5 rounded-[5px] bg-[#12bda9] hover:bg-[#0d9686] text-white font-extrabold text-xs uppercase tracking-wider transition-all shadow-md">
                        Book Free Consultation
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Global Dynamic Footer Anchor -->
    <footer id="site-footer"></footer>

    <!-- Embedded Posts Data (Guaranteed to load 100% reliably in any environment) -->
    <script>
    ` + postsDataRaw + `
    </script>
    <script src="../posts-data.js"></script>
    <script src="posts-data.js"></script>
    <script src="/posts-data.js"></script>

    <!-- Client-Side Reactive Blog Listing Controller -->
    <script>
        var currentType = "all";

        function getActivePosts() {
            try {
                var stored = localStorage.getItem("globalmed_posts_store");
                if (stored) {
                    var parsed = JSON.parse(stored);
                    if (Array.isArray(parsed) && parsed.length > 0) return parsed;
                }
            } catch (e) {}
            return (typeof GLOBALMED_POSTS !== "undefined") ? GLOBALMED_POSTS : [];
        }

        function getPostLink(slug) {
            if (window.location.protocol === "file:") {
                return "post.html?slug=" + encodeURIComponent(slug);
            }
            var p = window.location.pathname;
            var subMatch = p.match(/^(.*\\/)blog(\\/.*)?$/i);
            var base = (subMatch && subMatch[1]) ? subMatch[1] : "/";
            return base + "blog/post.html?slug=" + encodeURIComponent(slug);
        }

        function initCounts() {
            var posts = getActivePosts();
            if (!posts.length) return;
            document.getElementById("countAll").textContent = posts.length;
            document.getElementById("countArticles").textContent = posts.filter(function(p) { return p.post_type === "article"; }).length;
            document.getElementById("countNews").textContent = posts.filter(function(p) { return p.post_type === "news"; }).length;
            document.getElementById("countEvents").textContent = posts.filter(function(p) { return p.post_type === "event"; }).length;
            document.getElementById("countResources").textContent = posts.filter(function(p) { return p.post_type === "resource"; }).length;
        }

        function setBlogFilter(type) {
            currentType = type;
            var buttons = document.querySelectorAll(".blog-tab-btn");
            for (var i = 0; i < buttons.length; i++) {
                var btn = buttons[i];
                if (btn.getAttribute("data-type") === type) {
                    btn.className = "blog-tab-btn px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs bg-primary text-white";
                } else {
                    btn.className = "blog-tab-btn px-4 py-2 rounded-[5px] text-xs font-bold transition-all shadow-xs bg-white border border-slate-200 text-slate-700 hover:border-primary";
                }
            }
            renderBlogList();
        }

        function clearSearch() {
            document.getElementById("blogSearchInput").value = "";
            document.getElementById("searchIndicator").classList.add("hidden");
            renderBlogList();
        }

        function formatDate(dateStr) {
            try {
                var d = new Date(dateStr.replace(" ", "T"));
                return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
            } catch (e) {
                return dateStr || "Recent";
            }
        }

        function renderBlogList() {
            var posts = getActivePosts();
            if (!posts.length) return;

            var q = (document.getElementById("blogSearchInput").value || "").trim().toLowerCase();
            var searchIndicator = document.getElementById("searchIndicator");
            var searchQueryLabel = document.getElementById("searchQueryLabel");

            if (q) {
                searchIndicator.classList.remove("hidden");
                searchIndicator.classList.add("flex");
                searchQueryLabel.textContent = '"' + q + '"';
            } else {
                searchIndicator.classList.add("hidden");
                searchIndicator.classList.remove("flex");
            }

            var filtered = posts.filter(function(p) {
                var matchesType = (currentType === "all" || p.post_type === currentType);
                var matchesQuery = true;
                if (q) {
                    var title = (p.title || "").toLowerCase();
                    var excerpt = (p.excerpt || "").toLowerCase();
                    var category = (p.category || "").toLowerCase();
                    matchesQuery = title.includes(q) || excerpt.includes(q) || category.includes(q);
                }
                return matchesType && matchesQuery;
            });

            var featuredMount = document.getElementById("featuredPostMount");
            var gridMount = document.getElementById("postsGrid");
            var emptyState = document.getElementById("emptyState");

            featuredMount.innerHTML = "";
            gridMount.innerHTML = "";

            if (!filtered.length) {
                emptyState.classList.remove("hidden");
                return;
            } else {
                emptyState.classList.add("hidden");
            }

            var displayPosts = filtered;

            // When viewing All with no search, show top post as hero featured card
            if (currentType === "all" && !q && filtered.length > 0) {
                var fp = filtered[0];
                displayPosts = filtered.slice(1);

                featuredMount.innerHTML = 
                '<div class="bg-white rounded-[5px] border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 grid grid-cols-1 lg:grid-cols-12 group">' +
                    '<div class="lg:col-span-7 relative aspect-16/10 lg:aspect-auto overflow-hidden bg-slate-100 min-h-[260px]">' +
                        '<img src="' + fp.featured_image + '" alt="' + fp.title + '" class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500" />' +
                        '<span class="absolute top-4 left-4 bg-primary text-white text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-[3px] shadow-sm">' +
                            'Featured Publication' +
                        '</span>' +
                    '</div>' +
                    '<div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between space-y-4">' +
                        '<div class="space-y-3">' +
                            '<div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">' +
                                '<span class="px-2.5 py-0.5 rounded-[3px] bg-blue-50 text-primary font-bold text-[11px]">' + fp.category + '</span>' +
                                '<span>•</span>' +
                                '<span>' + formatDate(fp.created_at) + '</span>' +
                                '<span>•</span>' +
                                '<span>' + (fp.read_time || '5 min read') + '</span>' +
                            '</div>' +
                            '<h2 class="text-xl sm:text-2xl font-extrabold text-slate-950 group-hover:text-primary transition-colors leading-snug">' +
                                '<a href="' + getPostLink(fp.slug) + '">' + fp.title + '</a>' +
                            '</h2>' +
                            '<p class="text-sm text-slate-600 leading-relaxed line-clamp-3">' + fp.excerpt + '</p>' +
                        '</div>' +
                        '<div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">' +
                            '<span class="text-slate-500 font-medium">' + fp.author + '</span>' +
                            '<a href="' + getPostLink(fp.slug) + '" class="px-4 py-2 rounded-[4px] bg-primary hover:bg-primary-dark text-white font-bold inline-flex items-center gap-1.5 transition-colors">' +
                                '<span>Read Publication</span>' +
                                '<span class="material-symbols-outlined text-[15px]">arrow_forward</span>' +
                            '</a>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            }

            // Render remaining post cards
            var gridHtml = "";
            for (var i = 0; i < displayPosts.length; i++) {
                var p = displayPosts[i];
                var badgeClass = "bg-blue-100 text-primary";
                if (p.post_type === "news") badgeClass = "bg-emerald-100 text-emerald-800";
                if (p.post_type === "event") badgeClass = "bg-purple-100 text-purple-800";
                if (p.post_type === "resource") badgeClass = "bg-amber-100 text-amber-900";

                gridHtml += 
                '<article class="bg-white rounded-[5px] border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between group">' +
                    '<div>' +
                        '<div class="relative aspect-16/10 overflow-hidden bg-slate-100">' +
                            '<img src="' + p.featured_image + '" alt="' + p.title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />' +
                            '<span class="absolute top-3 left-3 ' + badgeClass + ' text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-[3px] shadow-xs">' +
                                p.category +
                            '</span>' +
                        '</div>' +
                        '<div class="p-6 space-y-3">' +
                            '<div class="flex items-center gap-2 text-xs text-slate-400">' +
                                '<span>' + formatDate(p.created_at) + '</span>' +
                                '<span>•</span>' +
                                '<span>' + (p.read_time || '5 min read') + '</span>' +
                            '</div>' +
                            '<h3 class="text-lg font-bold text-slate-950 group-hover:text-primary transition-colors leading-snug line-clamp-2">' +
                                '<a href="' + getPostLink(p.slug) + '">' + p.title + '</a>' +
                            '</h3>' +
                            '<p class="text-xs sm:text-sm text-slate-600 leading-relaxed line-clamp-3">' + p.excerpt + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="p-6 pt-0 border-t border-slate-100 flex items-center justify-between text-xs">' +
                        '<span class="text-slate-500 font-medium">' + p.author + '</span>' +
                        '<a href="' + getPostLink(p.slug) + '" class="font-extrabold text-primary hover:text-primary-dark inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">' +
                            '<span>Read</span>' +
                            '<span class="material-symbols-outlined text-[15px]">arrow_forward</span>' +
                        '</a>' +
                    '</div>' +
                '</article>';
            }
            gridMount.innerHTML = gridHtml;
        }

        // Initialize immediately
        initCounts();
        renderBlogList();

        document.addEventListener("DOMContentLoaded", function() {
            initCounts();
            renderBlogList();
        });
    </script>

    <!-- Centralized Layout Loader -->
    <script src="../layout.js"></script>
</body>
</html>`;

// 2. Build blog/post.html
const postHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title id="pageTitle">Article Details | GlobalMed Entry Ltd</title>
    <meta name="description" id="metaDescription" content="Authoritative medical education guidance from GlobalMed Entry Ltd." />
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

        <!-- 404 Container (hidden by default) -->
        <div id="notFoundContainer" class="hidden py-24 text-center max-w-lg mx-auto px-4">
            <span class="material-symbols-outlined text-6xl text-slate-300 block mb-3">search_off</span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Article Not Found</h1>
            <p class="text-slate-500 text-sm mt-2">The article you requested could not be located or may have been relocated.</p>
            <div class="pt-6">
                <a id="notFoundReturnBtn" href="index.html" class="px-5 py-2.5 rounded-[5px] bg-primary text-white font-bold text-xs uppercase tracking-wider hover:bg-primary-dark transition-colors inline-block">
                    &larr; Return to All Articles
                </a>
            </div>
        </div>

        <!-- Main Article Container -->
        <div id="articleContainer">
            <!-- 1. ARTICLE HERO HEADER -->
            <section class="bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#172554] text-white py-12 sm:py-16 relative overflow-hidden">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-[#12bda9]/15 blur-3xl pointer-events-none"></div>

                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-4">
                    <!-- Breadcrumbs -->
                    <div class="flex items-center gap-2 text-xs text-blue-200/80">
                        <a id="breadcrumbHome" href="../index.html" class="hover:underline">Home</a>
                        <span>/</span>
                        <a id="breadcrumbBlog" href="index.html" class="hover:underline">Blog &amp; Knowledge Base</a>
                        <span>/</span>
                        <span id="breadcrumbCategory" class="text-white font-semibold">Article</span>
                    </div>

                    <!-- Meta Tag Badges -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span id="articleCategoryBadge" class="bg-[#12bda9] text-white text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-[3px] shadow-sm">
                            Article
                        </span>
                        <span id="articleReadTime" class="bg-white/10 text-blue-100 text-xs px-3 py-1 rounded-[3px] border border-white/15">
                            5 min read
                        </span>
                        <span id="articleDate" class="text-xs text-blue-200/90 pl-1">
                            Date
                        </span>
                    </div>

                    <!-- Main Article Title -->
                    <h1 id="articleTitle" class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight pt-1">
                        Article Title
                    </h1>

                    <!-- Excerpt / Subheading -->
                    <p id="articleExcerpt" class="text-base sm:text-lg text-blue-100/90 leading-relaxed max-w-3xl font-normal pt-1">
                        Article Excerpt
                    </p>

                    <!-- Author Bylines -->
                    <div class="pt-4 flex items-center gap-3 border-t border-white/15 text-xs text-blue-100">
                        <div class="w-9 h-9 rounded-full bg-white/20 border border-white/30 flex items-center justify-center font-bold text-white text-sm">
                            GM
                        </div>
                        <div>
                            <div id="articleAuthor" class="font-bold text-white">GlobalMed Editorial Directorate</div>
                            <div class="text-[11px] text-blue-200/70">Verified European Admissions Advisory Board</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. ARTICLE BODY & SIDEBAR -->
            <section class="py-12 sm:py-16 bg-slate-50">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                        <!-- Main Content Column (8 cols) -->
                        <article class="lg:col-span-8 bg-white rounded-[5px] border border-slate-200 p-6 sm:p-10 shadow-xs space-y-8">
                            <!-- Featured Hero Image -->
                            <div class="rounded-[5px] overflow-hidden aspect-16/10 bg-slate-100 relative">
                                <img id="articleFeaturedImage" src="" alt="" class="w-full h-full object-cover" />
                            </div>

                            <!-- Social Share Bar -->
                            <div class="py-3 border-y border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span class="font-bold text-slate-700">Share this publication:</span>
                                <div class="flex items-center gap-2">
                                    <button onclick="shareArticle('twitter')" class="p-2 rounded-[4px] bg-slate-100 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Share on Twitter">
                                        <span class="font-bold">X</span>
                                    </button>
                                    <button onclick="shareArticle('linkedin')" class="p-2 rounded-[4px] bg-slate-100 hover:bg-blue-50 hover:text-blue-700 transition-colors" title="Share on LinkedIn">
                                        <span class="font-bold">in</span>
                                    </button>
                                    <button onclick="copyArticleLink()" id="copyLinkBtn" class="px-3 py-1.5 rounded-[4px] bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold inline-flex items-center gap-1 transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">link</span>
                                        <span>Copy Link</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Formatted Content Body -->
                            <div id="articleContent" class="prose max-w-none text-slate-700 text-base leading-relaxed space-y-4">
                                <!-- Populated dynamically -->
                            </div>

                            <!-- Post-Article Advisory Callout -->
                            <div class="p-6 rounded-[5px] bg-blue-50/70 border border-blue-200/80 space-y-3 mt-8">
                                <div class="flex items-center gap-2 text-primary font-bold text-sm">
                                    <span class="material-symbols-outlined text-[18px] text-[#12bda9]">verified_user</span>
                                    <span>Official European Medical Admissions Directorate</span>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    All guidance published by GlobalMed Entry Ltd is cross-referenced with university faculty guidelines and General Medical Council (GMC) requirements. Speak with our team for individualized eligibility reviews.
                                </p>
                            </div>
                        </article>

                        <!-- Sticky Sidebar Column (4 cols) -->
                        <aside class="lg:col-span-4 space-y-6">
                            <!-- Admissions CTA Card -->
                            <div class="bg-gradient-to-br from-[#02326f] to-[#01214a] rounded-[5px] p-6 text-white space-y-4 shadow-lg sticky top-28">
                                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px] text-[#12bda9]">calendar_month</span>
                                </div>
                                <h3 class="text-xl font-extrabold text-white leading-snug">
                                    Applying for 2026/2027 Medical Intake?
                                </h3>
                                <p class="text-xs sm:text-sm text-blue-100/90 leading-relaxed">
                                    Receive a tailored eligibility assessment covering your A-Level/IB grades, entrance exam dates, and seat availability at top European state universities.
                                </p>
                                <div class="pt-2">
                                    <a href="../contact.html#consultation-form"
                                        class="w-full inline-block text-center px-5 py-3 rounded-[5px] bg-[#12bda9] hover:bg-[#0d9686] text-white font-extrabold text-xs uppercase tracking-wider transition-colors shadow-sm">
                                        Book Free Assessment
                                    </a>
                                </div>
                                <div class="pt-2 border-t border-white/10 text-[11px] text-blue-200/80 flex items-center justify-between">
                                    <span>GMC &amp; UKMLA Aligned</span>
                                    <span>100% Confidential</span>
                                </div>
                            </div>

                            <!-- Quick Contact Info Card -->
                            <div class="bg-white rounded-[5px] border border-slate-200 p-6 space-y-3 text-xs text-slate-600 shadow-xs">
                                <h4 class="font-bold text-slate-900 text-sm">Admissions Advisory Directorate</h4>
                                <p class="text-slate-500 leading-relaxed">
                                    Official representatives for British and international students applying to accredited medical faculties across Bulgaria and Eastern Europe.
                                </p>
                                <div class="pt-2 space-y-2 border-t border-slate-100">
                                    <a href="mailto:info@globalmedentry.com" class="flex items-center gap-2 hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[16px] text-primary">mail</span>
                                        <span>info@globalmedentry.com</span>
                                    </a>
                                    <a href="tel:+447345692756" class="flex items-center gap-2 hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[16px] text-primary">call</span>
                                        <span>+44 7345 692756</span>
                                    </a>
                                </div>
                            </div>
                        </aside>

                    </div>
                </div>
            </section>

            <!-- 3. RELATED PUBLICATIONS -->
            <section class="py-12 sm:py-16 bg-white border-t border-slate-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 space-y-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-primary block">Recommended Reading</span>
                            <h2 class="text-2xl font-extrabold text-slate-950 tracking-tight mt-0.5">Related Insights &amp; Updates</h2>
                        </div>
                        <a id="relatedViewAllBtn" href="index.html" class="text-xs font-bold text-primary hover:underline">View All &rarr;</a>
                    </div>

                    <div id="relatedPostsGrid" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Related post cards populated dynamically -->
                    </div>
                </div>
            </section>
        </div>

    </main>

    <!-- Global Dynamic Footer Anchor -->
    <footer id="site-footer"></footer>

    <!-- Embedded Posts Data (Guaranteed to load 100% reliably in any environment) -->
    <script>
    ` + postsDataRaw + `
    </script>
    <script src="../posts-data.js"></script>
    <script src="posts-data.js"></script>
    <script src="/posts-data.js"></script>

    <!-- Post Details Loader Script -->
    <script>
        function getActivePosts() {
            try {
                var stored = localStorage.getItem("globalmed_posts_store");
                if (stored) {
                    var parsed = JSON.parse(stored);
                    if (Array.isArray(parsed) && parsed.length > 0) return parsed;
                }
            } catch (e) {}
            return (typeof GLOBALMED_POSTS !== "undefined") ? GLOBALMED_POSTS : [];
        }

        function getPostLink(slug) {
            if (window.location.protocol === "file:") {
                return "post.html?slug=" + encodeURIComponent(slug);
            }
            var p = window.location.pathname;
            var subMatch = p.match(/^(.*\\/)blog(\\/.*)?$/i);
            var base = (subMatch && subMatch[1]) ? subMatch[1] : "/";
            return base + "blog/post.html?slug=" + encodeURIComponent(slug);
        }

        function getBlogListLink() {
            if (window.location.protocol === "file:") {
                return "index.html";
            }
            var p = window.location.pathname;
            var subMatch = p.match(/^(.*\\/)blog(\\/.*)?$/i);
            var base = (subMatch && subMatch[1]) ? subMatch[1] : "/";
            return base + "blog/index.html";
        }

        function getParam(param) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        function formatDate(dateStr) {
            try {
                var d = new Date(dateStr.replace(" ", "T"));
                return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
            } catch (e) {
                return dateStr || "Recent";
            }
        }

        function copyArticleLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                var btn = document.getElementById("copyLinkBtn");
                btn.innerHTML = '<span class="material-symbols-outlined text-[14px] text-emerald-600">check</span><span class="text-emerald-700">Copied!</span>';
                setTimeout(function() {
                    btn.innerHTML = '<span class="material-symbols-outlined text-[14px]">link</span><span>Copy Link</span>';
                }, 2000);
            });
        }

        function shareArticle(network) {
            var url = encodeURIComponent(window.location.href);
            var title = encodeURIComponent(document.title);
            if (network === "twitter") {
                window.open("https://twitter.com/intent/tweet?url=" + url + "&text=" + title, "_blank");
            } else if (network === "linkedin") {
                window.open("https://www.linkedin.com/sharing/share-offsite/?url=" + url, "_blank");
            }
        }

        function loadPostDetails() {
            var posts = getActivePosts();
            if (!posts.length) {
                document.getElementById("notFoundContainer").classList.remove("hidden");
                document.getElementById("articleContainer").classList.add("hidden");
                return;
            }

            var slug = getParam("slug");
            var id = parseInt(getParam("id") || "0", 10);

            var post = null;
            if (slug) {
                post = posts.find(function(p) { return p.slug === slug; });
            } else if (id > 0) {
                post = posts.find(function(p) { return p.id === id; });
            } else {
                post = posts[0]; // Default to first post
            }

            if (!post) {
                document.getElementById("notFoundContainer").classList.remove("hidden");
                document.getElementById("articleContainer").classList.add("hidden");
                return;
            }

            // Sync Breadcrumb Links
            var blogListLink = getBlogListLink();
            var bBlog = document.getElementById("breadcrumbBlog");
            if (bBlog) bBlog.href = blogListLink;
            var rViewAll = document.getElementById("relatedViewAllBtn");
            if (rViewAll) rViewAll.href = blogListLink;
            var nfBtn = document.getElementById("notFoundReturnBtn");
            if (nfBtn) nfBtn.href = blogListLink;

            // Populate Document Meta
            document.title = post.title + " | GlobalMed Entry Ltd";
            var metaDesc = document.getElementById("metaDescription");
            if (metaDesc) metaDesc.setAttribute("content", (post.excerpt || "").slice(0, 160));

            // Populate Article Content
            document.getElementById("breadcrumbCategory").textContent = post.category || "Admissions";
            document.getElementById("articleCategoryBadge").textContent = post.category || "Article";
            document.getElementById("articleReadTime").textContent = post.read_time || "5 min read";
            document.getElementById("articleDate").textContent = formatDate(post.created_at);
            document.getElementById("articleTitle").textContent = post.title;
            document.getElementById("articleExcerpt").textContent = post.excerpt;
            document.getElementById("articleAuthor").textContent = post.author || "Admissions Directorate";
            document.getElementById("articleFeaturedImage").src = post.featured_image;
            document.getElementById("articleFeaturedImage").alt = post.title;
            document.getElementById("articleContent").innerHTML = post.content || ("<p>" + post.excerpt + "</p>");

            // Populate Related Posts (3 posts excluding current)
            var related = posts.filter(function(p) { return p.id !== post.id; }).slice(0, 3);
            var relGrid = document.getElementById("relatedPostsGrid");
            relGrid.innerHTML = "";

            for (var i = 0; i < related.length; i++) {
                var rp = related[i];
                relGrid.innerHTML += 
                '<div class="bg-white rounded-[5px] border border-slate-200 overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">' +
                    '<div>' +
                        '<div class="aspect-16/9 overflow-hidden relative">' +
                            '<img src="' + rp.featured_image + '" alt="' + rp.title + '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />' +
                            '<span class="absolute top-2.5 left-2.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-[3px]">' +
                                rp.category +
                            '</span>' +
                        '</div>' +
                        '<div class="p-5 space-y-2">' +
                            '<h3 class="font-bold text-slate-950 text-sm group-hover:text-primary transition-colors line-clamp-2">' +
                                '<a href="' + getPostLink(rp.slug) + '">' + rp.title + '</a>' +
                            '</h3>' +
                            '<p class="text-xs text-slate-500 line-clamp-2">' + rp.excerpt + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="p-5 pt-0 border-t border-slate-100 flex items-center justify-between text-xs">' +
                        '<span class="text-slate-400 text-[11px]">' + formatDate(rp.created_at) + '</span>' +
                        '<a href="' + getPostLink(rp.slug) + '" class="font-bold text-primary hover:underline">Read &rarr;</a>' +
                    '</div>' +
                '</div>';
            }
        }

        // Initialize immediately
        loadPostDetails();

        document.addEventListener("DOMContentLoaded", function() {
            loadPostDetails();
        });
    </script>

    <!-- Centralized Layout Loader -->
    <script src="../layout.js"></script>
</body>
</html>`;

fs.writeFileSync(path.join(__dirname, '../blog/index.html'), indexHtml);
console.log('Successfully wrote blog/index.html (' + indexHtml.length + ' bytes)');

fs.writeFileSync(path.join(__dirname, '../blog/post.html'), postHtml);
console.log('Successfully wrote blog/post.html (' + postHtml.length + ' bytes)');

// Also write post.html at root as fallback
fs.writeFileSync(path.join(__dirname, '../post.html'), postHtml);
console.log('Successfully wrote post.html (' + postHtml.length + ' bytes)');
