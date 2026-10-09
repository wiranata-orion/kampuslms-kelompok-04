<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('dashboard');
</script>

<template>
    <PageFrame title="Dashboard" :loading="page.loading.value" :error="page.error.value">
        <template v-if="page.data.value">
            <p>Halo, {{ page.user.value?.name }}. Ini ringkasan aktivitasmu.</p>
            <section class="stat-grid">
                <article v-for="(value, key) in page.data.value.stats" :key="key" class="panel">
                    <span>{{ key.replaceAll('_', ' ') }}</span><strong>{{ value }}</strong>
                </article>
            </section>
            <section class="panel">
                <span class="eyebrow">Mulai dari sini</span>
                <h2>Temukan langkah berikutnya</h2>
                <p>Lihat kelas, materi, dan tugas yang menunggumu di KampusLMS.</p>
                <div class="actions">
                    <RouterLink v-if="page.user.value?.role !== 'admin'" class="button" to="/my/courses">Buka kelas saya</RouterLink>
                    <RouterLink class="button" to="/courses">Jelajahi mata kuliah</RouterLink>
                    <RouterLink v-if="page.user.value?.role === 'admin'" class="button button-quiet" to="/users">Kelola pengguna</RouterLink>
                    <RouterLink v-if="page.user.value?.role === 'mahasiswa'" class="button button-quiet" to="/my/submissions">Pengumpulan saya</RouterLink>
                </div>
            </section>
        </template>
    </PageFrame>
</template>

<style scoped>
.stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; }
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.panel strong { display:block; margin-top:10px; font-size:2rem; }
.eyebrow { color:#7163bb; font-size:.78rem; font-weight:800; text-transform:uppercase; }
.actions { display:flex; flex-wrap:wrap; gap:10px; }
</style>
