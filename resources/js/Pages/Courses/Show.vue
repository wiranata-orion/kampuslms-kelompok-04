<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('course');
</script>

<template>
    <PageFrame :title="page.data.value?.name ?? 'Detail mata kuliah'" :subtitle="page.data.value ? `${page.data.value.code} · ${page.data.value.sks} SKS` : ''" :loading="page.loading.value" :error="page.error.value">
        <template #actions>
            <RouterLink v-if="page.data.value && page.user.value?.role === 'admin'" class="button" :to="`/courses/${page.data.value.id}/edit`">Edit mata kuliah</RouterLink>
            <RouterLink v-if="page.data.value && page.user.value?.role === 'admin'" class="button button-quiet" :to="`/courses/${page.data.value.id}/enrollments`">Kelola peserta</RouterLink>
            <RouterLink v-if="page.data.value && page.user.value?.role === 'dosen'" class="button" :to="`/courses/${page.data.value.id}/assignments/create`">Buat tugas</RouterLink>
            <RouterLink v-if="page.data.value && page.user.value?.role === 'dosen'" class="button button-quiet" :to="`/courses/${page.data.value.id}/materials/create`">Tambah materi</RouterLink>
        </template>
        <section v-if="page.data.value" class="panel">
            <span class="pill">{{ page.data.value.status }}</span><p>{{ page.data.value.description || 'Belum ada deskripsi.' }}</p><p>Dosen pengampu: {{ page.data.value.lecturer?.name ?? 'Belum ditentukan' }}</p>
            <div class="split">
                <section><h2>Tugas</h2><ul><li v-for="assignment in page.data.value.assignments" :key="assignment.id"><RouterLink :to="`/assignments/${assignment.id}`">{{ assignment.title }}</RouterLink> — {{ assignment.status }}</li></ul><p v-if="!page.data.value.assignments?.length">Belum ada tugas.</p></section>
                <section><h2>Materi</h2><ul><li v-for="material in page.data.value.materials" :key="material.id"><RouterLink :to="`/materials/${material.id}`">{{ material.title }}</RouterLink> — {{ material.type }}</li></ul><p v-if="!page.data.value.materials?.length">Belum ada materi.</p></section>
            </div>
            <section v-if="page.user.value?.role === 'admin'" class="enrollment"><h2>Peserta mata kuliah</h2>
                <ul><li v-for="student in page.data.value.students" :key="student.id">{{ student.name }} · {{ student.nim_nip || student.email }} <button class="text-button" @click="page.removeStudent(student)">Keluarkan</button></li></ul>
                <form class="actions" @submit.prevent="page.enroll(page.form.user_id)"><select v-model="page.form.user_id" required><option value="">Pilih mahasiswa</option><option v-for="candidate in page.candidates.value" :key="candidate.id" :value="candidate.id">{{ candidate.name }}</option></select><button class="button" :disabled="!page.candidates.value.length">Daftarkan</button></form>
            </section>
            <div class="actions"><RouterLink class="button button-quiet" to="/courses">Kembali ke katalog</RouterLink><button v-if="page.user.value?.role === 'admin'" class="button button-danger" @click="page.remove('courses', page.data.value)">Hapus mata kuliah</button></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.pill { display:inline-block; padding:5px 10px; border-radius:99px; background:#f0edff; color:#5744ad; }
.split { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
.enrollment { margin-top:24px; padding:18px; border-radius:12px; background:#f8f7fc; }
.actions { display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
.text-button { border:0; background:none; color:#a33; cursor:pointer; }
@media(max-width:680px) { .split { grid-template-columns:1fr; } }
</style>
