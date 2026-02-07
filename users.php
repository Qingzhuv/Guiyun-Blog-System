<?php 
require 'db.php';
session_start();

// 1. 权限拦截
if (!isAdmin()) { header("Location: admin.php"); exit; }

$tid = $_SESSION['tid'];
$my_uid = $_SESSION['uid'];

// 2. 处理新增逻辑
if (isset($_POST['add'])) {
    $n = $db->real_escape_string($_POST['n']); 
    // 建议：md5 较旧，若追求安全建议改用 password_hash，这里维持原逻辑
    $p = md5($_POST['p']);
    $r = $db->real_escape_string($_POST['r']);
    $db->query("INSERT INTO users (tid, name, pw, role) VALUES ($tid, '$n', '$p', '$r')");
    header("Location: users.php?msg=added");
    exit;
}

// 3. 处理删除逻辑
if (isset($_GET['del'])) {
    $uid = (int)$_GET['del'];
    // 不能删除自己，且只能删除属于自己租户的成员
    if($uid != $my_uid) {
        $db->query("DELETE FROM users WHERE uid = $uid AND tid = $tid");
    }
    header("Location: users.php?msg=deleted");
    exit;
}

$users = $db->query("SELECT * FROM users WHERE tid = $tid");
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
    <title>成员管理 - 归云后台</title>
    <style>
        :root { --sidebar-width: 240px; }
        body { margin: 0; background: #f4f7f6; font-family: -apple-system, sans-serif; display: flex; }

        /* 布局基础 */
        .sidebar-wrapper { width: var(--sidebar-width); height: 100vh; background: #fff; border-right: 1px solid #eee; position: fixed; left: 0; top: 0; z-index: 1000; transition: 0.3s; }
        .main { margin-left: var(--sidebar-width); flex: 1; padding: 30px; transition: 0.3s; width: 100%; box-sizing: border-box; }

        /* 新增表单适配 */
        .add-form { display: flex; gap: 15px; align-items: flex-end; padding: 25px; margin-bottom: 30px; }
        .form-group { flex: 1; display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 12px; color: #a4b0be; font-weight: bold; }

        /* 移动端顶部条 */
        .mobile-header { display: none; width: 100%; height: 60px; background: #fff; position: fixed; top: 0; left: 0; z-index: 999; border-bottom: 1px solid #eee; padding: 0 15px; align-items: center; box-sizing: border-box; }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .mobile-header { display: flex; }
            .sidebar-wrapper { left: -100%; }
            .sidebar-wrapper.active { left: 0; box-shadow: 20px 0 50px rgba(0,0,0,0.1); }
            .main { margin-left: 0; padding: 80px 15px 30px; }
            
            .add-form { flex-direction: column; align-items: stretch; }
            .add-form .btn { width: 100%; margin-top: 10px; }
            
            .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 998; }
            .overlay.active { display: block; }
        }

        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #f1f2f6; }
        th { background: #fafafa; font-size: 12px; color: #a4b0be; text-transform: uppercase; }
        .role-badge { padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .role-admin { background: #e3f2fd; color: #1e88e5; }
        .role-user { background: #f1f2f6; color: #747d8c; }
    </style>
</head>
<body>

<div class="mobile-header">
    <div id="menu-toggle" style="font-size: 24px; cursor: pointer; margin-right: 15px;">☰</div>
    <div style="font-weight: bold;">团队管理</div>
</div>

<div class="overlay" id="overlay"></div>

<div class="sidebar-wrapper" id="sidebar">
    <?php include 'sidebar.php'; ?>
</div>

<div class="main">
    <h2 style="margin-top:0;">团队成员管理</h2>
    
    <form method="post" class="card add-form">
        <div class="form-group">
            <label>新账号</label>
            <input name="n" class="input" placeholder="输入用户名" style="margin:0" required>
        </div>
        <div class="form-group">
            <label>初始密码</label>
            <input name="p" class="input" type="password" placeholder="输入密码" style="margin:0" required>
        </div>
        <div class="form-group">
            <label>职权角色</label>
            <select name="r" class="input" style="margin:0;">
                <option value="user">内容创作者 (作者)</option>
                <option value="admin">系统管理员</option>
            </select>
        </div>
        <button name="add" class="btn" style="height:42px; background:#2f3542; color:#fff; border:none; padding:0 25px; border-radius:8px; cursor:pointer;">新增成员</button>
    </form>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>用户名</th>
                    <th>角色</th>
                    <th style="text-align:right;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td style="font-weight:600; color:#2f3542;">
                        <?php echo htmlspecialchars($u['name']); ?>
                        <?php if($u['uid'] == $my_uid): ?> <small style="color:#a4b0be; font-weight:normal;">(你自己)</small><?php endif; ?>
                    </td>
                    <td>
                        <span class="role-badge <?php echo $u['role'] == 'admin' ? 'role-admin' : 'role-user'; ?>">
                            <?php echo $u['role'] == 'admin' ? '管理员' : '作者'; ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <?php if($u['uid'] != $my_uid): ?>
                            <a href="?del=<?php echo $u['uid']; ?>" 
                               onclick="return confirm('确定要移除该成员吗？该作者的文章将保留，但其将无法登录。')" 
                               style="color:#ff4757; text-decoration:none; font-size:14px; font-weight:bold;">移除</a>
                        <?php else: ?>
                            <span style="color:#ced4da; font-size:14px;">不可操作</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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
