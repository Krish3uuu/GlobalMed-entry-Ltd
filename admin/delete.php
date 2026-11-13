<?php
/**
 * GlobalMed Entry - Post Deletion Handler
 */
require_once __DIR__ . '/auth.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($csrf)) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db = get_db_connection();
            $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Post permanently deleted.');
        }
    } else {
        set_flash('error', 'Security token invalid. Action aborted.');
    }
}

header("Location: index.php");
exit;
