<?php
require_once __DIR__ . '/../entities/Book.php';

class BookService
{
    private $books;
    public function __construct($books) { $this->books = $books; }

    public function save($data, $id = null)
    {
        $title = trim($data['title'] ?? '');
        $author = trim($data['author'] ?? '');
        $genre = trim($data['genre'] ?? '');
        $year = filter_var($data['year_published'] ?? '', FILTER_VALIDATE_INT);
        $copies = filter_var($data['total_copies'] ?? '', FILTER_VALIDATE_INT);
        if ($title === '' || $author === '' || $genre === '' || !$year || $year < 1 || $year > (int) date('Y') || !$copies || $copies < 1) return 'Fill in every field, enter a valid publication year, and set stock to at least 1.';
        if ($id && $copies < $this->books->activeBorrowCount($id)) return 'Stock cannot be lower than the number of copies currently borrowed.';
        $book = new Book($id, $title, $author, $genre, $year, 'AVAILABLE', $copies);
        if ($id) $this->books->update($book); else $this->books->add($book);
        return true;
    }
}
