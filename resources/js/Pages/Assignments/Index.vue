<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('course-assignments');
</script>

<template>
    <PageFrame title="Tugas mata kuliah" :subtitle="page.data.value?.name" :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <template #actions><RouterLink v-if="['admin', 'dosen'].includes(page.user.value?.role)" class="button" :to="`/courses/${page.data.value?.id}/assignments/create`">Buat tugas</RouterLink></template>
        <section class="panel">
            <div v-if="page.rows.value.length" class="table-wrap"><table><thead><tr><th>Tugas</th><th>Tenggat</th><th>Nilai maks.</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                <tr v-for="assignment in page.rows.value" :key="assignment.id"><td><RouterLink :to="`/assignments/${assignment.id}`">{{ assignment.title }}</RouterLink><span class="meta">{{ assignment.instructions }}</span></td><td>{{ assignment.due_at ? new Date(assignment.due_at).toLocaleString('id-ID') : '—' }}</td><td>{{ assignment.max_score }}</td><td>{{ assignment.status === 'published' ? 'Terbit' : 'Draft' }}</td><td class="row-actions"><RouterLink :to="`/assignments/${assignment.id}`">Detail</RouterLink><RouterLink v-if="assignment.permissions?.can_update" :to="`/assignments/${assignment.id}/edit`">Edit</RouterLink><button v-if="assignment.permissions?.can_delete" class="text-button" @click="page.remove('assignments', assignment)">Hapus</button></td></tr>
            </tbody></table></div>
            <div v-else class="empty"><h2>Belum ada tugas</h2><p>Tugas yang dibuat untuk kelas ini akan muncul di sini.</p></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.table-wrap { overflow-x:auto; } table { width:100%; border-collapse:collapse; } td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }
.meta { display:block; max-width:400px; overflow:hidden; color:#777; text-overflow:ellipsis; white-space:nowrap; }
.empty { padding:32px; text-align:center; }
</style>
