<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('submission');
</script>

<template>
    <PageFrame :title="page.data.value?.assignment?.title ?? 'Detail pengumpulan'" :subtitle="page.data.value?.assignment?.course?.name" :loading="page.loading.value" :error="page.error.value">
        <section v-if="page.data.value" class="panel">
            <span class="pill" :class="{ late: page.data.value.is_late }">{{ page.data.value.is_late ? 'Terlambat' : 'Terkumpul' }}</span>
            <p>Dikumpulkan {{ page.data.value.submitted_at ? new Date(page.data.value.submitted_at).toLocaleString('id-ID') : '—' }}</p>
            <dl><dt>Mahasiswa</dt><dd>{{ page.data.value.student?.name ?? page.user.value?.name }}</dd><dt>File</dt><dd>{{ page.data.value.original_name }} · {{ Math.ceil((page.data.value.file_size ?? 0) / 1024) }} KB</dd><dt>Catatan</dt><dd>{{ page.data.value.note || 'Tidak ada catatan.' }}</dd><dt>Batas pengumpulan</dt><dd>{{ page.data.value.assignment?.due_at ? new Date(page.data.value.assignment.due_at).toLocaleString('id-ID') : '—' }}</dd></dl>
            <div class="actions">
                <button class="button button-quiet" @click="page.download(`/submissions/${page.data.value.id}/download`, page.data.value.original_name || 'pengumpulan')">Unduh pengumpulan</button>
                <RouterLink v-if="page.user.value?.role === 'mahasiswa'" class="button button-quiet" :to="`/submissions/${page.data.value.id}/edit`">Edit pengumpulan</RouterLink>
                <RouterLink v-if="page.user.value?.role === 'mahasiswa'" class="button" :to="`/submissions/${page.data.value.id}/grade`">Lihat nilai</RouterLink>
                <RouterLink v-if="page.user.value?.role === 'dosen'" class="button" :to="`/submissions/${page.data.value.id}/grade`">{{ page.data.value.grade ? 'Edit nilai' : 'Beri nilai' }}</RouterLink>
            </div>
            <section v-if="page.user.value?.role === 'dosen' && page.data.value.grade" class="grade-summary"><h2>{{ page.data.value.grade.score }} / {{ page.data.value.assignment?.max_score }} poin</h2><p>{{ page.data.value.grade.feedback || 'Belum ada umpan balik.' }}</p></section>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }.pill { padding:5px 10px; border-radius:99px; background:#e7f6eb; color:#287c43; }.pill.late { background:#fff0f0; color:#ae3549; }
dl { display:grid; grid-template-columns:180px 1fr; gap:12px; } dd { margin:0; }.actions { display:flex; flex-wrap:wrap; gap:10px; margin:18px 0; }.grade-summary { padding:18px; background:#f6f4fb; border-radius:12px; }
</style>
