# Movies Flix KH — Installation Guide

## Requirements

- PHP 8.2+ (8.3 recommended)
- Composer
- Node.js 18+
- Google Cloud project with Sheets + Drive APIs enabled

---

## 1. Create Google Spreadsheet

1. Create a spreadsheet named **MovieDatabase**
2. Create 5 sheets with these exact names:

### Sheet: Movies

```
id | title | slug | description | poster_url | banner_url | category | year | duration | rating | language | quality | drive_video_id | trailer_url | subtitle_url | status | created_at
```

### Sheet: Users

```
id | name | email | password | role | avatar | created_at
```

### Sheet: Categories

```
id | name | image
```

### Sheet: WatchHistory

```
id | user_id | movie_id | progress | watched_at
```

### Sheet: Favorites

```
id | user_id | movie_id
```

3. Import example CSV files from `docs/google-sheet-template/`
4. Copy the Spreadsheet ID from the URL:

```
https://docs.google.com/spreadsheets/d/{GOOGLE_SHEET_ID}/edit
```

---

## 2. Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a project (or select one)
3. Enable APIs:
   - Google Sheets API
   - Google Drive API
4. Configure OAuth consent screen (External or Internal)
5. Create OAuth Client ID → **Web application**
6. Add authorized redirect URI, for example:

```
https://developers.google.com/oauthplayground
```

7. Copy **Client ID** and **Client Secret**

---

## 3. Get Refresh Token

1. Open [OAuth 2.0 Playground](https://developers.google.com/oauthplayground/)
2. Click the gear icon → check **Use your own OAuth credentials**
3. Paste Client ID + Client Secret
4. Select scopes:

```
https://www.googleapis.com/auth/spreadsheets
https://www.googleapis.com/auth/drive
https://www.googleapis.com/auth/drive.file
```

5. Authorize APIs → Exchange authorization code for tokens
6. Copy the **Refresh token**

---

## 4. Google Drive Folder

1. Create a Drive folder for movie files (uses your Drive storage / Workspace 5TB plan)
2. Share the **MovieDatabase** spreadsheet and Drive folder with the Google account used for OAuth (owner account is fine)
3. Copy the folder ID from the URL:

```
https://drive.google.com/drive/folders/{GOOGLE_DRIVE_FOLDER_ID}
```

For public streaming, uploaded files are set to **Anyone with the link can view**.

Video streaming URL format used by the API:

```
https://drive.google.com/uc?id={FILE_ID}&export=download
```

Player embed URL:

```
https://drive.google.com/file/d/{FILE_ID}/preview
```

---

## 5. Backend Configuration

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME="Movies Flix KH"
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
APP_DEMO_MODE=false

GOOGLE_SHEET_ID=your_sheet_id
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REFRESH_TOKEN=your_refresh_token
GOOGLE_DRIVE_FOLDER_ID=your_folder_id

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:5173
```

Migrate Sanctum/token tables (SQLite local only):

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

### Create first admin in Google Sheets

Passwords must be **bcrypt** hashes. Generate one:

```bash
php artisan tinker
>>> echo bcrypt('admin123');
```

Paste the hash into the Users sheet `password` column. Set `role` to `admin`.

---

## 6. Frontend Configuration

```bash
cd frontend
npm install
```

Create `frontend/.env`:

```env
VITE_API_URL=http://127.0.0.1:8000/api
```

Run:

```bash
npm run dev
```

Open http://localhost:5173

---

## 7. PHP Upload Limits (for large videos)

In `php.ini` (XAMPP: `C:\xampp\php\php.ini`):

```ini
upload_max_filesize = 512M
post_max_size = 512M
max_execution_time = 300
memory_limit = 512M
```

Restart Apache / PHP after changes.

---

## 8. Production Notes

- Set `APP_DEBUG=false`
- Use HTTPS
- Restrict OAuth redirect URIs
- Keep refresh token secret (never commit `.env`)
- Prefer Drive **preview embed** for playback reliability
- Google Sheets has rate limits — cache aggressively for large catalogs if needed

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `Unable to reach Google Sheets` | Check refresh token, sheet ID, enabled APIs |
| 403 on Drive upload | Ensure Drive API enabled + folder shared with OAuth account |
| CORS errors | Confirm `FRONTEND_URL` and Sanctum domains |
| Login fails | Password in Users sheet must be bcrypt, not plain text |
| Empty homepage | Ensure Movies sheet has header row + `status=active` |
