# Performa sebelum optimasi
## Role: Mahasiswa
### Page: Login-Home Page
#### Request: 
| No | Verb | Path | Status | Duration |
|----|------|------|---------|----------|
| 1 | GET | `/api/v1/notifications?per_page=5` | 200 | 144ms |
| 2 | GET | `/api/v1/notifications/unread-count` | 200 | 138ms |
| 3 | GET | `/api/v1/dashboard` | 200 | 87ms |
| 4 | GET | `/api/v1/notifications/unread-count` | 200 | 101ms |
| 5 | GET | `/api/v1/notifications?per_page=5` | 200 | 105ms |
| 6 | GET | `/api/v1/me` | 200 | 92ms |
| 7 | GET | `/api/v1/me` | 200 | 108ms |
| 8 | POST | `/api/v1/auth/login` | 200 | 732ms |
| 9 | GET | `/` | 200 | 633ms |
| 10 | GET | `/` | 500 | 6458ms |
| 11 | GET | `/?herd=preview` | 500 | 15727ms |

- Endpoint API (/api/v1/*) semuanya berhasil dengan **HTTP 200**
- Ada dua request yang gagal dengan **HTTP 500**
    - GET /
    - GET /?herd=preview
- Request yang paling lambat
    - GET /?herd=preview --> 15727ms = ~15.7 detik
    - GET / --> 6458ms = ~6.5 detik

#### Query:
| No | Query | Duration |
|----|--------|----------|
| 1 | select * from `cache` where `key` in... | 2.54ms |
| 2 | select * from `notifications` where `notifications`.`notifiable_type` =... | 1.08ms |
| 3 | select count(*) as aggregate from `notifications` where... | 1.21ms |
| 4 | update `cache` set `value` = 'i:7;' where `key` =... | 1.79ms |
| 5 | select * from `cache` where `key` =... | 3.83ms |
| 6 | select * from `cache` where `key` in... | 0.70ms |
| 7 | select * from `cache` where `key` in... | 1.07ms |
| 8 | select * from `cache` where `key` in... | 1.15ms |
| 9 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 09:27:10'... | 2.71ms |
| 10 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.10ms |
| 11 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.08ms |
| 12 | select * from `cache` where `key` in... | 1.08ms |
| 13 | select count(*) as aggregate from `notifications` where... | 1.57ms |
| 14 | update `cache` set `value` = 'i:6;' where `key` =... | 0.87ms |
| 15 | select * from `cache` where `key` =... | 0.91ms |
| 16 | select * from `cache` where `key` in... | 0.84ms |
| 17 | select * from `cache` where `key` in... | 1.10ms |
| 18 | select * from `cache` where `key` in... | 1.06ms |
| 19 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 09:27:10'... | 0.98ms |
| 20 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 0.99ms |
| 21 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.43ms |
| 22 | select * from `cache` where `key` in... | 1.20ms |
| 23 | select count(*) as aggregate from `submissions` where `submissions`.`user_id` = 4 and... | 1.29ms |
| 24 | select count(*) as aggregate from `courses` inner join `course_user` on `courses`.`id`... | 1.54ms |
| 25 | update `cache` set `value` = 'i:5;' where `key` =... | 1.22ms |
| 26 | select * from `cache` where `key` =... | 1.41ms |
| 27 | select * from `cache` where `key` in... | 1.69ms |
| 28 | select * from `cache` where `key` in... | 1.28ms |
| 29 | select * from `cache` where `key` in... | 1.34ms |
| 30 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.31ms |
| 31 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.37ms |
| 32 | select * from `cache` where `key` in... | 1.04ms |
| 33 | select count(*) as aggregate from `notifications` where... | 0.98ms |
| 34 | update `cache` set `value` = 'i:4;' where `key` =... | 0.72ms |
| 35 | select * from `cache` where `key` =... | 1.08ms |
| 36 | select * from `cache` where `key` in... | 1.31ms |
| 37 | select * from `cache` where `key` in... | 8.78ms |
| 38 | select * from `cache` where `key` in... | 4.87ms |
| 39 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.48ms |
| 40 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 0.78ms |
| 41 | select * from `cache` where `key` in... | 1.10ms |
| 42 | select * from `notifications` where `notifications`.`notifiable_type` =... | 1.93ms |
| 43 | select count(*) as aggregate from `notifications` where... | 1.16ms |
| 44 | update `cache` set `value` = 'i:2;' where `key` =... | 0.72ms |
| 45 | select * from `cache` where `key` =... | 0.66ms |
| 46 | select * from `cache` where `key` in... | 0.91ms |
| 47 | select * from `cache` where `key` in... | 2.66ms |
| 48 | select * from `cache` where `key` in... | 0.55ms |
| 49 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 0.81ms |
| 50 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.15ms |
| 51 | select * from `cache` where `key` in... | 3.70ms |
| 52 | update `cache` set `value` = 'i:3;' where `key` =... | 2.13ms |
| 53 | select * from `cache` where `key` =... | 0.43ms |
| 54 | select * from `cache` where `key` in... | 0.81ms |
| 55 | select * from `cache` where `key` in... | 1.32ms |
| 56 | select * from `cache` where `key` in... | 0.56ms |
| 57 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 0.79ms |
| 58 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 0.99ms |
| 59 | select * from `cache` where `key` in... | 1.04ms |
| 60 | update `cache` set `value` = 'i:1;' where `key` =... | 0.66ms |
| 61 | select * from `cache` where `key` =... | 1.01ms |
| 62 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.46ms |
| 63 | select * from `cache` where `key` in... | 1.08ms |
| 64 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.32ms |
| 65 | delete from `cache` where `key` in... | 1.36ms |
| 66 | select * from `cache` where `key` in... | 0.67ms |
| 67 | delete from `cache` where `key` in... | 1.45ms |
| 68 | select * from `cache` where `key` in... | 0.78ms |
| 69 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 09:27:08'... | 3.97ms |
| 70 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 2.20ms |
| 71 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 0.79ms |
| 72 | select * from `cache` where `key` in... | 0.72ms |
| 73 | select * from `cache` where `key` in... | 0.75ms |
| 74 | insert into `personal_access_tokens` (`name`, `token`, `abilities`, `expires_at`... | 4.11ms |
| 75 | select * from `users` where `email` = 'mahasiswa@kampuslms.test' and... | 2.80ms |
| 76 | update `cache` set `value` = 'i:2;' where `key` =... | 0.71ms |
| 77 | select * from `cache` where `key` =... | 0.68ms |
| 78 | select * from `cache` where `key` in... | 0.75ms |
| 79 | select * from `cache` where `key` in... | 1.07ms |
| 80 | select * from `cache` where `key` in... | 0.83ms |
| 81 | update `cache` set `value` = 'i:1;' where `key` =... | 0.98ms |
| 82 | select * from `cache` where `key` =... | 1.04ms |
| 83 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.21ms |
| 84 | select * from `cache` where `key` in... | 0.92ms |
| 85 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.38ms |
| 86 | delete from `cache` where `key` in... | 1.55ms |
| 87 | select * from `cache` where `key` in... | 1.18ms |
| 88 | delete from `cache` where `key` in... | 2.87ms |
| 89 | select * from `cache` where `key` in... | 1.25ms |
| 90 | update `sessions` set `payload` =... | 10.78ms |
| 91 | select * from `sessions` where `id` = 'dG4QQWvtQkhMKQZ8789xNrOFpEgaVVpoiotcGMwG' limit 1 | 2.39ms |
| 92 | insert into `sessions` (`payload`, `last_activity`, `user_id`, `ip_address`... | 1.66ms |
| 93 | select * from `sessions` where `id` = 'dG4QQWvtQkhMKQZ8789xNrOFpEgaVVpoiotcGMwG' limit 1 | 1.11ms |
| 94 | select * from `sessions` where `id` = 'dG4QQWvtQkhMKQZ8789xNrOFpEgaVVpoiotcGMwG' limit 1 | 1.22ms |
| 95 | insert into `sessions` (`payload`, `last_activity`, `user_id`, `ip_address`... | 2.27ms |
| 96 | select * from `sessions` where `id` = 'CryEtrKXxZuxanzz9cCBwL0juTnDpTGbjf4CiUHp' limit 1 | 2.11ms |
| 97 | select * from `sessions` where `id` = 'CryEtrKXxZuxanzz9cCBwL0juTnDpTGbjf4CiUHp' limit 1 | 0.95ms |

#### Model
| No | Model | Action |
|----|--------|--------|
| 1 | Illuminate\Notifications\DatabaseNotification | retrieved |
| 2 | Laravel\Sanctum\PersonalAccessToken:11 | updated |
| 3 | App\Models\User | retrieved |
| 4 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 5 | Laravel\Sanctum\PersonalAccessToken:11 | updated |
| 6 | App\Models\User | retrieved |
| 7 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 8 | App\Models\User | retrieved |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 10 | App\Models\User | retrieved |
| 11 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 12 | Illuminate\Notifications\DatabaseNotification | retrieved |
| 13 | App\Models\User | retrieved |
| 14 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 15 | App\Models\User | retrieved |
| 16 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 17 | Laravel\Sanctum\PersonalAccessToken:11 | updated |
| 18 | App\Models\User | retrieved |
| 19 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 20 | Laravel\Sanctum\PersonalAccessToken:11 | created |
| 21 | App\Models\User | retrieved |

#### Cache
| No | Key | Action |
|----|-----|--------|
| 1 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 2 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 3 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 4 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 5 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 6 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 7 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 8 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 9 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 10 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 11 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 12 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 13 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 14 | 1b6453892473a467d07372d45eb05abc2031647a | missed |
| 15 | 5c785c036466adea360111aa28563bfd556b5fba | hit |
| 16 | 5c785c036466adea360111aa28563bfd556b5fba | hit |
| 17 | 5c785c036466adea360111aa28563bfd556b5fba | hit |
| 18 | 5c785c036466adea360111aa28563bfd556b5fba | missed |


No Exception

## Role: Mahasiswa
### Page: Kelas Saya

#### Request
| No | Verb | Path | Status | Duration |
|----|------|------|--------|----------|
| 1 | GET | /api/v1/my/courses?page=1 | 200 | 129ms |
| 2 | GET | /api/v1/me | 200 | 101ms |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 101ms |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 96ms |

#### Query
| No | Query | Duration |
|----|--------|----------|
| 1 | select * from `cache` where `key` in... | 1.44ms |
| 2 | select * from `users` where `users`.`id` in (2, 3, 5) and `users`.`deleted_at` is null | 1.33ms |
| 3 | select `courses`.*, (select count(*)) from `materials` where `courses`.`id` =... | 15.77ms |
| 4 | select count(*) as aggregate from `courses` inner join `course_user` on `courses`.`id`... | 1.46ms |
| 5 | update `cache` set `value` = 'i:20;' where `key` =... | 0.92ms |
| 6 | select * from `cache` where `key` =... | 0.99ms |
| 7 | select * from `cache` where `key` in... | 1.03ms |
| 8 | select * from `cache` where `key` in... | 1.65ms |
| 9 | select * from `cache` where `key` in... | 1.08ms |
| 10 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.24ms |

#### Model
| No | Model | Action |
|----|--------|--------|
| 1 | App\Models\Course | retrieved |
| 2 | App\Models\User | retrieved |
| 3 | Laravel\Sanctum\PersonalAccessToken | retrieved |
| 4 | Laravel\Sanctum\PersonalAccessToken:11 | updated |
| 5 | App\Models\User | retrieved |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved |

#### Cache
| No | Key | Action |
|----|-----|--------|
| 1 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 2 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 3 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 4 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 5 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 6 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 7 | 1b6453892473a467d07372d45eb05abc2031647a | hit |
| 8 | 1b6453892473a467d07372d45eb05abc2031647a | hit |

## Role: Mahasiswa
### Page: Lihat Kelas

#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/courses/3/materials | 200 | 95ms | 4s ago |
| 2 | GET | /api/v1/courses/3/assignments | 200 | 149ms | 4s ago |
| 3 | GET | /api/v1/courses/3 | 200 | 112ms | 5s ago |
| 4 | GET | /api/v1/notifications?per_page=5 | 200 | 216ms | 5s ago |
| 5 | GET | /api/v1/me | 200 | 230ms | 5s ago |
| 6 | GET | /api/v1/notifications/unread-count | 200 | 207ms | 5s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 0.77ms | 2:23m ago |
| 2 | select * from `courses` where `courses`.`id` in (3) | 0.89ms | 2:23m ago |
| 3 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 1.03ms | 2:23m ago |
| 4 | select * from `materials` where `materials`.`course_id` = 3 and `materials`.`course_id`... | 1.12ms | 2:23m ago |
| 5 | select count(*) as aggregate from `materials` where `materials`.`course_id` = 3 and... | 0.75ms | 2:23m ago |
| 6 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 0.62ms | 2:23m ago |
| 7 | select * from `courses` where `id` = '3' limit 1 | 0.78ms | 2:23m ago |
| 8 | update `cache` set `value` = 'i:4;' where `key` =... | 0.74ms | 2:23m ago |
| 9 | select * from `cache` where `key` =... | 0.59ms | 2:23m ago |
| 10 | select * from `cache` where `key` in... | 0.65ms | 2:23m ago |
| 11 | select * from `cache` where `key` in... | 0.67ms | 2:23m ago |
| 12 | select * from `cache` where `key` in... | 0.85ms | 2:23m ago |
| 13 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 0.81ms | 2:23m ago |
| 14 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.28ms | 2:23m ago |
| 15 | select * from `cache` where `key` in... | 1.25ms | 2:23m ago |
| 16 | select * from `courses` where `courses`.`id` in (3) | 1.02ms | 2:23m ago |
| 17 | select `assignments`.*, (select count(*)) from `grades` inner join `submissions` on... | 1.74ms | 2:23m ago |
| 18 | select count(*) as aggregate from `assignments` where `assignments`.`course_id` = 3 and... | 1.66ms | 2:23m ago |
| 19 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 1.31ms | 2:23m ago |
| 20 | select * from `courses` where `id` = '3' limit 1 | 0.82ms | 2:23m ago |
| 21 | update `cache` set `value` = 'i:3;' where `key` =... | 0.84ms | 2:23m ago |
| 22 | select * from `cache` where `key` =... | 0.90ms | 2:23m ago |
| 23 | select * from `cache` where `key` in... | 0.66ms | 2:23m ago |
| 24 | select * from `cache` where `key` in... | 0.98ms | 2:23m ago |
| 25 | select * from `cache` where `key` in... | 1.00ms | 2:23m ago |
| 26 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:11:52'... | 1.70ms | 2:23m ago |
| 27 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.02ms | 2:23m ago |
| 28 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 0.94ms | 2:23m ago |
| 29 | select * from `cache` where `key` in... | 1.89ms | 2:24m ago |
| 30 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 1.35ms | 2:24m ago |
| 31 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 2.32ms | 2:24m ago |
| 32 | select `id`, (select count(*) from `materials` where `courses`.`id` =... | 2.92ms | 2:24m ago |
| 33 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 1.11ms | 2:24m ago |
| 34 | select * from `courses` where `id` = '3' limit 1 | 0.85ms | 2:24m ago |
| 35 | update `cache` set `value` = 'i:2;' where `key` =... | 0.88ms | 2:24m ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Material | retrieved | 3:13m ago |
| 2 | App\Models\Course | retrieved | 3:13m ago |
| 3 | App\Models\User | retrieved | 3:13m ago |
| 4 | Laravel\Sanctum\PersonalAccessToken | retrieved | 3:13m ago |
| 5 | App\Models\Assignment | retrieved | 3:13m ago |
| 6 | App\Models\Course | retrieved | 3:13m ago |
| 7 | Laravel\Sanctum\PersonalAccessToken:11 | updated | 3:13m ago |
| 8 | App\Models\User | retrieved | 3:13m ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 3:13m ago |
| 10 | App\Models\Course | retrieved | 3:14m ago |
| 11 | App\Models\User | retrieved | 3:14m ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 3:14m ago |
| 13 | Illuminate\Notifications\DatabaseNotification | retrieved | 3:14m ago |
| 14 | Laravel\Sanctum\PersonalAccessToken:11 | updated | 3:14m ago |
| 15 | App\Models\User | retrieved | 3:14m ago |
| 16 | Laravel\Sanctum\PersonalAccessToken | retrieved | 3:14m ago |
| 17 | Laravel\Sanctum\PersonalAccessToken:11 | updated | 3:14m ago |
| 18 | App\Models\User | retrieved | 3:14m ago |
| 19 | Laravel\Sanctum\PersonalAccessToken | retrieved | 3:14m ago |
| 20 | Laravel\Sanctum\PersonalAccessToken:11 | updated | 3:14m ago |
| 21 | App\Models\User | retrieved | 3:14m ago |
| 22 | Laravel\Sanctum\PersonalAccessToken | retrieved | 3:14m ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:34m ago |
| 2 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:34m ago |
| 3 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:34m ago |
| 4 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:34m ago |
| 5 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:35m ago |
| 6 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:35m ago |
| 7 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:35m ago |
| 8 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 3:35m ago |
| 9 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:35m ago |
| 10 | 1b6453892473a467d07372d45eb05abc2031647a | set | 3:35m ago |
| 11 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 3:35m ago |
| 12 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 3:35m ago |
| 13 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 3:35m ago |

### Page: Pengumpulan Saya

#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/my/submissions?page=1 | 200 | 189ms | 2s ago |
| 2 | GET | /api/v1/me | 200 | 130ms | 2s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 130ms | 2s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 122ms | 2s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.13ms | 40s ago |
| 2 | select exists(select * from `grades` where `grades`.`submission_id` = 62 and... | 0.96ms | 40s ago |
| 3 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 1.05ms | 40s ago |
| 4 | select exists(select * from `grades` where `grades`.`submission_id` = 77 and... | 0.94ms | 40s ago |
| 5 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 1.23ms | 40s ago |
| 6 | select exists(select * from `grades` where `grades`.`submission_id` = 12 and... | 0.74ms | 40s ago |
| 7 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 0.91ms | 40s ago |
| 8 | select exists(select * from `grades` where `grades`.`submission_id` = 103 and... | 0.72ms | 40s ago |
| 9 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 0.92ms | 40s ago |
| 10 | select exists(select * from `grades` where `grades`.`submission_id` = 50 and... | 0.99ms | 40s ago |
| 11 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 0.91ms | 40s ago |
| 12 | select exists(select * from `grades` where `grades`.`submission_id` = 114 and... | 0.53ms | 40s ago |
| 13 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 0.76ms | 40s ago |
| 14 | select exists(select * from `grades` where `grades`.`submission_id` = 76 and... | 0.83ms | 40s ago |
| 15 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 0.94ms | 40s ago |
| 16 | select exists(select * from `grades` where `grades`.`submission_id` = 27 and... | 1.00ms | 40s ago |
| 17 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 0.87ms | 40s ago |
| 18 | select * from `grades` where `grades`.`submission_id` in (12, 27, 50, 62, 76, 77, 103... | 1.28ms | 40s ago |
| 19 | select * from `courses` where `courses`.`id` in (1, 2, 3, 4, 5) | 1.34ms | 40s ago |
| 20 | select * from `assignments` where `assignments`.`id` in (1, 2, 5, 7, 8, 10, 13, 14) | 0.97ms | 40s ago |
| 21 | select * from `submissions` where `submissions`.`user_id` = 4 and... | 1.18ms | 40s ago |
| 22 | select count(*) as aggregate from `submissions` where `submissions`.`user_id` = 4 and... | 1.03ms | 40s ago |
| 23 | update `cache` set `value` = 'i:2;' where `key` =... | 0.82ms | 40s ago |
| 24 | select * from `cache` where `key` =... | 0.91ms | 40s ago |
| 25 | select * from `cache` where `key` in... | 0.95ms | 40s ago |
| 26 | select * from `cache` where `key` in... | 0.95ms | 40s ago |
| 27 | select * from `cache` where `key` in... | 1.07ms | 40s ago |
| 28 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 0.99ms | 40s ago |
| 29 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.56ms | 40s ago |
| 30 | select * from `cache` where `key` in... | 1.30ms | 40s ago |
| 31 | update `cache` set `value` = 'i:3;' where `key` =... | 2.37ms | 40s ago |
| 32 | select * from `cache` where `key` =... | 6.20ms | 40s ago |
| 33 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.81ms | 40s ago |
| 34 | select * from `cache` where `key` in... | 0.72ms | 40s ago |
| 35 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 3.39ms | 40s ago |
| 36 | delete from `cache` where `key` in... | 1.03ms | 40s ago |
| 37 | select * from `cache` where `key` in... | 1.47ms | 40s ago |
| 38 | delete from `cache` where `key` in... | 0.88ms | 40s ago |
| 39 | select * from `cache` where `key` in... | 0.78ms | 40s ago |
| 40 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:15:51'... | 2.24ms | 40s ago |
| 41 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 2.54ms | 40s ago |
| 42 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.07ms | 40s ago |
| 43 | select * from `cache` where `key` in... | 1.37ms | 40s ago |
| 44 | select * from `notifications` where `notifications`.`notifiable_type` =... | 1.08ms | 40s ago |
| 45 | select count(*) as aggregate from `notifications` where... | 0.96ms | 40s ago |
| 46 | insert into `cache` (`expiration`, `key`, `value`) values (1793445411... | 7.98ms | 40s ago |
| 47 | update `cache` set `value` = 'i:1;' where `key` =... | 0.69ms | 40s ago |
| 48 | select * from `cache` where `key` =... | 0.69ms | 40s ago |
| 49 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.75ms | 40s ago |
| 50 | select * from `cache` where `key` in... | 0.88ms | 40s ago |
| 51 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.11ms | 40s ago |
| 52 | delete from `cache` where `key` in... | 1.86ms | 40s ago |
| 53 | select * from `cache` where `key` in... | 0.61ms | 40s ago |
| 54 | delete from `cache` where `key` in... | 1.82ms | 40s ago |
| 55 | select * from `cache` where `key` in... | 0.96ms | 40s ago |
| 56 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:15:51'... | 0.57ms | 40s ago |
| 57 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.36ms | 40s ago |
| 58 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.04ms | 40s ago |
| 59 | select * from `cache` where `key` in... | 0.69ms | 40s ago |
| 60 | select count(*) as aggregate from `notifications` where... | 3.28ms | 40s ago |
| 61 | update `cache` set `value` = 'i:2;' where `key` =... | 0.93ms | 40s ago |
| 62 | select * from `cache` where `key` =... | 3.72ms | 40s ago |
| 63 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.43ms | 40s ago |
| 64 | select * from `cache` where `key` in... | 0.64ms | 40s ago |
| 65 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.98ms | 40s ago |
| 66 | delete from `cache` where `key` in... | 1.03ms | 40s ago |
| 67 | select * from `cache` where `key` in... | 1.53ms | 40s ago |
| 68 | delete from `cache` where `key` in... | 0.93ms | 40s ago |
| 69 | select * from `cache` where `key` in... | 0.86ms | 40s ago |
| 70 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:15:51'... | 0.91ms | 40s ago |
| 71 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.38ms | 40s ago |
| 72 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '11' limit 1 | 1.19ms | 40s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Grade | retrieved | 1:02m ago |
| 2 | App\Models\Course | retrieved | 1:02m ago |
| 3 | App\Models\Assignment | retrieved | 1:02m ago |
| 4 | App\Models\Submission | retrieved | 1:02m ago |
| 5 | App\Models\User | retrieved | 1:02m ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:02m ago |
| 7 | Laravel\Sanctum\PersonalAccessToken:11 | updated | 1:02m ago |
| 8 | App\Models\User | retrieved | 1:02m ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:02m ago |
| 10 | Illuminate\Notifications\DatabaseNotification | retrieved | 1:02m ago |
| 11 | Laravel\Sanctum\PersonalAccessToken:11 | updated | 1:02m ago |
| 12 | App\Models\User | retrieved | 1:02m ago |
| 13 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:02m ago |
| 14 | Laravel\Sanctum\PersonalAccessToken:11 | updated | 1:02m ago |
| 15 | App\Models\User | retrieved | 1:02m ago |
| 16 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:02m ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 1:15m ago |
| 2 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 1:15m ago |
| 3 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 1:15m ago |
| 4 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 1:15m ago |
| 5 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 1:15m ago |
| 6 | 1b6453892473a467d07372d45eb05abc2031647a | set | 1:15m ago |
| 7 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 1:15m ago |
| 8 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 1:15m ago |
| 9 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 1:15m ago |

### Page: Notification
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/notifications?page=1 | 200 | 90ms | 11s ago |
| 2 | GET | /api/v1/me | 200 | 154ms | 11s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 154ms | 11s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 146ms | 11s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.07ms | 26s ago |
| 2 | select * from `notifications` where `notifications`.`notifiable_type` =... | 1.67ms | 26s ago |
| 3 | select count(*) as aggregate from `notifications` where... | 1.18ms | 26s ago |
| 4 | update `cache` set `value` = 'i:2;' where `key` =... | 0.88ms | 26s ago |
| 5 | select * from `cache` where `key` =... | 1.18ms | 26s ago |
| 6 | select * from `cache` where `key` in... | 1.33ms | 26s ago |
| 7 | select * from `cache` where `key` in... | 0.79ms | 26s ago |
| 8 | select * from `cache` where `key` in... | 0.94ms | 26s ago |
| 9 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.13ms | 26s ago |
| 10 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '15' limit 1 | 1.32ms | 26s ago |
| 11 | select * from `cache` where `key` in... | 1.27ms | 26s ago |
| 12 | update `cache` set `value` = 'i:2;' where `key` =... | 0.77ms | 26s ago |
| 13 | select * from `cache` where `key` =... | 4.19ms | 26s ago |
| 14 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.70ms | 26s ago |
| 15 | select * from `cache` where `key` in... | 0.78ms | 26s ago |
| 16 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 3.46ms | 26s ago |
| 17 | select * from `cache` where `key` in... | 0.52ms | 26s ago |
| 18 | select * from `cache` where `key` in... | 0.74ms | 26s ago |
| 19 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:52:41'... | 11.48ms | 26s ago |
| 20 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 0.67ms | 26s ago |
| 21 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '15' limit 1 | 0.91ms | 26s ago |
| 22 | select * from `cache` where `key` in... | 1.02ms | 26s ago |
| 23 | select * from `notifications` where `notifications`.`notifiable_type` =... | 0.80ms | 26s ago |
| 24 | select count(*) as aggregate from `notifications` where... | 1.37ms | 26s ago |
| 25 | update `cache` set `value` = 'i:3;' where `key` =... | 2.80ms | 26s ago |
| 26 | select * from `cache` where `key` =... | 6.79ms | 26s ago |
| 27 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.58ms | 26s ago |
| 28 | select * from `cache` where `key` in... | 0.71ms | 26s ago |
| 29 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 3.86ms | 26s ago |
| 30 | delete from `cache` where `key` in... | 0.85ms | 26s ago |
| 31 | select * from `cache` where `key` in... | 0.53ms | 26s ago |
| 32 | delete from `cache` where `key` in... | 3.59ms | 26s ago |
| 33 | select * from `cache` where `key` in... | 0.90ms | 26s ago |
| 34 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:52:41'... | 2.88ms | 26s ago |
| 35 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 0.89ms | 26s ago |
| 36 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '15' limit 1 | 1.21ms | 26s ago |
| 37 | select * from `cache` where `key` in... | 0.63ms | 26s ago |
| 38 | select count(*) as aggregate from `notifications` where... | 1.23ms | 26s ago |
| 39 | insert into `cache` (`expiration`, `key`, `value`) values (1793447621... | 8.35ms | 26s ago |
| 40 | update `cache` set `value` = 'i:1;' where `key` =... | 1.12ms | 26s ago |
| 41 | select * from `cache` where `key` =... | 0.71ms | 26s ago |
| 42 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.83ms | 26s ago |
| 43 | select * from `cache` where `key` in... | 0.95ms | 26s ago |
| 44 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.70ms | 26s ago |
| 45 | delete from `cache` where `key` in... | 1.78ms | 26s ago |
| 46 | select * from `cache` where `key` in... | 1.03ms | 26s ago |
| 47 | delete from `cache` where `key` in... | 1.10ms | 26s ago |
| 48 | select * from `cache` where `key` in... | 2.37ms | 26s ago |
| 49 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:52:41'... | 2.98ms | 26s ago |
| 50 | select * from `users` where `users`.`id` = 4 and `users`.`deleted_at` is null limit 1 | 1.52ms | 26s ago |
| 51 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '15' limit 1 | 0.91ms | 26s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | Illuminate\Notifications\DatabaseNotification | retrieved | 40s ago |
| 2 | App\Models\User | retrieved | 40s ago |
| 3 | Laravel\Sanctum\PersonalAccessToken | retrieved | 40s ago |
| 4 | Laravel\Sanctum\PersonalAccessToken:15 | updated | 40s ago |
| 5 | App\Models\User | retrieved | 40s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 40s ago |
| 7 | Illuminate\Notifications\DatabaseNotification | retrieved | 40s ago |
| 8 | Laravel\Sanctum\PersonalAccessToken:15 | updated | 40s ago |
| 9 | App\Models\User | retrieved | 40s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken | retrieved | 40s ago |
| 11 | Laravel\Sanctum\PersonalAccessToken:15 | updated | 40s ago |
| 12 | App\Models\User | retrieved | 40s ago |
| 13 | Laravel\Sanctum\PersonalAccessToken | retrieved | 40s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 51s ago |
| 2 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 51s ago |
| 3 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 51s ago |
| 4 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 51s ago |
| 5 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 51s ago |
| 6 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 51s ago |
| 7 | 1b6453892473a467d07372d45eb05abc2031647a | hit | 51s ago |
| 8 | 1b6453892473a467d07372d45eb05abc2031647a | set | 51s ago |
| 9 | 1b6453892473a467d07372d45eb05abc2031647a | missed | 51s ago |

---

## Role: Dosen

### Page: Login-Home Page-Notification
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/dashboard | 200 | 88ms | 3s ago |
| 2 | GET | /api/v1/me | 200 | 117ms | 3s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 103ms | 3s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 102ms | 3s ago |
| 5 | GET | /api/v1/me | 200 | 104ms | 3s ago |
| 6 | POST | /api/v1/auth/login | 200 | 569ms | 3s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 0.98ms | 28s ago |
| 2 | select `courses`.*, (select count(*)) from `assignments` where `courses`.`id` =... | 1.36ms | 28s ago |
| 3 | select count(*) as aggregate from `courses` where `courses`.`lecturer_id` = 2 and... | 1.29ms | 28s ago |
| 4 | update `cache` set `value` = 'i:5;' where `key` =... | 1.13ms | 28s ago |
| 5 | select * from `cache` where `key` =... | 0.93ms | 28s ago |
| 6 | select * from `cache` where `key` in... | 1.14ms | 28s ago |
| 7 | select * from `cache` where `key` in... | 1.16ms | 28s ago |
| 8 | select * from `cache` where `key` in... | 1.35ms | 28s ago |
| 9 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.14ms | 28s ago |
| 10 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.32ms | 28s ago |
| 11 | select * from `cache` where `key` in... | 1.52ms | 28s ago |
| 12 | update `cache` set `value` = 'i:4;' where `key` =... | 0.88ms | 28s ago |
| 13 | select * from `cache` where `key` =... | 4.51ms | 28s ago |
| 14 | select * from `cache` where `key` in... | 0.81ms | 28s ago |
| 15 | select * from `cache` where `key` in... | 0.83ms | 28s ago |
| 16 | select * from `cache` where `key` in... | 1.05ms | 28s ago |
| 17 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.86ms | 28s ago |
| 18 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 0.91ms | 28s ago |
| 19 | select * from `cache` where `key` in... | 0.97ms | 28s ago |
| 20 | select count(*) as aggregate from `notifications` where... | 1.08ms | 28s ago |
| 21 | update `cache` set `value` = 'i:3;' where `key` =... | 0.86ms | 28s ago |
| 22 | select * from `cache` where `key` =... | 2.31ms | 28s ago |
| 23 | select * from `cache` where `key` in... | 0.75ms | 28s ago |
| 24 | select * from `cache` where `key` in... | 1.38ms | 28s ago |
| 25 | select * from `cache` where `key` in... | 0.62ms | 28s ago |
| 26 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.77ms | 28s ago |
| 27 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 0.78ms | 28s ago |
| 28 | select * from `cache` where `key` in... | 0.64ms | 28s ago |
| 29 | select count(*) as aggregate from `notifications` where... | 1.30ms | 28s ago |
| 30 | update `cache` set `value` = 'i:2;' where `key` =... | 0.80ms | 28s ago |
| 31 | select * from `cache` where `key` =... | 0.83ms | 28s ago |
| 32 | select * from `cache` where `key` in... | 0.87ms | 28s ago |
| 33 | select * from `cache` where `key` in... | 0.60ms | 28s ago |
| 34 | select * from `cache` where `key` in... | 1.01ms | 28s ago |
| 35 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.79ms | 28s ago |
| 36 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.19ms | 28s ago |
| 37 | select * from `cache` where `key` in... | 3.08ms | 28s ago |
| 38 | update `cache` set `value` = 'i:1;' where `key` =... | 0.50ms | 28s ago |
| 39 | select * from `cache` where `key` =... | 0.56ms | 28s ago |
| 40 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.20ms | 28s ago |
| 41 | select * from `cache` where `key` in... | 0.59ms | 28s ago |
| 42 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.96ms | 28s ago |
| 43 | delete from `cache` where `key` in... | 1.98ms | 28s ago |
| 44 | select * from `cache` where `key` in... | 0.91ms | 28s ago |
| 45 | delete from `cache` where `key` in... | 1.40ms | 28s ago |
| 46 | select * from `cache` where `key` in... | 0.62ms | 28s ago |
| 47 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:26:09'... | 2.06ms | 28s ago |
| 48 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.37ms | 28s ago |
| 49 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.35ms | 28s ago |
| 50 | select * from `cache` where `key` in... | 1.17ms | 28s ago |
| 51 | select * from `cache` where `key` in... | 1.21ms | 28s ago |
| 52 | insert into `personal_access_tokens` (`name`, `token`, `abilities`, `expires_at`... | 9.70ms | 28s ago |
| 53 | select * from `users` where `email` = 'dosen@kampuslms.test' and `users`.`deleted_at`... | 1.80ms | 29s ago |
| 54 | update `cache` set `value` = 'i:2;' where `key` =... | 1.16ms | 29s ago |
| 55 | select * from `cache` where `key` =... | 0.91ms | 29s ago |
| 56 | select * from `cache` where `key` in... | 0.96ms | 29s ago |
| 57 | select * from `cache` where `key` in... | 0.94ms | 29s ago |
| 58 | select * from `cache` where `key` in... | 0.67ms | 29s ago |
| 59 | update `cache` set `value` = 'i:1;' where `key` =... | 0.79ms | 29s ago |
| 60 | select * from `cache` where `key` =... | 0.90ms | 29s ago |
| 61 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.25ms | 29s ago |
| 62 | select * from `cache` where `key` in... | 0.93ms | 29s ago |
| 63 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.35ms | 29s ago |
| 64 | delete from `cache` where `key` in... | 1.53ms | 29s ago |
| 65 | select * from `cache` where `key` in... | 0.74ms | 29s ago |
| 66 | delete from `cache` where `key` in... | 3.55ms | 29s ago |
| 67 | select * from `cache` where `key` in... | 0.95ms | 29s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Course | retrieved | 45s ago |
| 2 | App\Models\User | retrieved | 45s ago |
| 3 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 4 | App\Models\User | retrieved | 45s ago |
| 5 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 6 | App\Models\User | retrieved | 45s ago |
| 7 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 8 | App\Models\User | retrieved | 45s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 45s ago |
| 11 | App\Models\User | retrieved | 45s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 13 | Laravel\Sanctum\PersonalAccessToken:12 | created | 45s ago |
| 14 | App\Models\User | retrieved | 46s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 2 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 3 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 4 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 5 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 6 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 7 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 8 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 9 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 1:02m ago |
| 10 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 1:02m ago |
| 11 | 5c785c036466adea360111aa28563bfd556b5fba | hit | 1:02m ago |
| 12 | 5c785c036466adea360111aa28563bfd556b5fba | hit | 1:02m ago |
| 13 | 5c785c036466adea360111aa28563bfd556b5fba | hit | 1:03m ago |
| 14 | 5c785c036466adea360111aa28563bfd556b5fba | missed | 1:03m ago |

### Page: Kelas Saya
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/my/courses?page=1 | 200 | 78ms | 3s ago |
| 2 | GET | /api/v1/notifications/unread-count | 200 | 115ms | 4s ago |
| 3 | GET | /api/v1/me | 200 | 116ms | 4s ago |
| 4 | GET | /api/v1/notifications?per_page=5 | 200 | 116ms | 4s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 0.83ms | 14s ago |
| 2 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 0.70ms | 14s ago |
| 3 | select `courses`.*, (select count(*)) from `materials` where `courses`.`id` =... | 1.19ms | 14s ago |
| 4 | select count(*) as aggregate from `courses` where `courses`.`lecturer_id` = 2 and... | 0.81ms | 14s ago |
| 5 | update `cache` set `value` = 'i:4;' where `key` =... | 0.72ms | 14s ago |
| 6 | select * from `cache` where `key` =... | 1.47ms | 14s ago |
| 7 | select * from `cache` where `key` in... | 0.66ms | 14s ago |
| 8 | select * from `cache` where `key` in... | 0.79ms | 14s ago |
| 9 | select * from `cache` where `key` in... | 0.59ms | 14s ago |
| 10 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:27:56'... | 2.65ms | 14s ago |
| 11 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.83ms | 14s ago |
| 12 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 0.81ms | 14s ago |
| 13 | select * from `cache` where `key` in... | 1.02ms | 15s ago |
| 14 | select count(*) as aggregate from `notifications` where... | 0.85ms | 15s ago |
| 15 | update `cache` set `value` = 'i:3;' where `key` =... | 0.74ms | 15s ago |
| 16 | select * from `cache` where `key` =... | 6.16ms | 15s ago |
| 17 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.64ms | 15s ago |
| 18 | select * from `cache` where `key` in... | 2.48ms | 15s ago |
| 19 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.61ms | 15s ago |
| 20 | delete from `cache` where `key` in... | 1.12ms | 15s ago |
| 21 | select * from `cache` where `key` in... | 0.44ms | 15s ago |
| 22 | delete from `cache` where `key` in... | 1.52ms | 15s ago |
| 23 | select * from `cache` where `key` in... | 0.73ms | 15s ago |
| 24 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:27:55'... | 0.77ms | 15s ago |
| 25 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.71ms | 15s ago |
| 26 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 0.68ms | 15s ago |
| 27 | select * from `cache` where `key` in... | 2.40ms | 15s ago |
| 28 | update `cache` set `value` = 'i:2;' where `key` =... | 0.70ms | 15s ago |
| 29 | select * from `cache` where `key` =... | 4.53ms | 15s ago |
| 30 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 3.84ms | 15s ago |
| 31 | select * from `cache` where `key` in... | 0.56ms | 15s ago |
| 32 | select * from `cache` where `key` in... | 2.65ms | 15s ago |
| 33 | select * from `cache` where `key` in... | 0.59ms | 15s ago |
| 34 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:27:55'... | 9.61ms | 15s ago |
| 35 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.54ms | 15s ago |
| 36 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 3.36ms | 15s ago |
| 37 | select * from `cache` where `key` in... | 0.69ms | 15s ago |
| 38 | select count(*) as aggregate from `notifications` where... | 0.79ms | 15s ago |
| 39 | update `cache` set `value` = 'i:1;' where `key` =... | 2.08ms | 15s ago |
| 40 | select * from `cache` where `key` =... | 0.65ms | 15s ago |
| 41 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 2.72ms | 15s ago |
| 42 | select * from `cache` where `key` in... | 0.66ms | 15s ago |
| 43 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.15ms | 15s ago |
| 44 | select * from `cache` where `key` in... | 0.69ms | 15s ago |
| 45 | delete from `cache` where `key` in... | 2.94ms | 15s ago |
| 46 | select * from `cache` where `key` in... | 2.50ms | 15s ago |
| 47 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:27:55'... | 0.85ms | 15s ago |
| 48 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.42ms | 15s ago |
| 49 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 4.71ms | 15s ago |

#### Model

| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Course | retrieved | 28s ago |
| 2 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 28s ago |
| 3 | App\Models\User | retrieved | 28s ago |
| 4 | Laravel\Sanctum\PersonalAccessToken | retrieved | 28s ago |
| 5 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 29s ago |
| 6 | App\Models\User | retrieved | 29s ago |
| 7 | Laravel\Sanctum\PersonalAccessToken | retrieved | 29s ago |
| 8 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 29s ago |
| 9 | App\Models\User | retrieved | 29s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken | retrieved | 29s ago |
| 11 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 29s ago |
| 12 | App\Models\User | retrieved | 29s ago |
| 13 | Laravel\Sanctum\PersonalAccessToken | retrieved | 29s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 41s ago |
| 2 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 41s ago |
| 3 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 42s ago |
| 4 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 42s ago |
| 5 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 42s ago |
| 6 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 42s ago |
| 7 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 42s ago |
| 8 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 42s ago |

### Page: Lihat Kelas
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/courses/3/materials | 200 | 103ms | 3s ago |
| 2 | GET | /api/v1/courses/3/assignments | 200 | 135ms | 3s ago |
| 3 | GET | /api/v1/courses/3 | 200 | 141ms | 3s ago |
| 4 | GET | /api/v1/notifications?per_page=5 | 200 | 168ms | 4s ago |
| 5 | GET | /api/v1/notifications/unread-count | 200 | 161ms | 4s ago |
| 6 | GET | /api/v1/me | 200 | 165ms | 4s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.00ms | 22s ago |
| 2 | select * from `courses` where `courses`.`id` in (3) | 0.88ms | 22s ago |
| 3 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 1.88ms | 22s ago |
| 4 | select * from `materials` where `materials`.`course_id` = 3 and `materials`.`course_id`... | 1.44ms | 22s ago |
| 5 | select count(*) as aggregate from `materials` where `materials`.`course_id` = 3 and... | 2.24ms | 22s ago |
| 6 | select * from `courses` where `id` = '3' limit 1 | 1.01ms | 22s ago |
| 7 | update `cache` set `value` = 'i:4;' where `key` =... | 1.39ms | 22s ago |
| 8 | select * from `cache` where `key` =... | 1.02ms | 22s ago |
| 9 | select * from `cache` where `key` in... | 0.87ms | 22s ago |
| 10 | select * from `cache` where `key` in... | 1.02ms | 22s ago |
| 11 | select * from `cache` where `key` in... | 1.13ms | 22s ago |
| 12 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 2.67ms | 22s ago |
| 13 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 2.03ms | 22s ago |
| 14 | select * from `cache` where `key` in... | 1.19ms | 22s ago |
| 15 | select * from `courses` where `courses`.`id` in (3) | 0.91ms | 22s ago |
| 16 | select `assignments`.*, (select count(*)) from `grades` inner join `submissions` on... | 0.97ms | 22s ago |
| 17 | select count(*) as aggregate from `assignments` where `assignments`.`course_id` = 3 and... | 0.90ms | 22s ago |
| 18 | select * from `courses` where `id` = '3' limit 1 | 1.12ms | 22s ago |
| 19 | update `cache` set `value` = 'i:3;' where `key` =... | 0.57ms | 22s ago |
| 20 | select * from `cache` where `key` =... | 0.61ms | 22s ago |
| 21 | select * from `cache` where `key` in... | 1.04ms | 22s ago |
| 22 | select * from `cache` where `key` in... | 1.29ms | 22s ago |
| 23 | select * from `cache` where `key` in... | 1.51ms | 22s ago |
| 24 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.68ms | 22s ago |
| 25 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.15ms | 22s ago |
| 26 | select * from `cache` where `key` in... | 1.39ms | 22s ago |
| 27 | select `id`, (select count(*) from `materials` where `courses`.`id` =... | 1.38ms | 22s ago |
| 28 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 1.32ms | 22s ago |
| 29 | select * from `courses` where `id` = '3' limit 1 | 0.99ms | 22s ago |
| 30 | update `cache` set `value` = 'i:2;' where `key` =... | 1.42ms | 22s ago |
| 31 | select * from `cache` where `key` =... | 0.82ms | 22s ago |
| 32 | select * from `cache` where `key` in... | 0.91ms | 22s ago |
| 33 | select * from `cache` where `key` in... | 1.00ms | 22s ago |
| 34 | select * from `cache` where `key` in... | 1.29ms | 22s ago |
| 35 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:30:13'... | 5.22ms | 22s ago |
| 36 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.94ms | 22s ago |
| 37 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.95ms | 22s ago |
| 38 | select * from `cache` where `key` in... | 2.26ms | 23s ago |
| 39 | select count(*) as aggregate from `notifications` where... | 0.47ms | 23s ago |
| 40 | insert into `cache` (`expiration`, `key`, `value`) values (1793446272... | 5.34ms | 23s ago |
| 41 | update `cache` set `value` = 'i:1;' where `key` =... | 0.42ms | 23s ago |
| 42 | select * from `cache` where `key` =... | 0.59ms | 23s ago |
| 43 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.49ms | 23s ago |
| 44 | select * from `cache` where `key` in... | 0.64ms | 23s ago |
| 45 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.54ms | 23s ago |
| 46 | delete from `cache` where `key` in... | 1.29ms | 23s ago |
| 47 | select * from `cache` where `key` in... | 0.97ms | 23s ago |
| 48 | delete from `cache` where `key` in... | 5.22ms | 23s ago |
| 49 | select * from `cache` where `key` in... | 0.77ms | 23s ago |
| 50 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:30:12'... | 0.95ms | 23s ago |
| 51 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.02ms | 23s ago |
| 52 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.52ms | 23s ago |
| 53 | select * from `cache` where `key` in... | 0.71ms | 23s ago |
| 54 | select count(*) as aggregate from `notifications` where... | 0.82ms | 23s ago |
| 55 | update `cache` set `value` = 'i:3;' where `key` =... | 0.82ms | 23s ago |
| 56 | select * from `cache` where `key` =... | 3.43ms | 23s ago |
| 57 | select * from `cache` where `key` in... | 0.42ms | 23s ago |
| 58 | select * from `cache` where `key` in... | 1.21ms | 23s ago |
| 59 | select * from `cache` where `key` in... | 3.45ms | 23s ago |
| 60 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:30:12'... | 9.53ms | 23s ago |
| 61 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.92ms | 23s ago |
| 62 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 2.98ms | 23s ago |
| 63 | select * from `cache` where `key` in... | 0.53ms | 23s ago |
| 64 | update `cache` set `value` = 'i:2;' where `key` =... | 0.56ms | 23s ago |
| 65 | select * from `cache` where `key` =... | 6.41ms | 23s ago |
| 66 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.88ms | 23s ago |
| 67 | select * from `cache` where `key` in... | 0.72ms | 23s ago |
| 68 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.23ms | 23s ago |
| 69 | delete from `cache` where `key` in... | 1.59ms | 23s ago |
| 70 | select * from `cache` where `key` in... | 1.28ms | 23s ago |
| 71 | delete from `cache` where `key` in... | 1.02ms | 23s ago |
| 72 | select * from `cache` where `key` in... | 0.77ms | 23s ago |
| 73 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:30:12'... | 0.44ms | 23s ago |
| 74 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 8.19ms | 23s ago |
| 75 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.76ms | 23s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Material | retrieved | 40s ago |
| 2 | App\Models\Course | retrieved | 40s ago |
| 3 | App\Models\User | retrieved | 40s ago |
| 4 | Laravel\Sanctum\PersonalAccessToken | retrieved | 40s ago |
| 5 | App\Models\Assignment | retrieved | 40s ago |
| 6 | App\Models\Course | retrieved | 40s ago |
| 7 | App\Models\User | retrieved | 40s ago |
| 8 | Laravel\Sanctum\PersonalAccessToken | retrieved | 40s ago |
| 9 | App\Models\Course | retrieved | 40s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 40s ago |
| 11 | App\Models\User | retrieved | 40s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 40s ago |
| 13 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 41s ago |
| 14 | App\Models\User | retrieved | 41s ago |
| 15 | Laravel\Sanctum\PersonalAccessToken | retrieved | 41s ago |
| 16 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 41s ago |
| 17 | App\Models\User | retrieved | 41s ago |
| 18 | Laravel\Sanctum\PersonalAccessToken | retrieved | 41s ago |
| 19 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 41s ago |
| 20 | App\Models\User | retrieved | 41s ago |
| 21 | Laravel\Sanctum\PersonalAccessToken | retrieved | 41s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 53s ago |
| 2 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 53s ago |
| 3 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 53s ago |
| 4 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 53s ago |
| 5 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 53s ago |
| 6 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 53s ago |
| 7 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 54s ago |
| 8 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | set | 54s ago |
| 9 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 54s ago |
| 10 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 54s ago |
| 11 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 54s ago |
| 12 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 54s ago |
| 13 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 54s ago |

### Page: Lihat Pengumpulan
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/assignments/8/submissions?page=1 | 200 | 172ms | 3s ago |
| 2 | GET | /api/v1/assignments/8 | 200 | 107ms | 3s ago |
| 3 | GET | /api/v1/notifications/unread-count | 200 | 126ms | 4s ago |
| 4 | GET | /api/v1/me | 200 | 110ms | 4s ago |
| 5 | GET | /api/v1/notifications?per_page=5 | 200 | 108ms | 4s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.57ms | 19s ago |
| 2 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 1.06ms | 19s ago |
| 3 | select * from `grades` where `grades`.`submission_id` in (63, 64, 65, 66, 67, 68, 69...) | 2.84ms | 19s ago |
| 4 | select * from `users` where `users`.`id` in (4, 6, 8, 9, 11, 13, 18, 19, 22, 25, 27, 28...) | 0.84ms | 19s ago |
| 5 | select * from `courses` where `courses`.`id` in (3) | 1.03ms | 19s ago |
| 6 | select * from `assignments` where `assignments`.`id` in (8) | 1.01ms | 19s ago |
| 7 | select * from `submissions` where `submissions`.`assignment_id` = 8 and... | 1.42ms | 19s ago |
| 8 | select count(*) as aggregate from `submissions` where `submissions`.`assignment_id` = 8... | 2.31ms | 19s ago |
| 9 | select * from `courses` where `courses`.`id` in (3) | 1.33ms | 19s ago |
| 10 | select * from `assignments` where `id` = '8' limit 1 | 0.47ms | 19s ago |
| 11 | update `cache` set `value` = 'i:3;' where `key` =... | 0.83ms | 19s ago |
| 12 | select * from `cache` where `key` =... | 1.37ms | 19s ago |
| 13 | select * from `cache` where `key` in... | 0.42ms | 19s ago |
| 14 | select * from `cache` where `key` in... | 0.79ms | 19s ago |
| 15 | select * from `cache` where `key` in... | 0.80ms | 19s ago |
| 16 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.15ms | 19s ago |
| 17 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '23' limit 1 | 1.45ms | 19s ago |
| 18 | select * from `cache` where `key` in... | 1.77ms | 19s ago |
| 19 | select exists(select * from `grades` inner join `submissions` on `submissions`.`id` =... | 1.89ms | 19s ago |
| 20 | select exists(select * from `grades` inner join `submissions` on `submissions`.`id` =... | 3.27ms | 19s ago |
| 21 | select * from `courses` where `courses`.`id` in (3) | 0.77ms | 19s ago |
| 22 | select * from `assignments` where `id` = '8' limit 1 | 0.81ms | 19s ago |
| 23 | update `cache` set `value` = 'i:2;' where `key` =... | 0.75ms | 19s ago |
| 24 | select * from `cache` where `key` =... | 0.97ms | 19s ago |
| 25 | select * from `cache` where `key` in... | 1.22ms | 19s ago |
| 26 | select * from `cache` where `key` in... | 1.34ms | 19s ago |
| 27 | select * from `cache` where `key` in... | 1.19ms | 19s ago |
| 28 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 12:41:11'... | 3.75ms | 19s ago |
| 29 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.30ms | 19s ago |
| 30 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '23' limit 1 | 1.00ms | 19s ago |
| 31 | select * from `cache` where `key` in... | 0.88ms | 20s ago |
| 32 | select count(*) as aggregate from `notifications` where... | 1.18ms | 20s ago |
| 33 | insert into `cache` (`expiration`, `key`, `value`) values (1793450530... | 6.49ms | 20s ago |
| 34 | update `cache` set `value` = 'i:1;' where `key` =... | 1.24ms | 20s ago |
| 35 | select * from `cache` where `key` =... | 0.90ms | 20s ago |
| 36 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.81ms | 20s ago |
| 37 | select * from `cache` where `key` in... | 0.78ms | 20s ago |
| 38 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.26ms | 20s ago |
| 39 | delete from `cache` where `key` in... | 0.62ms | 20s ago |
| 40 | select * from `cache` where `key` in... | 0.84ms | 20s ago |
| 41 | delete from `cache` where `key` in... | 0.84ms | 20s ago |
| 42 | select * from `cache` where `key` in... | 3.01ms | 20s ago |
| 43 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 12:41:10'... | 1.14ms | 20s ago |
| 44 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.67ms | 20s ago |
| 45 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '23' limit 1 | 3.39ms | 20s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Grade | retrieved | 30s ago |
| 2 | App\Models\Submission | retrieved | 30s ago |
| 3 | App\Models\Course | retrieved | 30s ago |
| 4 | App\Models\Assignment | retrieved | 30s ago |
| 5 | App\Models\User | retrieved | 30s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 30s ago |
| 7 | App\Models\Course | retrieved | 30s ago |
| 8 | App\Models\Assignment | retrieved | 30s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken:23 | updated | 30s ago |
| 10 | App\Models\User | retrieved | 30s ago |
| 11 | Laravel\Sanctum\PersonalAccessToken | retrieved | 30s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken:23 | updated | 31s ago |
| 13 | App\Models\User | retrieved | 31s ago |
| 14 | Laravel\Sanctum\PersonalAccessToken | retrieved | 31s ago |
| 15 | Laravel\Sanctum\PersonalAccessToken:23 | updated | 31s ago |
| 16 | App\Models\User | retrieved | 31s ago |
| 17 | Laravel\Sanctum\PersonalAccessToken | retrieved | 31s ago |
| 18 | Laravel\Sanctum\PersonalAccessToken:23 | updated | 31s ago |
| 19 | App\Models\User | retrieved | 31s ago |
| 20 | Laravel\Sanctum\PersonalAccessToken | retrieved | 31s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 41s ago |
| 2 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 41s ago |
| 3 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 41s ago |
| 4 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 41s ago |
| 5 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 42s ago |
| 6 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | set | 42s ago |
| 7 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 42s ago |
| 8 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 42s ago |
| 9 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 42s ago |
| 10 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 42s ago |
| 11 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 42s ago |

### Page: Rekap Nilai
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/courses/3/grade-summary?page=1 | 200 | 109ms | 3s ago |
| 2 | GET | /api/v1/courses/3 | 200 | 83ms | 3s ago |
| 3 | GET | /api/v1/me | 200 | 120ms | 3s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 108ms | 3s ago |
| 5 | GET | /api/v1/notifications?per_page=5 | 200 | 109ms | 3s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 0.84ms | 24s ago |
| 2 | select * from `courses` where `courses`.`id` in (3) | 0.77ms | 24s ago |
| 3 | select * from `assignments` where `assignments`.`id` in (7, 8) | 1.14ms | 24s ago |
| 4 | select * from `users` where `users`.`id` in (4, 6, 8, 9, 10, 11, 29, 34) and... | 0.86ms | 24s ago |
| 5 | select * from `submissions` where `submissions`.`id` in (51, 52, 53, 54, 60, 63, 64, 65... | 0.85ms | 24s ago |
| 6 | select * from `grades` where exists (select * from `submissions` where... | 1.44ms | 24s ago |
| 7 | select count(*) as aggregate from `grades` where exists (select * from `submissions`... | 1.24ms | 24s ago |
| 8 | select * from `courses` where `id` = '3' limit 1 | 1.03ms | 24s ago |
| 9 | update `cache` set `value` = 'i:5;' where `key` =... | 0.71ms | 24s ago |
| 10 | select * from `cache` where `key` =... | 0.66ms | 24s ago |
| 11 | select * from `cache` where `key` in... | 0.68ms | 24s ago |
| 12 | select * from `cache` where `key` in... | 0.75ms | 24s ago |
| 13 | select * from `cache` where `key` in... | 2.05ms | 24s ago |
| 14 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.74ms | 24s ago |
| 15 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 2.89ms | 24s ago |
| 16 | select * from `cache` where `key` in... | 0.75ms | 24s ago |
| 17 | select `id`, (select count(*) from `materials` where `courses`.`id` =... | 0.94ms | 24s ago |
| 18 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 0.79ms | 24s ago |
| 19 | select * from `courses` where `id` = '3' limit 1 | 0.85ms | 24s ago |
| 20 | update `cache` set `value` = 'i:4;' where `key` =... | 0.60ms | 24s ago |
| 21 | select * from `cache` where `key` =... | 0.59ms | 24s ago |
| 22 | select * from `cache` where `key` in... | 0.90ms | 24s ago |
| 23 | select * from `cache` where `key` in... | 1.36ms | 24s ago |
| 24 | select * from `cache` where `key` in... | 0.74ms | 24s ago |
| 25 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.72ms | 24s ago |
| 26 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 0.72ms | 24s ago |
| 27 | select * from `cache` where `key` in... | 2.28ms | 24s ago |
| 28 | update `cache` set `value` = 'i:1;' where `key` =... | 1.88ms | 24s ago |
| 29 | select * from `cache` where `key` =... | 0.54ms | 24s ago |
| 30 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.96ms | 24s ago |
| 31 | select * from `cache` where `key` in... | 0.63ms | 24s ago |
| 32 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.58ms | 24s ago |
| 33 | delete from `cache` where `key` in... | 0.61ms | 24s ago |
| 34 | select * from `cache` where `key` in... | 0.70ms | 24s ago |
| 35 | delete from `cache` where `key` in... | 0.54ms | 24s ago |
| 36 | select * from `cache` where `key` in... | 1.78ms | 24s ago |
| 37 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:31:51'... | 2.60ms | 24s ago |
| 38 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.82ms | 24s ago |
| 39 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 1.10ms | 24s ago |
| 40 | select * from `cache` where `key` in... | 0.50ms | 24s ago |
| 41 | select count(*) as aggregate from `notifications` where... | 0.71ms | 24s ago |
| 42 | update `cache` set `value` = 'i:2;' where `key` =... | 0.67ms | 24s ago |
| 43 | select * from `cache` where `key` =... | 4.71ms | 24s ago |
| 44 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.47ms | 24s ago |
| 45 | select * from `cache` where `key` in... | 0.54ms | 24s ago |
| 46 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.57ms | 24s ago |
| 47 | delete from `cache` where `key` in... | 1.23ms | 24s ago |
| 48 | select * from `cache` where `key` in... | 0.62ms | 24s ago |
| 49 | delete from `cache` where `key` in... | 1.92ms | 24s ago |
| 50 | select * from `cache` where `key` in... | 0.64ms | 24s ago |
| 51 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:31:51'... | 0.54ms | 24s ago |
| 52 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.65ms | 24s ago |
| 53 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 0.94ms | 24s ago |
| 54 | select * from `cache` where `key` in... | 0.70ms | 24s ago |
| 55 | select count(*) as aggregate from `notifications` where... | 0.76ms | 24s ago |
| 56 | update `cache` set `value` = 'i:3;' where `key` =... | 0.57ms | 24s ago |
| 57 | select * from `cache` where `key` =... | 6.99ms | 24s ago |
| 58 | select * from `cache` where `key` in... | 3.52ms | 24s ago |
| 59 | select * from `cache` where `key` in... | 0.72ms | 24s ago |
| 60 | delete from `cache` where `key` in... | 3.68ms | 24s ago |
| 61 | select * from `cache` where `key` in... | 2.98ms | 24s ago |
| 62 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:31:51'... | 4.10ms | 24s ago |
| 63 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 2.85ms | 24s ago |
| 64 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '12' limit 1 | 0.93ms | 24s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Assignment | retrieved | 45s ago |
| 2 | App\Models\Submission | retrieved | 45s ago |
| 3 | App\Models\Grade | retrieved | 45s ago |
| 4 | App\Models\Course | retrieved | 45s ago |
| 5 | App\Models\User | retrieved | 45s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 7 | App\Models\Course | retrieved | 45s ago |
| 8 | App\Models\User | retrieved | 45s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 45s ago |
| 11 | App\Models\User | retrieved | 45s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 13 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 45s ago |
| 14 | App\Models\User | retrieved | 45s ago |
| 15 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |
| 16 | Laravel\Sanctum\PersonalAccessToken:12 | updated | 45s ago |
| 17 | App\Models\User | retrieved | 45s ago |
| 18 | Laravel\Sanctum\PersonalAccessToken | retrieved | 45s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 2 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 3 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 4 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 5 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 6 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 59s ago |
| 7 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 8 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 59s ago |
| 9 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 10 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 59s ago |

### Page: Notification
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/notifications?page=1 | 200 | 86ms | 3s ago |
| 2 | GET | /api/v1/notifications?per_page=5 | 200 | 268ms | 3s ago |
| 3 | GET | /api/v1/me | 200 | 269ms | 3s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 269ms | 3s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.07ms | 42s ago |
| 2 | select count(*) as aggregate from `notifications` where... | 0.92ms | 42s ago |
| 3 | update `cache` set `value` = 'i:4;' where `key` =... | 1.15ms | 42s ago |
| 4 | select * from `cache` where `key` =... | 1.95ms | 42s ago |
| 5 | select * from `cache` where `key` in... | 1.04ms | 42s ago |
| 6 | select * from `cache` where `key` in... | 1.03ms | 42s ago |
| 7 | select * from `cache` where `key` in... | 0.92ms | 42s ago |
| 8 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:48:35'... | 1.87ms | 42s ago |
| 9 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.25ms | 42s ago |
| 10 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '14' limit 1 | 1.20ms | 42s ago |
| 11 | select * from `cache` where `key` in... | 1.72ms | 42s ago |
| 12 | select count(*) as aggregate from `notifications` where... | 0.57ms | 42s ago |
| 13 | update `cache` set `value` = 'i:1;' where `key` =... | 0.56ms | 42s ago |
| 14 | select * from `cache` where `key` =... | 0.47ms | 42s ago |
| 15 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.96ms | 42s ago |
| 16 | select * from `cache` where `key` in... | 0.42ms | 42s ago |
| 17 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.25ms | 42s ago |
| 18 | delete from `cache` where `key` in... | 1.03ms | 42s ago |
| 19 | select * from `cache` where `key` in... | 1.21ms | 42s ago |
| 20 | delete from `cache` where `key` in... | 0.87ms | 42s ago |
| 21 | select * from `cache` where `key` in... | 0.96ms | 42s ago |
| 22 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:48:34'... | 1.14ms | 42s ago |
| 23 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.32ms | 43s ago |
| 24 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '14' limit 1 | 1.27ms | 43s ago |
| 25 | select * from `cache` where `key` in... | 1.40ms | 42s ago |
| 26 | update `cache` set `value` = 'i:3;' where `key` =... | 1.13ms | 42s ago |
| 27 | select * from `cache` where `key` =... | 6.23ms | 42s ago |
| 28 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.43ms | 42s ago |
| 29 | select * from `cache` where `key` in... | 0.77ms | 42s ago |
| 30 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.75ms | 42s ago |
| 31 | select * from `cache` where `key` in... | 0.66ms | 42s ago |
| 32 | delete from `cache` where `key` in... | 5.39ms | 42s ago |
| 33 | select * from `cache` where `key` in... | 0.87ms | 42s ago |
| 34 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:48:34'... | 1.10ms | 42s ago |
| 35 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 1.26ms | 43s ago |
| 36 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '14' limit 1 | 1.26ms | 43s ago |
| 37 | select * from `cache` where `key` in... | 0.57ms | 42s ago |
| 38 | select count(*) as aggregate from `notifications` where... | 0.85ms | 42s ago |
| 39 | update `cache` set `value` = 'i:2;' where `key` =... | 0.59ms | 42s ago |
| 40 | select * from `cache` where `key` =... | 3.36ms | 42s ago |
| 41 | select * from `cache` where `key` in... | 0.56ms | 42s ago |
| 42 | select * from `cache` where `key` in... | 0.60ms | 42s ago |
| 43 | select * from `cache` where `key` in... | 0.51ms | 42s ago |
| 44 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:48:34'... | 10.13ms | 42s ago |
| 45 | select * from `users` where `users`.`id` = 2 and `users`.`deleted_at` is null limit 1 | 0.81ms | 43s ago |
| 46 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '14' limit 1 | 1.26ms | 43s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | Laravel\Sanctum\PersonalAccessToken:14 | updated | 51s ago |
| 2 | App\Models\User | retrieved | 51s ago |
| 3 | Laravel\Sanctum\PersonalAccessToken | retrieved | 51s ago |
| 4 | Laravel\Sanctum\PersonalAccessToken:14 | updated | 51s ago |
| 5 | App\Models\User | retrieved | 52s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 52s ago |
| 7 | Laravel\Sanctum\PersonalAccessToken:14 | updated | 51s ago |
| 8 | App\Models\User | retrieved | 52s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 52s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken:14 | updated | 51s ago |
| 11 | App\Models\User | retrieved | 52s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 52s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 2 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 3 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 4 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 59s ago |
| 5 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 6 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 59s ago |
| 7 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | hit | 59s ago |
| 8 | da4b9237bacccdf19c0760cab7aec4a8359010b0 | missed | 59s ago |

---

## Role: Admin
### Page: Login-Home Page
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/dashboard | 200 | 93ms | 2s ago |
| 2 | GET | /api/v1/notifications/unread-count | 200 | 89ms | 2s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 87ms | 2s ago |
| 4 | GET | /api/v1/me | 200 | 84ms | 2s ago |
| 5 | GET | /api/v1/me | 200 | 108ms | 2s ago |
| 6 | POST | /api/v1/auth/login | 200 | 632ms | 2s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 0.93ms | 21s ago |
| 2 | select count(*) as aggregate from `courses` | 1.26ms | 21s ago |
| 3 | select count(*) as aggregate from `users` where `users`.`deleted_at` is null | 1.33ms | 21s ago |
| 4 | update `cache` set `value` = 'i:5;' where `key` =... | 0.54ms | 21s ago |
| 5 | select * from `cache` where `key` =... | 0.98ms | 21s ago |
| 6 | select * from `cache` where `key` in... | 0.97ms | 21s ago |
| 7 | select * from `cache` where `key` in... | 0.78ms | 21s ago |
| 8 | select * from `cache` where `key` in... | 1.08ms | 21s ago |
| 9 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.22ms | 21s ago |
| 10 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.76ms | 21s ago |
| 11 | select * from `cache` where `key` in... | 3.90ms | 21s ago |
| 12 | select count(*) as aggregate from `notifications` where... | 0.84ms | 21s ago |
| 13 | update `cache` set `value` = 'i:4;' where `key` =... | 0.72ms | 21s ago |
| 14 | select * from `cache` where `key` =... | 1.15ms | 21s ago |
| 15 | select * from `cache` where `key` in... | 0.80ms | 21s ago |
| 16 | select * from `cache` where `key` in... | 0.98ms | 21s ago |
| 17 | select * from `cache` where `key` in... | 2.11ms | 21s ago |
| 18 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.75ms | 21s ago |
| 19 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 2.45ms | 21s ago |
| 20 | select * from `cache` where `key` in... | 1.02ms | 21s ago |
| 21 | select count(*) as aggregate from `notifications` where... | 1.34ms | 21s ago |
| 22 | update `cache` set `value` = 'i:3;' where `key` =... | 0.97ms | 21s ago |
| 23 | select * from `cache` where `key` =... | 0.90ms | 21s ago |
| 24 | select * from `cache` where `key` in... | 1.03ms | 21s ago |
| 25 | select * from `cache` where `key` in... | 0.87ms | 21s ago |
| 26 | select * from `cache` where `key` in... | 1.04ms | 21s ago |
| 27 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.65ms | 21s ago |
| 28 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 0.93ms | 21s ago |
| 29 | select * from `cache` where `key` in... | 1.02ms | 21s ago |
| 30 | update `cache` set `value` = 'i:2;' where `key` =... | 1.49ms | 21s ago |
| 31 | select * from `cache` where `key` =... | 0.95ms | 21s ago |
| 32 | select * from `cache` where `key` in... | 0.82ms | 21s ago |
| 33 | select * from `cache` where `key` in... | 1.05ms | 21s ago |
| 34 | select * from `cache` where `key` in... | 0.99ms | 21s ago |
| 35 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.65ms | 21s ago |
| 36 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.26ms | 21s ago |
| 37 | select * from `cache` where `key` in... | 1.26ms | 21s ago |
| 38 | update `cache` set `value` = 'i:1;' where `key` =... | 1.18ms | 21s ago |
| 39 | select * from `cache` where `key` =... | 0.87ms | 21s ago |
| 40 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.98ms | 21s ago |
| 41 | select * from `cache` where `key` in... | 1.84ms | 21s ago |
| 42 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 2.12ms | 21s ago |
| 43 | delete from `cache` where `key` in... | 1.58ms | 21s ago |
| 44 | select * from `cache` where `key` in... | 0.81ms | 21s ago |
| 45 | delete from `cache` where `key` in... | 1.44ms | 21s ago |
| 46 | select * from `cache` where `key` in... | 0.84ms | 21s ago |
| 47 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:34:20'... | 2.67ms | 21s ago |
| 48 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.83ms | 21s ago |
| 49 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.10ms | 21s ago |
| 50 | select * from `cache` where `key` in... | 1.11ms | 21s ago |
| 51 | select * from `cache` where `key` in... | 1.35ms | 21s ago |
| 52 | insert into `personal_access_tokens` (`name`, `token`, `abilities`, `expires_at`... | 3.22ms | 21s ago |
| 53 | select * from `users` where `email` = 'admin@kampuslms.test' and `users`.`deleted_at`... | 1.24ms | 22s ago |
| 54 | update `cache` set `value` = 'i:2;' where `key` =... | 0.78ms | 22s ago |
| 55 | select * from `cache` where `key` =... | 0.67ms | 22s ago |
| 56 | select * from `cache` where `key` in... | 0.61ms | 22s ago |
| 57 | select * from `cache` where `key` in... | 0.58ms | 22s ago |
| 58 | select * from `cache` where `key` in... | 0.56ms | 22s ago |
| 59 | update `cache` set `value` = 'i:1;' where `key` =... | 0.35ms | 22s ago |
| 60 | select * from `cache` where `key` =... | 0.45ms | 22s ago |
| 61 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.20ms | 22s ago |
| 62 | select * from `cache` where `key` in... | 0.73ms | 22s ago |
| 63 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.76ms | 22s ago |
| 64 | delete from `cache` where `key` in... | 0.84ms | 22s ago |
| 65 | select * from `cache` where `key` in... | 0.46ms | 22s ago |
| 66 | delete from `cache` where `key` in... | 2.44ms | 22s ago |
| 67 | select * from `cache` where `key` in... | 0.84ms | 22s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\User | retrieved | 35s ago |
| 2 | Laravel\Sanctum\PersonalAccessToken | retrieved | 35s ago |
| 3 | App\Models\User | retrieved | 35s ago |
| 4 | Laravel\Sanctum\PersonalAccessToken | retrieved | 35s ago |
| 5 | App\Models\User | retrieved | 35s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 35s ago |
| 7 | App\Models\User | retrieved | 35s ago |
| 8 | Laravel\Sanctum\PersonalAccessToken | retrieved | 35s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 35s ago |
| 10 | App\Models\User | retrieved | 35s ago |
| 11 | Laravel\Sanctum\PersonalAccessToken | retrieved | 35s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken:13 | created | 35s ago |
| 13 | App\Models\User | retrieved | 36s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 2 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 3 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 4 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 5 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 6 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 7 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 8 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 9 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 44s ago |
| 10 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 44s ago |
| 11 | 5c785c036466adea360111aa28563bfd556b5fba | hit | 44s ago |
| 12 | 5c785c036466adea360111aa28563bfd556b5fba | hit | 44s ago |
| 13 | 5c785c036466adea360111aa28563bfd556b5fba | hit | 45s ago |
| 14 | 5c785c036466adea360111aa28563bfd556b5fba | missed | 45s ago |

### Page: Course
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/courses?page=1&scope=all | 200 | 121ms | 5s ago |
| 2 | GET | /api/v1/me | 200 | 135ms | 5s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 134ms | 5s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 133ms | 5s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.67ms | 25s ago |
| 2 | select * from `users` where `users`.`id` in (2, 3, 5) and `users`.`deleted_at` is null | 1.22ms | 25s ago |
| 3 | select `courses`.*, (select count(*)) from `materials` where `courses`.`id` =... | 1.58ms | 25s ago |
| 4 | select count(*) as aggregate from `courses` | 1.14ms | 25s ago |
| 5 | update `cache` set `value` = 'i:2;' where `key` =... | 1.65ms | 25s ago |
| 6 | select * from `cache` where `key` =... | 0.61ms | 25s ago |
| 7 | select * from `cache` where `key` in... | 0.90ms | 25s ago |
| 8 | select * from `cache` where `key` in... | 1.04ms | 25s ago |
| 9 | select * from `cache` where `key` in... | 0.77ms | 25s ago |
| 10 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 2.98ms | 25s ago |
| 11 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.36ms | 25s ago |
| 12 | select * from `cache` where `key` in... | 1.44ms | 25s ago |
| 13 | update `cache` set `value` = 'i:2;' where `key` =... | 0.93ms | 25s ago |
| 14 | select * from `cache` where `key` =... | 3.37ms | 25s ago |
| 15 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.52ms | 25s ago |
| 16 | select * from `cache` where `key` in... | 0.68ms | 25s ago |
| 17 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.65ms | 25s ago |
| 18 | delete from `cache` where `key` in... | 1.87ms | 25s ago |
| 19 | select * from `cache` where `key` in... | 0.82ms | 25s ago |
| 20 | delete from `cache` where `key` in... | 0.88ms | 25s ago |
| 21 | select * from `cache` where `key` in... | 0.81ms | 25s ago |
| 22 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:35:20'... | 0.81ms | 25s ago |
| 23 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 2.76ms | 25s ago |
| 24 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.73ms | 25s ago |
| 25 | select * from `cache` where `key` in... | 0.78ms | 25s ago |
| 26 | select count(*) as aggregate from `notifications` where... | 0.97ms | 25s ago |
| 27 | insert into `cache` (`expiration`, `key`, `value`) values (1793446580... | 8.11ms | 25s ago |
| 28 | update `cache` set `value` = 'i:1;' where `key` =... | 1.03ms | 25s ago |
| 29 | select * from `cache` where `key` =... | 0.96ms | 25s ago |
| 30 | select * from `cache` where `key` in... | 0.74ms | 25s ago |
| 31 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.61ms | 25s ago |
| 32 | delete from `cache` where `key` in... | 2.55ms | 25s ago |
| 33 | select * from `cache` where `key` in... | 1.21ms | 25s ago |
| 34 | delete from `cache` where `key` in... | 1.77ms | 25s ago |
| 35 | select * from `cache` where `key` in... | 0.75ms | 25s ago |
| 36 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:35:20'... | 2.69ms | 25s ago |
| 37 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.84ms | 25s ago |
| 38 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.31ms | 25s ago |
| 39 | select * from `cache` where `key` in... | 0.72ms | 25s ago |
| 40 | select count(*) as aggregate from `notifications` where... | 1.45ms | 25s ago |
| 41 | update `cache` set `value` = 'i:3;' where `key` =... | 1.28ms | 25s ago |
| 42 | select * from `cache` where `key` =... | 6.21ms | 25s ago |
| 43 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.70ms | 25s ago |
| 44 | select * from `cache` where `key` in... | 0.99ms | 25s ago |
| 45 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 4.26ms | 25s ago |
| 46 | delete from `cache` where `key` in... | 0.77ms | 25s ago |
| 47 | select * from `cache` where `key` in... | 1.05ms | 25s ago |
| 48 | delete from `cache` where `key` in... | 0.83ms | 25s ago |
| 49 | select * from `cache` where `key` in... | 1.02ms | 25s ago |
| 50 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:35:20'... | 0.90ms | 25s ago |
| 51 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.60ms | 25s ago |
| 52 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.22ms | 25s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Course | retrieved | 34s ago |
| 2 | App\Models\User | retrieved | 34s ago |
| 3 | Laravel\Sanctum\PersonalAccessToken | retrieved | 34s ago |
| 4 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 34s ago |
| 5 | App\Models\User | retrieved | 34s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 34s ago |
| 7 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 34s ago |
| 8 | App\Models\User | retrieved | 34s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 34s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 34s ago |
| 11 | App\Models\User | retrieved | 34s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 34s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 2 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 3 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 4 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 43s ago |
| 5 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 6 | 356a192b7913b04c54574d18c28d46e6395428ab | set | 43s ago |
| 7 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 43s ago |
| 8 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 9 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 43s ago |

### Page: Detail Course
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/courses/3/enrollments/candidates | 200 | 94ms | 2s ago |
| 2 | GET | /api/v1/courses/3/students | 200 | 94ms | 2s ago |
| 3 | GET | /api/v1/courses/3/materials | 200 | 102ms | 2s ago |
| 4 | GET | /api/v1/courses/3/assignments | 200 | 131ms | 2s ago |
| 5 | GET | /api/v1/courses/3 | 200 | 79ms | 3s ago |
| 6 | GET | /api/v1/notifications?per_page=5 | 200 | 154ms | 3s ago |
| 7 | GET | /api/v1/notifications/unread-count | 200 | 136ms | 3s ago |
| 8 | GET | /api/v1/me | 200 | 153ms | 3s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 0.77ms | 17s ago |
| 2 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 0.65ms | 17s ago |
| 3 | select * from `users` where `role` = 'mahasiswa' and not exists (select * from... | 2.71ms | 17s ago |
| 4 | select * from `courses` where `id` = '3' limit 1 | 0.99ms | 17s ago |
| 5 | update `cache` set `value` = 'i:7;' where `key` =... | 0.88ms | 17s ago |
| 6 | select * from `cache` where `key` =... | 0.40ms | 17s ago |
| 7 | select * from `cache` where `key` in... | 0.71ms | 17s ago |
| 8 | select * from `cache` where `key` in... | 1.04ms | 17s ago |
| 9 | select * from `cache` where `key` in... | 0.94ms | 17s ago |
| 10 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 2.99ms | 17s ago |
| 11 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.85ms | 17s ago |
| 12 | select * from `cache` where `key` in... | 0.80ms | 17s ago |
| 13 | select `users`.*, `course_user`.`course_id` as `pivot_course_id`... | 1.21ms | 17s ago |
| 14 | select count(*) as aggregate from `users` inner join `course_user` on `users`.`id` =... | 0.64ms | 17s ago |
| 15 | select * from `courses` where `id` = '3' limit 1 | 1.12ms | 17s ago |
| 16 | update `cache` set `value` = 'i:8;' where `key` =... | 0.87ms | 17s ago |
| 17 | select * from `cache` where `key` =... | 4.06ms | 17s ago |
| 18 | select * from `cache` where `key` in... | 0.96ms | 17s ago |
| 19 | select * from `cache` where `key` in... | 0.86ms | 17s ago |
| 20 | select * from `cache` where `key` in... | 2.30ms | 17s ago |
| 21 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.12ms | 17s ago |
| 22 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.49ms | 17s ago |
| 23 | select * from `cache` where `key` in... | 1.13ms | 17s ago |
| 24 | select * from `courses` where `courses`.`id` in (3) | 0.76ms | 17s ago |
| 25 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 0.93ms | 17s ago |
| 26 | select * from `materials` where `materials`.`course_id` = 3 and `materials`.`course_id`... | 1.66ms | 17s ago |
| 27 | select count(*) as aggregate from `materials` where `materials`.`course_id` = 3 and... | 1.81ms | 17s ago |
| 28 | select * from `courses` where `id` = '3' limit 1 | 0.82ms | 17s ago |
| 29 | update `cache` set `value` = 'i:6;' where `key` =... | 1.13ms | 17s ago |
| 30 | select * from `cache` where `key` =... | 0.91ms | 17s ago |
| 31 | select * from `cache` where `key` in... | 0.89ms | 17s ago |
| 32 | select * from `cache` where `key` in... | 0.93ms | 17s ago |
| 33 | select * from `cache` where `key` in... | 0.95ms | 17s ago |
| 34 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.19ms | 17s ago |
| 35 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.38ms | 17s ago |
| 36 | select * from `cache` where `key` in... | 1.05ms | 17s ago |
| 37 | select * from `courses` where `courses`.`id` in (3) | 0.86ms | 17s ago |
| 38 | select `assignments`.*, (select count(*)) from `grades` inner join `submissions` on... | 2.41ms | 17s ago |
| 39 | select count(*) as aggregate from `assignments` where `assignments`.`course_id` = 3 and... | 1.70ms | 17s ago |
| 40 | select * from `courses` where `id` = '3' limit 1 | 0.95ms | 17s ago |
| 41 | update `cache` set `value` = 'i:5;' where `key` =... | 1.06ms | 17s ago |
| 42 | select * from `cache` where `key` =... | 0.91ms | 17s ago |
| 43 | select * from `cache` where `key` in... | 1.01ms | 17s ago |
| 44 | select * from `cache` where `key` in... | 0.87ms | 17s ago |
| 45 | select * from `cache` where `key` in... | 0.81ms | 17s ago |
| 46 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:36:29'... | 2.81ms | 17s ago |
| 47 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.58ms | 17s ago |
| 48 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 0.98ms | 17s ago |
| 49 | select * from `cache` where `key` in... | 0.82ms | 18s ago |
| 50 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 1.20ms | 18s ago |
| 51 | select `id`, (select count(*) from `materials` where `courses`.`id` =... | 0.79ms | 18s ago |
| 52 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 0.76ms | 18s ago |
| 53 | select * from `courses` where `id` = '3' limit 1 | 1.02ms | 18s ago |
| 54 | update `cache` set `value` = 'i:4;' where `key` =... | 0.68ms | 18s ago |
| 55 | select * from `cache` where `key` =... | 0.74ms | 18s ago |
| 56 | select * from `cache` where `key` in... | 0.79ms | 18s ago |
| 57 | select * from `cache` where `key` in... | 0.95ms | 18s ago |
| 58 | select * from `cache` where `key` in... | 0.99ms | 18s ago |
| 59 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.93ms | 18s ago |
| 60 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 0.76ms | 18s ago |
| 61 | select * from `cache` where `key` in... | 0.59ms | 18s ago |
| 62 | select count(*) as aggregate from `notifications` where... | 0.66ms | 18s ago |
| 63 | update `cache` set `value` = 'i:3;' where `key` =... | 0.55ms | 18s ago |
| 64 | select * from `cache` where `key` =... | 4.17ms | 18s ago |
| 65 | select * from `cache` where `key` in... | 0.75ms | 18s ago |
| 66 | select * from `cache` where `key` in... | 0.95ms | 18s ago |
| 67 | select * from `cache` where `key` in... | 2.67ms | 18s ago |
| 68 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:36:28'... | 10.16ms | 18s ago |
| 69 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.88ms | 18s ago |
| 70 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.32ms | 18s ago |
| 71 | select * from `cache` where `key` in... | 0.69ms | 18s ago |
| 72 | select count(*) as aggregate from `notifications` where... | 0.64ms | 18s ago |
| 73 | update `cache` set `value` = 'i:2;' where `key` =... | 0.59ms | 18s ago |
| 74 | select * from `cache` where `key` =... | 5.21ms | 18s ago |
| 75 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.74ms | 18s ago |
| 76 | select * from `cache` where `key` in... | 0.77ms | 18s ago |
| 77 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.66ms | 18s ago |
| 78 | delete from `cache` where `key` in... | 0.84ms | 18s ago |
| 79 | select * from `cache` where `key` in... | 0.74ms | 18s ago |
| 80 | delete from `cache` where `key` in... | 3.94ms | 18s ago |
| 81 | select * from `cache` where `key` in... | 0.96ms | 18s ago |
| 82 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:36:28'... | 0.73ms | 18s ago |
| 83 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 3.78ms | 18s ago |
| 84 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.29ms | 18s ago |
| 85 | select * from `cache` where `key` in... | 0.47ms | 18s ago |
| 86 | update `cache` set `value` = 'i:1;' where `key` =... | 1.62ms | 18s ago |
| 87 | select * from `cache` where `key` =... | 0.75ms | 18s ago |
| 88 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.32ms | 18s ago |
| 89 | select * from `cache` where `key` in... | 0.77ms | 18s ago |
| 90 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.45ms | 18s ago |
| 91 | delete from `cache` where `key` in... | 1.18ms | 18s ago |
| 92 | select * from `cache` where `key` in... | 0.99ms | 18s ago |
| 93 | delete from `cache` where `key` in... | 1.25ms | 18s ago |
| 94 | select * from `cache` where `key` in... | 0.99ms | 18s ago |
| 95 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:36:28'... | 0.68ms | 18s ago |
| 96 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.99ms | 18s ago |
| 97 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.16ms | 18s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Course | retrieved | 30s ago |
| 2 | App\Models\User | retrieved | 30s ago |
| 3 | Laravel\Sanctum\PersonalAccessToken | retrieved | 30s ago |
| 4 | App\Models\Course | retrieved | 30s ago |
| 5 | App\Models\User | retrieved | 30s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 30s ago |
| 7 | App\Models\Material | retrieved | 30s ago |
| 8 | App\Models\Course | retrieved | 30s ago |
| 9 | App\Models\User | retrieved | 30s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken | retrieved | 30s ago |
| 11 | App\Models\Assignment | retrieved | 30s ago |
| 12 | App\Models\Course | retrieved | 30s ago |
| 13 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 30s ago |
| 14 | App\Models\User | retrieved | 30s ago |
| 15 | Laravel\Sanctum\PersonalAccessToken | retrieved | 30s ago |
| 16 | App\Models\Course | retrieved | 31s ago |
| 17 | App\Models\User | retrieved | 31s ago |
| 18 | Laravel\Sanctum\PersonalAccessToken | retrieved | 31s ago |
| 19 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 31s ago |
| 20 | App\Models\User | retrieved | 31s ago |
| 21 | Laravel\Sanctum\PersonalAccessToken | retrieved | 31s ago |
| 22 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 31s ago |
| 23 | App\Models\User | retrieved | 31s ago |
| 24 | Laravel\Sanctum\PersonalAccessToken | retrieved | 31s ago |
| 25 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 31s ago |
| 26 | App\Models\User | retrieved | 31s ago |
| 27 | Laravel\Sanctum\PersonalAccessToken | retrieved | 31s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 2 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 3 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 4 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 5 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 6 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 7 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 8 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 42s ago |
| 9 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 10 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 11 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 12 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 43s ago |
| 13 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 14 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 43s ago |
| 15 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 43s ago |
| 16 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 43s ago |

### Page: Rekap Nilai
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/courses/3/grade-summary?page=1 | 200 | 96ms | 4s ago |
| 2 | GET | /api/v1/courses/3 | 200 | 83ms | 4s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 145ms | 4s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 148ms | 4s ago |
| 5 | GET | /api/v1/me | 200 | 148ms | 4s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.54ms | 18s ago |
| 2 | select * from `courses` where `courses`.`id` in (3) | 0.60ms | 18s ago |
| 3 | select * from `assignments` where `assignments`.`id` in (7, 8) | 0.95ms | 18s ago |
| 4 | select * from `users` where `users`.`id` in (4, 6, 8, 9, 10, 11, 29, 34) and... | 0.89ms | 18s ago |
| 5 | select * from `submissions` where `submissions`.`id` in (51, 52, 53, 54, 60, 63, 64, 65... | 0.91ms | 18s ago |
| 6 | select * from `grades` where exists (select * from `submissions` where... | 1.66ms | 18s ago |
| 7 | select count(*) as aggregate from `grades` where exists (select * from `submissions`... | 1.40ms | 18s ago |
| 8 | select * from `courses` where `id` = '3' limit 1 | 0.92ms | 18s ago |
| 9 | update `cache` set `value` = 'i:5;' where `key` =... | 0.83ms | 18s ago |
| 10 | select * from `cache` where `key` =... | 2.96ms | 18s ago |
| 11 | select * from `cache` where `key` in... | 0.94ms | 18s ago |
| 12 | select * from `cache` where `key` in... | 0.79ms | 18s ago |
| 13 | select * from `cache` where `key` in... | 1.20ms | 18s ago |
| 14 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.80ms | 18s ago |
| 15 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.00ms | 18s ago |
| 16 | select * from `cache` where `key` in... | 0.76ms | 18s ago |
| 17 | select exists(select * from `users` inner join `course_user` on `users`.`id` =... | 1.16ms | 18s ago |
| 18 | select `id`, (select count(*) from `materials` where `courses`.`id` =... | 1.49ms | 18s ago |
| 19 | select * from `users` where `users`.`id` in (2) and `users`.`deleted_at` is null | 0.96ms | 18s ago |
| 20 | select * from `courses` where `id` = '3' limit 1 | 0.65ms | 18s ago |
| 21 | update `cache` set `value` = 'i:4;' where `key` =... | 0.85ms | 18s ago |
| 22 | select * from `cache` where `key` =... | 1.05ms | 18s ago |
| 23 | select * from `cache` where `key` in... | 1.11ms | 18s ago |
| 24 | select * from `cache` where `key` in... | 1.04ms | 18s ago |
| 25 | select * from `cache` where `key` in... | 0.73ms | 18s ago |
| 26 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.26ms | 18s ago |
| 27 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 0.87ms | 18s ago |
| 28 | select * from `cache` where `key` in... | 2.94ms | 18s ago |
| 29 | select count(*) as aggregate from `notifications` where... | 3.00ms | 18s ago |
| 30 | update `cache` set `value` = 'i:1;' where `key` =... | 0.67ms | 18s ago |
| 31 | select * from `cache` where `key` =... | 0.65ms | 18s ago |
| 32 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.11ms | 18s ago |
| 33 | select * from `cache` where `key` in... | 0.42ms | 18s ago |
| 34 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.98ms | 18s ago |
| 35 | delete from `cache` where `key` in... | 1.65ms | 18s ago |
| 36 | select * from `cache` where `key` in... | 1.31ms | 18s ago |
| 37 | delete from `cache` where `key` in... | 1.70ms | 18s ago |
| 38 | select * from `cache` where `key` in... | 0.65ms | 18s ago |
| 39 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:39:57'... | 2.84ms | 18s ago |
| 40 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.79ms | 18s ago |
| 41 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.02ms | 18s ago |
| 42 | select * from `cache` where `key` in... | 0.76ms | 18s ago |
| 43 | select count(*) as aggregate from `notifications` where... | 0.82ms | 18s ago |
| 44 | update `cache` set `value` = 'i:3;' where `key` =... | 0.67ms | 18s ago |
| 45 | select * from `cache` where `key` =... | 5.20ms | 18s ago |
| 46 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.55ms | 18s ago |
| 47 | select * from `cache` where `key` in... | 0.54ms | 18s ago |
| 48 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.56ms | 18s ago |
| 49 | delete from `cache` where `key` in... | 0.60ms | 18s ago |
| 50 | select * from `cache` where `key` in... | 0.62ms | 18s ago |
| 51 | delete from `cache` where `key` in... | 4.96ms | 18s ago |
| 52 | select * from `cache` where `key` in... | 1.01ms | 18s ago |
| 53 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:39:57'... | 2.32ms | 18s ago |
| 54 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.79ms | 18s ago |
| 55 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.21ms | 18s ago |
| 56 | select * from `cache` where `key` in... | 0.70ms | 18s ago |
| 57 | update `cache` set `value` = 'i:2;' where `key` =... | 1.01ms | 18s ago |
| 58 | select * from `cache` where `key` =... | 3.10ms | 18s ago |
| 59 | select * from `cache` where `key` in... | 0.61ms | 18s ago |
| 60 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 2.60ms | 18s ago |
| 61 | delete from `cache` where `key` in... | 1.38ms | 18s ago |
| 62 | select * from `cache` where `key` in... | 0.87ms | 18s ago |
| 63 | delete from `cache` where `key` in... | 2.52ms | 18s ago |
| 64 | select * from `cache` where `key` in... | 1.84ms | 18s ago |
| 65 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:39:57'... | 2.34ms | 18s ago |
| 66 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.80ms | 18s ago |
| 67 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.35ms | 18s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\Assignment | retrieved | 28s ago |
| 2 | App\Models\Submission | retrieved | 28s ago |
| 3 | App\Models\Grade | retrieved | 28s ago |
| 4 | App\Models\Course | retrieved | 28s ago |
| 5 | App\Models\User | retrieved | 28s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 28s ago |
| 7 | App\Models\Course | retrieved | 28s ago |
| 8 | App\Models\User | retrieved | 28s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 28s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 28s ago |
| 11 | App\Models\User | retrieved | 28s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 28s ago |
| 13 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 28s ago |
| 14 | App\Models\User | retrieved | 28s ago |
| 15 | Laravel\Sanctum\PersonalAccessToken | retrieved | 28s ago |
| 16 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 28s ago |
| 17 | App\Models\User | retrieved | 28s ago |
| 18 | Laravel\Sanctum\PersonalAccessToken | retrieved | 28s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 39s ago |
| 2 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 39s ago |
| 3 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 39s ago |
| 4 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 39s ago |
| 5 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 39s ago |
| 6 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 39s ago |
| 7 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 39s ago |
| 8 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 39s ago |
| 9 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 39s ago |
| 10 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 39s ago |

### Page: User
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/users?page=1 | 200 | 118ms | 4s ago |
| 2 | GET | /api/v1/notifications/unread-count | 200 | 133ms | 4s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 132ms | 4s ago |
| 4 | GET | /api/v1/me | 200 | 122ms | 4s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 0.96ms | 17s ago |
| 2 | select * from `users` where `users`.`deleted_at` is null order by `name` asc limit 15... | 1.34ms | 17s ago |
| 3 | select count(*) as aggregate from `users` where `users`.`deleted_at` is null | 1.33ms | 17s ago |
| 4 | update `cache` set `value` = 'i:4;' where `key` =... | 1.04ms | 17s ago |
| 5 | select * from `cache` where `key` =... | 1.31ms | 17s ago |
| 6 | select * from `cache` where `key` in... | 1.04ms | 17s ago |
| 7 | select * from `cache` where `key` in... | 1.08ms | 17s ago |
| 8 | select * from `cache` where `key` in... | 1.34ms | 17s ago |
| 9 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:41:00'... | 2.90ms | 17s ago |
| 10 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.89ms | 17s ago |
| 11 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.14ms | 17s ago |
| 12 | select * from `cache` where `key` in... | 0.58ms | 17s ago |
| 13 | select count(*) as aggregate from `notifications` where... | 0.74ms | 17s ago |
| 14 | update `cache` set `value` = 'i:3;' where `key` =... | 0.58ms | 17s ago |
| 15 | select * from `cache` where `key` =... | 8.01ms | 17s ago |
| 16 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.37ms | 17s ago |
| 17 | select * from `cache` where `key` in... | 0.83ms | 17s ago |
| 18 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.83ms | 17s ago |
| 19 | delete from `cache` where `key` in... | 0.72ms | 17s ago |
| 20 | select * from `cache` where `key` in... | 0.67ms | 17s ago |
| 21 | select * from `cache` where `key` in... | 0.80ms | 18s ago |
| 22 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:40:59'... | 10.40ms | 18s ago |
| 23 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 2.01ms | 18s ago |
| 24 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 3.51ms | 18s ago |
| 25 | select * from `cache` where `key` in... | 0.62ms | 17s ago |
| 26 | select count(*) as aggregate from `notifications` where... | 0.87ms | 17s ago |
| 27 | update `cache` set `value` = 'i:2;' where `key` =... | 0.71ms | 17s ago |
| 28 | select * from `cache` where `key` =... | 5.10ms | 17s ago |
| 29 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.73ms | 17s ago |
| 30 | select * from `cache` where `key` in... | 0.78ms | 17s ago |
| 31 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.52ms | 17s ago |
| 32 | delete from `cache` where `key` in... | 2.32ms | 17s ago |
| 33 | select * from `cache` where `key` in... | 0.61ms | 18s ago |
| 34 | delete from `cache` where `key` in... | 5.86ms | 18s ago |
| 35 | select * from `cache` where `key` in... | 0.85ms | 18s ago |
| 36 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:40:59'... | 0.58ms | 18s ago |
| 37 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.46ms | 18s ago |
| 38 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.04ms | 18s ago |
| 39 | select * from `cache` where `key` in... | 0.67ms | 17s ago |
| 40 | update `cache` set `value` = 'i:1;' where `key` =... | 1.23ms | 17s ago |
| 41 | select * from `cache` where `key` =... | 0.43ms | 17s ago |
| 42 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.95ms | 17s ago |
| 43 | select * from `cache` where `key` in... | 0.78ms | 17s ago |
| 44 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.27ms | 17s ago |
| 45 | delete from `cache` where `key` in... | 1.62ms | 17s ago |
| 46 | select * from `cache` where `key` in... | 0.82ms | 18s ago |
| 47 | select * from `cache` where `key` in... | 1.59ms | 18s ago |
| 48 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:40:59'... | 0.94ms | 18s ago |
| 49 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.19ms | 18s ago |
| 50 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.18ms | 18s ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 26s ago |
| 2 | App\Models\User | retrieved | 26s ago |
| 3 | Laravel\Sanctum\PersonalAccessToken | retrieved | 26s ago |
| 4 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 27s ago |
| 5 | App\Models\User | retrieved | 27s ago |
| 6 | Laravel\Sanctum\PersonalAccessToken | retrieved | 27s ago |
| 7 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 27s ago |
| 8 | App\Models\User | retrieved | 27s ago |
| 9 | Laravel\Sanctum\PersonalAccessToken | retrieved | 27s ago |
| 10 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 27s ago |
| 11 | App\Models\User | retrieved | 27s ago |
| 12 | Laravel\Sanctum\PersonalAccessToken | retrieved | 27s ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 34s ago |
| 2 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 34s ago |
| 3 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 34s ago |
| 4 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 34s ago |
| 5 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 34s ago |
| 6 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 35s ago |
| 7 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 34s ago |
| 8 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 35s ago |


### Page: Notification
#### Request
| No | Verb | Path | Status | Duration | Happened |
|----|------|------|--------|----------|----------|
| 1 | GET | /api/v1/notifications?page=1 | 200 | 72ms | 4s ago |
| 2 | GET | /api/v1/me | 200 | 136ms | 4s ago |
| 3 | GET | /api/v1/notifications?per_page=5 | 200 | 134ms | 4s ago |
| 4 | GET | /api/v1/notifications/unread-count | 200 | 128ms | 4s ago |

#### Queries
| No | Query | Duration | Happened |
|----|--------|----------|----------|
| 1 | select * from `cache` where `key` in... | 1.18ms | 1:06m ago |
| 2 | select count(*) as aggregate from `notifications` where... | 1.39ms | 1:06m ago |
| 3 | update `cache` set `value` = 'i:4;' where `key` =... | 1.24ms | 1:06m ago |
| 4 | select * from `cache` where `key` =... | 1.26ms | 1:06m ago |
| 5 | select * from `cache` where `key` in... | 1.06ms | 1:06m ago |
| 6 | select * from `cache` where `key` in... | 1.20ms | 1:06m ago |
| 7 | select * from `cache` where `key` in... | 0.63ms | 1:06m ago |
| 8 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.78ms | 1:06m ago |
| 9 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 1.75ms | 1:06m ago |
| 10 | select * from `cache` where `key` in... | 0.77ms | 1:06m ago |
| 11 | update `cache` set `value` = 'i:3;' where `key` =... | 0.60ms | 1:06m ago |
| 12 | select * from `cache` where `key` =... | 10.89ms | 1:06m ago |
| 13 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 2.27ms | 1:06m ago |
| 14 | select * from `cache` where `key` in... | 0.94ms | 1:06m ago |
| 15 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.34ms | 1:06m ago |
| 16 | delete from `cache` where `key` in... | 1.27ms | 1:06m ago |
| 17 | select * from `cache` where `key` in... | 0.52ms | 1:06m ago |
| 18 | select * from `cache` where `key` in... | 2.57ms | 1:06m ago |
| 19 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:43:56'... | 4.60ms | 1:06m ago |
| 20 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 4.00ms | 1:06m ago |
| 21 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 0.79ms | 1:06m ago |
| 22 | select * from `cache` where `key` in... | 0.75ms | 1:06m ago |
| 23 | select count(*) as aggregate from `notifications` where... | 0.73ms | 1:06m ago |
| 24 | update `cache` set `value` = 'i:2;' where `key` =... | 9.69ms | 1:06m ago |
| 25 | select * from `cache` where `key` =... | 2.25ms | 1:06m ago |
| 26 | select * from `cache` where `key` in... | 0.61ms | 1:06m ago |
| 27 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.50ms | 1:06m ago |
| 28 | delete from `cache` where `key` in... | 0.53ms | 1:06m ago |
| 29 | select * from `cache` where `key` in... | 1.11ms | 1:06m ago |
| 30 | select * from `cache` where `key` in... | 1.75ms | 1:06m ago |
| 31 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:43:56'... | 12.02ms | 1:06m ago |
| 32 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 1.67ms | 1:06m ago |
| 33 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 0.80ms | 1:06m ago |
| 34 | select * from `cache` where `key` in... | 1.72ms | 1:06m ago |
| 35 | select count(*) as aggregate from `notifications` where... | 1.83ms | 1:06m ago |
| 36 | update `cache` set `value` = 'i:1;' where `key` =... | 0.52ms | 1:06m ago |
| 37 | select * from `cache` where `key` =... | 0.65ms | 1:06m ago |
| 38 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 1.09ms | 1:06m ago |
| 39 | select * from `cache` where `key` in... | 0.60ms | 1:06m ago |
| 40 | insert ignore into `cache` (`key`, `value`, `expiration`) values... | 0.44ms | 1:06m ago |
| 41 | delete from `cache` where `key` in... | 1.40ms | 1:06m ago |
| 42 | select * from `cache` where `key` in... | 2.70ms | 1:06m ago |
| 43 | delete from `cache` where `key` in... | 2.03ms | 1:06m ago |
| 44 | select * from `cache` where `key` in... | 0.63ms | 1:06m ago |
| 45 | update `personal_access_tokens` set `last_used_at` = '2026-10-31 11:43:56'... | 6.21ms | 1:06m ago |
| 46 | select * from `users` where `users`.`id` = 1 and `users`.`deleted_at` is null limit 1 | 0.67ms | 1:06m ago |
| 47 | select * from `personal_access_tokens` where `personal_access_tokens`.`id` = '13' limit 1 | 0.81ms | 1:06m ago |

#### Model
| No | Model | Action | Happened |
|----|--------|--------|----------|
| 1 | App\Models\User | retrieved | 1:17m ago |
| 2 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:17m ago |
| 3 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 1:17m ago |
| 4 | App\Models\User | retrieved | 1:17m ago |
| 5 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:17m ago |
| 6 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 1:17m ago |
| 7 | App\Models\User | retrieved | 1:17m ago |
| 8 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:17m ago |
| 9 | Laravel\Sanctum\PersonalAccessToken:13 | updated | 1:17m ago |
| 10 | App\Models\User | retrieved | 1:17m ago |
| 11 | Laravel\Sanctum\PersonalAccessToken | retrieved | 1:17m ago |

#### Cache
| No | Key | Action | Happened |
|----|-----|--------|----------|
| 1 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 1:33m ago |
| 2 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 1:33m ago |
| 3 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 1:33m ago |
| 4 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 1:33m ago |
| 5 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 1:33m ago |
| 6 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 1:33m ago |
| 7 | 356a192b7913b04c54574d18c28d46e6395428ab | hit | 1:33m ago |
| 8 | 356a192b7913b04c54574d18c28d46e6395428ab | missed | 1:33m ago |