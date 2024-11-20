# Requirement

https://laraveldaily.com/post/demo-project-laravel-support-ticket-system

Xây dụng hệ thống quản lý support ticket

## User
- Có thể tạo ticket. Mỗi khi tạo ticket sẽ gửi email tới admin
- Chỉ list ticket do mình tạo. Cho phép filter theo status, priority, category

## Agent
- Chỉ list các ticket đang được assign
- Có thể update status của ticket

## Admin
- List toàn bộ ticket
- Có thể update các thông tin của ticket


# Trạng thái hiện tại của project:
- Đã hoàn thành authen & author
- Done category & label CRUD



# Tính năng sẽ làm:
- Tạo ticket
- List ticket
- Viết unit test


# Flow thực hiện:
- Tạo migration: ticket, ticket & category, ticket & label
- Khai báo model
- Tạo controller, khai báo logic cho action create & list ticket
- Viết unit test