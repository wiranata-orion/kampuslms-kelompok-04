<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('assignment-submissions');
</script>

<template>
    <PageFrame title="Pengumpulan mahasiswa" :subtitle="page.data.value?.title" :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <section class="panel">
            <div v-if="page.rows.value.length" class="table-wrap"><table><thead><tr><th>Mahasiswa</th><th>Waktu kirim</th><th>Status</th><th>Nilai</th><th>Aksi</th></tr></thead><tbody>
                <tr v-for="submission in page.rows.value" :key="submission.id"><td>{{ submission.student?.name ?? 'Mahasiswa' }}<span class="meta">{{ submission.student?.nim_nip ?? submission.student?.email }}</span></td><td>{{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString('id-ID') : '—' }}</td><td>{{ submission.is_late ? 'Terlambat' : 'Tepat waktu' }}</td><td>{{ submission.grade?.score ?? 'Belum dinilai' }}<span v-if="submission.grade"> / {{ page.data.value?.max_score }}</span></td><td><RouterLink :to="`/submissions/${submission.id}`">Periksa</RouterLink></td></tr>
            </tbody></table></div>
            <div v-else class="empty"><h2>Belum ada pengumpulan</h2><p>Pengumpulan mahasiswa akan muncul di halaman ini.</p></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.table-wrap { overflow-x:auto; } table { width:100%; border-collapse:collapse; } td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }
.meta { display:block; color:#777; font-size:.85rem; } .empty { padding:32px; text-align:center; }
</style>
