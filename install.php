<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * 归云博客系统 (GuiYun Blog) 安装程序
 * Powered by 西鹤软件 (WestCrane)
 * 全方位兼容 PHP 8.1+
 */

// 1. 彻底屏蔽安装过程中的 Deprecated 警告，防止干扰安装逻辑
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);

if (file_exists('config.php')) {
    die("系统已安装。如需重新安装，请先手动删除 config.php。");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 强制转换为字符串，防止 null 传入 mysqli
    $h = (string)$_POST['h']; 
    $n = (string)$_POST['n']; 
    $u = (string)$_POST['u']; 
    $p = (string)$_POST['p'];
    
    $db = @new mysqli($h, $u, $p);
    if ($db->connect_error) {
        $error = "数据库连接失败: " . $db->connect_error;
    } else {
        $db->query("CREATE DATABASE IF NOT EXISTS `$n` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        if (!$db->select_db($n)) {
            $error = "无法切换到数据库: $n";
        } else {
            // 清理旧结构
            $db->query("DROP TABLE IF EXISTS `contents`, `options`, `users` ");

            // 使用安全哈希加密管理员密码
            $hashed_pw = password_hash($_POST['ap'], PASSWORD_DEFAULT);
            $site_name = $db->real_escape_string($_POST['st']);
            $admin_name = $db->real_escape_string($_POST['an']);

            $sqls = [
                "CREATE TABLE `options` (
                    `name` VARCHAR(50) NOT NULL PRIMARY KEY,
                    `value` TEXT
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                "CREATE TABLE `users` (
                    `uid` INT AUTO_INCREMENT PRIMARY KEY,
                    `tid` INT DEFAULT 1,
                    `name` VARCHAR(32) NOT NULL,
                    `pw` VARCHAR(255) NOT NULL,
                    `role` ENUM('admin','user') DEFAULT 'user'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                "CREATE TABLE `contents` (
                    `cid` INT AUTO_INCREMENT PRIMARY KEY,
                    `tid` INT DEFAULT 1,
                    `aid` INT NOT NULL,
                    `title` VARCHAR(200) NOT NULL,
                    `slug` VARCHAR(100) NOT NULL,
                    `text` LONGTEXT,
                    `html` LONGTEXT,
                    `img` VARCHAR(255) DEFAULT '', 
                    `is_top` TINYINT(1) DEFAULT 0,
                    `created` INT(11) NOT NULL,
                    UNIQUE KEY `slug_idx` (`slug`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                "INSERT INTO `users` (`uid`, `name`, `pw`, `role`) VALUES (1, '$admin_name', '$hashed_pw', 'admin');",
                "INSERT INTO `options` (`name`, `value`) VALUES ('site_name', '$site_name');",
                "INSERT INTO `options` (`name`, `value`) VALUES ('copyright', '© " . date('Y') . " $site_name');",
                "INSERT INTO `options` (`name`, `value`) VALUES ('icp', ''), ('ga', ''), ('icp_link', 'https://beian.miit.gov.cn/'), ('ga_link', '#');",
                
                // 【修改处】将默认 slug 改为 'test'
                "INSERT INTO `contents` (`tid`, `aid`, `title`, `slug`, `text`, `html`, `img`, `created`) VALUES (1, 1, '欢迎使用归云博客', 'test', '# 归云大吉\n欢迎使用西鹤软件打造的开源博客。', '<h1>归云大吉</h1><p>欢迎使用西鹤软件打造的开源博客。</p>', '', ".time().");"
            ];

            foreach ($sqls as $sql) {
                if (!$db->query($sql)) { $error = "SQL 失败: " . $db->error; break; }
            }

            if (!$error) {
                // 构建配置文件
                $config = "<?php\n"
                        . "define('DB_H', '$h');\n"
                        . "define('DB_U', '$u');\n"
                        . "define('DB_P', '$p');\n"
                        . "define('DB_N', '$n');\n";
                
                if (file_put_contents('config.php', $config)) {
                    // 【核心逻辑】物理创建 s/test/ 目录及 index.php 入口
                    if (!is_dir('s')) @mkdir('s', 0777, true);
                    if (!is_dir('s/test')) @mkdir('s/test', 0777, true);
                    
                    $index_content = '<?php $cid = 1; include "../../post_template.php"; ?>';
                    file_put_contents('s/test/index.php', $index_content);

                    header("Location: login.php?installed=1");
                    exit;
                } else {
                    $error = "无法写入 config.php，请检查目录权限。";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>归云 - 系统初始化</title>
    <style>
        body { background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; font-family: -apple-system, sans-serif; }
        .card { background: #fff; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 400px; box-sizing: border-box; }
        .input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #eee; border-radius: 10px; box-sizing: border-box; background: #fafafa; font-size: 14px; transition: 0.3s; }
        .input:focus { border-color: #2d3436; outline: none; background: #fff; }
        .btn { width: 100%; padding: 14px; background: #2d3436; color: #fff; border: none; border-radius: 10px; cursor: pointer; margin-top: 15px; font-weight: bold; font-size: 16px; }
        .btn:hover { opacity: 0.9; }
        .error { color: #d63031; background: #fff2f2; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; text-align: center; border: 1px solid #fab1a0; }
        h2 { margin: 0 0 5px 0; font-weight: 800; color: #2d3436; }
    </style>
</head>
<body>
<div class="card">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2>安装归云</h2>
        <p style="color:#a4b0be; font-size:12px; letter-spacing: 1px;">GUIYUN SYSTEM INITIALIZE</p>
    </div>
    
    <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    
    <form method="post">
        <label style="font-size: 12px; color: #636e72; font-weight: bold;">数据库配置</label>
        <input name="h" class="input" value="127.0.0.1" placeholder="数据库主机">
        <input name="n" class="input" placeholder="数据库名" required>
        <input name="u" class="input" placeholder="数据库用户名" required>
        <input name="p" class="input" type="password" placeholder="数据库密码">
        
        <div style="height:1px; background:#eee; margin:20px 0;"></div>
        
        <label style="font-size: 12px; color: #636e72; font-weight: bold;">站点与管理</label>
        <input name="st" class="input" placeholder="站点名称 (如: 我的博客)" required>
        <input name="an" class="input" placeholder="管理员账号" required>
        <input name="ap" class="input" type="password" placeholder="管理员密码" required>
        
        <button class="btn">立即部署系统</button>
    </form>
    
    <p style="text-align:center; font-size:11px; color:#b2bec3; margin-top:25px;">
        安装完成后，请务必删除本安装程序以保安全。<br>Powered by GuiYun Blog. Licensed under GPL v3.0.
    </p>
</div>
</body>
</html>
