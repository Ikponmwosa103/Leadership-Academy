# Leadership Academy

## Run the site

Serve the project through PHP rather than opening the HTML files directly:

```bash
php -S 127.0.0.1:8080
```

The browser calls the PHP API at `./Api/`, so the site and API must use the
same origin.

## Database

The API uses MySQL or MariaDB. Set either the Railway-style variables:

- `MYSQLHOST`
- `MYSQLPORT` (optional, defaults to `3306`)
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

or the equivalent `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and
`DB_PASSWORD` variables. A `mysql://` `DATABASE_URL` is also supported.

On the first API request, the app creates the `users` and `contact_messages`
tables automatically. The same schema is available in `database.sql` for an
explicit migration. Set `DB_AUTO_MIGRATE=false` if your deployment requires
manual migrations.

## Contact email

Contact messages are always saved in the database. SMTP delivery is optional;
configure the `MAILTRAP_*` variables and install PHPMailer under `vendor/` to
also forward messages by email.