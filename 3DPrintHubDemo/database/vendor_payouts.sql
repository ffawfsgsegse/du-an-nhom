USE `3dprinthub_db`;

CREATE TABLE IF NOT EXISTS vendor_payouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    request_key CHAR(32) NOT NULL,
    amount DECIMAL(14,0) NOT NULL,
    fee DECIMAL(14,0) NOT NULL,
    transfer_amount DECIMAL(14,0) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    bank_account VARCHAR(50) NOT NULL,
    bank_holder VARCHAR(120) NOT NULL,
    status ENUM('pending','processing','paid','rejected') NOT NULL DEFAULT 'pending',
    handled_by INT NULL,
    bank_reference VARCHAR(120) NULL,
    rejection_reason VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    UNIQUE KEY uq_vendor_payout_request (vendor_id, request_key),
    UNIQUE KEY uq_vendor_payout_bank_ref (bank_reference),
    KEY idx_vendor_payout_status (status, id),
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE RESTRICT,
    FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vendor_payout_lines (
    payout_id BIGINT UNSIGNED NOT NULL,
    order_id INT NOT NULL,
    amount DECIMAL(14,0) NOT NULL,
    PRIMARY KEY (payout_id, order_id),
    KEY idx_payout_lines_order (order_id),
    FOREIGN KEY (payout_id) REFERENCES vendor_payouts(id) ON DELETE RESTRICT,
    FOREIGN KEY (order_id) REFERENCES vendor_earnings(order_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vendor_order_settlements (
    order_id INT NOT NULL PRIMARY KEY,
    amount DECIMAL(14,0) NOT NULL,
    bank_reference VARCHAR(120) NOT NULL,
    admin_id INT NOT NULL,
    confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
