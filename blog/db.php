<?php
/**
 * GlobalMed Entry - Database & Models (SQLite / PDO)
 * 
 * Modular and lightweight: Uses SQLite so it runs out-of-the-box on standard PHP.
 * Matches WordPress core schema conventions for straightforward WP migration later.
 */

// Protect direct database file access
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$htaccess = $dataDir . '/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
}

function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $dbPath = __DIR__ . '/data/blog.db';
        try {
            $pdo = new PDO("sqlite:" . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("PRAGMA foreign_keys = ON;");
        } catch (PDOException $e) {
            die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

// Auto-initialize schema if not exists
function init_database() {
    $db = get_db_connection();

    // 1. Posts Table (mirrors WordPress wp_posts structure)
    $db->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        post_type TEXT DEFAULT 'article', -- 'article', 'news', 'event', 'resource'
        category TEXT DEFAULT 'General',
        excerpt TEXT,
        content TEXT,
        featured_image TEXT,
        author TEXT DEFAULT 'Admissions Directorate',
        status TEXT DEFAULT 'published', -- 'published', 'draft'
        show_on_home INTEGER DEFAULT 0, -- 1 = Show on homepage
        home_section TEXT DEFAULT 'articles', -- 'articles' (3 featured cards), 'news' (latest updates column)
        home_order INTEGER DEFAULT 0,
        read_time TEXT DEFAULT '5 min read',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Users Table (for protected Admin authentication)
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        email TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Login Attempts Table (rate limiting / security lockout)
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address TEXT NOT NULL,
        attempt_time INTEGER NOT NULL
    )");

    // Seed default admin if table is empty
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $defaultUser = 'admin';
        $defaultPass = 'GlobalMed2026!';
        $hash = password_hash($defaultPass, PASSWORD_DEFAULT);
        $insertUser = $db->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
        $insertUser->execute([$defaultUser, $hash, 'info@globalmedentry.com']);
    }

    // Seed initial posts if empty
    $postCount = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    if ($postCount == 0) {
        $samplePosts = [
            // Homepage 3 Featured Articles
            [
                'title' => 'Choosing the Right European Medical School',
                'slug' => 'choosing-the-right-european-medical-school',
                'post_type' => 'article',
                'category' => 'Admissions Guide',
                'excerpt' => 'Compare entry criteria, tuition fees, clinical hospital partnerships, and living costs across Bulgaria, Romania, Poland, and Italy.',
                'content' => '<p>Choosing where to study medicine in Europe is one of the most critical decisions an aspiring doctor will make. With dozens of accredited state faculties offering 6-year English-taught Doctor of Medicine (MD) degrees across Bulgaria, Romania, and Eastern Europe, understanding how each institution aligns with your clinical goals is paramount.</p>
<h3>1. Clinical Hospital Infrastructure</h3>
<p>Ensure your chosen university possesses its own multi-disciplinary university hospital network. For example, Medical University of Plovdiv and Medical University of Sofia provide early direct bedside exposure across tertiary referral university hospitals, preparing candidates with significant patient contact hours.</p>
<h3>2. EU Directive 2005/36/EC Compliance</h3>
<p>Every European medical qualification you consider must fulfill EU Directive 2005/36/EC, requiring a minimum of 5,500 hours of theoretical and practical instruction. This compliance is essential for direct recognition and licensing pathways back into the UK NHS through the UKMLA.</p>
<h3>3. Tuition Affordability & Living Standards</h3>
<p>European medical faculties remain substantially more accessible than private overseas alternatives, with annual state tuition averaging €7,500 to €10,000 and cost of living in vibrant university cities like Plovdiv and Sofia averaging just £400 to £600 per month.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80',
                'author' => 'European Admissions Directorate',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'articles',
                'home_order' => 1,
                'read_time' => '6 min read'
            ],
            [
                'title' => 'Cost of Studying Medicine & Dentistry Abroad',
                'slug' => 'cost-of-studying-medicine-and-dentistry-abroad',
                'post_type' => 'article',
                'category' => 'Tuition & Finance',
                'excerpt' => 'Comprehensive cost-of-living breakdowns, flight routes, health insurance, and affordable European medical education guides.',
                'content' => '<p>Affordability remains one of the primary reasons hundreds of UK and international students choose European medical and dental faculties each year. Unlike UK universities with limited quotas and steep cost curves, European state universities offer transparent, fixed tuition structures.</p>
<h3>Tuition Fee Comparisons</h3>
<p>Medical programmes (6-Year MD) across accredited Bulgarian universities range from €8,000 to €10,000 per academic year, payable in two installments each semester. Master of Dental Medicine (DMD) degrees feature identical fee frameworks.</p>
<h3>Accommodation & Monthly Living Expenses</h3>
<p>Modern student apartments in Lozenets (Sofia) or Youth Hill (Plovdiv) range from €300 to €450 monthly for fully furnished private residences within walking distance of preclinical laboratories and dissection halls.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Finance & Advisory Directorate',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'articles',
                'home_order' => 2,
                'read_time' => '5 min read'
            ],
            [
                'title' => 'How to Prepare for University Entrance Exams',
                'slug' => 'how-to-prepare-for-university-entrance-exams',
                'post_type' => 'article',
                'category' => 'Exam Strategy',
                'excerpt' => 'Proven syllabus reviews, biology & chemistry multiple choice strategies, and mock papers curated by academic mentors.',
                'content' => '<p>European medical entrance examinations assess foundational competence in general and organic chemistry, human biology, cytology, genetics, and English medical terminology.</p>
<h3>Mastering the MCQ Format</h3>
<p>European entrance exams rely heavily on multiple-choice questions requiring rapid recall rather than long essay prose. Focus revision on cellular respiration, mendelian genetics, cardiovascular physiology, chemical equilibria, and stoichiometry.</p>
<h3>Diagnostic Testing & Timed Mock Runs</h3>
<p>GlobalMed Entry provides candidates with past entrance exam papers spanning recent years, complete with faculty-specific question banks and mock testing simulations under timed conditions.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Pre-Admissions Diagnostic Directorate',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'articles',
                'home_order' => 3,
                'read_time' => '7 min read'
            ],

            // Homepage Latest News & Updates items
            [
                'title' => 'GMC Registration & UKMLA Framework for UK Students Studying Medicine in Europe',
                'slug' => 'gmc-registration-and-ukmla-framework-for-european-graduates',
                'post_type' => 'news',
                'category' => 'Regulatory Update',
                'excerpt' => 'Official guidance on recognition and Foundation Programme allocations under the UKMLA framework for 2026/2027.',
                'content' => '<p>The UK General Medical Council (GMC) framework for international and European medical graduates establishes clear guidelines under the Medical Licensing Assessment (UKMLA). Graduates holding primary medical qualifications complying with EU Directive 2005/36/EC follow verified registration pathways to secure Foundation Year training posts.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Clinical Advisory Directorate',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'news',
                'home_order' => 1,
                'read_time' => '4 min read'
            ],
            [
                'title' => 'Plovdiv vs Sofia: Which Bulgarian Medical University Fits You Best?',
                'slug' => 'plovdiv-vs-sofia-which-bulgarian-medical-university-fits-you-best',
                'post_type' => 'news',
                'category' => 'University Comparison',
                'excerpt' => 'Deep-dive into curriculum style, entrance exam formats, and living costs in Plovdiv and Sofia.',
                'content' => '<p>Comparing Bulgaria’s premier medical faculties: Sofia Medical University offers the bustling energy of a major European capital, while Plovdiv Medical University boasts state-of-the-art simulation training centers and a compact, walkable campus lifestyle.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80',
                'author' => 'European Admissions Directorate',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'news',
                'home_order' => 2,
                'read_time' => '5 min read'
            ],
            [
                'title' => 'Tuition Fees & Living Expenses: 2026/2027 European Medical University Breakdown',
                'slug' => 'tuition-fees-and-living-expenses-2026-2027-breakdown',
                'post_type' => 'news',
                'category' => 'Financial Guide',
                'excerpt' => 'Transparent overview of tuition, rent, groceries, and travel expenses across partner faculties.',
                'content' => '<p>A complete financial guide for prospective medical candidates and parents, detailing exact tuition schedules, bank transfer requirements, and budget planning for the 2026/2027 academic intake.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Finance Directorate',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'news',
                'home_order' => 3,
                'read_time' => '4 min read'
            ],
            [
                'title' => 'Medical University Entrance Exam Preparation Guides & Sample Papers',
                'slug' => 'medical-university-entrance-exam-preparation-guides-sample-papers',
                'post_type' => 'news',
                'category' => 'Examination Support',
                'excerpt' => 'Biology and Chemistry multiple choice question strategies from top scorers.',
                'content' => '<p>Access verified sample papers, topic-by-topic question banks, and revision strategies designed to ensure candidates secure top scores on university entrance examinations.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Academic Tutoring Division',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'news',
                'home_order' => 4,
                'read_time' => '4 min read'
            ],
            [
                'title' => 'Dental School in Europe: Entry Requirements, Clinical Hours & GDC Licensure',
                'slug' => 'dental-school-in-europe-entry-requirements-clinical-hours-gdc',
                'post_type' => 'news',
                'category' => 'Dental Education',
                'excerpt' => 'Complete pathway for UK applicants seeking 5 to 6-year DMD degrees.',
                'content' => '<p>Dental medicine education in Europe features cutting-edge phantom head simulation laboratories, rigorous conservative dentistry training, and extensive patient clinics fulfilling General Dental Council benchmarks.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Dental Education Division',
                'status' => 'published',
                'show_on_home' => 1,
                'home_section' => 'news',
                'home_order' => 5,
                'read_time' => '5 min read'
            ],

            // Sample Events & Resources
            [
                'title' => 'Live Webinar: European Medical Admissions & NHS Practice 2026',
                'slug' => 'live-webinar-european-medical-admissions-nhs-2026',
                'post_type' => 'event',
                'category' => 'Admissions Event',
                'excerpt' => 'Join our senior admissions directors for an interactive Q&A covering entrance exams, visa processing, and GMC licensing.',
                'content' => '<p>Join GlobalMed Entry Ltd for our flagship online briefing session. Prospective applicants and parents will learn how to navigate entrance exams, understand university accreditation under EU Directive 2005/36/EC, and plan clinical rotations.</p>
<h3>Webinar Highlights:</h3>
<ul>
<li>Direct entrance exam format breakdowns for Sofia, Plovdiv, and Varna</li>
<li>Live demonstration of our diagnostic MCQ revision portal</li>
<li>Interactive Q&A on UKMLA licensing and Foundation Programme matching</li>
</ul>',
                'featured_image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Admissions Directorate',
                'status' => 'published',
                'show_on_home' => 0,
                'home_section' => 'articles',
                'home_order' => 0,
                'read_time' => 'Event'
            ],
            [
                'title' => '2026 European Medical University Prospectus & Syllabus Pack',
                'slug' => '2026-european-medical-university-prospectus-syllabus-pack',
                'post_type' => 'resource',
                'category' => 'Admissions Pack',
                'excerpt' => 'Official curriculum outlines, preclinical laboratory guides, and tuition schedules for all partner medical faculties.',
                'content' => '<p>This comprehensive admissions prospectus outlines course modules, semester structures, clinical rotation timetables, and campus facilities across leading European state universities.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=1200&q=80',
                'author' => 'Admissions Directorate',
                'status' => 'published',
                'show_on_home' => 0,
                'home_section' => 'news',
                'home_order' => 0,
                'read_time' => 'Downloadable'
            ]
        ];

        $insertStmt = $db->prepare("INSERT INTO posts 
            (title, slug, post_type, category, excerpt, content, featured_image, author, status, show_on_home, home_section, home_order, read_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($samplePosts as $p) {
            $insertStmt->execute([
                $p['title'],
                $p['slug'],
                $p['post_type'],
                $p['category'],
                $p['excerpt'],
                $p['content'],
                $p['featured_image'],
                $p['author'],
                $p['status'],
                $p['show_on_home'],
                $p['home_section'],
                $p['home_order'],
                $p['read_time']
            ]);
        }
    }
}

// Auto-run init
init_database();
