# Book Catalog

A small PHP and MySQL catalog with a basic MVC layout. It is intended for a local XAMPP setup.

## Start it in XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin`, choose **Import**, and import `database.sql` for a fresh install. If you are upgrading an older catalog, import the needed upgrade files once: `upgrade_existing.sql` if you have not yet added due dates and fines, then `upgrade_stock_and_borrowers.sql` for stock tracking.
3. Check the MySQL username and password in `config/database.php`. The defaults are XAMPP's common `root` account with a blank password.
4. Visit `http://localhost/project/setup_admin.php` and create an administrator account.
5. Remove `setup_admin.php` after the administrator is created.
   The admin registration code for the Admin registration page is `BOOK-ADMIN-2026`. Change it in `config/database.php` if you want a different code.
6. Open `http://localhost/project/` to use the catalog.

Students can register from the site. They can borrow up to three books at a time for 14 days and return books from the “My borrowed books” section. The account page shows loan history and fines at ₱10 per overdue day. Fine payment is a demo record only; no real card or wallet is charged. The receipt can be printed from the browser. Admins can add, edit, and delete books, set total stock, and view current borrowers with their due dates. A currently borrowed book must be returned before it can be deleted.

## Folders

- `entities/` holds the User and Book classes.
- `repositories/` contains SQL queries.
- `services/` contains login, registration, and book validation rules.
- `controllers/` connects requests to services and views.
- `views/` contains the HTML pages.
- `config/` contains the database connection.
