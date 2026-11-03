<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('course-assignments');
</script>

<template>
    <PageFrame title="Tugas mata kuliah" :subtitle="page.data.value?.name" :loading="page.loading.value" keep-content-while-loading :error="page.error.value">
        <template #actions>
            <RouterLink v-if="page.data.value" class="button button-quiet" :to="`/courses/${page.data.value.id}`">Kembali ke detail</RouterLink>
            <RouterLink v-if="page.data.value && ['admin', 'dosen'].includes(page.user.value?.role)" class="button" :to="`/courses/${page.data.value.id}/assignments/create`">Buat tugas</RouterLink>
        </template>
        <section class="panel">
            <p v-if="page.loading.value" role="status">Memuat tugas…</p>
            <div v-else-if="page.rows.value.length" class="table-wrap"><table><thead><tr><th>Tugas</th><th>Tenggat</th><th>Nilai maks.</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                <tr v-for="assignment in page.rows.value" :key="assignment.id"><td><RouterLink :to="`/assignments/${assignment.id}`">{{ assignment.title }}</RouterLink><span class="meta">{{ assignment.instructions }}</span></td><td>{{ assignment.due_at ? new Date(assignment.due_at).toLocaleString('id-ID') : '—' }}</td><td>{{ assignment.max_score }}</td><td>{{ assignment.status === 'published' ? 'Terbit' : 'Draft' }}</td><td class="row-actions"><RouterLink :to="`/assignments/${assignment.id}`">Detail</RouterLink><RouterLink v-if="assignment.permissions?.can_update" :to="`/assignments/${assignment.id}/edit`">Edit</RouterLink><button v-if="assignment.permissions?.can_delete" class="text-button" @click="page.remove('assignments', assignment)">Hapus</button></td></tr>
            </tbody></table></div>
            <div v-else class="empty"><h2>Belum ada tugas</h2><p>Tugas yang dibuat untuk kelas ini akan muncul di sini.</p></div>
            <nav class="pagination" aria-label="Pagination tugas">
                <button class="button button-quiet" :disabled="page.loading.value || page.pagination.value.current_page <= 1" @click="page.goToPage(page.pagination.value.current_page - 1)">Sebelumnya</button>
                <span aria-current="page">Halaman {{ page.pagination.value.current_page }} dari {{ page.pagination.value.last_page }} · {{ page.pagination.value.total }} data</span>
                <button class="button button-quiet" :disabled="page.loading.value || page.pagination.value.current_page >= page.pagination.value.last_page" @click="page.goToPage(page.pagination.value.current_page + 1)">Selanjutnya</button>
            </nav>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.table-wrap { overflow-x:auto; } table { width:100%; border-collapse:collapse; } td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }
.meta { display:block; max-width:400px; overflow:hidden; color:#777; text-overflow:ellipsis; white-space:nowrap; }
.empty { padding:32px; text-align:center; }
.pagination { display:flex; align-items:center; justify-content:center; gap:14px; margin:20px 0 0; padding-top:18px; border-top:1px solid #eee; }
</style>
