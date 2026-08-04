# Deploy Movies Flix KH

- **Backend (Laravel):** [Render](https://render.com)
- **Frontend (Vue):** [Vercel](https://vercel.com)
- **Data:** Google Sheets + Google Drive

Repo: https://github.com/veasnag8/Movies-Flix

---

## 1. Deploy backend on Render

1. Go to [https://dashboard.render.com](https://dashboard.render.com) → **New** → **Blueprint**
2. Connect GitHub repo `veasnag8/Movies-Flix`
3. Render reads `render.yaml` and creates `movies-flix-kh-api`
4. Set these environment variables in the Render dashboard:

| Key | Value |
|-----|--------|
| `APP_URL` | `https://movies-flix-kh-api.onrender.com` (use your real Render URL) |
| `FRONTEND_URL` | `https://your-app.vercel.app` (set after Vercel deploy) |
| `GOOGLE_SHEET_ID` | your sheet id |
| `GOOGLE_CLIENT_ID` | your client id |
| `GOOGLE_CLIENT_SECRET` | your client secret |
| `GOOGLE_REFRESH_TOKEN` | your refresh token |
| `GOOGLE_DRIVE_FOLDER_ID` | your drive folder id |
| `SANCTUM_STATEFUL_DOMAINS` | `your-app.vercel.app` |

5. Deploy → copy the service URL (example: `https://movies-flix-kh-api.onrender.com`)
6. Check health: `https://YOUR-API.onrender.com/up`

> Free Render services sleep after idle time. First request may take ~30–60s.
>
> This repo includes `.github/workflows/keep-render-awake.yml` which pings
> `https://movies-flix-kh.onrender.com/up` every 10 minutes to reduce cold starts.
> Enable GitHub Actions on the repo (Actions tab → allow workflows).

---

## 2. Deploy frontend on Vercel

1. Go to [https://vercel.com/new](https://vercel.com/new)
2. Import `veasnag8/Movies-Flix`
3. Configure:
   - **Root Directory:** `frontend`
   - **Framework Preset:** Vite
   - **Build Command:** `npm run build`
   - **Output Directory:** `dist`
4. Environment variable:

| Key | Value |
|-----|--------|
| `VITE_API_URL` | `https://YOUR-API.onrender.com/api` |

5. Deploy → copy the Vercel URL
6. Go back to Render and set `FRONTEND_URL` to that Vercel URL, then **Manual Deploy** once

---

## 3. Google OAuth note

Your OAuth client already works with a refresh token. No extra redirect URI is required for server-side Sheets/Drive access.

---

## 4. Sheet tabs required

`Movies`, `Users`, `Categories`, `WatchHistory`, `Favorites` with header row 1.

CSV templates: `docs/google-sheet-template/`

---

## Local names

Website brand: **Movies Flix KH**
