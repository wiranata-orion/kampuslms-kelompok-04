<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('courses');
</script>

<template>
    <PageFrame title="Jelajahi mata kuliah" subtitle="Cari kelas dan temukan ruang untuk belajar hal baru." :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <template #actions><RouterLink v-if="page.user.value?.role === 'admin'" class="button" to="/courses/create">Tambah mata kuliah</RouterLink></template>
        <section class="panel">
            <form class="toolbar" @submit.prevent="page.goToPage(1)">
                <input v-model="page.search.value" type="search" placeholder="Cari kode atau nama kelas">
                <select v-model="page.status.value" aria-label="Filter status">
                    <option value="">Semua status</option><option value="draft">Draft</option><option value="active">Aktif</option><option value="archived">Arsip</option>
                </select>
                <button class="button">Cari</button><button class="button button-quiet" type="button" @click="page.search.value = ''; page.status.value = ''; page.goToPage(1)">Atur ulang</button>
            </form>
            <div v-if="page.rows.value.length" class="table-wrap"><table><thead><tr><th>Kode</th><th>Mata kuliah</th><th>SKS</th><th>Dosen</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody><tr v-for="course in page.rows.value" :key="course.id"><td>{{ course.code }}</td><td><RouterLink :to="`/courses/${course.id}`">{{ course.name }}</RouterLink></td><td>{{ course.sks }}</td><td>{{ course.lecturer?.name ?? 'Belum ditentukan' }}</td><td>{{ course.status }}</td><td><RouterLink :to="`/courses/${course.id}`">Detail</RouterLink><template v-if="page.user.value?.role === 'admin'"> · <RouterLink :to="`/courses/${course.id}/edit`">Edit</RouterLink><button class="text-button" @click="page.remove('courses', course)">Hapus</button></template></td></tr></tbody>
            </table></div>
            <div v-else class="empty"><h2>Belum ada mata kuliah</h2><p>Coba ubah kata kunci atau filter yang dipilih.</p></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:white; }
.toolbar { display:grid; grid-template-columns:minmax(180px,1fr) auto auto auto; gap:10px; margin-bottom:16px; }
.toolbar input,.toolbar select { padding:10px; border:1px solid #d8d4e2; border-radius:8px; }
.table-wrap { overflow-x:auto; } table { width:100%; border-collapse:collapse; } td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }
.empty { padding:32px; text-align:center; color:#6b6879; }
.text-button { margin-left:8px; border:0; background:none; color:#a33; cursor:pointer; }
@media(max-width:680px) { .toolbar { grid-template-columns:1fr; } }
</style>
