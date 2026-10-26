# unibizhub-web
Entrepreneur website for university of Ruhuna

## Local setup (XAMPP)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open phpMyAdmin, create/import the project database by importing `database.sql` (it creates and selects `unibizhub`).
3. The default connection uses MySQL at `127.0.0.1:3306` with user `root` and no password. If your local MySQL host or port differs, set the `DB_HOST` and `DB_PORT` environment variables for Apache before starting it.

If the site reports that it cannot connect, check that MySQL is running and that its configured port matches `DB_PORT`. The underlying connection error is written to the PHP error log.
