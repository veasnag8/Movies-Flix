# Example Data

## Default accounts (after importing Users.csv)

| Role  | Email                 | Password  |
|-------|-----------------------|-----------|
| Admin | admin@moviesflix.test | admin123  |
| User  | user@moviesflix.test  | user123   |

Passwords are stored as bcrypt hashes in the Google Sheet.

## Sample movie row

| Field | Example |
|-------|---------|
| id | 1 |
| title | Spider-Man: Neon City |
| slug | spider-man-neon-city |
| description | An action-packed adventure... |
| poster_url | https://... |
| banner_url | https://... |
| category | Action |
| year | 2026 |
| duration | 2h 14m |
| rating | 8.5 |
| language | English |
| quality | 1080p |
| drive_video_id | 1ABC123XYZ |
| trailer_url | https://youtube.com/... |
| subtitle_url | https://.../subs.vtt |
| status | active |
| created_at | 2026-01-10 12:00:00 |

## Drive video ID

From a Drive share link:

```
https://drive.google.com/file/d/1ABC123XYZ/view?usp=sharing
                         ^^^^^^^^^^
                         FILE ID
```

Store only the file ID in `drive_video_id`.
