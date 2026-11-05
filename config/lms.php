<?php

return [
//TTL (detik) hanyalah batas maksimal basi. Pembersihan utama berbasis event (lihat App\Observers\DashboardCacheObserver). Hanya blok "stats" yang di-cache.
    
    'cache' => [
        'dashboard_ttl' => [
            'admin' => (int) env('LMS_CACHE_DASHBOARD_ADMIN_TTL', 600),
            'dosen' => (int) env('LMS_CACHE_DASHBOARD_DOSEN_TTL', 300),
            'mahasiswa' => (int) env('LMS_CACHE_DASHBOARD_MAHASISWA_TTL', 300),
        ],
    ],

];