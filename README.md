# Leadership Academy — Next.js

Converted from the supplied React/Vite project while preserving the existing visual design, responsive navigation, animations, sections, authentication/account flows, contact form, assets, and MySQL data model.

## Run
1. `npm install`
2. Copy `.env.example` to `.env.local` and set the MySQL values.
3. Make sure MySQL is running and the database exists.
4. `npm run dev`

Routes:
- `/` — website
- `/auth` — login/register
- `/account` — account
- `/api/login`, `/api/register`, `/api/account`, `/api/logout`, `/api/contact` — Next.js backend routes

The database tables are automatically created on first API use.
