# HỆ THỐNG QUẢN LÝ KHO MÁY TÍNH & THIẾT BỊ CNTT

Hệ thống được phát triển theo **thuần mô hình MVC (Model - View - Controller)** kết hợp **Service Layer**, tuân thủ 100% tài liệu phân tích nghiệp vụ, Class Diagram, Database ERD và 13 Sequence Diagrams.

---

## 1. Công Nghệ Sử Dụng
* **Kiến trúc**: MVC (Model - View - Controller) kết hợp Service Layer theo đúng Class Diagram.
* **Backend**: PHP 8.x, Laravel Framework.
* **Database**: SQLite (mặc định sẵn sàng chạy ngay không cần cài đặt XAMPP/MySQL) hoặc MySQL (chuyển đổi qua `.env`).
* **Frontend / View**: Blade Template Engine, HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, Bootstrap Icons.
* **Giao dịch**: Database Transactions (`DB::transaction`) đảm bảo tính toàn vẹn dữ liệu.

---

## 2. Các Quy Tắc Nghiệp Vụ Đã Cài Đặt (QT1 -> QT8)
* **QT1**: Phiếu nhập kho bắt buộc chọn Nhà cung cấp, số lượng nhập > 0, đơn giá nhập > 0.
* **QT2**: Phiếu xuất kho bắt buộc chọn Khách hàng/nơi nhận. Số lượng xuất không được vượt quá số lượng tồn hiện tại ($SL_{xuất} \le Tồn$).
* **QT3**: Lưu phiếu và cập nhật tồn kho trong cùng một Database Transaction. Nếu có bất kỳ bước nào lỗi thì tự động Rollback toàn bộ.
* **QT4**: Mỗi lần tồn thay đổi (nhập, xuất, kiểm kê, hủy phiếu), hệ thống tự động ghi 1 bản ghi vào Thẻ kho (`stock_cards`) gồm ngày, loại giao dịch, mã phiếu, số lượng biến động và tồn lũy kế sau giao dịch.
* **QT5**: Khi hủy phiếu, hệ thống hoàn tác tồn kho. Nếu việc hoàn tác làm tồn bị âm thì chặn không cho hủy. Chỉ Quản lý và Admin mới được phép hủy phiếu.
* **QT6**: Giá vốn được tính theo **phương pháp bình quân gia quyền**, cập nhật sau mỗi lần nhập:
  $$\text{Giá vốn mới} = \frac{(\text{Tồn cũ} \times \text{Giá vốn cũ}) + (\text{SL nhập} \times \text{Đơn giá nhập})}{\text{Tồn cũ} + \text{SL nhập}}$$
  Khi xuất kho, giá vốn giữ nguyên không thay đổi.
* **QT7**: Khi tồn của một mặt hàng $\le$ Mức tồn tối thiểu (`min_quantity`), hệ thống tự động hiển thị cảnh báo hàng sắp hết trên Dashboard và Báo cáo.
* **QT8 (Mới nâng cấp theo yêu cầu giảng viên)**: **Cảnh báo hàng công nghệ tồn ứ đọng lâu năm theo thời gian mở bán (Aging Tech Inventory Alert)**:
  - Thiết bị điện tử (iPhone, Laptop, GPU...) khấu hao và rớt giá rất nhanh theo thời gian ra mắt.
  - Mỗi mặt hàng được quản lý theo **Ngày bắt đầu mở bán / kinh doanh (`release_date`)**.
  - **Ví dụ đề bài giảng viên**: *iPhone 17 bắt đầu bán từ tháng 09/2025, đến nay đã qua hơn 14 tháng mà vẫn còn tồn kho nhiều máy (13 máy đọng vốn hơn 370 triệu VNĐ)* &rarr; Hệ thống lập tức kích hoạt **Báo Động Đỏ** trên Dashboard, màn hình Tồn Kho và Báo Cáo Hàng Chậm Luân Chuyển, đồng thời đưa ra đề xuất giải pháp xử lý (Flash sale giảm giá 15-20%, bán buôn xả lô) để giải phóng dòng tiền.

---

## 3. Cải Tiến Giao Diện & Trải Nghiệm Người Dùng (UI/UX)
* **Bảng điều khiển trực quan (Dashboard Pro)**: Tích hợp sẵn sơ đồ 4 bước quy trình nghiệp vụ chuẩn giúp người dùng/giảng viên mới nhìn vào biết ngay cách vận hành hệ thống.
* **Cẩm nang hướng dẫn sử dụng (Help Modal)**: Nút "Hướng dẫn sử dụng" trực tiếp trên thanh điều hướng đầu trang, hướng dẫn chi tiết từng chức năng.
* **Giao diện bảng hiện đại (Modern Tables)**: Bảng dữ liệu được thiết kế lại đẹp mắt với font chữ Plus Jakarta Sans, màu sắc phân cấp rõ ràng, các thông số kỹ thuật (CPU, RAM, Ổ cứng, GPU) hiển thị dạng tag/badge gọn gàng.
* **Phân trang hoàn chỉnh (Pagination)**: Tất cả bảng dữ liệu (Danh mục máy tính, Phiếu nhập, Phiếu xuất, Nhà cung cấp, Khách hàng, Thẻ kho, Tài khoản) đều được phân trang chuyên nghiệp theo chuẩn Bootstrap 5, kèm thông tin đếm số lượng bản ghi hiển thị.

Mật khẩu chung cho tất cả tài khoản mẫu là: `password123`

| Tên đăng nhập | Mật khẩu | Họ và tên | Vai trò (Role) | Quyền hạn |
| :--- | :--- | :--- | :--- | :--- |
| **admin** | `password123` | Nguyễn Quản Trị | **Admin** (Quản trị viên) | Toàn quyền hệ thống, quản lý tài khoản & phân quyền, xóa/sửa |
| **quanly** | `password123` | Trần Quản Lý Kho | **Manager** (Quản lý kho) | Quản lý danh mục, kiểm kê & điều chỉnh tồn, hủy phiếu |
| **nhanvien** | `password123` | Lê Nhân Viên Kho | **Staff** (Nhân viên kho) | Lập phiếu nhập, lập phiếu xuất, xem tồn kho |

*(Màn hình đăng nhập có tích hợp sẵn 3 nút click nhanh tiện lợi để kiểm thử ngay lập tức).*

---

## 4. Hướng Dẫn Khởi Chạy Ứng Dụng

Khởi động web server:
```bash
php artisan serve
```

Truy cập trên trình duyệt tại địa chỉ:
```
http://localhost:8000
```

---

## 5. Chạy Kiểm Thử Tự Động (Unit / Feature Tests)
Hệ thống đã viết sẵn bộ test tự động kiểm chứng toàn bộ quy tắc nghiệp vụ:
```bash
php artisan test
```
