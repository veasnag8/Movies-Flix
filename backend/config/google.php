<?php

return [
    'sheet_id' => env('GOOGLE_SHEET_ID'),
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),
    'drive_folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
    'demo_mode' => filter_var(env('APP_DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN),

    'sheets' => [
        'movies' => 'Movies',
        'users' => 'Users',
        'categories' => 'Categories',
        'watch_history' => 'WatchHistory',
        'favorites' => 'Favorites',
    ],

    'movie_headers' => [
        'id', 'title', 'slug', 'description', 'poster_url', 'banner_url',
        'category', 'year', 'duration', 'rating', 'language', 'quality',
        'drive_video_id', 'trailer_url', 'subtitle_url', 'status', 'created_at',
    ],

    'user_headers' => [
        'id', 'name', 'email', 'password', 'role', 'avatar', 'created_at',
    ],

    'category_headers' => [
        'id', 'name', 'image',
    ],

    'watch_history_headers' => [
        'id', 'user_id', 'movie_id', 'progress', 'watched_at',
    ],

    'favorite_headers' => [
        'id', 'user_id', 'movie_id',
    ],
];
