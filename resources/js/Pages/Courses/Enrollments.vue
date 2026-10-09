<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('enrollments');
</script>

<template>
    <PageFrame title="Peserta mata kuliah" :subtitle="page.data.value?.name" :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <template #actions><RouterLink v-if="page.data.value" class="button" :to="`/courses/${page.data.value.id}/enrollments/create`">Tambah peserta</RouterLink></template>
        <section class="panel">
            <div v-if="page.data.value?.students?.length" class="table-wrap"><table><thead><tr><th>Mahasiswa</th><th>Email</th><th>NIM</th><th>Aksi</th></tr></thead><tbody>
                <tr v-for="student in page.data.value.students" :key="student.id"><td>{{ student.name }}</td><td>{{ student.email }}</td><td>{{ student.nim_nip || '—' }}</td><td><button class="text-button" @click="page.removeStudent(student)">Keluarkan</button></td></tr>
            </tbody></table></div>
            <div v-else class="empty"><h2>Belum ada peserta</h2><p>Daftarkan mahasiswa ke kelas ini untuk memulai.</p></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.table-wrap { overflow-x:auto; } table { width:100%; border-collapse:collapse; } td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }
.text-button { border:0; background:none; color:#a33; cursor:pointer; }.empty { padding:32px; text-align:center; }
</style>
