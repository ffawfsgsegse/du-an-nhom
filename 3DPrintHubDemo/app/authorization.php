<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
/* PHÂN QUYỀN 3 VAI TRÒ: client | vendor | admin.
   Đọc role trực tiếp từ MySQL mỗi request, KHÔNG tin dữ liệu role do trình duyệt gửi. */
$currentUser=null;
if(!empty($_SESSION['uid'])){
 $currentUser=dbone('SELECT id,full_name,email,role,status FROM users WHERE id=?',[(int)$_SESSION['uid']]);
 if(!$currentUser||!(int)$currentUser['status']){unset($_SESSION['uid']);$currentUser=null;}
}
function user(): ?array { return $GLOBALS['currentUser']??null; }
function need_login(): array { if(!user()){flash('Bạn cần đăng nhập trước.','error');go('login');}return user(); }
function need_role(string ...$roles): array { $u=need_login(); if(!in_array($u['role'],$roles,true)){http_response_code(403);exit('403: Bạn không có quyền truy cập chức năng này.');}return $u; }
function vendor_of_user(): ?array { $u=user();return $u?dbone('SELECT * FROM vendors WHERE user_id=? ORDER BY id DESC LIMIT 1',[(int)$u['id']]):null; }
function approved_vendor(): array { need_role('vendor'); $v=vendor_of_user(); if(!$v||$v['status']!=='approved'){flash('Gian hàng của bạn chưa được duyệt.','error');go('vendor_dashboard');}return $v; }
