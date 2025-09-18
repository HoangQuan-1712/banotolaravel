# Hướng dẫn cài đặt Live Chat

## 1. Cấu hình Database
Chạy migrations để tạo bảng chats, messages và notifications:
```bash
php artisan migrate
```

## 2. Cấu hình Pusher
Trong file `.env`, thay đổi các giá trị sau:

```env
# Thay đổi BROADCAST_DRIVER từ "log" thành "pusher"
BROADCAST_DRIVER=pusher

# Cấu hình Pusher (đăng ký tại https://pusher.com)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1

# Hoặc sử dụng local pusher server (Laravel WebSockets)
# PUSHER_HOST=127.0.0.1
# PUSHER_PORT=6001
# PUSHER_SCHEME=http
```

## 3. Cài đặt Pusher PHP SDK
```bash
composer require pusher/pusher-php-server
```

## 4. Tính năng Live Chat

### Cho khách hàng:
- Nút "Live Chat" floating ở góc phải dưới màn hình
- Click vào nút sẽ mở chat widget trực tiếp trên trang
- Chat widget có thể thu gọn/mở rộng
- Hiển thị số tin nhắn chưa đọc trên nút chat
- Có thể gửi tin nhắn và file đính kèm
- Hiển thị trạng thái admin online/offline
- Hiển thị khi admin đang gõ
- Giao diện chat đẹp với bubble messages

### Cho admin:
- Menu "Live Chat" trong dropdown Admin
- **Giao diện chat kiểu Messenger/WhatsApp Web:**
  + **Sidebar trái**: Danh sách tất cả cuộc chat với khách hàng
  + **Khung chat chính**: Hiển thị cuộc trò chuyện được chọn
- **Tính năng thông minh:**
  + Tin nhắn mới tự động đẩy chat lên đầu danh sách
  + Hiển thị số tin nhắn chưa đọc (badge đỏ)
  + Avatar với chữ cái đầu tên khách hàng
  + Trạng thái online/offline của khách hàng
- **Realtime features:**
  + Tin nhắn hiển thị ngay lập tức
  + Typing indicator khi khách đang gõ
  + Auto-assign chat khi admin click vào
- **Quản lý chat:**
  + Tìm kiếm cuộc trò chuyện
  + Nhận/đóng chat từ dropdown menu
  + Upload file đính kèm

## 5. Routes đã được tạo:
- `GET /chat` - Mở chat box cho user
- `GET /admin/chats` - Danh sách chat cho admin
- `GET /admin/chats/{chat}` - Giao diện chat chi tiết cho admin
- `POST /admin/chats/{chat}/assign` - Admin nhận chat
- `POST /admin/chats/{chat}/close` - Đóng chat
- `GET /chat/{chat}/messages` - Lấy danh sách tin nhắn
- `POST /chat/{chat}/messages` - Gửi tin nhắn mới
- `POST /chat/{chat}/typing` - Gửi trạng thái đang gõ

## 6. Broadcasting Channels:
- `chat.{chatId}` - Private channel cho từng chat
- `presence.admins` - Presence channel theo dõi admin online

## 7. Lưu ý:
- Đảm bảo đã chạy `php artisan config:cache` sau khi thay đổi .env
- Nếu sử dụng Pusher cloud, cần đăng ký tài khoản tại pusher.com
- Nếu sử dụng local, cần cài đặt Laravel WebSockets package

## 8. Bảo mật:
- Thông tin Pusher credentials được lưu trong file .env (không commit lên git)
- JavaScript chỉ sử dụng thông tin từ meta tags được render từ server
- Private channels được bảo vệ bằng authentication trong routes/channels.php
- Chỉ user sở hữu chat hoặc admin mới có thể truy cập channel

## 9. Test:
1. Đăng nhập với tài khoản user (không phải admin)
2. Thấy nút "Live Chat" ở góc phải dưới
3. Click vào nút để mở chat
4. Đăng nhập admin ở tab khác, vào menu Admin > Live Chat
5. Admin có thể thấy và trả lời chat của user

## 10. Chạy lệnh sau khi cập nhật .env:
```bash
php artisan config:cache
php artisan route:cache
```
npm run dev
php artisan queue:work