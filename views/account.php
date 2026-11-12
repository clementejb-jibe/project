<?php require __DIR__ . '/header.php'; ?>
<h1>My account</h1>
<section class="card"><h2>Account information</h2><p><strong>Username:</strong> <?= e($account['username']) ?></p><p><strong>Account type:</strong> Student / user</p><p><strong>Member since:</strong> <?= e(readableDate($account['created_at'])) ?></p></section>
<h2>Borrowing history and fines</h2>
<?php if (!$loans): ?><div class="card">You have not borrowed any books yet.</div><?php endif; ?>
<?php foreach ($loans as $loan): $due = new DateTime($loan['due_at']); $daysLate = max(0, (int) $due->diff($today)->format('%r%a')); $fine = max(0, $daysLate * 10 - (float) $loan['paid']); ?>
<section class="card loan"><h3><?= e($loan['title']) ?> <small>by <?= e($loan['author']) ?></small></h3><p>Borrowed: <?= e(readableDate($loan['borrowed_at'])) ?></p><p>Return by: <?= e(readableDate($loan['due_at'])) ?></p><p>Status: <?= $loan['returned_at'] ? 'Returned on ' . e(readableDate($loan['returned_at'])) : ($daysLate ? 'Overdue by ' . $daysLate . ' day(s)' : 'On loan') ?></p><p>Overdue fine: <strong>₱<?= number_format($fine, 2) ?></strong> <small>(₱10 per day)</small></p><?php if ($fine > 0): ?><form method="post" action="index.php?page=pay-fine&id=<?= (int) $loan['id'] ?>" onsubmit="return confirm('Pay ₱<?= number_format($fine, 2) ?> using the demo payment flow?')"><button class="button">Pay fine and get receipt</button></form><?php endif; ?></section>
<?php endforeach; ?>
<?php require __DIR__ . '/footer.php'; ?>
