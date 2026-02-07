<?php 
require 'db.php';
session_start(); // 修复：必须开启session才能读取uid

// 1. 权限与状态检查
if(!isset($_SESSION['uid'])){ header("Location: login.php"); exit; }

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tid = $_SESSION['tid']; 
$uid = $_SESSION['uid'];

$p = ['title' => '', 'slug' => '', 'text' => '', 'img' => '', 'is_top' => 0];

if ($id > 0) {
    $res = $db->query("SELECT * FROM contents WHERE cid = $id AND tid = $tid");
    if ($res && $res->num_rows > 0) $p = $res->fetch_assoc();
}

// 2. 处理保存逻辑
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $db->real_escape_string($_POST['title']);
    $text = $db->real_escape_string($_POST['text']);
    $html = $db->real_escape_string($_POST['html']); 
    $img = $db->real_escape_string($_POST['img']);
    $is_top = isset($_POST['is_top']) ? 1 : 0;
    
    // 【安全加固】防止路径穿越攻击：只允许小写字母、数字和中划线
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($_POST['slug']));
    if(empty($slug)) $slug = 'post-'.time();
    
    $time = time();

    if ($id > 0) {
        $old_slug = $p['slug'];
        $db->query("UPDATE contents SET title='$title', text='$text', html='$html', slug='$slug', img='$img', is_top=$is_top WHERE cid=$id");
        // 如果改了路径，同步重命名文件夹
        if ($old_slug !== $slug && is_dir("s/$old_slug")) rename("s/$old_slug", "s/$slug");
    } else {
        $db->query("INSERT INTO contents (aid, tid, title, slug, text, html, img, is_top, created) VALUES ($uid, $tid, '$title', '$slug', '$text', '$html', '$img', $is_top, $time)");
        $id = $db->insert_id;
    }

    $dir = 's/' . $slug;
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $static_code = '<?php $cid = ' . $id . '; include "../../post_template.php"; ?>';
    file_put_contents($dir . '/index.php', $static_code);

    header("Location: admin.php?msg=success");
    exit;
}
?>
<!DOCTYPE html>
<!-- 
/**
 * GuiYun Blog - 归云博客系统
 * * Copyright (C) 2026-现在  WESTCRAN西鹤软件 (https://www.westcran.tech | https://github.com/QingzhuV)
 * * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
 -->
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="style.css">
    <title>归云编辑器 - <?php echo $id ? '修改' : '撰写'; ?></title>
    <style>
        :root { --sidebar-width: 240px; }
        body { margin: 0; background: #f4f7f6; font-family: -apple-system, sans-serif; display: flex; }
        
        /* 布局适配 */
        .sidebar-wrapper { width: var(--sidebar-width); height: 100vh; background: #fff; border-right: 1px solid #eee; position: fixed; left: 0; top: 0; z-index: 1000; transition: 0.3s; }
        .main { margin-left: var(--sidebar-width); flex: 1; padding: 30px; transition: 0.3s; width: 100%; box-sizing: border-box; }

        /* 编辑器核心布局 */
        .editor-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .input-area { height: 600px; font-family: 'Consolas', 'Monaco', monospace; padding: 15px; line-height: 1.6; border: 1px solid #ddd; border-radius: 8px; width: 100%; box-sizing: border-box; resize: vertical; font-size: 14px; }
        #preview-area { height: 600px; padding: 15px; border: 1px solid #eee; border-radius: 8px; overflow-y: auto; background: #fff; box-sizing: border-box; }
        
        /* 表单控件适配 */
        .config-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .input-group { display: flex; flex-direction: column; gap: 5px; }
        .input-group label { font-size: 12px; color: #a4b0be; font-weight: bold; }

        /* 移动端顶部条 */
        .mobile-header { display: none; width: 100%; height: 60px; background: #fff; position: fixed; top: 0; left: 0; z-index: 999; border-bottom: 1px solid #eee; padding: 0 15px; align-items: center; box-sizing: border-box; }

        /* 响应式媒体查询 */
        @media (max-width: 1024px) {
            .editor-layout { grid-template-columns: 1fr; } /* 平板/手机端改为单列 */
            .input-area, #preview-area { height: 400px; }
        }

        @media (max-width: 768px) {
            .mobile-header { display: flex; }
            .sidebar-wrapper { left: -100%; }
            .sidebar-wrapper.active { left: 0; box-shadow: 20px 0 50px rgba(0,0,0,0.1); }
            .main { margin-left: 0; padding: 80px 15px 30px; }
            .config-grid { grid-template-columns: 1fr; } /* 配置项改为垂直堆叠 */
            .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 998; }
            .overlay.active { display: block; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <div id="menu-toggle" style="font-size: 24px; cursor: pointer; margin-right: 15px;">☰</div>
    <div style="font-weight: bold;">归云编辑器</div>
</div>

<div class="overlay" id="overlay"></div>

<div class="sidebar-wrapper" id="sidebar">
    <?php include 'sidebar.php'; ?>
</div>

<div class="main">
    <form id="writeForm" method="post" class="card" style="padding:25px; border-radius:16px; background:#fff; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <h2 style="margin-top:0;"><?php echo $id ? '修改文章' : '撰写新文'; ?></h2>
        
        <div class="input-group" style="margin-bottom:15px;">
            <label>文章标题</label>
            <input name="title" class="input" value="<?php echo htmlspecialchars($p['title']); ?>" placeholder="输入一个吸引人的标题..." required>
        </div>
        
        <div class="config-grid">
            <div class="input-group">
                <label>访问路径 (Slug)</label>
                <input name="slug" class="input" value="<?php echo htmlspecialchars($p['slug']); ?>" placeholder="例如: my-first-post" required>
            </div>
            <div class="input-group">
                <label>封面图链接</label>
                <input name="img" class="input" value="<?php echo htmlspecialchars($p['img']); ?>" placeholder="http://...">
            </div>
            <div class="input-group" style="flex-direction: row; align-items: center; padding-top: 20px;">
                <label style="cursor:pointer; display:flex; align-items:center; gap:8px; color:#2f3542;">
                    <input type="checkbox" name="is_top" <?php echo $p['is_top']?'checked':''; ?> style="width:18px; height:18px;"> 置顶显示
                </label>
            </div>
        </div>

        <input type="hidden" name="html" id="html-hidden">

        <div class="editor-layout">
            <div class="input-group">
                <label>Markdown 源码</label>
                <textarea id="markdown-input" name="text" class="input-area" placeholder="支持标准 Markdown 语法..."><?php echo htmlspecialchars($p['text']); ?></textarea>
            </div>
            <div class="input-group">
                <label>预览效果</label>
                <div id="preview-area" class="content"></div>
            </div>
        </div>

        <div style="margin-top:25px; display:flex; gap:15px; flex-wrap:wrap;">
            <button type="submit" class="btn" style="min-width:150px; background:#2f3542; color:#fff;">发布文章</button>
            <a href="admin.php" style="line-height:40px; color:#a4b0be; text-decoration:none; font-size:14px;">取消并返回</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    // 1. 侧边栏切换逻辑
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    if(menuToggle) {
        menuToggle.onclick = () => {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        };
        overlay.onclick = () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        };
    }

    // 2. 编辑器渲染逻辑
    const input = document.getElementById('markdown-input');
    const preview = document.getElementById('preview-area');
    const htmlHidden = document.getElementById('html-hidden');
    const form = document.getElementById('writeForm');

    function render() {
        // 使用 marked 渲染
        const rawHtml = marked.parse(input.value);
        preview.innerHTML = rawHtml;
        htmlHidden.value = rawHtml;
    }

    input.addEventListener('input', render);
    window.onload = render;

    form.onsubmit = function() {
        htmlHidden.value = marked.parse(input.value);
    };
</script>
</body>
</html>
