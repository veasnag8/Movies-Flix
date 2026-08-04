# Movies Flix

Netflix-style movie streaming website powered by:

- **Backend:** Laravel 12 + PHP 8.2/8.3 + Laravel Sanctum
- **Frontend:** Vue 3 + Vite + Tailwind CSS + Pinia + Axios
- **Database:** Google Sheets API (Movies, Users, Categories, WatchHistory, Favorites)
- **Storage:** Google Drive API (videos, posters, banners)

SQLite is used only for Laravel internals (sessions, cache, Sanctum API tokens). All business data lives in Google Sheets.

---

## Quick Start

### 1. Backend

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

API: `http://127.0.0.1:8000`

### 2. Frontend

```bash
cd frontend
npm install
npm run dev
```

App: `http://localhost:5173`

### Demo mode (no Google credentials)

In `backend/.env`:

```
APP_DEMO_MODE=true
GOOGLE_SHEET_ID=
```

Then:

```bash
php artisan sheets:seed-demo
```

Demo logins:

| Role  | Email                   | Password  |
|-------|-------------------------|-----------|
| Admin | admin@moviesflix.test   | admin123  |
| User  | user@moviesflix.test    | user123   |

---

## Google Cloud Setup

See [INSTALLATION.md](INSTALLATION.md) for full Google Sheets + Drive OAuth setup.

Required `.env` keys:

```
APP_DEMO_MODE=false
GOOGLE_SHEET_ID=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER_ID=
```

---

## Project Structure

```
Movies Flix/
├── backend/                 Laravel API
│   ├── app/Services/        GoogleSheetService, GoogleDriveService
│   ├── app/Http/Controllers/Api/
│   └── routes/api.php
├── frontend/                Vue 3 SPA
│   └── src/views/           Home, Detail, Player, Admin
└── docs/
    └── google-sheet-template/   CSV templates for each sheet
```

---

## API Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register |
| POST | `/api/login` | Login |
| POST | `/api/logout` | Logout (auth) |
| GET | `/api/movies` | List movies + homepage meta |
| GET | `/api/movies/{id}` | Movie detail |
| GET | `/api/search?q=` | Search |
| GET | `/api/categories` | Categories |
| POST | `/api/admin/movie` | Create movie (admin) |
| PUT | `/api/admin/movie/{id}` | Update movie (admin) |
| DELETE | `/api/admin/movie/{id}` | Delete movie (admin) |

---

## Features

- Netflix-style homepage (hero, trending, latest, categories, recommended)
- Movie detail + Watch Now
- Google Drive video player (embed + fullscreen)
- Subtitles via `.vtt` URL
- Continue watching / favorites
- Auth with bcrypt passwords stored in Google Sheets
- Admin panel for movies, categories, users
- Upload video/poster directly to Google Drive

---

## License

MIT
