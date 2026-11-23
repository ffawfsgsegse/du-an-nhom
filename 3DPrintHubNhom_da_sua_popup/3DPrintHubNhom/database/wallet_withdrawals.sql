USE `3dprinthub_db`;

CREATE TABLE IF NOT EXISTS wallet_withdrawals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
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
    UNIQUE KEY uq_wallet_withdrawal_request (user_id,request_key),
    KEY idx_wallet_withdrawal_status (status,id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS withdrawal_bank_receipts (
    bank_reference VARCHAR(120) NOT NULL PRIMARY KEY,
    source_kind ENUM('wallet','vendor') NOT NULL,
    source_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uq_withdrawal_receipt_source (source_kind,source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO withdrawal_bank_receipts(bank_reference,source_kind,source_id)
SELECT bank_reference,'vendor',id FROM vendor_payouts WHERE status='paid' AND bank_reference IS NOT NULL;

SET @tx_type = (
    SELECT COLUMN_TYPE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='wallet_transactions' AND COLUMN_NAME='type'
);
SET @tx_extra = CONCAT(
    IF(LOCATE('''wallet_withdrawal''',@tx_type)>0,'',', ''wallet_withdrawal'''),
    IF(LOCATE('''wallet_withdrawal_return''',@tx_type)>0,'',', ''wallet_withdrawal_return''')
);
SET @tx_sql = IF(@tx_extra='', 'DO 0',
    CONCAT('ALTER TABLE wallet_transactions MODIFY COLUMN type ',LEFT(@tx_type,CHAR_LENGTH(@tx_type)-1),@tx_extra,') NOT NULL')
);
PREPARE withdrawal_tx_stmt FROM @tx_sql;
EXECUTE withdrawal_tx_stmt;
DEALLOCATE PREPARE withdrawal_tx_stmt;
