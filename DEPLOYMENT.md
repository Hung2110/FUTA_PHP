# Hướng dẫn Triển khai - FUTA Advertising (Windows Server / IIS)

Tài liệu này cung cấp các bước chi tiết để DevOps triển khai dự án lên môi trường **Windows Server**, sử dụng **IIS** và **MySQL**.

---

## 🛠 1. Yêu cầu Hệ thống (System Requirements)

Đảm bảo Server đã cài đặt các thành phần sau trước khi bắt đầu:

*   **OS:** Windows Server (2016, 2019 hoặc 2022).
*   **Web Server:** IIS với tính năng **CGI (FastCGI)** đã được bật.
*   **IIS Modules (Bắt buộc phải cài thêm):** 
    *   URL Rewrite 2.1 (Để hỗ trợ Friendly URL định tuyến tự động).
    *   Application Request Routing (ARR) 3.0 (Để làm Reverse Proxy cho dịch vụ WebSocket).
*   **PHP:** Từ `8.1` trở lên (Khuyến nghị bản **Non-Thread Safe - NTS** cho IIS). Sử dụng PHP Manager cho IIS để dễ cấu hình.
*   **Database:** MySQL Server 5.7+ hoặc MariaDB.
*   **Công cụ:** `Composer` dành cho Windows.

**Cấu hình `php.ini` (Bắt buộc):**
Bật các extension: `mysqli`, `mbstring`, `fileinfo`, `openssl`, `curl`.
Cập nhật các thông số sau để hỗ trợ tính năng upload CV, Ảnh và Video Chat (hỗ trợ tối đa lên tới 50MB):
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 120
extension_dir = "ext"
```

---

## 2. Bước 1: Triển khai Mã nguồn & Cài đặt Thư viện

1. Clone/Copy mã nguồn vào thư mục root của Web Server (VD: `/var/www/html/futa_ads`).
2. Di chuyển vào thư mục dự án và cài đặt các thư viện thông qua Composer (Bao gồm PHPMailer và Ratchet WebSocket):
   ```bash
   cd /var/www/html/futa_ads
   composer install --no-dev --optimize-autoloader
   ```

---

## 3. Bước 2: Phân quyền Thư mục (Directory Permissions)
Hệ thống có nhiều tính năng Upload (Ảnh bài viết, CV ứng viên, File/Video chat, Carousel). Thư mục `uploads` và các thư mục con phải được cấp quyền ghi (write/775) cho Web Server User (VD: `www-data` trên Ubuntu).

```bash
# Tạo các thư mục nếu chưa tồn tại
mkdir -p /var/www/html/futa_ads/uploads/cv
mkdir -p /var/www/html/futa_ads/uploads/chat
mkdir -p /var/www/html/futa_ads/uploads/posts
mkdir -p /var/www/html/futa_ads/uploads/carousel

# Cấp quyền cho thư mục uploads
sudo chown -R www-data:www-data /var/www/html/futa_ads/uploads
sudo chmod -R 775 /var/www/html/futa_ads/uploads
```
*(Lưu ý: Nếu chưa có thư mục `uploads/cv/` và `uploads/posts/`, hệ thống sẽ tự tạo, nhưng thư mục cha `uploads/` phải có quyền ghi).*

---

## 4. Bước 3: Cấu hình Cơ sở dữ liệu (Database Configuration)
1. Import file SQL dump của dự án (nếu có) vào MySQL Server.
2. Mở file **`db.php`** ở thư mục gốc và thay đổi thông tin kết nối cho phù hợp với môi trường Production:

```php
// db.php
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$DB_NAME = getenv('DB_NAME') ?: 'futa_advertising';
$DB_CHARSET = getenv('DB_CHARSET') ?: 'utf8mb4';
$DB_COLLATE = getenv('DB_COLLATE') ?: 'utf8mb4_unicode_ci';
```

---

## 5. Bước 4: Cấu hình Gửi Email (PHPMailer)
Tính năng nhận form liên hệ (`contact.php`) và nhận đơn ứng tuyển (`includes/process_application.php`) đang sử dụng Gmail SMTP để gửi thông báo.

- **DevOps cần lưu ý:** Nếu server bị chặn port `587` (SMTP), cần mở port này trên Firewall.
- Để thay đổi email nhận thông báo, hãy sửa mảng `$adminEmails` bên trong 2 file trên:
  ```php
  $adminEmails = ['email_cua_ban@futa.vn'];
  ```

---

## 6. Bước 5: Thiết lập và Chạy WebSocket Server (RẤT QUAN TRỌNG)
Dự án sử dụng WebSocket (Ratchet PHP) cho tính năng Live Chat giữa Khách hàng và Admin.
File chạy WebSocket là **`chat_server.php`**, lắng nghe ở **Port 8080**.

### A. Mở Port trên Firewall
Đảm bảo Server đã mở Port `8080` (hoặc cấu hình Proxy qua Nginx/Apache để dùng wss:// nếu có chứng chỉ SSL).

### B. Chạy WebSocket Server ngầm (Background Service)
Không nên chạy lệnh `php chat_server.php` trực tiếp vì khi tắt Terminal nó sẽ chết. DevOps nên sử dụng **Supervisor** hoặc **Systemd** để quản lý.

**Ví dụ cấu hình bằng Systemd (`/etc/systemd/system/futa-chat.service`):**
```ini
[Unit]
Description=FUTA Advertising WebSocket Chat Server
After=network.target

[Service]
Type=simple
User=www-data
ExecStart=/usr/bin/php /var/www/html/futa_ads/chat_server.php
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
```

**Kích hoạt và chạy Service:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable futa-chat
sudo systemctl start futa-chat
sudo systemctl status futa-chat # Kiểm tra trạng thái
```

### C. Cấu hình WSS (WebSocket over SSL) - Khuyến nghị cho Production
Nếu website chạy HTTPS, trình duyệt sẽ chặn WebSocket chạy HTTP (`ws://`). Do đó, cần cấu hình Nginx/Apache làm Reverse Proxy để chuyển đổi `wss://` thành `ws://`.

**Ví dụ cấu hình Nginx block cho WebSocket:**
```nginx
# Thêm vào trong block server { ... } của Nginx
location /chat/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_read_timeout 86400;
}
```
*Sau khi cấu hình Nginx, cần sửa đường dẫn kết nối trong file mã nguồn (gồm `chat_widget.php` và `admin/chat.php`) từ `ws://localhost:8080` thành `wss://yourdomain.com/chat/`.*

---

## 7. Cấu hình Web Server Security (Tùy chọn)
Để tăng cường bảo mật, hãy đảm bảo Web Server không cho phép người dùng truy cập trực tiếp vào các file include cấu hình.

**Đối với Nginx:**
```nginx
location ~ \.(env|log|md|gitignore|json|lock)$ {
    deny all;
}
location ^~ /includes/ {
    deny all;
}
```
*(Dự án đã có sẵn file `web.config` cho IIS).*