<?php
/**
 * GlobalMed Entry - Quick 1-Click Homepage Toggle Handler
 */
require_once __DIR__ . '/auth.php';
check_auth();

$id = (int)($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if ($id > 0 && verify_csrf_token($csrf)) {
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT show_on_home, title FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if ($post) {
        $newStatus = $post['show_on_home'] ? 0 : 1;
        $up = $db->prepare("UPDATE posts SET show_on_home = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $up->execute([$newStatus, $id]);

        $msg = $newStatus 
            ? "Added '{$post['title']}' to the Homepage!" 
            : "Removed '{$post['title']}' from the Homepage.";
        set_flash('success', $msg);
    }
} else {
    set_flash('error', 'Security session invalid. Action aborted.');
}

header("Location: index.php");
exit;
