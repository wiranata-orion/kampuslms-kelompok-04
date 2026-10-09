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
            <RouterLink v-if="page.user.value?.role === 'dosen' && page.data.value" class="button button-quiet" :to="`/assignments/${page.data.value.id}/edit`">Edit tugas</RouterLink>
            <RouterLink v-if="page.user.value?.role === 'dosen' && page.data.value" class="button" :to="`/assignments/${page.data.value.id}/submissions`">Lihat pengumpulan</RouterLink>
        </template>
        <section v-if="page.data.value" class="panel">
            <div class="pills"><span class="pill">{{ page.data.value.status === 'published' ? 'Terbit' : 'Draft' }}</span><span class="pill">Maks. {{ page.data.value.max_score }} poin</span><span class="pill">{{ page.data.value.allow_late ? 'Terlambat diizinkan' : 'Tanpa keterlambatan' }}</span></div>
            <h2>Instruksi</h2><p class="instructions">{{ page.data.value.instructions }}</p>
            <div v-if="page.user.value?.role === 'mahasiswa' && page.data.value.submission" class="notice"><h2>Tugas sudah dikumpulkan</h2><RouterLink class="button" :to="`/submissions/${page.data.value.submission.id}`">Lihat pengumpulan</RouterLink></div>
            <form v-else-if="page.user.value?.role === 'mahasiswa' && page.data.value.status === 'published'" class="submit-form" @submit.prevent="page.submitAssignment(file, note)">
                <h2>Kumpulkan tugas</h2><label for="submission-file">Berkas (maksimum 10 MB)</label><input id="submission-file" type="file" required @change="file = $event.target.files?.[0] ?? null">
                <label for="submission-note">Catatan untuk dosen</label><textarea id="submission-note" v-model="note" /><button class="button">Kirim pengumpulan</button>
            </form>
            <h2 v-if="page.user.value?.role === 'dosen'">Pengumpulan terbaru</h2><ul v-if="page.user.value?.role === 'dosen'"><li v-for="submission in page.data.value.submissions" :key="submission.id"><RouterLink :to="`/submissions/${submission.id}`">{{ submission.student?.name ?? 'Mahasiswa' }}</RouterLink> — {{ submission.grade?.score ?? 'Belum dinilai' }}</li></ul>
            <button v-if="page.user.value?.role === 'dosen'" class="button button-danger" @click="page.remove('assignments', page.data.value)">Hapus tugas</button>
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
</style>
