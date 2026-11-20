# Admin riêng theo video — 3DPrintHub

Phiên bản giao diện: dashboard kính mờ xanh ngọc, sidebar bên trái, thống kê/chức năng theo đúng mô hình PHP + MySQL của dự án.

## Mở trang Admin

1. Giải nén **toàn bộ dự án**, đặt thư mục `3DPrintHubDemo` trong `D:\laragon\www\`.
2. Bật **Start All** trong Laragon; sử dụng database MySQL đang có (nếu chưa có mới import file SQL trong `database/`).
3. Truy cập `http://localhost/3DPrintHubDemo/index.php?page=login` và đăng nhập bằng tài khoản có `users.role = 'admin'`.
4. Truy cập **`http://localhost/3DPrintHubDemo/admin.php`**. Mọi chức năng Admin liên kết qua file này (`?section=products`, `?section=users`, v.v.).
5. Có thể truy cập đường dẫn cũ `index.php?page=admin_dashboard`; nó cũng hiển thị giao diện Admin mới và giữ nguyên phân quyền.

**Lưu ý:** Giữ nguyên `config.php` phù hợp máy của bạn. Không nhập lại file SQL lên database có dữ liệu chỉ để đổi giao diện. Trước khi thay source, nên sao lưu file và MySQL. Không dùng tài khoản/khóa API demo khi triển khai công khai.

## Các file sửa giao diện dễ nhất

| Nhu cầu | File để sửa |
|---|---|
| Điểm vào Admin, tên các trang | **`admin.php`** |
| Màu, kích thước, bố cục, responsive Admin | **`assets/css/admin_portal.css`** (màu ở đầu file) |
| Sidebar / Topbar / avatar | `views/admin/layout_start.php` |
| Dashboard, KPI, bảng chờ duyệt | **`views/admin/dashboard.php`** |
| Trang sản phẩm / danh mục / shop | `views/admin/products.php`, `categories.php`, `vendors.php` |
| Trang người dùng / đơn hàng | `views/admin/users.php`, `orders.php` |
| Biểu đồ & xếp hạng doanh thu | `views/admin/analytics.php`, `assets/js/admin_charts.js` |
| Bộ lọc tìm kiếm chung | `views/admin/_filters.php`, `assets/js/admin_portal.js` |
| Cập nhật KPI tự động | **`admin_api.php`** (chỉ đọc, mỗi 30 giây ở Dashboard) |
| Các nút duyệt/khóa/ẩn/lưu | `app/actions/admin.php`, `app/actions/products.php` |
| Truy vấn dữ liệu | `app/repositories/analytics.php`, `app/repositories/products.php` |

## Chức năng thực tế

- Sidebar riêng theo video: tổng quan, sản phẩm, danh mục, gian hàng, người dùng, đơn hàng, thống kê, chi trả shop và rút ví.
- KPI số người dùng, shop, sản phẩm, đơn, doanh thu của **đơn đã hoàn tất**, số hồ sơ đang chờ duyệt.
- Biểu đồ cột doanh thu theo tháng trên dashboard, phân bổ trạng thái đơn, bảng đơn mới, hàng chờ duyệt. Trang thống kê có phân tích ngày/tuần/tháng/quý/năm, xếp hạng shop, khách.
- Tìm kiếm **tức thì** theo nội dung đã tải trên trang; lọc **nhiều thuộc tính đồng thời** (trạng thái + shop + danh mục; trạng thái + phương thức thanh toán + shop; vai trò + trạng thái). Không tải lại trang khi lọc.
- Danh sách người dùng, shop, đơn: mặc định nạp **tối đa 500 bản ghi gần nhất** để tránh trang chậm. Lọc trên những dòng đã tải, **không phải truy vấn toàn bộ CSDL**. Nếu dự án có hàng nghìn bản ghi, cần bổ sung phân trang và tìm kiếm SQL ở máy chủ.
- Dashboard tự làm mới dữ liệu từ MySQL qua **`admin_api.php` mỗi 30 giây** khi tab đang hiển thị; có thể bấm **Làm mới**. Các trang danh sách nạp dữ liệu mới khi tải lại trang hoặc sau mỗi thao tác.
- Giao diện tương thích màn hình điện thoại (menu mở/đóng), màu xanh ngọc giống tinh thần video; không thay CSS trang chủ hay trang người bán.
- Đã giữ lại kiểm tra phiên đăng nhập, vai trò lấy từ MySQL, CSRF với các form POST, và chức năng CRUD/kiểm duyệt sẵn có.

## Kiểm tra nhanh

1. Vào `admin.php` thấy dashboard, thống kê là số liệu MySQL (không có dữ liệu giả).
2. Vào **Sản phẩm**, gõ tên rồi chọn **Trạng thái** và **Gian hàng**: bảng chỉ còn dòng khớp **tất cả** điều kiện. Chọn **Xóa lọc** để hiện toàn bộ.
3. Vào **Gian hàng**, duyệt 1 hồ sơ đang chờ. Tải dashboard để đối chiếu số lượng chờ duyệt thay đổi.
4. Vào **Sản phẩm** → sửa/ẩn/duyệt: thử bằng tài khoản Admin; tài khoản khác phải bị từ chối khi truy cập Admin.
5. Vào **Thống kê** thử thay Ngày / Tuần / Tháng / Quý / Năm; xem biểu đồ lấy dữ liệu của đơn hoàn tất.

### Cấu trúc & an toàn

`admin.php` là **cửa vào riêng**, **không** sao chép toàn bộ xử lý backend vào một file để tránh hai bản logic khác nhau. Các màn hình nằm trong thư mục `views/admin/` dễ chỉnh và các file CSS/JS Admin cũng tách biệt. Nếu PHP truy cập không được CSDL, xem thông tin MySQL trong `config.php`.

## Thống kê mới: Sản phẩm và tỉnh/thành mua nhiều / ít nhất

Truy cập `admin.php` (Tổng quan) hoặc `admin.php?section=analytics` (Thống kê). Sau biểu đồ doanh thu sẽ có bốn bảng:

- **Sản phẩm bán nhiều nhất:** 5 sản phẩm đang mở bán có tổng số chiếc bán trong đơn hoàn tất cao nhất.
- **Sản phẩm bán ít nhất:** 5 sản phẩm đang mở bán có tổng số chiếc thấp nhất, kể cả 0 chiếc.
- **Địa điểm mua nhiều nhất:** 5 tỉnh/thành nhận được nhiều đơn hoàn tất nhất, dựa vào địa chỉ giao hàng.
- **Địa điểm mua ít nhất:** 5 tỉnh/thành có ít đơn hoàn tất nhất, chỉ so các địa điểm **có phát sinh đơn**.

**Quy ước dữ liệu:** Chỉ tính `orders.order_status = 'completed'`; sản phẩm dùng `order_items.product_id`, `order_items.quantity`, loại trừ mẫu in đặt riêng không có `product_id`. Doanh thu sản phẩm dựa vào `order_items.unit_price`, không chứa phí vận chuyển. Các sản phẩm ở trạng thái chờ duyệt/ẩn/xóa mềm không nằm trong bảng đang mở bán.

**Địa điểm:** Do đơn hàng chỉ có cột `shipping_address` dạng văn bản, hệ thống cố gắng nhận diện tên tỉnh/thành ở CUỐI địa chỉ (có hỗ trợ các cách viết TP.HCM, HCM, Hà Nội, v.v.). Đơn không nhận diện được sẽ được **đếm riêng**, không đoán nhầm hoặc xếp hạng 'Không xác định' là ít mua. Các tỉnh chưa từng có đơn hoàn tất không được coi là nhóm 'mua ít nhất'. Việc xác định có thể không đầy đủ nếu địa chỉ thiếu tỉnh/thành, hoặc cách viết lạ. Muốn thống kê chính xác 100%, cần lưu `province_id` hoặc mã tỉnh/thành trong quy trình đặt hàng.

**Tách file để dễ sửa:**

| Nội dung | Đường dẫn |
|---|---|
| HTML của 4 bảng | `views/admin/_sales_insights.php` |
| Giao diện/màu sắc/kích thước | `assets/css/admin_sales_insights.css` |
| Cập nhật bảng tự động | `assets/js/admin_sales_insights.js` |
| SQL và xử lý tỉnh/thành | `app/repositories/sales_insights.php` |
| API làm mới bảng 30 giây | `admin_api.php` |

**Không cần import thêm SQL** để dùng thống kê này nếu cơ sở dữ liệu đã có cấu trúc của bản Admin trước. Dashboard làm mới số liệu sau 30 giây khi tab được mở hoặc bấm Làm mới; trang Thống kê nạp lại khi F5.
