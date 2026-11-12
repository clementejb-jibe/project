USE book_catalog;

-- Run once on a database that already has the borrowing and fine tables.
ALTER TABLE books ADD COLUMN total_copies INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE books ADD COLUMN available_copies INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE books SET total_copies = GREATEST(1, (SELECT COUNT(*) FROM borrowings WHERE borrowings.book_id=books.id AND borrowings.returned_at IS NULL));
UPDATE books SET available_copies = total_copies - (SELECT COUNT(*) FROM borrowings WHERE borrowings.book_id=books.id AND borrowings.returned_at IS NULL);
UPDATE books SET status = IF(available_copies=0, 'BORROWED', 'AVAILABLE');
