## 5. Kontrak API
Prefix /api/v1. Autentikasi: Laravel Sanctum (Bearer token). Semua response dibungkus API Resource.

| Method | Endpoint | Akses | Keterangan |
|--------|----------|-------|------------|
| POST | `/auth/login` | publik | mengembalikan token |
| POST | `/auth/logout` | auth | |
| GET | `/me` | auth | profil + role |
| GET | `/courses` | auth | dosen: MK yang diajar; mahasiswa: MK yang diikuti |
| GET | `/courses/{id}` | auth+scope | detail + jumlah materi/tugas |
| GET | `/courses/{id}/materials` | auth+scope | |
| GET | `/courses/{id}/assignments` | auth+scope | mendukung `?status=`, `?page=` |
| POST | `/assignments` | dosen | |
| PUT/PATCH | `/assignments/{id}` | dosen (pemilik) | |
| DELETE | `/assignments/{id}` | dosen (pemilik) | |
| GET | `/assignments/{id}/submissions` | dosen (pemilik) | |
| POST | `/assignments/{id}/submissions` | mahasiswa (terdaftar) | multipart, `file` |
| PUT | `/submissions/{id}/grade` | dosen (pemilik) | `score`, `feedback` — *upsert*, aman dipanggil berulang |
| GET | `/notifications` | auth | |
| POST | `/notifications/{id}/read` | auth | |

## 6. Jalur frontend
### Inertia 2 + Vue/React/Svelte
- Inertia bukan framework frontend, tapi “bridge” antara Laravel (backend) dan framework JS (Vue/React/Svelte).
- Routing tetap di Laravel, tapi setiap route me-render komponen frontend.
- Tidak perlu bikin API layer terpisah (beda dengan SPA murni).
- Jadi kamu bisa menulis frontend modern tanpa meninggalkan ekosistem Laravel.

| framework | Anu | Plus | Minus | Suitable for |
|-----------|-----|------|-------|--------------|
| Vue 3 | Declarative, reactive, berbasis template | Mudah dipelajari, Dokumentasi jelas, Komunitas besar, Cocok untuk tim PHP yang baru masuk JS | Ekosistem besar kadang membingungkan, Performa bagus tapi bukan paling ringan. | Tim laravel yang ingin transisi smooth ke frontend modern |
| React 18 | Component-based, declarative, pakai JSX | Ekosistem raksasa, Banyak library siap pakai, Didukung industri besar (Meta, dll), Cocok untuk aplikasi kompleks | JSX bisa terasa asing, Boilerplate lebih banyak, Learning curve lebih tinggi | belajar how to big application works (Saas, marketplace) |
| Svelte 4/5 | Compiler-based, minimal runtime | Sangat ringan & cepat, Syntax sederhana (mirip HTML/JS), Bundle kecil, UX halus | Komunitas lebih kecil, Ekosistem belum sebesar Vue/React | Proyek yang butuh performa tinggi, tim kecil yang suka simplicity |

### Cara installasi and integrasi
- **Vue**: `npm install @inertiajs/vue3` → buat komponen `.vue` di `resources/js/Pages`.
- **React**: `npm install @inertiajs/react` → buat komponen `.jsx` atau `.tsx`.
    1. `composer require inertiajs/inertia-laravel`
    2. `npm install react react-dom @inertiajs/react`
    3. Buat resources/js/app.jsx
    4. Gunakan inertia untuk panggil komponen React: `Route::get('/dashboard', function () { return Inertia::render('Dashboard'); });`
- **Svelte**: `npm install @inertiajs/svelte` → buat komponen `.svelte`.
- Di laravel route:
```php
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});
```

### Contoh kode
#### Vue 3
```vue
<script setup>
import Layout from './Layout'
import { Head } from '@inertiajs/vue3'
defineProps({ user: Object })
</script>

<template>
  <Layout>
    <Head title="Welcome" />
    <h1>Welcome</h1>
    <p>Hello {{ user.name }}, welcome to your first Inertia app!</p>
  </Layout>
</template>
```

#### React 18
```Jsx
import Layout from './Layout'
import { Head } from '@inertiajs/react'

export default function Welcome({ user }) {
  return (
    <Layout>
      <Head title="Welcome" />
      <h1>Welcome</h1>
      <p>Hello {user.name}, welcome to your first Inertia app!</p>
    </Layout>
  )
}
```

#### Svelte 4/5
```Svelte
<script>
  import Layout from './Layout.svelte'
  export let user
</script>

<svelte:head>
  <title>Welcome</title>
</svelte:head>

<Layout>    
  <h1>Welcome</h1>
  <p>Hello {user.name}, welcome to your first Inertia app!</p>
</Layout>
```

## Dan kita akan memilih React 18
karena dipakai oleh Meta, Netflix, Airbnb, Uber, Amazon, PayPal, Tesla, Spotify, Disney+. React adalah default industri karena ekosistem luas dan mudah cari developer. Jadi pas banget kalau mau belajar soal frontend yang dipakai di industri.