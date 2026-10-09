<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('my-submissions');
</script>

<template>
    <PageFrame title="Pengumpulan saya" subtitle="Lihat status dan nilai tugas yang sudah dikumpulkan." :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <section class="panel">
            <div v-if="page.rows.value.length" class="table-wrap"><table><thead><tr><th>Tugas</th><th>Mata kuliah</th><th>Dikumpulkan</th><th>Status</th><th>Nilai</th></tr></thead><tbody>
                <tr v-for="item in page.rows.value" :key="item.id"><td><RouterLink :to="`/submissions/${item.id}`">{{ item.assignment?.title ?? `Pengumpulan ${item.id}` }}</RouterLink></td><td>{{ item.assignment?.course?.name }}</td><td>{{ item.submitted_at ? new Date(item.submitted_at).toLocaleString('id-ID') : '—' }}</td><td>{{ item.is_late ? 'Terlambat' : 'Terkumpul' }}</td><td>{{ item.grade?.score ?? 'Belum dinilai' }}</td></tr>
            </tbody></table></div>
            <div v-else class="empty"><h2>Belum ada pengumpulan</h2><p>Pengumpulan tugasmu akan tampil di sini.</p></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; } td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }.empty { padding:32px; text-align:center; }
</style>
