<script setup>
import { ref } from 'vue';
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('assignment');
const file = ref(null);
const note = ref('');
</script>

<template>
    <PageFrame :title="page.data.value?.title ?? 'Detail tugas'" :subtitle="page.data.value ? `${page.data.value.course?.code ?? ''} · Batas ${new Date(page.data.value.due_at).toLocaleString('id-ID')}` : ''" :loading="page.loading.value" :error="page.error.value">
        <template #actions>
            <RouterLink v-if="page.data.value?.permissions?.can_update" class="button button-quiet" :to="`/assignments/${page.data.value.id}/edit`">Edit tugas</RouterLink>
            <RouterLink v-if="page.user.value?.role === 'dosen' && page.data.value" class="button" :to="`/assignments/${page.data.value.id}/submissions`">Lihat pengumpulan</RouterLink>
        </template>
        <section v-if="page.data.value" class="panel">
            <div class="pills"><span class="pill">{{ page.data.value.status === 'published' ? 'Terbit' : 'Draft' }}</span><span class="pill">Maks. {{ page.data.value.max_score }} poin</span><span class="pill">{{ page.data.value.allow_late ? 'Terlambat diizinkan' : 'Tanpa keterlambatan' }}</span></div>
            <h2>Instruksi</h2><p class="instructions">{{ page.data.value.instructions }}</p>
            <div v-if="page.user.value?.role === 'mahasiswa' && page.data.value.submission" class="notice">
                <h2>Status Pengumpulan</h2>
                <div class="submission-summary">
                    <div class="status-pills">
                        <span class="pill" :class="{ late: page.data.value.submission.is_late }">{{ page.data.value.submission.is_late ? 'Terlambat' : 'Terkumpul Tepat Waktu' }}</span>
                        <span v-if="page.data.value.submission.is_graded || page.data.value.submission.grade" class="pill pill-graded">Sudah Dinilai</span>
                        <span v-else class="pill pill-waiting">Menunggu Penilaian</span>
                    </div>
                    <p>Berkas: <strong>{{ page.data.value.submission.original_name }}</strong> ({{ page.data.value.submission.file_size_formatted || (Math.ceil((page.data.value.submission.file_size ?? 0) / 1024) + ' KB') }})</p>
                    <p>Waktu pengumpulan: {{ page.data.value.submission.submitted_at ? new Date(page.data.value.submission.submitted_at).toLocaleString('id-ID') : '—' }}</p>
                </div>
                <div class="actions">
                    <button class="button button-quiet" @click="page.download(`/submissions/${page.data.value.submission.id}/download`, page.data.value.submission.original_name || 'jawaban')">Unduh Berkas Saya</button>
                    <RouterLink class="button" :to="`/submissions/${page.data.value.submission.id}`">Lihat Detail Pengumpulan</RouterLink>
                </div>

                <div v-if="page.data.value.submission.is_graded || page.data.value.submission.grade" class="graded-notice">
                    <p>🔒 <strong>Tugas sudah dinilai.</strong> Pengumpulan ini sudah dinilai oleh dosen sehingga tidak dapat diubah atau dikumpulkan ulang.</p>
                </div>

                <details v-else class="resubmit-box">
                    <summary class="button button-quiet">Kumpulkan Ulang / Revisi Tugas</summary>
                    <form class="submit-form" @submit.prevent="page.submitAssignment(file, note)">
                        <p v-if="page.error.value" class="error-msg" role="alert">{{ page.error.value }}</p>
                        <label for="resubmission-file">Pilih Berkas Revisi (PDF, DOC, DOCX maks. 5 MB)</label>
                        <input id="resubmission-file" type="file" accept=".pdf,.doc,.docx" required @change="file = $event.target.files?.[0] ?? null">
                        <label for="resubmission-note">Catatan Tambahan untuk Dosen</label>
                        <textarea id="resubmission-note" v-model="note" placeholder="Catatan atau keterangan revisi..." />
                        <button class="button">Kirim Revisi Tugas</button>
                    </form>
                </details>
            </div>
            <form v-else-if="page.user.value?.role === 'mahasiswa' && page.data.value.status === 'published'" class="submit-form" @submit.prevent="page.submitAssignment(file, note)">
                <h2>Kumpulkan tugas</h2>
                <p v-if="page.error.value" class="error-msg" role="alert">{{ page.error.value }}</p>
                <label for="submission-file">Berkas (PDF, DOC, DOCX maks. 5 MB)</label>
                <input id="submission-file" type="file" accept=".pdf,.doc,.docx" required @change="file = $event.target.files?.[0] ?? null">
                <label for="submission-note">Catatan untuk dosen</label>
                <textarea id="submission-note" v-model="note" placeholder="Tuliskan catatan untuk dosen (opsional)..." />
                <button class="button">Kirim pengumpulan</button>
            </form>
            <h2 v-if="page.user.value?.role === 'dosen'">Pengumpulan terbaru</h2><ul v-if="page.user.value?.role === 'dosen'"><li v-for="submission in page.data.value.submissions" :key="submission.id"><RouterLink :to="`/submissions/${submission.id}`">{{ submission.student?.name ?? 'Mahasiswa' }}</RouterLink> — {{ submission.grade?.score ?? 'Belum dinilai' }}</li></ul>
            <button v-if="page.data.value.permissions?.can_delete" class="button button-danger" @click="page.remove('assignments', page.data.value)">Hapus tugas</button>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.pills,.submit-form { display:flex; flex-wrap:wrap; gap:12px; }
.pill { padding:5px 10px; border-radius:99px; background:#f0edff; color:#5744ad; }
.instructions { white-space:pre-line; }
.submit-form { max-width:640px; margin-top:28px; }
.submit-form label { width:100%; }
.submit-form textarea { width:100%; min-height:100px; }
.notice { margin:24px 0; padding:18px; border-radius:12px; background:#f5f3fc; }
.submission-summary { margin: 12px 0; display: flex; flex-direction: column; gap: 6px; }
.submission-summary .pill.late { background:#fff0f0; color:#ae3549; }
.resubmit-box { margin-top: 16px; padding-top: 12px; border-top: 1px dashed #d8d4e2; }
.resubmit-box summary { cursor: pointer; list-style: none; display: inline-block; }
.actions { display:flex; flex-wrap:wrap; gap:10px; margin:12px 0; }
.error-msg { width:100%; padding:10px 14px; border-radius:8px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; font-size:0.9rem; font-weight:500; margin:4px 0 10px; }
.status-pills { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.pill.pill-graded { background: #e0f2fe; color: #0284c7; font-weight: 600; }
.pill.pill-waiting { background: #fef3c7; color: #b45309; }
.graded-notice { margin-top: 16px; padding: 14px 18px; border-radius: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; font-size: 0.95rem; }
.graded-notice p { margin: 0; }
</style>
