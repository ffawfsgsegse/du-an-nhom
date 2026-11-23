-- Chỉ dùng khi database chưa có bảng vòng quay. Chọn database 3dprinthub_db trước.
CREATE TABLE IF NOT EXISTS promotions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 discount_percent INT NOT NULL DEFAULT 0,
 quantity INT NOT NULL DEFAULT 100,
 start_date DATE NULL,
 end_date DATE NULL,
 status TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS user_rewards (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 promotion_id INT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 used_at DATETIME NULL,
 INDEX idx_user_created(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO promotions(name,discount_percent,quantity,status)
SELECT 'Voucher demo 10%',10,100,1 WHERE NOT EXISTS(SELECT 1 FROM promotions);
