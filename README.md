# Project Tracker

A simple web app for tracking client projects: what the project is, who it's for, its status and priority, and when it starts and is due.

Built with **Laravel 12** (API) and **Vue 3 + TypeScript + PrimeVue** (UI), served from XAMPP.

---

## Features

### Projects
- **Create, view, edit and delete** projects from a single page.
- Each project has:
  | Field | Required | Notes |
  |---|---|---|
  | Client Name | Yes | Max 255 characters |
  | Project Name | Yes | Max 255 characters |
  | Description | No | Max 255 characters |
  | Status | Yes | `Planning`, `In Progress`, `On Hold`, `Completed` |
  | Priority | Yes | `Low`, `Medium`, `High` |
  | Start Date | No | `YYYY-MM-DD` |
  | Due Date | No | `YYYY-MM-DD`, cannot be earlier than Start Date |
- **Search** across client name, project name and description (waits until you stop typing).
- **Filter** by Status and by Priority; filters and search can be combined, and cleared with one click.
- **Sort** by clicking any column header. Status and Priority sort in their logical order (e.g. Low → Medium → High), not alphabetically.
- **Pagination** handled on the server (10/15/25/50/100 per page).
- Overdue projects (due date passed and not Completed) show their due date in red.

### Validation
Validation runs in the form **and** in the API, so bad data is rejected even if the API is called directly.

- Client Name is required.
- Project Name is required.
- Status must be one of the valid values.
- Priority must be one of the valid values.
- Due Date cannot be earlier than Start Date.
- Invalid list parameters (unknown status, priority or sort field) are rejected too.

Invalid requests return HTTP **422** with a readable message and per-field errors:

```json
{
  "success": false,
  "message": "Due Date cannot be earlier than Start Date.",
  "errors": {
    "due_date": ["Due Date cannot be earlier than Start Date."]
  }
}
```

The UI shows the message as a toast and puts each error under its field.

### Users and login
- Log in with email and password (session-based, using Laravel Sanctum).
- Manage user accounts (name, email, password). When editing a user, leaving the password blank keeps the current one.
- Every page except login, and every API endpoint, requires a logged-in user.

---

## Requirements

- XAMPP (or any Apache + MySQL setup) with **PHP 8.2+**
- **Composer**
- **Node.js 20+** and npm
- MySQL / MariaDB

---

## Setup

The steps below assume the project lives at `C:\xampp\htdocs\project-tracker` and is opened at `http://localhost/project-tracker`. If your folder name or port is different, see [Changing the URL or port](#changing-the-url-or-port).

1. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Create the environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Create the database.** In phpMyAdmin (or the MySQL CLI) create an empty database called `project_tracker`. Set `DB_USERNAME` / `DB_PASSWORD` in `.env` if yours aren't the XAMPP defaults (`root`, no password).

4. **Run migrations and seed the admin user**
   ```bash
   php artisan migrate --seed
   ```

5. **Build the frontend**
   ```bash
   npm run build
   ```
   During development, use `npm run dev` instead to rebuild automatically as you edit.

6. **Start Apache and MySQL** in the XAMPP control panel and open:
   ```
   http://localhost/project-tracker
   ```

7. **Log in** with the seeded account:
   | Email | Password |
   |---|---|
   | `admin@example.com` | `password` |

   Change this password after the first login.

### Changing the URL or port

The app is served from a subfolder, so these `.env` values must match the folder name and port you use:

```env
APP_URL=http://localhost/project-tracker
VITE_APP_URL=/project-tracker/
VITE_APP_API_URL=/project-tracker/public/
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,localhost:8000
```

- If Apache runs on another port (e.g. `8180`), use `APP_URL=http://localhost:8180/project-tracker` and add `localhost:8180` to `SANCTUM_STATEFUL_DOMAINS`.
- If you rename the folder, update the three paths above **and** the `/project-tracker/public` path in the root `.htaccess`.
- After changing any `VITE_*` value, run `npm run build` again.

### Troubleshooting

- **Login does nothing / 419 or 401 errors.** `SESSION_DOMAIN` and `SANCTUM_STATEFUL_DOMAINS` don't match the host and port in the browser's address bar. Fix them, then run `php artisan config:clear`.
- **Blank page.** The frontend hasn't been built, or `VITE_APP_URL` doesn't match the folder name. Run `npm run build`.
- **"Table not found".** Run `php artisan migrate`.

---

## API

All endpoints are under `/project-tracker/public/api` and require an authenticated session. Responses use the shape `{ success, message, data }`.

### Projects

| Method | Endpoint | Description |
|---|---|---|
| GET | `/projects` | List projects (paginated) |
| GET | `/projects/options` | Allowed status and priority values |
| POST | `/projects` | Create a project |
| GET | `/projects/{pid}` | View a project |
| PUT | `/projects/{pid}` | Update a project |
| DELETE | `/projects/{pid}` | Delete a project (soft delete) |

**List query parameters** (all optional):

| Parameter | Values | Default |
|---|---|---|
| `search` | any text | none |
| `status` | `Planning`, `In Progress`, `On Hold`, `Completed` | all |
| `priority` | `Low`, `Medium`, `High` | all |
| `sort_by` | `client_name`, `project_name`, `status`, `priority`, `start_date`, `due_date`, `created_at` | `created_at` |
| `sort_dir` | `asc`, `desc` | `desc` |
| `page` | 1 or more | `1` |
| `per_page` | 1 to 100 | `15` |

Example: `GET /projects?search=website&status=In%20Progress&sort_by=due_date&sort_dir=asc`

### Users

| Method | Endpoint | Description |
|---|---|---|
| POST | `/user/login` *(outside `/api`)* | Log in |
| POST | `/api/user/logout` | Log out |
| GET | `/api/user` | Current logged-in user |
| GET | `/api/user/list` | List users |
| POST | `/api/user/register` | Create a user |
| GET | `/api/user/{pid}` | View a user |
| PUT | `/api/user/{pid}` | Update a user |
| DELETE | `/api/user/{pid}` | Delete a user (soft delete) |

---

## Assumptions

- **Single team, no roles.** Every logged-in user can see and edit every project and every user. There are no permissions or project ownership.
- **No public sign-up.** Accounts are created by an existing user on the Users page. The first account comes from the seeder.
- **Fixed status and priority lists.** They are defined once in `app/Models/Project.php` (`STATUSES`, `PRIORITIES`). The API validates against them and the UI loads them from `/api/projects/options`, so changing them there updates both.
- **Dates are optional.** A project can be saved without a Start Date or Due Date. The "Due Date cannot be earlier than Start Date" rule only applies when both are set. A due date equal to the start date is allowed.
- **Dates only, no times or time zones.** Dates are stored and sent as plain `YYYY-MM-DD`.
- **"Overdue"** means the due date is before today and the status is not `Completed`. It only affects how the date is displayed.
- **Deletes are soft deletes.** Deleted projects and users stay in the database (`deleted_at` is set) but are hidden from the app. There is no restore screen.
- **Search is a simple "contains" match** (SQL `LIKE`) on client name, project name and description, not case-sensitive under MySQL's default collation.
- **Records are identified by a UUID (`pid`)** in URLs and the API. The numeric database `id` is never exposed.
- **MySQL/MariaDB is the target database.** Sorting by Status/Priority uses MySQL's `FIELD()` function, so it won't work on SQLite or PostgreSQL without changes.
- **Runs locally under XAMPP** from a subfolder, with session-cookie auth on the same domain. Production deployment (HTTPS, a real domain, a separate frontend host) isn't covered.

---

## Project structure

```
app/
  Http/Controllers/     ProjectController, UserController (thin; logic in traits)
  Http/Requests/        Validation rules and error messages
  Models/               Project, User
  Repositories/         Database queries (search, filter, sort, pagination)
  Traits/               Endpoint logic (ProjectTrait, UserTrait)
database/
  migrations/           Table definitions
  seeders/              Admin user
resources/js/
  pages/                Login, Dashboard, Projects, Users
  store/                Pinia stores that call the API
  router/               Routes and login guard
routes/
  api/project.php       Project API routes
  api/user.php          User API routes
  web.php               Login route and SPA catch-all
```
