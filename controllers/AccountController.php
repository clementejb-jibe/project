<?php
class AccountController
{
    private $users;
    private $books;
    public function __construct($users, $books) { $this->users = $users; $this->books = $books; }

    public function show()
    {
        $account = $this->users->getAccount($_SESSION['user']['id']);
        $loans = $this->books->accountLoans($_SESSION['user']['id']);
        $today = new DateTime('today');
        require __DIR__ . '/../views/account.php';
    }

    public function pay($borrowingId)
    {
        $loans = $this->books->accountLoans($_SESSION['user']['id']);
        foreach ($loans as $loan) {
            if ((int) $loan['id'] !== (int) $borrowingId) continue;
            $due = new DateTime($loan['due_at']);
            $daysLate = max(0, (int) $due->diff(new DateTime('today'))->format('%r%a'));
            $amount = max(0, $daysLate * 10 - (float) $loan['paid']);
            if ($amount > 0) {
                $paymentId = $this->books->payFine($borrowingId, $_SESSION['user']['id'], $amount);
                header('Location: index.php?page=receipt&id=' . (int) $paymentId); exit;
            }
        }
        $_SESSION['flash'] = 'There is no unpaid fine for that book.';
        header('Location: index.php?page=account'); exit;
    }

    public function receipt($paymentId)
    {
        $receipt = $this->books->paymentReceipt($paymentId, $_SESSION['user']['id']);
        if (!$receipt) { http_response_code(404); exit('Receipt not found.'); }
        require __DIR__ . '/../views/receipt.php';
    }
}
