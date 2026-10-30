# MySQL and Laragon

**MySQL** is the database: it stores the tables. **Laragon** (Windows) installs and runs PHP, MySQL and a database viewer (HeidiSQL) in one click, so you don't have to set up each one yourself.

Download Laragon: https://laragon.org/download

---

## Starting

1. Open Laragon and press **Start All**. This starts MySQL (and Apache, which we don't need because we use `php artisan serve`).
2. In a terminal opened from Laragon (**Terminal** button), `php` and `composer` already work.

Check: `php -v` should say 8.1 or newer. To switch versions, right-click Laragon → **PHP** → **Version**.

## Creating the database

Laragon's MySQL user is `root` with no password, which matches `.env.example`.

- **HeidiSQL:** click **Database** in Laragon, connect, right-click the server → **Create new** → **Database** → `simple_boilerplate`, collation `utf8mb4_unicode_ci`.
- **Or in the terminal:** `mysql -uroot -e "CREATE DATABASE simple_boilerplate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"`

Then `php artisan migrate --seed` creates the tables.

## The `.env` lines

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simple_boilerplate
DB_USERNAME=root
DB_PASSWORD=
```

## Looking at the data

In HeidiSQL, open `simple_boilerplate` → a table → the **Data** tab. You can also run SQL in the **Query** tab:

```sql
SELECT code, name, stock FROM products WHERE stock = 0;
SELECT * FROM oauth_access_tokens ORDER BY created_at DESC LIMIT 5;   -- recent sign-ins
```

Good for checking that a save really reached the database. Make changes through the app or migrations, not by hand, so the code stays the source of truth.

## Not on Windows?

- **macOS:** Laravel Herd (https://herd.laravel.com) plus DBngin for MySQL, or `brew install php composer mysql`.
- **Linux:** install `php`, `composer` and `mysql-server` from your package manager.

The rest of the setup is the same.

## Problems

| Symptom | Fix |
| --- | --- |
| `SQLSTATE[HY000] [2002] No connection could be made` | MySQL isn't running: Laragon → Start All |
| `Unknown database 'simple_boilerplate'` | create the database (above) |
| `Access denied for user 'root'` | the password in `.env` doesn't match your MySQL |
| `Base table or view already exists` | the table is there from an earlier try: `php artisan migrate:fresh --seed` |
