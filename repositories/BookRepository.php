<?php
require_once __DIR__ . '/../entities/Book.php';

class BookRepository
{
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function getAll()
    {
        return $this->db->query('SELECT * FROM books ORDER BY title')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $statement = $this->db->prepare('SELECT * FROM books WHERE id = ?');
        $statement->execute(array($id));
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function add($book)
    {
        $statement = $this->db->prepare('INSERT INTO books (title, author, genre, year_published, total_copies, available_copies) VALUES (?, ?, ?, ?, ?, ?)');
        return $statement->execute(array($book->title, $book->author, $book->genre, $book->yearPublished, $book->totalCopies, $book->totalCopies));
    }

    public function update($book)
    {
        $statement = $this->db->prepare('UPDATE books SET title=?, author=?, genre=?, year_published=?, total_copies=?, available_copies=?, status=IF(? - (SELECT COUNT(*) FROM borrowings WHERE book_id=? AND returned_at IS NULL)=0,"BORROWED","AVAILABLE") WHERE id=?');
        return $statement->execute(array($book->title, $book->author, $book->genre, $book->yearPublished, $book->totalCopies, $book->totalCopies - $this->activeBorrowCount($book->id), $book->totalCopies, $book->id, $book->id));
    }

    public function activeBorrowCount($bookId)
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM borrowings WHERE book_id=? AND returned_at IS NULL');
        $statement->execute(array($bookId));
        return (int) $statement->fetchColumn();
    }

    public function activeBorrowers()
    {
        return $this->db->query("SELECT users.username, books.title, books.author, borrowings.borrowed_at, borrowings.due_at FROM borrowings JOIN users ON users.id=borrowings.user_id JOIN books ON books.id=borrowings.book_id WHERE borrowings.returned_at IS NULL ORDER BY borrowings.due_at, books.title")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $statement = $this->db->prepare('DELETE FROM books WHERE id=? AND status="AVAILABLE"');
        return $statement->execute(array($id));
    }

    public function borrowedBy($userId)
    {
        $statement = $this->db->prepare('SELECT books.*, borrowings.id AS borrowing_id, borrowings.borrowed_at, borrowings.due_at FROM books JOIN borrowings ON books.id=borrowings.book_id WHERE borrowings.user_id=? AND borrowings.returned_at IS NULL ORDER BY books.title');
        $statement->execute(array($userId));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function accountLoans($userId)
    {
        $statement = $this->db->prepare('SELECT borrowings.*, books.title, books.author, COALESCE((SELECT SUM(amount) FROM fine_payments WHERE borrowing_id=borrowings.id), 0) AS paid FROM borrowings JOIN books ON books.id=borrowings.book_id WHERE borrowings.user_id=? ORDER BY borrowings.borrowed_at DESC');
        $statement->execute(array($userId));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function payFine($borrowingId, $userId, $amount)
    {
        $receipt = 'RCPT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $statement = $this->db->prepare('INSERT INTO fine_payments (borrowing_id, user_id, amount, payment_method, receipt_number) VALUES (?, ?, ?, ?, ?)');
        $statement->execute(array($borrowingId, $userId, $amount, 'DEMO', $receipt));
        return $this->db->lastInsertId();
    }

    public function paymentReceipt($paymentId, $userId)
    {
        $statement = $this->db->prepare('SELECT fine_payments.*, books.title, borrowings.borrowed_at, borrowings.due_at FROM fine_payments JOIN borrowings ON borrowings.id=fine_payments.borrowing_id JOIN books ON books.id=borrowings.book_id WHERE fine_payments.id=? AND fine_payments.user_id=?');
        $statement->execute(array($paymentId, $userId));
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function borrow($bookId, $userId)
    {
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare('SELECT available_copies FROM books WHERE id=? FOR UPDATE');
            $lock->execute(array($bookId));
            if ((int) $lock->fetchColumn() < 1) { $this->db->rollBack(); return false; }
            $count = $this->db->prepare('SELECT COUNT(*) FROM borrowings WHERE user_id=? AND returned_at IS NULL');
            $count->execute(array($userId));
            if ((int) $count->fetchColumn() >= 3) { $this->db->rollBack(); return false; }
            $this->db->prepare('INSERT INTO borrowings (user_id, book_id, due_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 14 DAY))')->execute(array($userId, $bookId));
            $this->db->prepare('UPDATE books SET available_copies=available_copies-1, status=IF(available_copies-1=0,"BORROWED","AVAILABLE") WHERE id=?')->execute(array($bookId));
            $this->db->commit();
            return true;
        } catch (Exception $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    public function returnBook($bookId, $userId)
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare('UPDATE borrowings SET returned_at=NOW() WHERE book_id=? AND user_id=? AND returned_at IS NULL');
            $statement->execute(array($bookId, $userId));
            if ($statement->rowCount() === 0) { $this->db->rollBack(); return false; }
            $this->db->prepare('UPDATE books SET available_copies=available_copies+1, status="AVAILABLE" WHERE id=?')->execute(array($bookId));
            $this->db->commit();
            return true;
        } catch (Exception $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }
}
