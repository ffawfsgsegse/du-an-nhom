USE `3dprinthub_db`;

CREATE TABLE IF NOT EXISTS order_cancel_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    request_token CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    reason VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    review_note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    UNIQUE KEY uq_order_cancel_request_token (request_token),
    KEY ix_order_cancel_order (order_id, id),
    KEY ix_order_cancel_status (order_id, status),
    CONSTRAINT fk_order_cancel_order FOREIGN KEY (order_id)
        REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
