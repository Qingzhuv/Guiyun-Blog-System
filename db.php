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
if (session_status() == PHP_SESSION_NONE) session_start();

// 全局屏蔽 Deprecated 警告，防止污染 HTML/URL
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);

$config_file = __DIR__ . '/config.php';
if (!file_exists($config_file) && basename($_SERVER['PHP_SELF']) != 'install.php') {
    header("Location: install.php"); exit;
}

if (file_exists($config_file)) {
    require_once $config_file;
    $db = new mysqli(DB_H, DB_U, DB_P, DB_N);
    if ($db->connect_error) {
        die("数据库连接失败: " . $db->connect_error);
    }
    $db->set_charset('utf8mb4');
}

function isAdmin() { 
    return (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'); 
}

/**
 * 【关键修复】获取设置项
 * 确保永远不会返回 null
 */
function opt($key, $default = '') {
    global $db;
    if (!$db) return $default;
    $stmt = $db->prepare("SELECT value FROM options WHERE name = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        // 使用 Null 合并运算符，确保即使数据库里是 NULL 也返回字符串
        return $row['value'] ?? $default;
    }
    return $default;
}
?>
