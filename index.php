<?php 
require 'db.php'; 
// 在入口处屏蔽警告输出，防止污染 HTML
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);
?>
<!DOCTYPE html><html lang="zh">
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
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(opt('site_name','Journal')); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root { --accent: #1a1a1a; --soft: #888; --border: #f0f0f0; }
        body { background: #fff; margin: 0; padding: 0; font-family: -apple-system, "Noto Sans SC", sans-serif; -webkit-font-smoothing: antialiased; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 40px; }
        .hero { padding: 100px 0 60px; }
        .hero h1 { font-size: 3.5rem; font-weight: 800; letter-spacing: -2px; margin: 0; }
        .feed-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 60px 50px; }
        .post-card { cursor: pointer; border: 1px solid var(--border); border-radius: 20px; overflow: hidden; transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); background: #fff; display: flex; flex-direction: column; }
        .post-card:hover { transform: translateY(-10px); border-color: #ddd; box-shadow: 0 30px 60px rgba(0,0,0,0.06); }
        .post-thumb-wrap { width: 100%; aspect-ratio: 16/9; overflow: hidden; background: #fcfcfc; }
        .post-thumb { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }
        .post-content { padding: 40px; flex-grow: 1; }
        .post-content .date { font-size: 12px; color: var(--soft); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; display: block; }
        .post-content h3 { font-size: 1.6rem; margin: 0; line-height: 1.4; font-weight: 700; color: #000; }
        
        footer { margin: 120px 0 60px; padding-top: 50px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: flex-start; }
        .beian-wrap { display: flex; align-items: center; gap: 30px; margin-top: 15px; flex-wrap: wrap; }
        
        @media (max-width: 800px) { 
            footer { flex-direction: column; gap: 40px; }
            .feed-grid { grid-template-columns: 1fr; } 
            .hero h1 { font-size: 2.5rem; } 
            .post-content { padding: 30px; } 
            .container { padding: 0 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="hero"><h1><?php echo htmlspecialchars(opt('site_name','Journal.')); ?></h1></section>
        <div id="feed" class="feed-grid"></div>
        <div id="sentinel" style="height:50px;"></div>
        
        <footer style="margin-top:120px; padding: 30px 0 60px; border-top:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <div class="footer-left">
                <div style="font-weight: bold; color: #2f3542; margin-bottom: 8px;">
                    <?php echo htmlspecialchars(opt('copyright')); ?>
                </div>
                <div class="beian-wrap" style="display:flex; gap:20px;">
                    <?php if($icp = opt('icp')): ?>
                        <a href="<?php echo opt('icp_link','https://beian.miit.gov.cn/'); ?>" target="_blank" style="color:#a4b0be; text-decoration:none; font-size:12px;"><?php echo htmlspecialchars($icp); ?></a>
                    <?php endif; ?>
                    <?php if($ga = opt('ga')): ?>
                        <a href="<?php echo opt('ga_link','#'); ?>" target="_blank" style="color:#a4b0be; text-decoration:none; font-size:12px; display:flex; align-items:center;">
                            <img src="https://oss.rnm.tax/zhudetong/beian.png" style="width:14px; height:14px; margin-right:5px;"><?php echo htmlspecialchars($ga); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div style="text-align: right; line-height: 1.6;">
                <span style="color: #a4b0be; font-size: 12px;">
                    Powered by <strong>西鹤软件</strong><br>
                    归云 GuiYun Blog System<br>
                    Licensed under GPL v3.0.
                </span>
            </div>
        </footer>
    </div>

    <script>
        let page = 0, loading = false;
        async function load() {
            if(loading) return; loading = true;
            try {
                const r = await fetch('api_posts.php?page=' + page);
                if(!r.ok) throw new Error('Network error');
                const d = await r.json();
                
                if(!d || d.length === 0) { 
                    loading = true; // 停止加载
                    return; 
                } 

                d.forEach(p => {
                    const date = new Date(p.created * 1000).toLocaleDateString('zh-CN');
                    // 【优化】前端兜底：如果 img 为空或包含报错信息，则使用随机图
                    const cover = (p.img && p.img.length > 10 && !p.img.includes('Deprecated')) 
                                  ? p.img 
                                  : `https://picsum.photos/seed/${p.cid}/800/450`;

                    const html = `
                        <div class="post-card" onclick="location.href='s/${p.slug}/'">
                            <div class="post-thumb-wrap"><img src="${cover}" class="post-thumb" onerror="this.src='https://picsum.photos/seed/error/800/450'"></div>
                            <div class="post-content">
                                <span class="date">${date} · BY ${p.author}</span>
                                <h3>${p.title}</h3>
                            </div>
                        </div>`;
                    document.getElementById('feed').insertAdjacentHTML('beforeend', html);
                });
                page++; 
                loading = false;
            } catch (e) {
                console.error("加载失败:", e);
            }
        }
        
        const observer = new IntersectionObserver(e => {
            if(e[0].isIntersecting) load();
        }, { threshold: 0.1 });
        
        observer.observe(document.getElementById('sentinel'));
    </script>
</body></html>
