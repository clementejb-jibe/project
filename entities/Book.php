<?php
class Book
{
    public $id;
    public $title;
    public $author;
    public $genre;
    public $yearPublished;
    public $status;
    public $totalCopies;

    public function __construct($id = null, $title = '', $author = '', $genre = '', $yearPublished = null, $status = 'AVAILABLE', $totalCopies = 1)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->genre = $genre;
        $this->yearPublished = $yearPublished;
        $this->status = $status;
        $this->totalCopies = $totalCopies;
    }
}
