<?php
require __DIR__.'/config.php';
require_login();
$id=(int)($_GET['id']??0);
$tx=dbone('SELECT id,status,amount FROM wallet_topups WHERE id=? AND user_id=?',[$id,user()['id']]);
if(!$tx){http_response_code(404);exit('Không tìm thấy giao dịch');}
flash($tx['status']==='paid' ? 'Nạp tiền đã được đối soát và cộng số dư.' : 'Giao dịch đang chờ payOS xác nhận. Tải lại trang số dư để kiểm tra.');
go('wallet');
