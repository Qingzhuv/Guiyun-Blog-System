<?php 
require 'db.php';
session_start(); // 确保 Session 环境

// 1. 权限拦截：仅限管理员
if (!isAdmin()) { 
    header("Location: admin.php"); 
    exit; 
}

// 2. 处理保存逻辑
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $k => $v) {
        $k = $db->real_escape_string($k);
        $v = $db->real_escape_string($v);
        $db->query("REPLACE INTO options (name, value) VALUES ('$k', '$v')");
    }
    header("Location: settings.php?msg=success"); 
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
    <title>系统设置 - 归云</title>
    <style>
        :root { --sidebar-width: 240px; --accent: #2f3542; }
        body { margin: 0; background: #f4f7f6; font-family: -apple-system, sans-serif; display: flex; }

        /* 基础布局 */
        .sidebar-wrapper { width: var(--sidebar-width); height: 100vh; background: #fff; border-right: 1px solid #eee; position: fixed; left: 0; top: 0; z-index: 1000; transition: 0.3s; }
        .main { margin-left: var(--sidebar-width); flex: 1; padding: 30px; transition: 0.3s; width: 100%; box-sizing: border-box; }

        /* 响应式表单栅格 */
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-size: 13px; color: #747d8c; font-weight: 600; margin-bottom: 8px; }
        
        /* 移动端条 */
        .mobile-header { display: none; width: 100%; height: 60px; background: #fff; position: fixed; top: 0; left: 0; z-index: 999; border-bottom: 1px solid #eee; padding: 0 15px; align-items: center; box-sizing: border-box; }

        @media (max-width: 768px) {
            .mobile-header { display: flex; }
            .sidebar-wrapper { left: -100%; }
            .sidebar-wrapper.active { left: 0; box-shadow: 20px 0 50px rgba(0,0,0,0.1); }
            .main { margin-left: 0; padding: 80px 15px 30px; }
            
            .settings-grid { grid-template-columns: 1fr; } /* 移动端改为单列 */
            
            .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 998; }
            .overlay.active { display: block; }
        }

        .card { background: #fff; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .input { width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .btn-save { background: var(--accent); color: #fff; border: none; cursor: pointer; transition: 0.3s; }
        .btn-save:hover { opacity: 0.9; }
    </style>
</head>
<body>

<div class="mobile-header">
    <div id="menu-toggle" style="font-size: 24px; cursor: pointer; margin-right: 15px;">☰</div>
    <div style="font-weight: bold;">全局配置</div>
</div>

<div class="overlay" id="overlay"></div>

<div class="sidebar-wrapper" id="sidebar">
    <?php include 'sidebar.php'; ?>
</div>

<div class="main">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap: wrap; gap:10px;">
        <h2 style="margin:0">全局配置</h2>
        <?php if(isset($_GET['msg'])): ?>
            <span style="color:#00b894; font-weight:600; background:#e6fffb; padding:5px 12px; border-radius:20px; font-size:13px;">✓ 配置已保存</span>
        <?php endif; ?>
    </div>
    
    <form method="post" class="card">
        <div class="settings-grid">
            <div class="input-group">
                <label>博客名称</label>
                <input name="site_name" class="input" value="<?php echo htmlspecialchars(opt('site_name')); ?>">
            </div>
            <div class="input-group">
                <label>版权信息</label>
                <input name="copyright" class="input" value="<?php echo htmlspecialchars(opt('copyright')); ?>">
            </div>
            
            <div class="input-group">
                <label>ICP备案号</label>
                <input name="icp" class="input" placeholder="京ICP备..." value="<?php echo htmlspecialchars(opt('icp')); ?>">
            </div>
            <div class="input-group">
                <label>ICP跳转链接</label>
                <input name="icp_link" class="input" value="<?php echo htmlspecialchars(opt('icp_link','https://beian.miit.gov.cn/')); ?>">
            </div>
            
            <div class="input-group">
                <label>公安备案号</label>
                <input name="ga" class="input" placeholder="京公网安备..." value="<?php echo htmlspecialchars(opt('ga')); ?>">
            </div>
            <div class="input-group">
                <label>公安跳转链接</label>
                <input name="ga_link" class="input" value="<?php echo htmlspecialchars(opt('ga_link')); ?>">
            </div>
        </div>
        
        <hr style="border:0; border-top:1px solid #f0f0f0; margin:25px 0;">
        
        <div class="input-group">
            <label>页头脚本注入 (CSS/Meta)</label>
            <textarea name="head_js" class="input" style="height:100px; font-family:monospace; resize: vertical;"><?php echo opt('head_js'); ?></textarea>
        </div>
        
        <div class="input-group">
            <label>页脚脚本注入 (Analytics/JS)</label>
            <textarea name="foot_js" class="input" style="height:100px; font-family:monospace; resize: vertical;"><?php echo opt('foot_js'); ?></textarea>
        </div>
        
        <button class="btn btn-save" style="width:100%; height:50px; font-size:16px; margin-top:10px; border-radius:12px">确认并保存系统配置</button>
    </form>
    
    <div style="text-align:center; color:#ccc; font-size:12px; margin-top:30px;">
        配置更改将即时应用于全站页面
    </div>
</div>

<script>
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
</script>

</body>
</html>
