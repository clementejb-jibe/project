<?php
$allBooks = $allBooks ?? array();
$myBooks = $myBooks ?? array();
$borrowNotice = $borrowNotice ?? null;
require __DIR__ . '/header.php';
?>
<?php if (!empty($borrowNotice)): ?><div class="modal-backdrop"><section class="modal card"><h2>Borrowing confirmed</h2><p><strong><?= e($borrowNotice['title']) ?></strong> by <?= e($borrowNotice['author']) ?></p><p>Borrowed: <?= e(readableDate($borrowNotice['borrowed_at'])) ?></p><p>Return by: <?= e(readableDate($borrowNotice['due_at'])) ?></p><a class="button" href="index.php?page=account">View my account</a><a class="close-link" href="index.php">Close</a></section></div><?php endif; ?>
<div class="heading">
    <div><h1>Books</h1><p>Browse the catalog and see what is available.</p></div>
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'ADMIN'): ?><div><a class="button" href="index.php?page=borrowers">View borrowers</a> <a class="button" href="index.php?page=add">Add a book</a></div><?php endif; ?>
</div>
<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'USER'): ?>
<section class="card"><h2>My borrowed books (<?= count($myBooks) ?>/3)</h2>
<?php if (!$myBooks): ?><p>You have not borrowed any books yet.</p><?php else: ?><ul class="borrowed-list"><?php foreach ($myBooks as $book): ?><li><?= e($book['title']) ?> <form method="post" action="index.php?page=return&id=<?= (int) $book['id'] ?>"><button class="link-button">Return</button></form></li><?php endforeach; ?></ul><?php endif; ?></section>
<?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Title</th><th>Author</th><th>Genre</th><th>Published</th><th>Stock</th><th>Status</th><?php if (isset($_SESSION['user'])): ?><th>Action</th><?php endif; ?></tr></thead><tbody>
<?php if (!$allBooks): ?><tr><td colspan="<?= isset($_SESSION['user']) ? 7 : 6 ?>">No books have been added yet.</td></tr><?php endif; ?>
<?php foreach ($allBooks as $book): ?><tr><td><?= e($book['title']) ?></td><td><?= e($book['author']) ?></td><td><?= e($book['genre']) ?></td><td><?= e($book['year_published']) ?></td><td><?= (int) $book['available_copies'] ?> available / <?= (int) $book['total_copies'] ?> total</td><td><span class="status <?= strtolower(e($book['status'])) ?>"><?= e($book['status']) ?></span></td>
<?php if (isset($_SESSION['user'])): ?><td class="actions"><?php if ($_SESSION['user']['role'] === 'ADMIN'): ?><a href="index.php?page=edit&id=<?= (int) $book['id'] ?>">Edit</a><form method="post" action="index.php?page=delete&id=<?= (int) $book['id'] ?>" onsubmit="return confirm('Delete this book?')"><button class="link-button danger">Delete</button></form><?php elseif ((int) $book['available_copies'] > 0 && count($myBooks) < 3): ?><form method="post" action="index.php?page=borrow&id=<?= (int) $book['id'] ?>"><button class="link-button">Borrow</button></form><?php else: ?>—<?php endif; ?></td><?php endif; ?></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/footer.php'; ?>
