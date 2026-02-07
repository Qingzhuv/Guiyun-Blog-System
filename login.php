<?php 
require 'db.php'; 
if (session_status() == PHP_SESSION_NONE) session_start();

$err = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = $db->real_escape_string($_POST['u']);
    $p = $_POST['p']; // 明文，用于后续 verify
    
    $res = $db->query("SELECT * FROM users WHERE name='$u' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        
        // 使用安全函数验证哈希后的密码
        if (password_verify($p, $user['pw'])) {
            session_regenerate_id(true); // 刷新ID，防止会话固定攻击
            $_SESSION['uid']  = $user['uid'];
            $_SESSION['tid']  = $user['tid'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            header("Location: admin.php");
            exit;
        } else {
            $err = "账号或密码错误";
        }
    } else {
        $err = "账号或密码错误";
    }
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
<html lang="zh"><head><meta charset="UTF-8"><title>登录 - 归云</title>
<style>
    body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: sans-serif; }
    .login-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); width: 320px; }
    .input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #eee; border-radius: 8px; box-sizing: border-box; }
    .btn { width: 100%; padding: 14px; background: #2f3542; color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 20px; }
    .err-msg { background: #fff2f2; color: #d63031; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; }
</style></head>
<body>
<div class="login-card">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="margin:0;">归云 · 登录</h2>
        <p style="color:#a4b0be; font-size:12px; margin-top:5px;">GuiYun Blog System</p>
    </div>
    <?php if($err): ?><div class="err-msg"><?php echo $err; ?></div><?php endif; ?>
    <form method="post">
        <input name="u" class="input" placeholder="账号" required autofocus>
        <input name="p" class="input" type="password" placeholder="密码" required>
        <button class="btn">进入管理后台</button>
    </form>
    <div style="margin-top: 30px; text-align: center; border-top: 1px solid #eee; padding-top: 20px; font-size: 12px; color: #ced4da;">
        Powered by <strong>西鹤软件</strong>
    </div>
</div>
</body></html>
