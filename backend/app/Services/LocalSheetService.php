<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Local JSON "sheet" storage used when GOOGLE credentials are not configured
 * or APP_DEMO_MODE=true. Same shape as Google Sheets rows.
 */
class LocalSheetService extends GoogleSheetService
{
    public function __construct()
    {
        // Skip Google client wiring for demo mode.
    }

    protected function path(string $sheet): string
    {
        $dir = storage_path('app/sheets');
        File::ensureDirectoryExists($dir);

        return $dir.'/'.Str::slug($sheet).'.json';
    }

    protected function read(string $sheetName): array
    {
        $path = $this->path($sheetName);

        if (! File::exists($path)) {
            return [];
        }

        $rows = json_decode(File::get($path), true) ?: [];
        $result = [];

        foreach ($rows as $index => $row) {
            $row['_row'] = $index + 2;
            $result[] = $row;
        }

        return $result;
    }

    protected function append(string $sheetName, array $headers, array $data): array
    {
        $rows = array_map(fn ($r) => $this->withoutMeta($r), $this->read($sheetName));
        $ordered = [];
        foreach ($headers as $header) {
            $ordered[$header] = $data[$header] ?? '';
        }
        $rows[] = $ordered;
        File::put($this->path($sheetName), json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $ordered;
    }

    protected function updateRow(string $sheetName, int $rowNumber, array $headers, array $data): array
    {
        $rows = array_map(fn ($r) => $this->withoutMeta($r), $this->read($sheetName));
        $index = $rowNumber - 2;
        $ordered = [];
        foreach ($headers as $header) {
            $ordered[$header] = $data[$header] ?? '';
        }
        $rows[$index] = $ordered;
        File::put($this->path($sheetName), json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $ordered;
    }

    protected function deleteRow(string $sheetName, int $rowNumber): void
    {
        $rows = array_map(fn ($r) => $this->withoutMeta($r), $this->read($sheetName));
        $index = $rowNumber - 2;
        unset($rows[$index]);
        File::put($this->path($sheetName), json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function seedDemoData(): void
    {
        $movies = [
            [
                'id' => '1',
                'title' => 'Spider-Man: Neon City',
                'slug' => 'spider-man-neon-city',
                'description' => 'An action-packed adventure through a neon-lit metropolis as a young hero learns what it means to protect the city.',
                'poster_url' => 'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=400&h=600&fit=crop',
                'banner_url' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1600&h=900&fit=crop',
                'category' => 'Action',
                'year' => '2026',
                'duration' => '2h 14m',
                'rating' => '8.5',
                'language' => 'English',
                'quality' => '1080p',
                'drive_video_id' => 'DEMO_VIDEO_1',
                'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'subtitle_url' => '',
                'status' => 'active',
                'created_at' => '2026-01-10 12:00:00',
            ],
            [
                'id' => '2',
                'title' => 'Midnight Horizon',
                'slug' => 'midnight-horizon',
                'description' => 'A sci-fi thriller about a crew racing against time on the edge of a collapsing star system.',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=400&h=600&fit=crop',
                'banner_url' => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=1600&h=900&fit=crop',
                'category' => 'Sci-Fi',
                'year' => '2025',
                'duration' => '1h 58m',
                'rating' => '8.1',
                'language' => 'English',
                'quality' => '4K',
                'drive_video_id' => 'DEMO_VIDEO_2',
                'trailer_url' => '',
                'subtitle_url' => '',
                'status' => 'active',
                'created_at' => '2026-02-01 09:00:00',
            ],
            [
                'id' => '3',
                'title' => 'The Last Laugh',
                'slug' => 'the-last-laugh',
                'description' => 'A sharp comedy about a washed-up comedian who gets one final shot at fame.',
                'poster_url' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=400&h=600&fit=crop',
                'banner_url' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=1600&h=900&fit=crop',
                'category' => 'Comedy',
                'year' => '2024',
                'duration' => '1h 42m',
                'rating' => '7.6',
                'language' => 'English',
                'quality' => '1080p',
                'drive_video_id' => 'DEMO_VIDEO_3',
                'trailer_url' => '',
                'subtitle_url' => '',
                'status' => 'active',
                'created_at' => '2026-03-12 18:30:00',
            ],
            [
                'id' => '4',
                'title' => 'Echoes of Rain',
                'slug' => 'echoes-of-rain',
                'description' => 'A quiet drama following two strangers whose lives intertwine during a week of endless rain.',
                'poster_url' => 'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?w=400&h=600&fit=crop',
                'banner_url' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=1600&h=900&fit=crop',
                'category' => 'Drama',
                'year' => '2025',
                'duration' => '2h 05m',
                'rating' => '8.8',
                'language' => 'English',
                'quality' => '1080p',
                'drive_video_id' => 'DEMO_VIDEO_4',
                'trailer_url' => '',
                'subtitle_url' => '',
                'status' => 'active',
                'created_at' => '2026-04-02 11:15:00',
            ],
            [
                'id' => '5',
                'title' => 'Shadow Protocol',
                'slug' => 'shadow-protocol',
                'description' => 'An elite agent must expose a global conspiracy before the final protocol activates.',
                'poster_url' => 'https://images.unsplash.com/photo-1509347525968-275fe57f4bd5?w=400&h=600&fit=crop',
                'banner_url' => 'https://images.unsplash.com/photo-1574267432553-4b4628081c31?w=1600&h=900&fit=crop',
                'category' => 'Action',
                'year' => '2026',
                'duration' => '2h 21m',
                'rating' => '8.3',
                'language' => 'English',
                'quality' => '4K',
                'drive_video_id' => 'DEMO_VIDEO_5',
                'trailer_url' => '',
                'subtitle_url' => '',
                'status' => 'active',
                'created_at' => '2026-05-20 16:00:00',
            ],
            [
                'id' => '6',
                'title' => 'Forest Whispers',
                'slug' => 'forest-whispers',
                'description' => 'A horror tale set deep in an ancient forest where the trees remember every secret.',
                'poster_url' => 'https://images.unsplash.com/photo-1509248961158-e54f6934749c?w=400&h=600&fit=crop',
                'banner_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1600&h=900&fit=crop',
                'category' => 'Horror',
                'year' => '2023',
                'duration' => '1h 49m',
                'rating' => '7.2',
                'language' => 'English',
                'quality' => '1080p',
                'drive_video_id' => 'DEMO_VIDEO_6',
                'trailer_url' => '',
                'subtitle_url' => '',
                'status' => 'active',
                'created_at' => '2026-06-01 20:00:00',
            ],
        ];

        $categories = [
            ['id' => '1', 'name' => 'Action', 'image' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?w=600&h=400&fit=crop'],
            ['id' => '2', 'name' => 'Sci-Fi', 'image' => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=600&h=400&fit=crop'],
            ['id' => '3', 'name' => 'Comedy', 'image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=600&h=400&fit=crop'],
            ['id' => '4', 'name' => 'Drama', 'image' => 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=600&h=400&fit=crop'],
            ['id' => '5', 'name' => 'Horror', 'image' => 'https://images.unsplash.com/photo-1509248961158-e54f6934749c?w=600&h=400&fit=crop'],
        ];

        $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $userPassword = password_hash('user123', PASSWORD_BCRYPT);

        $users = [
            [
                'id' => '1',
                'name' => 'Admin',
                'email' => 'admin@moviesflix.test',
                'password' => $adminPassword,
                'role' => 'admin',
                'avatar' => '',
                'created_at' => '2026-01-01 00:00:00',
            ],
            [
                'id' => '2',
                'name' => 'Demo User',
                'email' => 'user@moviesflix.test',
                'password' => $userPassword,
                'role' => 'user',
                'avatar' => '',
                'created_at' => '2026-01-02 00:00:00',
            ],
        ];

        File::put($this->path(config('google.sheets.movies')), json_encode($movies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($this->path(config('google.sheets.categories')), json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($this->path(config('google.sheets.users')), json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($this->path(config('google.sheets.watch_history')), json_encode([], JSON_PRETTY_PRINT));
        File::put($this->path(config('google.sheets.favorites')), json_encode([], JSON_PRETTY_PRINT));
    }
}
