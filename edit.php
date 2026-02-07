<?php require 'db.php';
if (!isAdmin()) exit;
$cid = intval($_GET['id']);
$p = $db->query("SELECT * FROM contents WHERE cid = $cid")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $db->real_escape_string($_POST['title']);
    $text = $db->real_escape_string($_POST['text']);
    $new_slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($_POST['slug']));
    $old_slug = $p['slug'];

    $db->query("UPDATE contents SET title='$title', text='$text', slug='$new_slug' WHERE cid=$cid");

    // 目录更名与同步
    if ($old_slug !== $new_slug && is_dir("s/$old_slug")) rename("s/$old_slug", "s/$new_slug");
    $dir = "s/$new_slug";
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents($dir . '/index.php', "<?php \$cid = $cid; include '../../post_template.php'; ?>");

    header("Location: admin.php?msg=updated"); exit;
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><link rel="stylesheet" href="style.css"><title>编辑文章</title></head>
<body><div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <h2>编辑文章</h2>
        <form method="post" class="card">
            <label>标题</label><input name="title" class="input" value="<?php echo htmlspecialchars($p['title']); ?>" required>
            <label>Slug (URL路径)</label><input name="slug" class="input" value="<?php echo htmlspecialchars($p['slug']); ?>" required>
            <label>内容</label><textarea name="text" class="input" style="height:400px"><?php echo htmlspecialchars($p['text']); ?></textarea>
            <button class="btn">保存修改</button>
        </form>
    </div>
</div></body></html>
