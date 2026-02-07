<?php
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
if(session_status() == PHP_SESSION_NONE) session_start();

// 直接从 Session 获取角色，增加默认值兜底
$role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'user';

// 双重验证：函数存在则用函数，不存在则看 Session
$show_admin_menu = false;
if (function_exists('isAdmin')) {
    $show_admin_menu = isAdmin();
} else {
    $show_admin_menu = ($role === 'admin');
}

$current = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="brand">
        <h2><?php echo opt('site_name', '归云'); ?></h2>
        <div class="role-tag">
            <?php echo $role === 'admin' ? 'SYSTEM ADMIN' : 'CREATOR'; ?>
        </div>
    </div>

    <nav>
        <a href="admin.php" class="nav-link <?php echo ($current=='admin.php' || ($current=='write.php' && isset($_GET['id']))) ? 'active' : ''; ?>">内容管理</a>
        <a href="write.php" class="nav-link <?php echo ($current=='write.php' && !isset($_GET['id'])) ? 'active' : ''; ?>">撰写新文</a>
        
        <?php if($show_admin_menu): ?>
            <div class="nav-divider"></div>
            <a href="users.php" class="nav-link <?php echo $current=='users.php'?'active':''; ?>">成员管理</a>
            <a href="settings.php" class="nav-link <?php echo $current=='settings.php'?'active':''; ?>">系统设置</a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="index.php" target="_blank" class="nav-link" style="color:#a4b0be; font-size:13px;">查看首页</a>
        <a href="logout.php" class="nav-link" style="color:#ff4757; font-size:13px;">退出登录</a>
        
        <div class="brand-copy">
            Powered by<br>
            <strong>西鹤软件 · 归云</strong>
        </div>
    </div>
</div>

<style>
    /* 保持白色极简风格 */
    .sidebar { width: 240px; background: #fff; height: 100vh; position: fixed; border-right: 1px solid #f1f2f6; display: flex; flex-direction: column; }
    .brand { padding: 40px 25px 20px; }
    .brand h2 { margin: 0; font-size: 20px; color: #2d3436; font-weight: 800; }
    .role-tag { font-size: 10px; color: #a4b0be; margin-top: 5px; font-weight: bold; letter-spacing: 1px; }
    nav { padding: 10px 15px; flex: 1; }
    .nav-link { display: block; padding: 12px 15px; color: #57606f; text-decoration: none; font-size: 14px; border-radius: 10px; transition: 0.2s; }
    .nav-link:hover { background: #f8f9fa; color: #1e90ff; }
    .nav-link.active { background: #f1f2f6; color: #1e90ff; font-weight: bold; }
    .nav-divider { height: 1px; background: #f1f2f6; margin: 15px; }
    .sidebar-footer { padding: 20px 15px 30px; border-top: 1px solid #f8f9fa; }
    .brand-copy { margin-top: 20px; padding: 0 15px; font-size: 11px; color: #ced4da; line-height: 1.5; }
</style>
