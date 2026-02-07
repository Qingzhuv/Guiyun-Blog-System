<?php 
require_once __DIR__ . '/db.php';
if (!in_array('ob_gzhandler', ob_list_handlers())) ob_start('ob_gzhandler');

// 安全检查
if(!isset($cid)) die("Access Denied");

// 查询文章
$res = $db->query("SELECT c.*, u.name as author FROM contents c LEFT JOIN users u ON c.aid = u.uid WHERE c.cid = $cid");
$p = $res->fetch_assoc();
if(!$p) die("Post Not Found");

/**
 * 兼容性处理：防止 NULL 注入 URL
 */
$post_title = htmlspecialchars($p['title'] ?? '未命名文章');
$raw_img = trim($p['img'] ?? ''); 

if (strlen($raw_img) < 10 || str_contains($raw_img, 'Deprecated')) {
    $post_cover = "https://picsum.photos/seed/" . $cid . "/1200/600";
} else {
    $post_cover = htmlspecialchars($raw_img);
}

// 上下篇导航
$prev_p = $db->query("SELECT title, slug FROM contents WHERE cid < $cid ORDER BY cid DESC LIMIT 1")->fetch_assoc();
$next_p = $db->query("SELECT title, slug FROM contents WHERE cid > $cid ORDER BY cid ASC LIMIT 1")->fetch_assoc();
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
    <title><?php echo $post_title; ?> - <?php echo htmlspecialchars(opt('site_name')); ?></title>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body { margin: 0; padding: 0; background: #fff; font-family: -apple-system, "Noto Sans SC", "PingFang SC", sans-serif; color: #2f3542; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; padding: 40px 20px; }
        
        .article-header { margin-bottom: 30px; }
        .article-header h1 { font-size: 2.2rem; margin: 0 0 15px 0; color: #1e272e; line-height: 1.3; font-weight: 800; }
        .meta { color: #a4b0be; font-size: 13px; display: flex; gap: 15px; margin-bottom: 25px; }

        .featured-image { width: 100%; height: auto; aspect-ratio: 16/9; object-fit: cover; border-radius: 16px; margin-bottom: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.07); background: #f8f9fa; }

        .content { font-size: 17px; line-height: 1.8; color: #2f3542; }
        .content img { max-width: 100%; height: auto; border-radius: 12px; margin: 20px 0; }
        .content h2 { margin-top: 40px; font-weight: 700; border-left: 4px solid #2f3542; padding-left: 15px; }
        
        /* 响应式导航 */
        .post-nav-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 50px; padding-top: 30px; border-top: 1px solid #f1f2f6; }
        .nav-card { text-decoration: none; display: flex; flex-direction: column; padding: 18px; border: 1px solid #f1f2f6; border-radius: 12px; transition: 0.2s; }
        .nav-card:hover { border-color: #2f3542; background: #fafafa; }
        .nav-card.empty { opacity: 0.5; border-style: dashed; }
        .nav-card .label { font-size: 11px; color: #a4b0be; text-transform: uppercase; margin-bottom: 8px; font-weight: bold; }
        .nav-card .title { font-size: 14px; font-weight: 600; color: #2f3542; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        footer { margin-top: 80px; padding: 30px 0; border-top: 1px solid #f1f2f6; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; }
        .footer-left b { display: block; margin-bottom: 8px; font-size: 14px; }
        .beian-wrap { display: flex; gap: 15px; flex-wrap: wrap; }
        .beian-link { text-decoration: none; color: #a4b0be; font-size: 11px; }
        .brand-right { text-align: right; color: #ced4da; font-size: 11px; flex-grow: 1; }

        @media (max-width: 600px) {
            .article-header h1 { font-size: 1.8rem; }
            .post-nav-grid { grid-template-columns: 1fr; }
            .featured-image { border-radius: 12px; }
            footer { flex-direction: column; align-items: flex-start; }
            .brand-right { text-align: left; }
        }
    </style>
</head>
<body>

<div class="container">
    <header class="article-header">
        <h1><?php echo $post_title; ?></h1>
        <div class="meta">
            <span>BY <?php echo htmlspecialchars($p['author'] ?? 'Admin'); ?></span>
            <span><?php echo date('Y-m-d', $p['created'] ?? time()); ?></span>
        </div>
    </header>

    <img src="<?php echo $post_cover; ?>" class="featured-image" alt="Article Cover">

    <article class="content">
        <?php echo $p['html'] ?? '<p>内容为空</p>'; ?>
    </article>

    <div class="post-nav-grid">
        <?php if($prev_p): ?>
            <a href="../<?php echo $prev_p['slug']; ?>/" class="nav-card">
                <span class="label">PREVIOUS</span>
                <span class="title"><?php echo htmlspecialchars($prev_p['title'] ?? ''); ?></span>
            </a>
        <?php else: ?>
            <div class="nav-card empty">
                <span class="label">PREVIOUS</span>
                <span class="title">已经是第一篇</span>
            </div>
        <?php endif; ?>

        <?php if($next_p): ?>
            <a href="../<?php echo $next_p['slug']; ?>/" class="nav-card" style="text-align: right;">
                <span class="label">NEXT</span>
                <span class="title"><?php echo htmlspecialchars($next_p['title'] ?? ''); ?></span>
            </a>
        <?php else: ?>
            <div class="nav-card empty" style="text-align: right;">
                <span class="label">NEXT</span>
                <span class="title">最后一篇了</span>
            </div>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <a href="../../" style="text-decoration: none; color: #2f3542; font-size: 14px; font-weight: 600; padding: 10px 20px; border: 1px solid #eee; border-radius: 30px;">← 返回首页</a>
    </div>

    <footer>
        <div class="footer-left">
            <b><?php echo htmlspecialchars(opt('copyright')); ?></b>
            <div class="beian-wrap">
                <?php if($icp = opt('icp')): ?>
                    <a class="beian-link" href="<?php echo opt('icp_link','https://beian.miit.gov.cn/'); ?>" target="_blank"><?php echo htmlspecialchars($icp); ?></a>
                <?php endif; ?>
                <?php if($ga = opt('ga')): ?>
                    <a class="beian-link" href="<?php echo opt('ga_link','#'); ?>" target="_blank" style="display:flex; align-items:center; gap:4px;">
                        <img src="https://oss.rnm.tax/zhudetong/beian.png" style="width:12px; height:12px;"> <?php echo htmlspecialchars($ga); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="brand-right">
            Powered by <strong>西鹤软件</strong> · GuiYun System <br>Licensed under GPL v3.0.
        </div>
    </footer>
</div>

</body>
</html>
