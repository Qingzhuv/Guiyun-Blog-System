<?php 
require 'db.php';
session_start();

// 1. 安全检查
if (!isset($_SESSION['uid'])) { header("Location: login.php"); exit; }

$tid = $_SESSION['tid'];
$uid = $_SESSION['uid'];

// 2. 处理删除请求 (之前代码里漏掉的逻辑)
if (isset($_GET['del'])) {
    $del_id = (int)$_GET['del'];
    // 管理员可以删所有，普通用户只能删自己的
    if (isAdmin()) {
        $db->query("DELETE FROM contents WHERE cid = $del_id AND tid = $tid");
    } else {
        $db->query("DELETE FROM contents WHERE cid = $del_id AND tid = $tid AND aid = $uid");
    }
    header("Location: admin.php");
    exit;
}

// 3. 获取文章列表
$sql = isAdmin() 
    ? "SELECT c.*, u.name as author FROM contents c LEFT JOIN users u ON c.aid = u.uid WHERE c.tid = $tid ORDER BY c.is_top DESC, c.created DESC" 
    : "SELECT c.*, u.name as author FROM contents c LEFT JOIN users u ON c.aid = u.uid WHERE c.tid = $tid AND c.aid = $uid ORDER BY c.is_top DESC, c.created DESC";
$posts = $db->query($sql);
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
    <title>内容管理 - <?php echo htmlspecialchars(opt('site_name')); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root { --sidebar-width: 240px; --accent: #2f3542; }
        body { margin: 0; background: #f4f7f6; font-family: -apple-system, sans-serif; display: flex; }

        .sidebar-wrapper { 
            width: var(--sidebar-width); height: 100vh; background: #fff; 
            border-right: 1px solid #eee; position: fixed; left: 0; top: 0; z-index: 1000; transition: 0.3s;
        }
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 30px; transition: 0.3s; width: 100%; box-sizing: border-box; }
        
        .mobile-header { 
            display: none; width: 100%; height: 60px; background: #fff; 
            position: fixed; top: 0; left: 0; z-index: 999; border-bottom: 1px solid #eee;
            padding: 0 15px; align-items: center; justify-content: space-between; box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .mobile-header { display: flex; }
            .sidebar-wrapper { left: -100%; }
            .sidebar-wrapper.active { left: 0; box-shadow: 20px 0 50px rgba(0,0,0,0.1); }
            .main-content { margin-left: 0; padding: 80px 15px 30px; }
            .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 998; }
            .overlay.active { display: block; }
            .hide-on-mobile { display: none; }
        }

        .table-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 15px; text-align: left; border-bottom: 1px solid #f1f2f6; }
        th { background: #fafafa; font-size: 13px; color: #747d8c; text-transform: uppercase; }
        
        /* 按钮小样式 */
        .action-link { text-decoration: none; font-size: 14px; margin-left: 12px; transition: 0.2s; }
        .action-link:hover { opacity: 0.7; }
        .tag-top { background: #ffa502; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px; vertical-align: middle; margin-right: 5px; }
    </style>
</head>
<body>

<div class="mobile-header">
    <div id="menu-toggle" style="font-size: 24px; cursor: pointer;">☰</div>
    <div style="font-weight: bold;">内容管理</div>
    <div style="width: 24px;"></div>
</div>

<div class="overlay" id="overlay"></div>

<div class="sidebar-wrapper" id="sidebar">
    <?php include 'sidebar.php'; ?>
</div>

<main class="main-content">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin:0">内容管理</h2>
        <a href="write.php" class="btn" style="padding: 10px 20px; background: var(--accent); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">+ 撰写新文章</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>文章标题</th>
                    <th class="hide-on-mobile">作者</th>
                    <th style="text-align:right">管理操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $posts->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div>
                            <?php if($p['is_top']): ?><span class="tag-top">置顶</span><?php endif; ?>
                            <span style="font-weight:600; color:#2d3436;"><?php echo htmlspecialchars($p['title']); ?></span>
                        </div>
                        <div style="font-size:11px; color:#b2bec3; margin-top:4px">路径: /s/<?php echo $p['slug']; ?>/</div>
                    </td>
                    <td class="hide-on-mobile" style="color:#747d8c; font-size:14px;"><?php echo htmlspecialchars($p['author']); ?></td>
                    <td style="text-align:right; white-space:nowrap">
                        <a href="s/<?php echo $p['slug']; ?>/" target="_blank" class="action-link" style="color:#2ed573;">预览</a>
                        
                        <a href="write.php?id=<?php echo $p['cid']; ?>" class="action-link" style="color:#1e90ff;">编辑</a>
                        
                        <a href="admin.php?del=<?php echo $p['cid']; ?>" onclick="return confirm('确定要删除这篇文章吗？此操作不可撤销。')" class="action-link" style="color:#ff4757;">删除</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($posts->num_rows == 0): ?>
                <tr>
                    <td colspan="3" style="text-align:center; padding:50px; color:#a4b0be;">暂无文章内容</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    menuToggle.onclick = function() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    };

    overlay.onclick = function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    };
</script>

</body>
</html>
