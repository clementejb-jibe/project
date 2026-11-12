<?php require __DIR__ . '/header.php'; ?>
<h1>Current borrowers</h1>
<p>These are the books that have not been returned yet.</p>
<div class="table-wrap"><table><thead><tr><th>Student username</th><th>Book</th><th>Author</th><th>Borrowed</th><th>Return by</th><th>Loan status</th></tr></thead><tbody>
<?php if (!$borrowers): ?><tr><td colspan="6">No books are currently borrowed.</td></tr><?php endif; ?>
<?php foreach ($borrowers as $borrower): $late = new DateTime($borrower['due_at']) < new DateTime(); ?><tr><td><?= e($borrower['username']) ?></td><td><?= e($borrower['title']) ?></td><td><?= e($borrower['author']) ?></td><td><?= e(readableDate($borrower['borrowed_at'])) ?></td><td><?= e(readableDate($borrower['due_at'])) ?></td><td><?= $late ? 'Overdue' : 'On loan' ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/footer.php'; ?>
