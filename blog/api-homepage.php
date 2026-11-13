<?php
/**
 * GlobalMed Entry - Homepage Dynamic Feed API (JSON)
 * 
 * Allows index.html or external scripts to dynamically retrieve
 * whatever articles, news, events, or resources the admin selects.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';
$db = get_db_connection();

try {
    // 1. Featured Articles (for top navy 3-card grid)
    $stmtArticles = $db->query("
        SELECT id, title, slug, post_type, category, excerpt, featured_image, read_time, author, created_at
        FROM posts
        WHERE status = 'published' AND show_on_home = 1 AND home_section = 'articles'
        ORDER BY home_order ASC, created_at DESC
        LIMIT 3
    ");
    $articles = $stmtArticles->fetchAll();

    // Fallback if none set on home
    if (empty($articles)) {
        $stmtArticles = $db->query("
            SELECT id, title, slug, post_type, category, excerpt, featured_image, read_time, author, created_at
            FROM posts
            WHERE status = 'published' AND post_type = 'article'
            ORDER BY created_at DESC
            LIMIT 3
        ");
        $articles = $stmtArticles->fetchAll();
    }

    // 2. Latest News & Updates (for right column list)
    $stmtNews = $db->query("
        SELECT id, title, slug, post_type, category, excerpt, created_at
        FROM posts
        WHERE status = 'published' AND show_on_home = 1 AND home_section = 'news'
        ORDER BY home_order ASC, created_at DESC
        LIMIT 6
    ");
    $news = $stmtNews->fetchAll();

    if (empty($news)) {
        $stmtNews = $db->query("
            SELECT id, title, slug, post_type, category, excerpt, created_at
            FROM posts
            WHERE status = 'published' AND post_type = 'news'
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $news = $stmtNews->fetchAll();
    }

    echo json_encode([
        'success' => true,
        'articles' => $articles,
        'news' => $news
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
