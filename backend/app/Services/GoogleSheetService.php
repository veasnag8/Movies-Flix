<?php

namespace App\Services;

use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Str;

class GoogleSheetService
{
    public function __construct(protected GoogleClientFactory $google)
    {
    }

    protected function sheetId(): string
    {
        $id = config('google.sheet_id');

        if (! $id) {
            throw new \RuntimeException('GOOGLE_SHEET_ID is not set in .env');
        }

        return $id;
    }

    protected function range(string $sheet, string $cells = 'A:Z'): string
    {
        return sprintf('%s!%s', $sheet, $cells);
    }

    protected function read(string $sheetName): array
    {
        $response = $this->google->sheets()->spreadsheets_values->get(
            $this->sheetId(),
            $this->range($sheetName)
        );

        $values = $response->getValues() ?? [];

        if (count($values) < 2) {
            return [];
        }

        $headers = array_map(fn ($h) => Str::snake(trim((string) $h)), $values[0]);
        $rows = [];

        for ($i = 1; $i < count($values); $i++) {
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $values[$i][$index] ?? '';
            }
            $row['_row'] = $i + 1;
            $rows[] = $row;
        }

        return $rows;
    }

    protected function append(string $sheetName, array $headers, array $data): array
    {
        $ordered = [];
        foreach ($headers as $header) {
            $ordered[] = $data[$header] ?? '';
        }

        $body = new ValueRange([
            'values' => [$ordered],
        ]);

        $this->google->sheets()->spreadsheets_values->append(
            $this->sheetId(),
            $this->range($sheetName),
            $body,
            [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
            ]
        );

        return $data;
    }

    protected function updateRow(string $sheetName, int $rowNumber, array $headers, array $data): array
    {
        $ordered = [];
        foreach ($headers as $header) {
            $ordered[] = $data[$header] ?? '';
        }

        $body = new ValueRange([
            'values' => [$ordered],
        ]);

        $endCol = chr(64 + count($headers));
        $range = sprintf('%s!A%d:%s%d', $sheetName, $rowNumber, $endCol, $rowNumber);

        $this->google->sheets()->spreadsheets_values->update(
            $this->sheetId(),
            $range,
            $body,
            ['valueInputOption' => 'USER_ENTERED']
        );

        return $data;
    }

    protected function deleteRow(string $sheetName, int $rowNumber): void
    {
        $spreadsheet = $this->google->sheets()->spreadsheets->get($this->sheetId());
        $sheetId = null;

        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                $sheetId = $sheet->getProperties()->getSheetId();
                break;
            }
        }

        if ($sheetId === null) {
            throw new \RuntimeException("Sheet [{$sheetName}] not found.");
        }

        $requests = [
            new \Google\Service\Sheets\Request([
                'deleteDimension' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'ROWS',
                        'startIndex' => $rowNumber - 1,
                        'endIndex' => $rowNumber,
                    ],
                ],
            ]),
        ];

        $batch = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => $requests,
        ]);

        $this->google->sheets()->spreadsheets->batchUpdate($this->sheetId(), $batch);
    }

    protected function nextId(array $rows): int
    {
        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) ($row['id'] ?? 0));
        }

        return $max + 1;
    }

    protected function withoutMeta(array $row): array
    {
        unset($row['_row']);

        return $row;
    }

    // ─── Movies ───────────────────────────────────────────────

    public function getMovies(?string $status = 'active'): array
    {
        $movies = $this->read(config('google.sheets.movies'));

        if ($status !== null) {
            $movies = array_values(array_filter(
                $movies,
                fn ($m) => strtolower((string) ($m['status'] ?? '')) === strtolower($status)
            ));
        }

        return array_map(fn ($m) => $this->withoutMeta($m), $movies);
    }

    public function getAllMovies(): array
    {
        return array_map(
            fn ($m) => $this->withoutMeta($m),
            $this->read(config('google.sheets.movies'))
        );
    }

    public function getMovieById(int|string $id): ?array
    {
        foreach ($this->read(config('google.sheets.movies')) as $movie) {
            if ((string) $movie['id'] === (string) $id) {
                return $this->withoutMeta($movie);
            }
        }

        return null;
    }

    public function getMovieBySlug(string $slug): ?array
    {
        foreach ($this->read(config('google.sheets.movies')) as $movie) {
            if (($movie['slug'] ?? '') === $slug) {
                return $this->withoutMeta($movie);
            }
        }

        return null;
    }

    public function searchMovies(string $query): array
    {
        $query = strtolower(trim($query));

        if ($query === '') {
            return $this->getMovies();
        }

        $results = array_filter($this->getMovies(), function ($movie) use ($query) {
            $haystack = strtolower(implode(' ', [
                $movie['title'] ?? '',
                $movie['description'] ?? '',
                $movie['category'] ?? '',
                $movie['language'] ?? '',
            ]));

            return str_contains($haystack, $query);
        });

        return array_values($results);
    }

    public function createMovie(array $data): array
    {
        $headers = config('google.movie_headers');
        $existing = $this->read(config('google.sheets.movies'));

        $movie = [
            'id' => (string) $this->nextId($existing),
            'title' => $data['title'] ?? '',
            'slug' => $data['slug'] ?? Str::slug($data['title'] ?? Str::random(8)),
            'description' => $data['description'] ?? '',
            'poster_url' => $data['poster_url'] ?? '',
            'banner_url' => $data['banner_url'] ?? '',
            'category' => $data['category'] ?? '',
            'year' => (string) ($data['year'] ?? ''),
            'duration' => $data['duration'] ?? '',
            'rating' => (string) ($data['rating'] ?? ''),
            'language' => $data['language'] ?? 'English',
            'quality' => $data['quality'] ?? '1080p',
            'drive_video_id' => $data['drive_video_id'] ?? '',
            'trailer_url' => $data['trailer_url'] ?? '',
            'subtitle_url' => $data['subtitle_url'] ?? '',
            'status' => $data['status'] ?? 'active',
            'created_at' => now()->toDateTimeString(),
        ];

        return $this->append(config('google.sheets.movies'), $headers, $movie);
    }

    public function updateMovie(int|string $id, array $data): ?array
    {
        $headers = config('google.movie_headers');

        foreach ($this->read(config('google.sheets.movies')) as $movie) {
            if ((string) $movie['id'] !== (string) $id) {
                continue;
            }

            $updated = $this->withoutMeta($movie);
            foreach ($headers as $header) {
                if ($header === 'id' || $header === 'created_at') {
                    continue;
                }
                if (array_key_exists($header, $data)) {
                    $updated[$header] = is_bool($data[$header])
                        ? ($data[$header] ? '1' : '0')
                        : (string) $data[$header];
                }
            }

            return $this->updateRow(
                config('google.sheets.movies'),
                (int) $movie['_row'],
                $headers,
                $updated
            );
        }

        return null;
    }

    public function deleteMovie(int|string $id): bool
    {
        foreach ($this->read(config('google.sheets.movies')) as $movie) {
            if ((string) $movie['id'] === (string) $id) {
                $this->deleteRow(config('google.sheets.movies'), (int) $movie['_row']);

                return true;
            }
        }

        return false;
    }

    // ─── Users ────────────────────────────────────────────────

    public function getUsers(): array
    {
        return array_map(function ($user) {
            $clean = $this->withoutMeta($user);
            unset($clean['password']);

            return $clean;
        }, $this->read(config('google.sheets.users')));
    }

    public function getUserById(int|string $id): ?array
    {
        foreach ($this->read(config('google.sheets.users')) as $user) {
            if ((string) $user['id'] === (string) $id) {
                return $this->withoutMeta($user);
            }
        }

        return null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));

        foreach ($this->read(config('google.sheets.users')) as $user) {
            if (strtolower((string) ($user['email'] ?? '')) === $email) {
                return $this->withoutMeta($user);
            }
        }

        return null;
    }

    public function createUser(array $data): array
    {
        $headers = config('google.user_headers');
        $existing = $this->read(config('google.sheets.users'));

        $user = [
            'id' => (string) $this->nextId($existing),
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => $data['password'] ?? '',
            'role' => $data['role'] ?? 'user',
            'avatar' => $data['avatar'] ?? '',
            'created_at' => now()->toDateTimeString(),
        ];

        return $this->append(config('google.sheets.users'), $headers, $user);
    }

    public function updateUser(int|string $id, array $data): ?array
    {
        $headers = config('google.user_headers');

        foreach ($this->read(config('google.sheets.users')) as $user) {
            if ((string) $user['id'] !== (string) $id) {
                continue;
            }

            $updated = $this->withoutMeta($user);
            foreach (['name', 'email', 'role', 'avatar'] as $field) {
                if (array_key_exists($field, $data)) {
                    $updated[$field] = (string) $data[$field];
                }
            }
            if (! empty($data['password'])) {
                $updated['password'] = $data['password'];
            }

            return $this->updateRow(
                config('google.sheets.users'),
                (int) $user['_row'],
                $headers,
                $updated
            );
        }

        return null;
    }

    public function deleteUser(int|string $id): bool
    {
        foreach ($this->read(config('google.sheets.users')) as $user) {
            if ((string) $user['id'] === (string) $id) {
                $this->deleteRow(config('google.sheets.users'), (int) $user['_row']);

                return true;
            }
        }

        return false;
    }

    // ─── Categories ───────────────────────────────────────────

    public function getCategories(): array
    {
        return array_map(
            fn ($c) => $this->withoutMeta($c),
            $this->read(config('google.sheets.categories'))
        );
    }

    public function getCategoryById(int|string $id): ?array
    {
        foreach ($this->read(config('google.sheets.categories')) as $category) {
            if ((string) $category['id'] === (string) $id) {
                return $this->withoutMeta($category);
            }
        }

        return null;
    }

    public function createCategory(array $data): array
    {
        $headers = config('google.category_headers');
        $existing = $this->read(config('google.sheets.categories'));

        $category = [
            'id' => (string) $this->nextId($existing),
            'name' => $data['name'] ?? '',
            'image' => $data['image'] ?? '',
        ];

        return $this->append(config('google.sheets.categories'), $headers, $category);
    }

    public function updateCategory(int|string $id, array $data): ?array
    {
        $headers = config('google.category_headers');

        foreach ($this->read(config('google.sheets.categories')) as $category) {
            if ((string) $category['id'] !== (string) $id) {
                continue;
            }

            $updated = $this->withoutMeta($category);
            foreach (['name', 'image'] as $field) {
                if (array_key_exists($field, $data)) {
                    $updated[$field] = (string) $data[$field];
                }
            }

            return $this->updateRow(
                config('google.sheets.categories'),
                (int) $category['_row'],
                $headers,
                $updated
            );
        }

        return null;
    }

    public function deleteCategory(int|string $id): bool
    {
        foreach ($this->read(config('google.sheets.categories')) as $category) {
            if ((string) $category['id'] === (string) $id) {
                $this->deleteRow(config('google.sheets.categories'), (int) $category['_row']);

                return true;
            }
        }

        return false;
    }

    // ─── Watch History ────────────────────────────────────────

    public function getWatchHistory(int|string $userId): array
    {
        $rows = array_filter(
            $this->read(config('google.sheets.watch_history')),
            fn ($row) => (string) ($row['user_id'] ?? '') === (string) $userId
        );

        return array_map(fn ($r) => $this->withoutMeta($r), array_values($rows));
    }

    public function upsertWatchHistory(int|string $userId, int|string $movieId, float|string $progress): array
    {
        $headers = config('google.watch_history_headers');
        $sheet = config('google.sheets.watch_history');
        $rows = $this->read($sheet);

        foreach ($rows as $row) {
            if ((string) $row['user_id'] === (string) $userId
                && (string) $row['movie_id'] === (string) $movieId) {
                $updated = $this->withoutMeta($row);
                $updated['progress'] = (string) $progress;
                $updated['watched_at'] = now()->toDateTimeString();

                return $this->updateRow($sheet, (int) $row['_row'], $headers, $updated);
            }
        }

        $entry = [
            'id' => (string) $this->nextId($rows),
            'user_id' => (string) $userId,
            'movie_id' => (string) $movieId,
            'progress' => (string) $progress,
            'watched_at' => now()->toDateTimeString(),
        ];

        return $this->append($sheet, $headers, $entry);
    }

    // ─── Favorites ────────────────────────────────────────────

    public function getFavorites(int|string $userId): array
    {
        $rows = array_filter(
            $this->read(config('google.sheets.favorites')),
            fn ($row) => (string) ($row['user_id'] ?? '') === (string) $userId
        );

        return array_map(fn ($r) => $this->withoutMeta($r), array_values($rows));
    }

    public function addFavorite(int|string $userId, int|string $movieId): array
    {
        $existing = $this->getFavorites($userId);
        foreach ($existing as $fav) {
            if ((string) $fav['movie_id'] === (string) $movieId) {
                return $fav;
            }
        }

        $headers = config('google.favorite_headers');
        $rows = $this->read(config('google.sheets.favorites'));

        $favorite = [
            'id' => (string) $this->nextId($rows),
            'user_id' => (string) $userId,
            'movie_id' => (string) $movieId,
        ];

        return $this->append(config('google.sheets.favorites'), $headers, $favorite);
    }

    public function removeFavorite(int|string $userId, int|string $movieId): bool
    {
        foreach ($this->read(config('google.sheets.favorites')) as $fav) {
            if ((string) $fav['user_id'] === (string) $userId
                && (string) $fav['movie_id'] === (string) $movieId) {
                $this->deleteRow(config('google.sheets.favorites'), (int) $fav['_row']);

                return true;
            }
        }

        return false;
    }

    public function clearSheet(string $sheetName): void
    {
        $this->google->sheets()->spreadsheets_values->clear(
            $this->sheetId(),
            $this->range($sheetName),
            new ClearValuesRequest
        );
    }
}
