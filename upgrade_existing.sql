USE book_catalog;

-- Run once only when upgrading a database created with the first version.
ALTER TABLE borrowings DROP INDEX book_id;
ALTER TABLE borrowings ADD COLUMN due_at DATETIME NULL;
ALTER TABLE borrowings ADD COLUMN returned_at DATETIME NULL;
UPDATE borrowings SET due_at = DATE_ADD(borrowed_at, INTERVAL 14 DAY) WHERE due_at IS NULL;
ALTER TABLE borrowings MODIFY due_at DATETIME NOT NULL;
CREATE TABLE fine_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    borrowing_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    receipt_number VARCHAR(40) NOT NULL UNIQUE,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_borrowing FOREIGN KEY (borrowing_id) REFERENCES borrowings(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
