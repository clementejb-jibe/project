<?php
require_once __DIR__ . '/../services/BookService.php';

class BookController
{
    private $books;
    private $service;
    public function __construct($books, $service) { $this->books = $books; $this->service = $service; }

    public function list()
    {
        $allBooks = $this->books->getAll();
        $myBooks = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'USER' ? $this->books->borrowedBy($_SESSION['user']['id']) : array();
        $borrowNotice = $_SESSION['borrow_notice'] ?? null;
        unset($_SESSION['borrow_notice']);
        require __DIR__ . '/../views/books.php';
    }

    public function borrowers()
    {
        $borrowers = $this->books->activeBorrowers();
        require __DIR__ . '/../views/borrowers.php';
    }

    public function form($id = null)
    {
        $book = $id ? $this->books->find($id) : null;
        if ($id && !$book) { $_SESSION['flash'] = 'Book not found.'; header('Location: index.php'); exit; }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->service->save($_POST, $id);
            if ($result === true) { $_SESSION['flash'] = $id ? 'Book updated.' : 'Book added.'; header('Location: index.php'); exit; }
            $error = $result;
            $book = array_merge($book ?: array(), $_POST);
        }
        require __DIR__ . '/../views/book_form.php';
    }

    public function delete($id)
    {
        if (!$this->books->delete($id)) $_SESSION['flash'] = 'This book could not be deleted. Return it first if it is borrowed.';
        else $_SESSION['flash'] = 'Book deleted.';
        header('Location: index.php'); exit;
    }

    public function borrow($id)
    {
        if ($this->books->borrow($id, $_SESSION['user']['id'])) {
            foreach ($this->books->borrowedBy($_SESSION['user']['id']) as $book) if ((int) $book['id'] === (int) $id) $_SESSION['borrow_notice'] = $book;
            $_SESSION['flash'] = 'Book borrowed successfully.';
        } else $_SESSION['flash'] = 'Could not borrow this book. It may be unavailable or you may already have three books.';
        header('Location: index.php'); exit;
    }

    public function returnBook($id)
    {
        $_SESSION['flash'] = $this->books->returnBook($id, $_SESSION['user']['id']) ? 'Book returned.' : 'Could not return that book.';
        header('Location: index.php'); exit;
    }
}
