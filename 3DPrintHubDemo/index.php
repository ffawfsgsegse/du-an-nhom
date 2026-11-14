<?php
require __DIR__.'/config.php';
require __DIR__.'/payments.php';
require __DIR__.'/payos_lib.php';
require __DIR__.'/wallet_lib.php';
$page=$_GET['page']??'home';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
 verify();$action=$_POST['action']??'';
 if($action==='register'){$name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$pw=$_POST['password']??'';if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pw)<6)throw new Exception('Nhập họ tên, email hợp lệ và mật khẩu tối thiểu 6 ký tự');execute('INSERT INTO users(full_name,email,password,role) VALUES(?,?,?,"client")',[$name,$email,password_hash($pw,PASSWORD_DEFAULT)]);flash('Đăng ký thành công, hãy đăng nhập');go('login');}
 if($action==='login'){$u=dbone('SELECT * FROM users WHERE email=?',[trim($_POST['email']??'')]);if(!$u||!password_verify($_POST['password']??'',$u['password'])||!$u['status'])throw new Exception('Email hoặc mật khẩu không đúng / tài khoản bị khóa');session_regenerate_id(true);$_SESSION['user']=['id'=>$u['id'],'full_name'=>$u['full_name'],'role'=>$u['role']];go($u['role']==='admin'?'admin':($u['role']==='vendor'?'seller':'home'));}
 if($action==='logout'){$_SESSION=[];session_destroy();header('Location: index.php');exit;}
 require_login();
 // Người bán khai báo tài khoản nhận tiền. Không bao giờ thay đổi tài khoản của đơn đã lập.
 if($action==='save_bank_account'){
  require_role('vendor');$v=vendor();
  if(!$v||$v['status']!=='approved')throw new Exception('Gian hàng chưa được duyệt');
  $bn=trim($_POST['bank_name']??'');$acc=preg_replace('/\s+/', '', $_POST['bank_account']??'');$holder=trim($_POST['bank_holder']??'');
  if($bn===''||strlen($bn)>100||!preg_match('/^[0-9]{4,25}$/',$acc)||$holder===''||strlen($holder)>120)throw new Exception('Ngân hàng, số tài khoản hoặc chủ tài khoản không hợp lệ.');
  execute('UPDATE vendors SET bank_name=?,bank_account=?,bank_account_holder=? WHERE id=?',[$bn,$acc,$holder,$v['id']]);
  flash('Đã cập nhật thông tin ngân hàng. Các đơn cũ vẫn giữ thông tin tại thời điểm mua.');go('seller');
 }
 // Khách xác nhận ĐÃ GỬI chuyển khoản, không tự đánh dấu ĐÃ NHẬN TIỀN.
 if($action==='payment_submit'){
  $oid=(int)($_POST['order_id']??0);
  $o=dbone('SELECT o.*,op.status AS payment_status FROM orders o JOIN order_payments op ON op.order_id=o.id WHERE o.id=? AND o.user_id=? AND o.payment_method="banking" AND op.payment_provider="manual"',[$oid,user()['id']]);
  if(!$o||$o['order_status']==='cancelled'||$o['payment_status']==='confirmed')throw new Exception('Không thể gửi thông báo chuyển khoản cho đơn này.');
  $mime=null;$binary=null;
  if(isset($_FILES['proof']) && $_FILES['proof']['error']!==UPLOAD_ERR_NO_FILE){
   $f=$_FILES['proof'];
   if($f['error']!==UPLOAD_ERR_OK||$f['size']<1||$f['size']>2*1024*1024)throw new Exception('Ảnh biên lai phải nhỏ hơn 2MB (JPG, PNG, WEBP).');
   $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
   if(!in_array($mime,['image/jpeg','image/png','image/webp'],true)||!getimagesize($f['tmp_name']))throw new Exception('File biên lai phải là hình ảnh hợp lệ.');
   $binary=file_get_contents($f['tmp_name']);if($binary===false)throw new Exception('Không thể đọc file biên lai.');
  }
  if($mime){
   execute('UPDATE order_payments SET status="submitted",receipt_mime=?,receipt_data=?,submitted_at=NOW(),review_note=NULL,reviewed_by=NULL,reviewed_at=NULL WHERE order_id=? AND status!="confirmed"',[$mime,$binary,$oid]);
  }else{
   execute('UPDATE order_payments SET status="submitted",receipt_mime=NULL,receipt_data=NULL,submitted_at=NOW(),review_note=NULL,reviewed_by=NULL,reviewed_at=NULL WHERE order_id=? AND status!="confirmed"',[$oid]);
  }
  flash('Đã gửi thông báo. Người bán/Admin sẽ đối soát ngân hàng trước khi xác nhận.');go('payment',['id'=>$oid]);
 }
 // Người bán sở hữu đơn hoặc Admin mới được xác nhận sau khi kiểm tra tiền về.
 if($action==='payment_review'){
  $oid=(int)($_POST['order_id']??0);$decision=$_POST['decision']??'';
  if(!in_array($decision,['confirmed','rejected'],true))throw new Exception('Quyết định không hợp lệ');
  $o=dbone('SELECT o.id,o.order_status,o.vendor_id,op.status,v.user_id AS vendor_user_id FROM orders o JOIN order_payments op ON op.order_id=o.id JOIN vendors v ON v.id=o.vendor_id WHERE o.id=? AND o.payment_method="banking" AND op.payment_provider="manual"',[$oid]);
  if(!$o||$o['order_status']==='cancelled'||$o['status']!=='submitted')throw new Exception('Không thể đối soát đơn này.');
  if(user()['role']!=='admin' && !(user()['role']==='vendor'&&(int)$o['vendor_user_id']===(int)user()['id']))throw new Exception('Bạn không có quyền đối soát đơn này.');
  $reason=trim($_POST['reason']??'');if(strlen($reason)>255)$reason=substr($reason,0,255);
  if($decision==='rejected' && !$reason)$reason='Chưa tìm thấy giao dịch khớp số tiền và nội dung chuyển khoản';
  global $pdo;$stmt=$pdo->prepare('UPDATE order_payments op JOIN orders o ON o.id=op.order_id SET op.status=?,op.reviewed_at=NOW(),op.reviewed_by=?,op.review_note=? WHERE op.order_id=? AND op.status="submitted" AND o.order_status!="cancelled"');
  $stmt->execute([$decision,user()['id'],$decision==='rejected'?$reason:null,$oid]);
  if($stmt->rowCount()!==1)throw new Exception('Trạng thái thanh toán vừa thay đổi, vui lòng tải lại.');
  flash($decision==='confirmed'?'Đã xác nhận tiền về tài khoản.':'Đã yêu cầu khách kiểm tra giao dịch.');go(user()['role']==='admin'?'admin':'seller');
 }
// ====== THANH TOÁN MoMo / QR NGÂN HÀNG - MÔ PHỎNG LOCALHOST ======
if(in_array($action,['demo_topup_start','demo_payment_confirm','demo_payment_refresh'],true)){
 if(!wallet_demo_enabled())throw new Exception('Thanh toán DEMO chỉ được dùng từ localhost trên máy Laragon. Không xử lý tiền thật.');
 if($action==='demo_topup_start'){
  if(user()['role']!=='client')throw new Exception('Chỉ tài khoản khách hàng được nạp số dư DEMO');
  $amount=(int)($_POST['amount']??0);$provider=$_POST['provider']??'';
  if($amount<10000||$amount>2000000||$amount%1000!==0||!in_array($provider,['momo','bank_qr'],true))throw new Exception('Số tiền hoặc phương thức nạp DEMO không hợp lệ');
  $pdo->beginTransaction();
  try{
   $fakeCode=random_int(10000000000,99999999999);
   execute('INSERT INTO wallet_topups(user_id,amount,status,payos_order_code,payment_link_id,checkout_url) VALUES(?,? ,"pending",?,?,"")',[(int)user()['id'],$amount,$fakeCode,'LOCAL-DEMO-'.bin2hex(random_bytes(6))]);
   $topupId=(int)$pdo->lastInsertId();
   execute('INSERT INTO demo_payments(topup_id,provider,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 15 MINUTE))',[$topupId,$provider]);
   execute('UPDATE wallet_topups SET checkout_url=? WHERE id=?',[url('demo_topup',['id'=>$topupId]),$topupId]);
   $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  go('demo_topup',['id'=>$topupId]);
 }
 $kind=$_POST['kind']??'';$id=(int)($_POST['id']??0);
 if(!in_array($kind,['order','topup'],true)||$id<1)throw new Exception('Đường dẫn thanh toán DEMO không hợp lệ');
 $back=$kind==='order'?'payment':'demo_topup';
 $pdo->beginTransaction();
 try{
  if($kind==='order'){
   $o=dbone('SELECT * FROM orders WHERE id=? AND user_id=? AND payment_method="banking" FOR UPDATE',[$id,(int)user()['id']]);
   $p=dbone('SELECT * FROM order_payments WHERE order_id=? FOR UPDATE',[$id]);
   $d=dbone('SELECT * FROM demo_payments WHERE order_id=? FOR UPDATE',[$id]);
   if(!$o||!$p||!$d||!in_array($p['payment_provider'],['demo_momo','demo_bank'],true))throw new Exception('Không tìm thấy giao dịch DEMO của bạn');
   if($o['order_status']==='cancelled')throw new Exception('Đơn hàng đã hủy, không thể thanh toán');
   $alreadyPaid=$d['status']==='paid' && $p['status']==='confirmed';
  }else{
   $t=dbone('SELECT * FROM wallet_topups WHERE id=? AND user_id=? FOR UPDATE',[$id,(int)user()['id']]);
   $d=dbone('SELECT * FROM demo_payments WHERE topup_id=? FOR UPDATE',[$id]);
   if(!$t||!$d)throw new Exception('Không tìm thấy yêu cầu nạp DEMO của bạn');
   $alreadyPaid=$d['status']==='paid' && $t['status']==='paid';
   if(!$alreadyPaid && $t['status']!=='pending')throw new Exception('Yêu cầu nạp không còn hiệu lực');
  }
  if(!$alreadyPaid){
   if($d['status']!=='pending')throw new Exception('Giao dịch đã thay đổi trạng thái');
   if($action==='demo_payment_refresh'){
    execute('UPDATE demo_payments SET expires_at=DATE_ADD(NOW(),INTERVAL 15 MINUTE) WHERE id=? AND status="pending"',[$d['id']]);
   }else{
    // Việc thanh toán thành công sau đây hoàn toàn giả lập, KHÔNG có webhook hay giao dịch ngân hàng.
    $stmt=$pdo->prepare('UPDATE demo_payments SET status="paid",confirmed_at=NOW() WHERE id=? AND status="pending" AND expires_at>=NOW()');
    $stmt->execute([$d['id']]);
    if($stmt->rowCount()!==1)throw new Exception('Mã DEMO đã hết hạn. Hãy làm mới mã rồi thử lại.');
    if($kind==='order'){
     if($p['status']!=='unpaid')throw new Exception('Đơn đã được xử lý thanh toán');
     execute('UPDATE order_payments SET status="confirmed",reviewed_at=NOW(),review_note="MÔ PHỎNG DEMO: không có giao dịch ngân hàng hoặc MoMo" WHERE order_id=? AND status="unpaid"',[$id]);
    }else{
     $amount=(int)$t['amount'];
     execute('UPDATE wallet_topups SET status="paid",paid_at=NOW(),bank_reference=? WHERE id=? AND status="pending"',['DEMO-'.strtoupper($d['provider']).'-'.$id,$id]);
     wallet_post_locked((int)user()['id'],$amount,'credit','topup','wallet_topups',$id,'Nạp tiền MÔ PHỎNG '.$d['provider'].' #'.$id);
    }
   }
  }
  $pdo->commit();
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
 if($action==='demo_payment_confirm')flash('Thanh toán DEMO thành công! Không có tiền thật được chuyển.');
 go($back,['id'=>$id]);
}
// ====== KẾT THÚC THANH TOÁN DEMO ======
 if($action==='wallet_demo_credit'){
  require_role('admin');
  if(!wallet_demo_enabled())throw new Exception('Chỉ được cấp số dư DEMO trên localhost.');
  $buyerId=(int)($_POST['user_id']??0);$amount=(int)($_POST['amount']??0);
  if($amount<1000 || $amount>200000 || !dbone('SELECT id FROM users WHERE id=? AND role="client" AND status=1',[$buyerId]))throw new Exception('Khách hoặc số tiền DEMO không hợp lệ');
  $pdo->beginTransaction();
  try {
    // Reference id is generated in ledger table itself in this demo only.
    $token=random_int(100000000,999999999);
    wallet_post_locked($buyerId,$amount,'credit','demo_credit','demo',$token,'Cộng số dư DEMO (không có giao dịch ngân hàng)');
    $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  flash('Đã cộng số dư DEMO, không phải tiền thật.');go('admin');
 }
 if($action==='cod_collect'){
  require_role('vendor');$v=vendor();if(!$v||$v['status']!=='approved')throw new Exception('Gian hàng không hợp lệ');
  $oid=(int)($_POST['id']??0);
  $pdo->beginTransaction();
  try{
   $o=dbone('SELECT * FROM orders WHERE id=? AND vendor_id=? FOR UPDATE',[$oid,$v['id']]);
   if(!$o||$o['payment_method']!=='cod'||$o['order_status']!=='shipping')throw new Exception('Chỉ đánh dấu thu COD cho đơn đang giao');
   if(dbone('SELECT order_id FROM cod_collections WHERE order_id=?',[$oid]))throw new Exception('Đơn này đã được ghi nhận COD');
   $note=trim((string)($_POST['note']??''));
   execute('INSERT INTO cod_collections(order_id,collected_by,note) VALUES(?,?,?)',[$oid,user()['id'],substr($note,0,255)]);
   $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  flash('Đã ghi nhận người bán xác nhận thu tiền COD. Đây là xác nhận THỦ CÔNG, không phải đối soát với hãng vận chuyển.');go('seller');
 }
 if($action==='apply_vendor'){if(user()['role']!=='client')throw new Exception('Tài khoản không phù hợp');if(dbone('SELECT id FROM vendors WHERE user_id=?',[user()['id']]))throw new Exception('Đã gửi yêu cầu');$name=trim($_POST['shop_name']??'');$address=trim($_POST['address']??'');if(!$name||!$address)throw new Exception('Thiếu tên và địa chỉ xưởng');execute('INSERT INTO vendors(user_id,shop_name,address,printer_capacity) VALUES(?,?,?,?)',[user()['id'],$name,$address,trim($_POST['capacity']??'')]);flash('Đã gửi hồ sơ. Chờ Admin duyệt');go('apply');}
 if($action==='cart_add'){$id=(int)($_POST['product_id']??0);$p=dbone('SELECT p.* FROM products p JOIN vendors v ON v.id=p.vendor_id WHERE p.id=? AND p.status="active" AND v.status="approved"',[$id]);if(!$p)throw new Exception('Sản phẩm không khả dụng');$qty=max(1,min(99,(int)($_POST['quantity']??1)));$_SESSION['cart'][$id]=min((int)$p['stock'],($_SESSION['cart'][$id]??0)+$qty);flash('Đã thêm vào giỏ hàng');go('cart');}
 if($action==='cart_update'){$id=(int)($_POST['product_id']??0);$qty=(int)($_POST['quantity']??0);if($qty<=0)unset($_SESSION['cart'][$id]);else{ $p=dbone('SELECT stock FROM products WHERE id=?',[$id]);if($p)$_SESSION['cart'][$id]=min((int)$p['stock'],$qty);}go('cart');}
 if($action==='checkout'){
  global $pdo;
  $cart=$_SESSION['cart']??[];if(!$cart)throw new Exception('Giỏ hàng trống');
  $receiver=trim($_POST['receiver']??'');$phone=trim($_POST['phone']??'');$address=trim($_POST['address']??'');$method=$_POST['method']??'cod';
  if(!$receiver||!preg_match('/^[0-9+ .-]{9,20}$/',$phone)||!$address||!in_array($method,['cod','banking','wallet','demo_momo','demo_bank'],true))throw new Exception('Thông tin nhận hàng chưa hợp lệ');
  $demoMode=in_array($method,['demo_momo','demo_bank'],true);
  if($demoMode && !wallet_demo_enabled())throw new Exception('Phương thức DEMO chỉ khả dụng trên localhost, không phải thanh toán thật');
  $storedMethod=$demoMode?'banking':$method;
  $payosMode=($method==='banking' && payos_enabled());
  $pdo->beginTransaction();
  try{
   $groups=[];$created=[];
   if($method==='wallet')wallet_lock((int)user()['id']);
   foreach($cart as $id=>$qty){
    $p=dbone('SELECT p.* FROM products p JOIN vendors v ON v.id=p.vendor_id WHERE p.id=? AND p.status="active" AND v.status="approved" FOR UPDATE',[(int)$id]);
    if(!$p||$qty<1||$p['stock']<$qty)throw new Exception('Sản phẩm đã hết hàng hoặc không khả dụng');
    $groups[$p['vendor_id']][]=['p'=>$p,'qty'=>$qty];
   }
   foreach($groups as $vid=>$items){
    $bank=null;
    if($method==='banking' && !$payosMode){
     $bank=dbone('SELECT bank_name,bank_account,bank_account_holder FROM vendors WHERE id=? AND status="approved" FOR UPDATE',[$vid]);
     if(!$bank||!trim($bank['bank_name']??'')||!trim($bank['bank_account_holder']??'')||!preg_match('/^[0-9]{4,25}$/',preg_replace('/\s+/', '', $bank['bank_account']??'')))throw new Exception('Có gian hàng chưa cấu hình đủ ngân hàng/số tài khoản/tên chủ tài khoản. Vui lòng chọn COD hoặc liên hệ xưởng.');
    }
    $total=0;foreach($items as $i)$total+=(float)$i['p']['price']*(int)$i['qty'];
    $code='3DP-'.strtoupper(bin2hex(random_bytes(5)));
    execute('INSERT INTO orders(order_code,user_id,vendor_id,receiver_name,receiver_phone,shipping_address,total_amount,payment_method) VALUES(?,?,?,?,?,?,?,?)',[$code,user()['id'],$vid,$receiver,$phone,$address,$total,$storedMethod]);
    $oid=(int)$pdo->lastInsertId();$created[]=$oid;
    if($method==='wallet'){
     $amountInt=(int)round($total);if(abs($total-$amountInt)>0.0001 || $amountInt<1)throw new Exception('Giá trị đơn chưa phù hợp để thanh toán số dư');
     wallet_post_locked((int)user()['id'],$amountInt,'debit','order_payment','orders',$oid,'Thanh toán đơn '.$code);
     wallet_vendor_earnings($oid,(int)$vid,$amountInt);
    }
    if($method==='banking'){
     if($payosMode) execute('INSERT INTO order_payments(order_id,bank_name,bank_account,bank_account_holder,status,payment_provider) VALUES(?,?,?,?,"unpaid","payos")',[$oid,'payOS','Kênh ngân hàng đã liên kết','Chủ tài khoản payOS']);
     else execute('INSERT INTO order_payments(order_id,bank_name,bank_account,bank_account_holder,status,payment_provider) VALUES(?,?,?,? ,"unpaid","manual")',[$oid,$bank['bank_name'],$bank['bank_account'],$bank['bank_account_holder']]);
    }
    if($demoMode){
     $provider=$method==='demo_momo'?'momo':'bank_qr';
     execute('INSERT INTO order_payments(order_id,bank_name,bank_account,bank_account_holder,status,payment_provider) VALUES(?,?,?,? ,"unpaid",?)',[$oid,$method==='demo_momo'?'MoMo DEMO':'QR ngân hàng DEMO','KHONG-CO-TK-THAT','Giao dịch mô phỏng',$method]);
     execute('INSERT INTO demo_payments(order_id,provider,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 15 MINUTE))',[$oid,$provider]);
    }
    foreach($items as $i){
     execute('INSERT INTO order_items(order_id,product_id,quantity,unit_price) VALUES(?,?,?,?)',[$oid,$i['p']['id'],$i['qty'],$i['p']['price']]);
     execute('UPDATE products SET stock=stock-? WHERE id=?',[$i['qty'],$i['p']['id']]);
    }
   }
   $pdo->commit();unset($_SESSION['cart']);
   if(($method==='banking'||$demoMode) && count($created)===1){if(!$demoMode)flash('Đã tạo đơn. Hãy quét QR và chuyển đúng số tiền, nội dung.');go('payment',['id'=>$created[0]]);}
   flash($method==='wallet'?'Thanh toán bằng số dư thành công!':($demoMode?'Đã tạo '.count($created).' đơn thanh toán MÔ PHỎNG. Bấm Thanh toán tại từng đơn.':($method==='banking'?'Đã tạo '.count($created).' đơn chuyển khoản. Vui lòng thanh toán TỪNG ĐƠN theo hướng dẫn.':'Đặt hàng COD thành công!')));go('orders');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
 }
 if($action==='review'){$pid=(int)$_POST['product_id'];$rating=(int)($_POST['rating']??0);if($rating<1||$rating>5)throw new Exception('Số sao không hợp lệ');$bought=dbone('SELECT oi.id FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.product_id=? AND o.user_id=? AND o.order_status="completed" LIMIT 1',[$pid,user()['id']]);if(!$bought)throw new Exception('Chỉ đánh giá sản phẩm đã mua và hoàn tất');execute('INSERT INTO reviews(user_id,product_id,rating,comment) VALUES(?,?,?,?)',[user()['id'],$pid,$rating,trim($_POST['comment']??'')]);flash('Cảm ơn đánh giá của bạn');go('product',['id'=>$pid]);}
 if($action==='quote'){ $vid=(int)$_POST['vendor_id'];$mid=(int)$_POST['material_id'];$m=dbone('SELECT m.* FROM materials m JOIN vendors v ON v.id=m.vendor_id WHERE m.id=? AND m.vendor_id=? AND m.status=1 AND v.status="approved"',[$mid,$vid]);if(!$m)throw new Exception('Vật liệu không hợp lệ');$f=$_FILES['model']??null;if(!$f||$f['error']!==UPLOAD_ERR_OK||$f['size']>15*1024*1024||$f['size']<1)throw new Exception('File 3D tối đa 15MB');$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));if(!in_array($ext,['stl','obj'],true))throw new Exception('Chỉ nhận STL/OBJ');$vol=(float)($_POST['volume']??0);$qty=(int)($_POST['quantity']??1);$infill=(int)($_POST['infill']??20);$layer=(float)($_POST['layer']??0.2);if($vol<=0||$vol>100000||$qty<1||$qty>100||$infill<1||$infill>100||$layer<0.05||$layer>1)throw new Exception('Thông số không hợp lệ');$path='uploads/'.bin2hex(random_bytes(16)).'.'.$ext;if(!move_uploaded_file($f['tmp_name'],__DIR__.'/'.$path))throw new Exception('Không thể lưu file');$estimate=$vol*(float)$m['price_per_cm3']*$qty;execute('INSERT INTO custom_quotes(user_id,vendor_id,material_id,file_path,volume_cm3,infill_percent,layer_height,quantity,estimated_price) VALUES(?,?,?,?,?,?,?,?,?)',[user()['id'],$vid,$mid,$path,$vol,$infill,$layer,$qty,$estimate]);flash('Đã gửi yêu cầu báo giá (giá tạm tính)');go('orders');}
 if(in_array($action,['product_save','product_hide','order_status','quote_status'],true)){require_role('vendor');$v=vendor();if(!$v||$v['status']!=='approved')throw new Exception('Gian hàng chưa được duyệt');
 if($action==='product_save'){$id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');$price=(float)($_POST['price']??0);$stock=(int)($_POST['stock']??0);$cat=(int)($_POST['category_id']??0);$mid=(int)($_POST['material_id']??0);if(!$name||$price<=0||$stock<0||!dbone('SELECT id FROM categories WHERE id=? AND status=1',[$cat])||!dbone('SELECT id FROM materials WHERE id=? AND vendor_id=?',[$mid,$v['id']]))throw new Exception('Thông tin sản phẩm không hợp lệ');$image=upload_image();if($id){$old=dbone('SELECT * FROM products WHERE id=? AND vendor_id=?',[$id,$v['id']]);if(!$old)throw new Exception('Không tìm thấy sản phẩm');execute('UPDATE products SET name=?,price=?,stock=?,category_id=?,material_id=?,dimensions=?,status="pending" WHERE id=? AND vendor_id=?',[$name,$price,$stock,$cat,$mid,trim($_POST['dimensions']??''),$id,$v['id']]);}else{execute('INSERT INTO products(vendor_id,category_id,material_id,name,price,stock,dimensions,status) VALUES(?,?,?,?,?,?,?,"pending")',[$v['id'],$cat,$mid,$name,$price,$stock,trim($_POST['dimensions']??'')]);$id=(int)$pdo->lastInsertId();}if($image){execute('INSERT INTO product_images(product_id,image_path) VALUES(?,?) ON DUPLICATE KEY UPDATE image_path=VALUES(image_path)',[$id,$image]);}flash('Đã lưu sản phẩm, chờ Admin duyệt');go('seller');}
 if($action==='product_hide'){execute('UPDATE products SET status="hidden" WHERE id=? AND vendor_id=?',[(int)$_POST['id'],$v['id']]);flash('Đã ẩn sản phẩm');go('seller');}
 if($action==='order_status'){
   $s=$_POST['status']??'';
   if(!in_array($s,['printing','shipping','completed','cancelled'],true))throw new Exception('Trạng thái không hợp lệ');
   $pdo->beginTransaction();
   try {
    $o=dbone('SELECT * FROM orders WHERE id=? AND vendor_id=? FOR UPDATE',[(int)$_POST['id'],$v['id']]);
    if(!$o||in_array($o['order_status'],['completed','cancelled'],true))throw new Exception('Đơn không thể cập nhật');
    $allowed=['pending'=>['printing','cancelled'],'printing'=>['shipping','cancelled'],'shipping'=>['completed']];
    if(!in_array($s,$allowed[$o['order_status']]??[],true))throw new Exception('Chuyển trạng thái không hợp lệ');
    if($o['payment_method']==='banking'){
      $ps=dbone('SELECT status FROM order_payments WHERE order_id=?',[$o['id']]);
      if($s!=='cancelled'&&(!$ps||$ps['status']!=='confirmed'))throw new Exception('Chỉ xử lý đơn chuyển khoản khi xác nhận đã nhận tiền.');
      if($s==='cancelled'&&$ps&&in_array($ps['status'],['submitted','confirmed'],true))throw new Exception('Cần đối soát/hoàn tiền trước khi hủy đơn chuyển khoản.');
      if($s==='cancelled'&&dbone('SELECT order_id FROM payos_transactions WHERE order_id=?',[$o['id']]))throw new Exception('Phải hủy link payOS và đối soát trước khi hủy đơn.');
    }
    if($o['payment_method']==='wallet'){
      if($s==='cancelled'){
       if($o['order_status']!=='pending')throw new Exception('Chỉ hoàn tiền số dư tự động khi đơn còn chờ xử lý');
       wallet_post_locked((int)$o['user_id'],(int)round($o['total_amount']),'credit','order_refund','orders',(int)$o['id'],'Hoàn tiền hủy đơn '.$o['order_code']);
       execute('UPDATE vendor_earnings SET status="cancelled" WHERE order_id=? AND status="held"',[$o['id']]);
      }
      if($s==='completed')execute('UPDATE vendor_earnings SET status="eligible",eligible_at=NOW() WHERE order_id=? AND status="held"',[$o['id']]);
    }
    if($o['payment_method']==='cod' && $s==='completed' && !dbone('SELECT order_id FROM cod_collections WHERE order_id=?',[$o['id']])){
      throw new Exception('Hãy ghi nhận đã thu COD trước khi hoàn tất đơn.');
    }
    if($s==='cancelled'){
      foreach(dball('SELECT product_id,quantity FROM order_items WHERE order_id=?',[$o['id']]) as $item){
        if($item['product_id'])execute('UPDATE products SET stock=stock+? WHERE id=?',[(int)$item['quantity'],(int)$item['product_id']]);
      }
    }
    execute('UPDATE orders SET order_status=? WHERE id=?',[$s,$o['id']]);
    $pdo->commit();
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
   flash($s==='cancelled' && $o['payment_method']==='wallet'?'Đã hủy đơn và hoàn tiền vào số dư.':'Đã cập nhật đơn');go('seller');
 }
 if($action==='quote_status'){$s=$_POST['status']??'';if(!in_array($s,['approved','rejected'],true))throw new Exception('Trạng thái không hợp lệ');execute('UPDATE custom_quotes SET status=? WHERE id=? AND vendor_id=? AND status="pending_review"',[$s,(int)$_POST['id'],$v['id']]);flash('Đã cập nhật yêu cầu');go('seller');}}
 if(in_array($action,['approve_vendor','approve_product','toggle_user'],true)){require_role('admin');if($action==='approve_vendor'){$s=$_POST['status']??'';if(!in_array($s,['approved','rejected'],true))throw new Exception('Trạng thái không hợp lệ');$v=dbone('SELECT * FROM vendors WHERE id=?',[(int)$_POST['id']]);if(!$v)throw new Exception('Không tìm thấy gian hàng');execute('UPDATE vendors SET status=? WHERE id=?',[$s,$v['id']]);if($s==='approved')execute('UPDATE users SET role="vendor" WHERE id=?',[$v['user_id']]);flash('Đã xử lý gian hàng');}
 if($action==='approve_product'){$s=$_POST['status']??'';if(!in_array($s,['active','hidden'],true))throw new Exception('Trạng thái không hợp lệ');execute('UPDATE products SET status=? WHERE id=?',[$s,(int)$_POST['id']]);flash('Đã duyệt sản phẩm');}
 if($action==='toggle_user'){$id=(int)$_POST['id'];if($id===user()['id'])throw new Exception('Không thể khóa chính mình');execute('UPDATE users SET status=1-status WHERE id=? AND role!="admin"',[$id]);flash('Đã cập nhật tài khoản');}go('admin');}
 throw new Exception('Thao tác không hợp lệ');
 }catch(Throwable $e){flash($e->getMessage(),'error');header('Location: '.($_SERVER['HTTP_REFERER']??'index.php'));exit;}
}
if($page==='logout')go('home');
$cartCount=array_sum($_SESSION['cart']??[]);
function formtoken(){echo '<input type="hidden" name="csrf" value="'.h(token()).'">';}
function actionform($action,$fields=[],$label='Xác nhận',$class='btn btn-sm btn-primary'){echo '<form method="post" class="inline-form">';formtoken();echo '<input type="hidden" name="action" value="'.h($action).'">';foreach($fields as $k=>$v)echo '<input type="hidden" name="'.h($k).'" value="'.h($v).'">';echo '<button class="'.h($class).'">'.h($label).'</button></form>';}
function cardproduct($p){$img=product_image($p);echo '<a class="product-card" href="'.h(url('product',['id'=>$p['id']])).'"><div class="product-visual">';if($img)echo '<img src="'.h($img).'" alt="'.h($p['name']).'">';else echo '<div class="shape">✦</div>';echo '<span class="chip">3D PRINT</span></div><div class="product-info"><small>'.h($p['category_name']).' · '.h($p['shop_name']).'</small><h3>'.h($p['name']).'</h3><div class="product-bottom"><strong>'.money($p['price']).'</strong><span class="round-arrow">↗</span></div></div></a>';}
?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>3DPrintHub · Sáng tạo không giới hạn</title><link rel="stylesheet" href="assets/style.css"></head><body><div class="ambient ambient-one"></div><div class="ambient ambient-two"></div><header class="topbar"><a class="logo" href="index.php"><span class="logo-icon">⬡</span> 3D<span>PRINT</span>HUB</a><nav><a href="index.php">Trang chủ</a><a href="<?=url('catalog')?>">Khám phá</a><a href="<?=url('quote')?>">In 3D theo yêu cầu</a><?php if(user()&&user()['role']==='admin'):?><a href="<?=url('admin')?>">Admin</a><?php elseif(user()&&user()['role']==='vendor'):?><a href="<?=url('seller')?>">Kênh người bán</a><?php else:?><a href="<?=url('apply')?>">Trở thành người bán</a><?php endif;?></nav><div class="nav-actions"><?php if(user()):?><a class="nav-wallet" href="<?=url('wallet')?>">💳 Số dư</a><?php endif;?><a class="cart-link" href="<?=url('cart')?>">🛒 <span><?=$cartCount?></span></a><?php if(user()):?><a class="nav-user" href="<?=url('orders')?>"><?=h(user()['full_name'])?></a><form method="post"><?php formtoken();?><input type="hidden" name="action" value="logout"><button class="btn btn-ghost btn-sm">Thoát</button></form><?php else:?><a class="btn btn-ghost btn-sm" href="<?=url('login')?>">Đăng nhập</a><a class="btn btn-primary btn-sm" href="<?=url('register')?>">Đăng ký</a><?php endif;?></div></header><main><?php if(!empty($_SESSION['flash'])):[$msg,$type]=$_SESSION['flash'];unset($_SESSION['flash']);?><div class="toast <?=$type==='error'?'toast-error':''?>" id="toast"><?=h($msg)?></div><?php endif;?>
<?php if($page==='home'):$featured=dball('SELECT p.*,c.name category_name,v.shop_name,pi.image_path FROM products p JOIN categories c ON c.id=p.category_id JOIN vendors v ON v.id=p.vendor_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.status="active" AND v.status="approved" ORDER BY p.created_at DESC LIMIT 8');$cats=dball('SELECT * FROM categories WHERE status=1');?><section class="hero container"><div class="hero-content"><div class="eyebrow"><span class="pulse"></span> THE FUTURE OF MAKING IS HERE</div><h1>Biến ý tưởng thành <em>hiện thực.</em></h1><p>Khám phá thế giới sáng tạo với hàng ngàn sản phẩm in 3D độc đáo. Mua sắm, thiết kế và kết nối với những xưởng in hàng đầu.</p><div class="hero-buttons"><a class="btn btn-primary btn-lg" href="<?=url('catalog')?>">Khám phá sản phẩm <span>↗</span></a><a class="btn btn-outline btn-lg" href="<?=url('quote')?>">✦ Đặt in theo yêu cầu</a></div><div class="hero-stats"><div><b>3D</b><small>Không giới hạn sáng tạo</small></div><div><b>24/7</b><small>Khám phá mọi lúc</small></div><div><b>100%</b><small>Đam mê công nghệ</small></div></div></div><div class="hero-art"><div class="orbit orbit1"></div><div class="orbit orbit2"></div><div class="orb-core">⬡<span>3D</span></div><div class="float-label label1">✦ Precision Printing</div><div class="float-label label2">◈ Your imagination</div></div></section><div class="ticker"><div>✦ DESIGN &nbsp; · &nbsp; CREATE &nbsp; · &nbsp; PRINT &nbsp; · &nbsp; INNOVATE &nbsp; · &nbsp; DESIGN &nbsp; · &nbsp; CREATE &nbsp; · &nbsp; PRINT &nbsp; · &nbsp; INNOVATE &nbsp; ✦</div></div><section class="container section"><div class="section-head"><div><div class="eyebrow">CURATED COLLECTION</div><h2>Khám phá <em>danh mục</em></h2></div><a href="<?=url('catalog')?>">Xem tất cả ↗</a></div><div class="categories"><?php foreach($cats as $i=>$c):?><a class="category-tile cat<?=$i%3?>" href="<?=url('catalog',['category'=>$c['id']])?>"><div class="cat-icon"><?=['⬡','✧','▣'][$i%3]?></div><div><h3><?=h($c['name'])?></h3><small><?=h($c['description'])?></small></div><span>↗</span></a><?php endforeach;?></div></section><section class="container section"><div class="section-head"><div><div class="eyebrow">TRENDING NOW</div><h2>Sản phẩm <em>nổi bật</em></h2></div><a href="<?=url('catalog')?>">Tất cả sản phẩm ↗</a></div><div class="product-grid"><?php foreach($featured as $p)cardproduct($p);?></div></section><section class="container cta"><div class="eyebrow">YOUR IDEA, OUR TECHNOLOGY</div><h2>Bạn có ý tưởng <em>độc đáo?</em></h2><p>Tải lên file STL/OBJ, chọn vật liệu và gửi yêu cầu đến xưởng in phù hợp.</p><a class="btn btn-light btn-lg" href="<?=url('quote')?>">Bắt đầu in 3D ↗</a></section>
<?php elseif($page==='catalog'):$q=trim($_GET['q']??'');$cat=(int)($_GET['category']??0);$params=[];$where='p.status="active" AND v.status="approved"';if($q!==''){$where.=' AND p.name LIKE ?';$params[]='%'.$q.'%';}if($cat){$where.=' AND p.category_id=?';$params[]=$cat;}$products=dball('SELECT p.*,c.name category_name,v.shop_name,pi.image_path FROM products p JOIN categories c ON c.id=p.category_id JOIN vendors v ON v.id=p.vendor_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE '.$where.' ORDER BY p.created_at DESC',$params);?><section class="container section page-top"><div class="eyebrow">THE MARKETPLACE</div><h1 class="page-title">Khám phá <em>sản phẩm</em></h1><form class="filters" method="get"><input type="hidden" name="page" value="catalog"><input name="q" placeholder="⌕ Tìm sản phẩm in 3D..." value="<?=h($q)?>"><select name="category"><option value="0">Tất cả danh mục</option><?php foreach(dball('SELECT * FROM categories WHERE status=1') as $c):?><option value="<?=$c['id']?>" <?=$cat==$c['id']?'selected':''?>><?=h($c['name'])?></option><?php endforeach;?></select><button class="btn btn-primary">Tìm kiếm ↗</button></form><p class="muted">Tìm thấy <?=count($products)?> sản phẩm</p><div class="product-grid"><?php foreach($products as $p)cardproduct($p);?></div><?php if(!$products):?><div class="empty">Không tìm thấy sản phẩm phù hợp.</div><?php endif;?></section>
<?php elseif($page==='product'):$p=dbone('SELECT p.*,c.name category_name,v.shop_name,v.address,pi.image_path,m.name material_name FROM products p JOIN categories c ON c.id=p.category_id JOIN vendors v ON v.id=p.vendor_id JOIN materials m ON m.id=p.material_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.id=? AND p.status="active" AND v.status="approved"',[(int)($_GET['id']??0)]);if(!$p):?><section class="container section"><h2>Sản phẩm không tồn tại</h2></section><?php else:$reviews=dball('SELECT r.*,u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? ORDER BY r.created_at DESC',[$p['id']]);?><section class="container section page-top"><a class="back" href="<?=url('catalog')?>">← Quay lại sản phẩm</a><div class="detail"><div class="detail-visual"><?php if(product_image($p)):?><img src="<?=h($p['image_path'])?>" alt="<?=h($p['name'])?>"><?php else:?><span>⬡</span><?php endif;?></div><div class="detail-info"><div class="eyebrow"><?=h($p['category_name'])?></div><h1><?=h($p['name'])?></h1><p class="muted">✦ <?=h($p['shop_name'])?> · <?=h($p['address'])?></p><div class="big-price"><?=money($p['price'])?></div><div class="specs"><div>Vật liệu <b><?=h($p['material_name'])?></b></div><div>Kích thước <b><?=h($p['dimensions']?:'Theo mẫu')?></b></div><div>Kho <b><?=$p['stock']?> sản phẩm</b></div></div><form method="post" class="buy-form"><?php formtoken();?><input type="hidden" name="action" value="cart_add"><input type="hidden" name="product_id" value="<?=$p['id']?>"><input type="number" name="quantity" min="1" max="<?=max(1,$p['stock'])?>" value="1"><button class="btn btn-primary btn-lg" <?=$p['stock']<1?'disabled':''?>>🛒 Thêm vào giỏ hàng</button></form></div></div><div class="panel reviews"><h2>Đánh giá khách hàng (<?=count($reviews)?>)</h2><?php foreach($reviews as $r):?><div class="review"><strong><?=h($r['full_name'])?></strong><span class="stars"><?=str_repeat('★',(int)$r['rating'])?></span><p><?=h($r['comment'])?></p></div><?php endforeach;?><?php if(user()):?><form method="post" class="stack"><?php formtoken();?><input type="hidden" name="action" value="review"><input type="hidden" name="product_id" value="<?=$p['id']?>"><select name="rating"><?php for($i=5;$i>=1;$i--):?><option value="<?=$i?>"><?=$i?> sao</option><?php endfor;?></select><textarea name="comment" placeholder="Cảm nhận của bạn..."></textarea><button class="btn btn-outline">Gửi đánh giá (đơn đã hoàn tất)</button></form><?php endif;?></div></section><?php endif;?>
<?php elseif(in_array($page,['login','register'],true)):?><section class="auth-wrap"><div class="auth-card"><div class="eyebrow">WELCOME TO 3DPRINTHUB</div><h1><?=$page==='login'?'Chào mừng <em>trở lại.</em>':'Tham gia <em>cộng đồng.</em>'?></h1><p class="muted">Sáng tạo không giới hạn, bắt đầu từ đây.</p><form method="post" class="stack"><?php formtoken();?><input type="hidden" name="action" value="<?=$page?>"><?php if($page==='register'):?><label>Họ và tên<input required name="name" placeholder="Nguyễn Văn A"></label><?php endif;?><label>Email<input required type="email" name="email" placeholder="you@example.com"></label><label>Mật khẩu<input required type="password" name="password" minlength="6" placeholder="••••••••"></label><button class="btn btn-primary btn-lg"><?=$page==='login'?'Đăng nhập →':'Tạo tài khoản →'?></button></form><p class="muted"><?=$page==='login'?'Chưa có tài khoản?':'Đã có tài khoản?'?> <a href="<?=url($page==='login'?'register':'login')?>"><?=$page==='login'?'Đăng ký ngay':'Đăng nhập'?></a></p></div></section>
<?php elseif($page==='cart'):$items=[];$total=0;$canBank=true;$payosMode=payos_enabled();foreach($_SESSION['cart']??[] as $id=>$qty){$p=dbone('SELECT p.*,v.shop_name,v.bank_name,v.bank_account,v.bank_account_holder,pi.image_path FROM products p JOIN vendors v ON v.id=p.vendor_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.id=? AND p.status="active" AND v.status="approved"',[(int)$id]);if($p){$p['qty']=$qty;$items[]=$p;$total+=$p['price']*$qty;if(!$payosMode && (!trim($p['bank_name']??'')||!trim($p['bank_account_holder']??'')||!preg_match('/^[0-9]{4,25}$/',preg_replace('/\s+/', '', $p['bank_account']??''))))$canBank=false;}}?><section class="container section page-top"><div class="eyebrow">YOUR SELECTION</div><h1 class="page-title">Giỏ hàng <em>của bạn</em></h1><?php if(!$items):?><div class="empty">Giỏ hàng đang trống. <a href="<?=url('catalog')?>">Khám phá sản phẩm →</a></div><?php else:?><div class="two-cols"><div class="panel"><h2>Sản phẩm (<?=count($items)?>)</h2><?php foreach($items as $p):?><div class="cart-item"><div class="mini-visual">⬡</div><div class="grow"><strong><?=h($p['name'])?></strong><p class="muted"><?=h($p['shop_name'])?></p><b><?=money($p['price'])?></b></div><form method="post" class="qty-form"><?php formtoken();?><input type="hidden" name="action" value="cart_update"><input type="hidden" name="product_id" value="<?=$p['id']?>"><input type="number" name="quantity" min="0" max="<?=$p['stock']?>" value="<?=$p['qty']?>"><button class="btn btn-ghost btn-sm">Cập nhật</button></form></div><?php endforeach;?></div><div class="panel"><h2>Thông tin giao hàng</h2><form method="post" class="stack"><?php formtoken();?><input type="hidden" name="action" value="checkout"><label>Người nhận<input required name="receiver" value="<?=h(user()['full_name']??'')?>"></label><label>Số điện thoại<input required name="phone" placeholder="09xxxxxxxx"></label><label>Địa chỉ giao hàng<textarea required name="address" placeholder="Số nhà, đường, phường, tỉnh/thành phố"></textarea></label><div class="checkout-methods"><h3>Chọn phương thức thanh toán</h3><div class="pay-method-grid">
<label class="pay-method-card"><input type="radio" name="method" value="demo_momo" checked><span class="pay-method-icon demo-momo-mark">M</span><span><b>MoMo <small>DEMO</small></b><em>Thanh toán giả lập · không chuyển tiền</em></span></label>
<label class="pay-method-card"><input type="radio" name="method" value="demo_bank"><span class="pay-method-icon demo-bank-mark">▦</span><span><b>QR ngân hàng <small>DEMO</small></b><em>Quét mã minh họa, xác nhận mô phỏng</em></span></label>
<label class="pay-method-card"><input type="radio" name="method" value="wallet"><span class="pay-method-icon">💳</span><span><b>Số dư 3DPrintHub</b><em>Trừ số dư đã nạp DEMO</em></span></label>
<label class="pay-method-card"><input type="radio" name="method" value="cod"><span class="pay-method-icon">📦</span><span><b>COD</b><em>Thanh toán khi nhận hàng</em></span></label>
<label class="pay-method-card <?= $canBank?'':'method-disabled' ?>"><input type="radio" name="method" value="banking" <?=$canBank?'':'disabled'?>><span class="pay-method-icon">🏦</span><span><b>Chuyển khoản thật</b><em><?= $payosMode ? 'Qua payOS' : 'Theo thông tin gian hàng' ?></em></span></label>
</div></div><?php if(!$canBank):?><div class="notice notice-danger">Một số gian hàng chưa có đủ tài khoản ngân hàng, nên chuyển khoản THẬT bị khóa. Bạn vẫn có thể thử MoMo DEMO, QR DEMO, số dư hoặc COD.</div><?php endif;?><div class="checkout-hint" style="display:block">💳 Số dư hiện tại: <strong><?=money(user()?wallet_account((int)user()['id'])['balance']:0)?></strong> · <a href="<?=url('wallet')?>">Quản lý số dư ↗</a>. Nếu không đủ, hãy chọn COD hoặc chuyển khoản.</div><div class="checkout-hint" id="transferHint" hidden>✓ Sau khi đặt hàng, website sẽ hiện thanh toán VietQR<?= $payosMode ? " tự động qua payOS" : " theo gian hàng" ?>.<?= $payosMode ? " Tiền sẽ về tài khoản chủ sàn liên kết payOS và được xác minh bằng webhook." : " Bạn gửi xác nhận để người bán duyệt." ?></div><div class="total">Tổng cộng <strong><?=money($total)?></strong></div><button class="btn btn-primary btn-lg">Đặt hàng ngay ↗</button></form></div></div><?php endif;?></section>
<?php elseif($page==='payment'):require_login();$oid=(int)($_GET['id']??0);$order=dbone('SELECT o.*,v.shop_name FROM orders o JOIN vendors v ON v.id=o.vendor_id WHERE o.id=? AND o.user_id=? AND o.payment_method="banking"',[$oid,user()['id']]);if(!$order){http_response_code(404);echo '<section class="container section"><h1>Không tìm thấy đơn thanh toán</h1></section>';}else{$payment=dbone('SELECT * FROM order_payments WHERE order_id=?',[$oid]);if(!$payment){http_response_code(500);echo '<section class="container section"><h1>Thiếu dữ liệu thanh toán. Hãy chạy payment_upgrade.sql</h1></section>';}else{if(in_array($payment['payment_provider']??'',['demo_momo','demo_bank'],true)){$demo=dbone('SELECT d.*,TIMESTAMPDIFF(SECOND,NOW(),d.expires_at) AS seconds_left FROM demo_payments d WHERE d.order_id=?',[$oid]);$kind='order';$demoAmount=(int)round($order['total_amount']);$demoCode=$order['order_code'];require __DIR__.'/views/demo_payment_screen.php';}elseif(($payment['payment_provider']??'manual')==='payos')require __DIR__.'/views/payos_payment.php';else require __DIR__.'/views/payment.php';}}?>
<?php elseif($page==='demo_topup'):require_login();$tid=(int)($_GET['id']??0);$topup=dbone('SELECT * FROM wallet_topups WHERE id=? AND user_id=?',[$tid,(int)user()['id']]);$demo=$topup?dbone('SELECT d.*,TIMESTAMPDIFF(SECOND,NOW(),d.expires_at) AS seconds_left FROM demo_payments d WHERE d.topup_id=?',[$tid]):null;$kind='topup';$demoAmount=$topup?(int)$topup['amount']:0;$demoCode=$topup?'TOPUP-DEMO-'.$topup['id']:'';require __DIR__.'/views/demo_payment_screen.php';?>
<?php elseif($page==='wallet'):require_login();$wallet=wallet_account((int)user()['id']);$transactions=dball('SELECT * FROM wallet_transactions WHERE user_id=? ORDER BY id DESC LIMIT 100',[user()['id']]);$topups=dball('SELECT * FROM wallet_topups WHERE user_id=? ORDER BY id DESC LIMIT 50',[user()['id']]);require __DIR__.'/views/wallet.php';?>
<?php elseif($page==='orders'):require_login();$orders=dball('SELECT o.*,v.shop_name,op.status AS payment_status,op.payment_provider,cc.collected_at AS cod_collected_at FROM orders o JOIN vendors v ON v.id=o.vendor_id LEFT JOIN order_payments op ON op.order_id=o.id LEFT JOIN cod_collections cc ON cc.order_id=o.id WHERE o.user_id=? ORDER BY o.created_at DESC',[user()['id']]);$quotes=dball('SELECT q.*,v.shop_name FROM custom_quotes q JOIN vendors v ON v.id=q.vendor_id WHERE q.user_id=? ORDER BY q.created_at DESC',[user()['id']]);require __DIR__.'/views/orders.php';?>
<?php elseif($page==='apply'):require_login();$existing=dbone('SELECT * FROM vendors WHERE user_id=?',[user()['id']]);?><section class="container section page-top narrow"><div class="eyebrow">BECOME A CREATOR</div><h1 class="page-title">Mở gian hàng <em>của bạn</em></h1><div class="panel"><?php if($existing):?><h2>Hồ sơ gian hàng</h2><p><?=h($existing['shop_name'])?></p><p>Trạng thái: <span class="status"><?=h($existing['status'])?></span></p><?php elseif(user()['role']!=='client'):?><p>Tài khoản này không thể đăng ký thêm gian hàng.</p><?php else:?><form method="post" class="stack"><?php formtoken();?><input type="hidden" name="action" value="apply_vendor"><label>Tên gian hàng<input required name="shop_name"></label><label>Địa chỉ xưởng<input required name="address"></label><label>Năng lực máy in<input name="capacity" placeholder="Ví dụ: 4 máy FDM, 2 máy SLA"></label><button class="btn btn-primary btn-lg">Gửi yêu cầu duyệt ↗</button></form><?php endif;?></div></section>
<?php elseif($page==='quote'):require_login();$vendors=dball('SELECT * FROM vendors WHERE status="approved"');$materials=dball('SELECT m.*,v.shop_name FROM materials m JOIN vendors v ON v.id=m.vendor_id WHERE m.status=1 AND v.status="approved"');?><section class="container section page-top narrow"><div class="eyebrow">CUSTOM 3D PRINTING</div><h1 class="page-title">In theo <em>yêu cầu</em></h1><p class="muted">Tải file STL/OBJ, nhập thể tích ước lượng (cm³) và thông số in. Giá hiển thị là tạm tính, chưa phải báo giá cuối cùng.</p><div class="panel"><form method="post" enctype="multipart/form-data" class="stack"><?php formtoken();?><input type="hidden" name="action" value="quote"><label>Xưởng in<select name="vendor_id" id="vendorSelect" required><?php foreach($vendors as $v):?><option value="<?=$v['id']?>"><?=h($v['shop_name'])?></option><?php endforeach;?></select></label><label>Vật liệu<select name="material_id" id="materialSelect" required><?php foreach($materials as $m):?><option data-vendor="<?=$m['vendor_id']?>" value="<?=$m['id']?>"><?=h($m['name'])?> — <?=money($m['price_per_cm3'])?>/cm³</option><?php endforeach;?></select></label><label>File mô hình STL / OBJ<input required type="file" name="model" accept=".stl,.obj"></label><div class="form-grid"><label>Thể tích ước tính (cm³)<input type="number" step="0.01" min="0.01" name="volume" required></label><label>Số lượng<input type="number" min="1" max="100" name="quantity" value="1" required></label><label>Infill (%)<input type="number" min="1" max="100" name="infill" value="20"></label><label>Layer height (mm)<input type="number" step="0.01" min="0.05" max="1" name="layer" value="0.2"></label></div><button class="btn btn-primary btn-lg">Gửi yêu cầu báo giá ↗</button></form></div></section>
<?php elseif($page==='seller'):require_role('vendor');$v=vendor();?><section class="container section page-top"><div class="eyebrow">SELLER STUDIO</div><h1 class="page-title">Kênh <em>người bán</em></h1><?php if(!$v||$v['status']!=='approved'):?><div class="panel">Gian hàng đang chờ duyệt hoặc chưa được kích hoạt.</div><?php else:$products=dball('SELECT p.*,c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.vendor_id=? ORDER BY p.created_at DESC',[$v['id']]);$orders=dball('SELECT o.*,op.status AS payment_status,op.payment_provider,op.receipt_mime,op.bank_name,op.bank_account,cc.collected_at AS cod_collected_at FROM orders o LEFT JOIN order_payments op ON op.order_id=o.id LEFT JOIN cod_collections cc ON cc.order_id=o.id WHERE o.vendor_id=? ORDER BY o.created_at DESC',[$v['id']]);$quotes=dball('SELECT * FROM custom_quotes WHERE vendor_id=? ORDER BY created_at DESC',[$v['id']]);$revenue=dbone('SELECT COALESCE(SUM(total_amount),0) total FROM orders WHERE vendor_id=? AND order_status="completed"',[$v['id']]);?><div class="stat-grid"><div class="stat-card">Sản phẩm <strong><?=count($products)?></strong></div><div class="stat-card">Đơn hàng <strong><?=count($orders)?></strong></div><div class="stat-card">Doanh thu hoàn tất <strong><?=money($revenue['total'])?></strong></div></div><?php require __DIR__.'/views/seller_bank.php';?><div class="panel"><h2>✦ Đăng / chỉnh sửa sản phẩm</h2><form method="post" enctype="multipart/form-data" class="stack" id="productForm"><?php formtoken();?><input type="hidden" name="action" value="product_save"><input type="hidden" name="id" id="editId" value=""><div class="form-grid"><label>Tên sản phẩm<input name="name" id="editName" required></label><label>Giá (VNĐ)<input type="number" min="1" name="price" id="editPrice" required></label><label>Số lượng tồn<input type="number" min="0" name="stock" id="editStock" required></label><label>Kích thước<input name="dimensions" id="editDimensions" placeholder="100 x 100 x 100 mm"></label><label>Danh mục<select name="category_id" id="editCategory"><?php foreach(dball('SELECT * FROM categories WHERE status=1') as $c):?><option value="<?=$c['id']?>"><?=h($c['name'])?></option><?php endforeach;?></select></label><label>Vật liệu<select name="material_id" id="editMaterial"><?php foreach(dball('SELECT * FROM materials WHERE vendor_id=? AND status=1',[$v['id']]) as $m):?><option value="<?=$m['id']?>"><?=h($m['name'])?></option><?php endforeach;?></select></label></div><label>Ảnh sản phẩm (JPG, PNG, WEBP)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><button class="btn btn-primary">Lưu sản phẩm · Chờ duyệt ↗</button></form></div><div class="panel"><h2>Sản phẩm của xưởng</h2><div class="table-wrap"><table><tr><th>Tên</th><th>Giá</th><th>Tồn</th><th>Trạng thái</th><th>Thao tác</th></tr><?php foreach($products as $p):?><tr><td><?=h($p['name'])?></td><td><?=money($p['price'])?></td><td><?=$p['stock']?></td><td><span class="status"><?=h($p['status'])?></span></td><td><button class="btn btn-ghost btn-sm" onclick='editProduct(<?=h(json_encode(['id'=>$p['id'],'name'=>$p['name'],'price'=>$p['price'],'stock'=>$p['stock'],'dimensions'=>$p['dimensions'],'category_id'=>$p['category_id'],'material_id'=>$p['material_id']],JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP))?>)'>Sửa</button> <?php actionform('product_hide',['id'=>$p['id']],'Ẩn','btn btn-ghost btn-sm');?></td></tr><?php endforeach;?></table></div></div><?php require __DIR__.'/views/seller_orders.php';?><div class="panel"><h2>Yêu cầu báo giá</h2><div class="table-wrap"><table><tr><th>ID</th><th>File</th><th>Thể tích</th><th>Giá tạm tính</th><th>Trạng thái</th><th>Xử lý</th></tr><?php foreach($quotes as $q):?><tr><td>#<?=$q['id']?></td><td><?=h(basename($q['file_path']))?></td><td><?=$q['volume_cm3']?> cm³</td><td><?=money($q['estimated_price'])?></td><td><?=h($q['status'])?></td><td><?php if($q['status']==='pending_review'){actionform('quote_status',['id'=>$q['id'],'status'=>'approved'],'Duyệt');actionform('quote_status',['id'=>$q['id'],'status'=>'rejected'],'Từ chối','btn btn-ghost btn-sm');}?></td></tr><?php endforeach;?></table></div></div><?php endif;?></section>
<?php elseif($page==='admin'):require_role('admin');$stats=[dbone('SELECT COUNT(*) n FROM users')['n'],dbone('SELECT COUNT(*) n FROM vendors')['n'],dbone('SELECT COUNT(*) n FROM products')['n'],dbone('SELECT COUNT(*) n FROM orders')['n']];$vendors=dball('SELECT v.*,u.email FROM vendors v JOIN users u ON u.id=v.user_id ORDER BY v.created_at DESC');$products=dball('SELECT p.*,v.shop_name FROM products p JOIN vendors v ON v.id=p.vendor_id ORDER BY p.created_at DESC');$users=dball('SELECT id,full_name,email,role,status FROM users ORDER BY created_at DESC');$walletLiability=dbone('SELECT COALESCE(SUM(balance),0) total FROM wallet_accounts');$topupStats=dbone('SELECT COUNT(*) paid_count FROM wallet_topups WHERE status="paid"');$walletOrderCount=dbone('SELECT COUNT(*) n FROM orders WHERE payment_method="wallet"');$walletPayables=dball('SELECT e.*,o.order_code,v.shop_name FROM vendor_earnings e JOIN orders o ON o.id=e.order_id JOIN vendors v ON v.id=e.vendor_id ORDER BY e.created_at DESC LIMIT 40');$codForAdmin=dball('SELECT o.order_code,o.total_amount,o.order_status,v.shop_name,c.collected_at FROM orders o JOIN vendors v ON v.id=o.vendor_id LEFT JOIN cod_collections c ON c.order_id=o.id WHERE o.payment_method="cod" ORDER BY o.id DESC LIMIT 40');$adminTopups=dball('SELECT t.*,u.full_name FROM wallet_topups t JOIN users u ON u.id=t.user_id ORDER BY t.id DESC LIMIT 30');$walletClients=wallet_demo_enabled()?dball('SELECT id,full_name,email FROM users WHERE role="client" AND status=1 ORDER BY id DESC LIMIT 100'):[];$payments=dball('SELECT o.id,o.order_code,o.receiver_name,o.total_amount,o.order_status,v.shop_name,op.status AS payment_status,op.payment_provider,op.bank_name,op.bank_account,op.receipt_mime FROM order_payments op JOIN orders o ON o.id=op.order_id JOIN vendors v ON v.id=o.vendor_id ORDER BY CASE op.status WHEN "submitted" THEN 0 WHEN "unpaid" THEN 1 WHEN "rejected" THEN 2 ELSE 3 END,op.created_at DESC LIMIT 150');?><section class="container section page-top"><div class="eyebrow">CONTROL CENTER</div><h1 class="page-title">Admin <em>Dashboard</em></h1><div class="stat-grid"><?php foreach(['Người dùng','Gian hàng','Sản phẩm','Đơn hàng'] as $i=>$label):?><div class="stat-card"><?=h($label)?><strong><?=$stats[$i]?></strong></div><?php endforeach;?></div><div class="panel"><h2>Duyệt gian hàng</h2><div class="table-wrap"><table><tr><th>Gian hàng</th><th>Email</th><th>Trạng thái</th><th>Hành động</th></tr><?php foreach($vendors as $v):?><tr><td><?=h($v['shop_name'])?></td><td><?=h($v['email'])?></td><td><?=h($v['status'])?></td><td><?php actionform('approve_vendor',['id'=>$v['id'],'status'=>'approved'],'Duyệt');actionform('approve_vendor',['id'=>$v['id'],'status'=>'rejected'],'Từ chối','btn btn-ghost btn-sm');?></td></tr><?php endforeach;?></table></div></div><div class="panel"><h2>Kiểm duyệt sản phẩm</h2><div class="table-wrap"><table><tr><th>Sản phẩm</th><th>Gian hàng</th><th>Giá</th><th>Trạng thái</th><th>Hành động</th></tr><?php foreach($products as $p):?><tr><td><?=h($p['name'])?></td><td><?=h($p['shop_name'])?></td><td><?=money($p['price'])?></td><td><?=h($p['status'])?></td><td><?php actionform('approve_product',['id'=>$p['id'],'status'=>'active'],'Hiển thị');actionform('approve_product',['id'=>$p['id'],'status'=>'hidden'],'Ẩn','btn btn-ghost btn-sm');?></td></tr><?php endforeach;?></table></div></div><?php require __DIR__.'/views/admin_payments.php';?><?php require __DIR__.'/views/admin_wallet.php';?><div class="panel"><h2>Quản lý tài khoản</h2><div class="table-wrap"><table><tr><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr><?php foreach($users as $u):?><tr><td><?=h($u['full_name'])?></td><td><?=h($u['email'])?></td><td><?=h($u['role'])?></td><td><?=$u['status']?'Hoạt động':'Đã khóa'?></td><td><?php if($u['role']!=='admin')actionform('toggle_user',['id'=>$u['id']],$u['status']?'Khóa':'Mở khóa','btn btn-ghost btn-sm');?></td></tr><?php endforeach;?></table></div></div></section>
<?php else:?><section class="container section"><h1>404 · Trang không tồn tại</h1></section><?php endif;?></main><footer><div class="container footer-inner"><div><div class="logo"><span class="logo-icon">⬡</span> 3D<span>PRINT</span>HUB</div><p>Biến mọi ý tưởng thành hiện thực.</p></div><div>Explore<br><a href="<?=url('catalog')?>">Sản phẩm</a><a href="<?=url('quote')?>">In theo yêu cầu</a></div><div>Community<br><a href="<?=url('apply')?>">Trở thành người bán</a><a href="<?=url('orders')?>">Đơn hàng</a></div></div><div class="container copyright">© <?=date('Y')?> 3DPrintHub · Built for creators.</div></footer><script src="assets/app.js"></script></body></html>
