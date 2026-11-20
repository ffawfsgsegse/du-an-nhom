# 3DPrintHub — Sàn bán sản phẩm in 3D / 3 vai trò

**Phiên bản:** PHP thuần + MySQL + Laragon, source chia theo chức năng (không dùng framework).

**Mục đích:** đồ án và trình diễn quy trình marketplace nhiều gian hàng. Có COD và MoMo/QR **DEMO**, không có chuyển tiền thật, không có tích hợp đối tác vận chuyển. Chưa được kiểm thử đủ để vận hành thương mại công khai.

## 1. Cài trên Laragon (cài mới)

1. Giải nén, đổi tên thư mục thành `3DPrintHubDemo`, đặt trong `D:\laragon\www\3DPrintHubDemo` (hoặc nơi Laragon của bạn cấu hình).
2. Mở Laragon > **Start All** để bật Apache và MySQL.
3. Mở Laragon > **Database** / **HeidiSQL**, kết nối MySQL.
4. Trong HeidiSQL: **File > Run SQL file...**, chọn `database\3DPrintHub_DATABASE_ALL_IN_ONE.sql` trong dự án. File này tạo database `3dprinthub_db`, 18 bảng, dữ liệu mẫu nếu bảng `users` đang rỗng.
5. Trong `config.php`, kiểm tra tên database, tài khoản MySQL và mật khẩu. Mặc định Laragon thường là `root` / mật khẩu để trống.
6. Vào trình duyệt: `http://localhost/3DPrintHubDemo/`.

**QUAN TRỌNG:** Nếu bạn đã có database `3dprinthub_db`, **hãy xuất file SQL sao lưu trước** khi import. File all-in-one không có `DROP TABLE` hay `DELETE`, nhưng có `ALTER TABLE` và các lệnh nâng cấp; tránh chạy đi chạy lại khi không cần. Nếu bảng đã đủ như trong file SQL, bạn chỉ cần chép source mới và dùng database cũ.

## 2. Tài khoản mẫu (chỉ có khi cài mới và `users` trống)

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | admin@3dprinthub.vn | password |
| Người bán đã duyệt | techlab@3dprinthub.vn | password |
| Người bán đã duyệt | artprint@3dprinthub.vn | password |
| Khách hàng | nguyenvana@gmail.com | password |

**Nếu import vào DB đã có tài khoản và chưa có Admin:** mở Terminal của Laragon tại thư mục `3DPrintHubDemo` và chạy `php tools/create_admin.php`. Nhập email, họ tên và mật khẩu mới có độ dài tối thiểu 10 ký tự. Tool chỉ hoạt động qua Terminal, không chạy trên web. Nếu email đã tồn tại, script không thay đổi tài khoản cũ.

Đừng đẩy `config.php` đã thay bằng mật khẩu thật lên GitHub public. Đổi mật khẩu demo trước khi dùng ngoài lớp học.

## 3. Phân quyền 3 vai trò

**Khách hàng (`client`):** đăng ký/đăng nhập, xem sản phẩm, giỏ hàng, đặt đơn COD hoặc MoMo/QR DEMO, lịch sử đơn, gửi hồ sơ trở thành người bán.

**Người bán (`vendor`):** sau khi Admin duyệt hồ sơ, có Dashboard, quản lý vật liệu, đăng/sửa/ẩn/xóa mềm sản phẩm thuộc shop, theo dõi và chuyển trạng thái đơn hàng, xem doanh thu hoàn tất.

**Quản trị (`admin`):** Dashboard toàn sàn, quản lý sản phẩm tất cả shop, duyệt/ẩn/xóa mềm, quản lý danh mục, duyệt shop, khóa/mở tài khoản không phải Admin, xem đơn, thống kê doanh thu **ngày/tuần/tháng/quý/năm**, shop bán nhiều/ít và khách mua nhiều nhất.

`app/authorization.php` là **file trung tâm phân quyền**. Trạng thái `role` được đọc từ DB ở mỗi request; nút có thể ẩn trên giao diện, nhưng quyền luôn được kiểm tra ở PHP.

## 4. Muốn sửa chức năng nào thì mở file nào?

| Muốn sửa | File |
|---|---|
| Thông tin kết nối DB | `config.php` |
| Phân quyền client/vendor/admin | `app/authorization.php` |
| Trang nào được hiển thị | `index.php` (bảng `$routes`) |
| Đăng ký/đăng nhập/đăng xuất | `app/actions/auth.php`, `views/pages/login.php`, `views/pages/register.php` |
| Gửi hồ sơ mở gian hàng | `views/pages/apply.php`, `app/actions/auth.php` |
| Menu / header | `views/layouts/header.php`, `assets/css/header.css` |
| Trang chủ | `views/pages/home.php`, `assets/css/home.css` |
| Danh sách/chi tiết sản phẩm | `views/pages/catalog.php`, `views/pages/product.php` |
| Thẻ sản phẩm chung | `views/partials/product_card.php` |
| Giỏ hàng | `views/pages/cart.php`, `app/actions/cart.php` |
| Đặt hàng / thanh toán demo | `views/pages/checkout.php`, `views/pages/demo_payment.php`, `app/actions/checkout.php` |
| Lịch sử đơn khách | `views/pages/orders.php` |
| Dashboard người bán | `views/vendor/dashboard.php` |
| Trang quản lý SP người bán | `views/vendor/products.php`, `views/vendor/product_form.php` |
| Mẫu thêm/sửa SP dùng chung | `views/partials/product_form.php` |
| Đơn hàng người bán | `views/vendor/orders.php`, `app/actions/vendor.php` |
| CRUD sản phẩm / soft delete | `app/actions/products.php` |
| Dashboard Admin | `views/admin/dashboard.php` |
| Quản lý SP Admin | `views/admin/products.php`, `views/admin/product_form.php` |
| Quản lý danh mục | `views/admin/categories.php`, `app/actions/admin.php` |
| Duyệt gian hàng | `views/admin/vendors.php`, `app/actions/admin.php` |
| Khóa/mở người dùng | `views/admin/users.php`, `app/actions/admin.php` |
| Đơn hàng toàn sàn | `views/admin/orders.php` |
| Biểu đồ / top shop/khách | `views/admin/analytics.php`, `app/repositories/analytics.php`, `assets/js/admin_charts.js` |
| SQL sản phẩm | `app/repositories/products.php` |
| SQL đơn hàng | `app/repositories/orders.php` |
| CSS của phần sàn bán hàng | `assets/css/marketplace.css` |
| CSS Admin và người bán | `assets/css/dashboard.css` |
| JavaScript chung | `assets/js/marketplace.js` |
| Database đầy đủ | `database/3DPrintHub_DATABASE_ALL_IN_ONE.sql` |
| Tạo Admin bằng Terminal | `tools/create_admin.php` |

## 5. Quy trình kiểm thử đề xuất

1. **Khách:** đăng ký tài khoản mới, đăng nhập, truy cập `?page=apply`, nộp tên và địa chỉ gian hàng.
2. **Admin:** đăng nhập, mở `?page=admin_vendors`, duyệt shop mới. Lúc này role `users.role` của khách được cập nhật thành `vendor`.
3. **Người bán:** mở `?page=vendor_products`, thêm vật liệu, sau đó mở `?page=vendor_product_edit` để tạo sản phẩm và tải ảnh JPG/PNG/WEBP. Sản phẩm ở trạng thái `pending`.
4. **Admin:** vào `?page=admin_products` để duyệt sản phẩm. Sau duyệt, sản phẩm xuất hiện công khai ở `?page=catalog`.
5. **Khách:** thêm SP vào giỏ rồi đặt đơn **COD** hoặc **MoMo/QR DEMO**. Với DEMO, mở trang giao dịch từ lịch sử đơn và bấm mô phỏng thành công. Không chuyển tiền thật.
6. **Người bán:** vào `?page=vendor_orders`, chuyển đơn qua các trạng thái `pending → printing → shipping → completed`; với COD, nút hoàn tất hàm ý đã giao hàng và thu tiền trong môi trường demo.
7. **Admin:** mở `?page=admin_analytics` để xem đồ thị doanh thu thực từ các đơn `completed` và bảng xếp hạng shop/khách. Nếu chưa có đơn hoàn tất, biểu đồ chưa có dữ liệu.

## 6. Luồng code và bảo vệ dữ liệu

- `index.php` là router duy nhất, sử dụng `?page=...` cho trang và POST `action=...` cho chức năng.
- `app/helpers.php` gồm SQL prepared statements, HTML escaping, CSRF, flash, upload ảnh.
- `app/authorization.php` kiểm tra tài khoản và role từ `users` tại mỗi request.
- `app/actions/*.php` nhận POST, kiểm tra quyền, kiểm tra dữ liệu; `app/repositories/*.php` đọc dữ liệu bằng PDO.
- `checkout` gom sản phẩm theo shop, khóa hàng tồn bằng `FOR UPDATE`, tạo **một đơn cho mỗi shop** và trừ tồn kho trong MySQL transaction.
- Người mua chỉ hủy đơn ở `pending` nếu chưa được xác nhận thanh toán demo; kho được hoàn lại trong transaction.
- Người bán chỉ sửa sản phẩm thuộc shop mình. Admin có thể sửa sản phẩm của shop bất kỳ đã duyệt nhưng phải chọn vật liệu đúng shop.
- Xóa sản phẩm dùng `products.deleted_at`, không xóa vĩnh viễn bản ghi đơn hàng cũ.
- Biểu đồ tính từ `orders.order_status = completed`. Các đơn hoàn tất cũ có `completed_at` xấp xỉ theo `created_at` từ bản SQL nâng cấp.
- Upload ảnh đổi sang tên ngẫu nhiên, kiểm MIME, giới hạn 3MB; `uploads/products/.htaccess` chặn thực thi PHP trong thư mục ảnh trên Apache.
- QR/MoMo demo sử dụng ảnh minh họa; xác nhận mô phỏng chỉ cho phép `localhost`. **Không có webhook, không ghi nhận tiền thật.**

## 7. Hạn chế bản học tập

Không được dùng mã nguồn này trực tiếp để mở sàn thương mại công khai hoặc nhận tiền thật. Cần thêm kiểm thử trên MySQL và trình duyệt, phân quyền theo đối tượng mở rộng, kiểm soát nhiều máy chủ, thanh toán được cấp phép, đăng nhập an toàn hơn, quy trình hoàn trả, tích hợp vận chuyển, nhật ký và giám sát bảo mật.

## 8. Yêu cầu môi trường

- PHP 8.1+ với PDO MySQL, Fileinfo và `getimagesize()`.
- MySQL 8.0+/MariaDB tương thích cấu trúc SQL; PHP server Apache/Laragon.
- Cần bật MySQL, Apache, đường dẫn quyền ghi `uploads/products`.
- Hình minh họa QR được cung cấp sẵn trong `assets/`; không cần kết nối ngân hàng.
