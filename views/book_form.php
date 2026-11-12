<?php require __DIR__ . '/header.php'; $editing = !empty($book); ?>
<h1><?= $editing ? 'Edit book' : 'Add a book' ?></h1>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form class="card form" method="post">
    <label>Title<input name="title" required maxlength="200" value="<?= e($book['title'] ?? '') ?>"></label>
    <label>Author<input name="author" required maxlength="150" value="<?= e($book['author'] ?? '') ?>"></label>
    <label>Genre<input name="genre" required maxlength="100" value="<?= e($book['genre'] ?? '') ?>"></label>
    <label>Year published<input type="number" name="year_published" required min="1" max="<?= (int) date('Y') ?>" value="<?= e($book['year_published'] ?? '') ?>"></label>
    <label>Total copies in stock<input type="number" name="total_copies" required min="1" value="<?= e($book['total_copies'] ?? 1) ?>"></label>
    <div class="form-actions"><button class="button" type="submit"><?= $editing ? 'Save changes' : 'Add book' ?></button><a href="index.php">Cancel</a></div>
</form>
<?php require __DIR__ . '/footer.php'; ?>
