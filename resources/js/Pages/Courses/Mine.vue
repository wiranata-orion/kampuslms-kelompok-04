<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('my-courses');
</script>

<template>
    <PageFrame title="Kelas saya" subtitle="Semua mata kuliah yang terhubung dengan akunmu." :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <section v-if="page.rows.value.length" class="course-grid">
            <article v-for="course in page.rows.value" :key="course.id" class="panel">
                <span class="pill">{{ course.code }}</span><h2><RouterLink :to="`/courses/${course.id}`">{{ course.name }}</RouterLink></h2>
                <p>{{ course.lecturer?.name ?? 'Dosen belum ditentukan' }} · {{ course.sks }} SKS</p>
                <div class="actions"><RouterLink class="button button-quiet" :to="`/courses/${course.id}`">Lihat kelas</RouterLink>
                    <template v-if="page.user.value?.role === 'dosen'"><RouterLink class="button button-quiet" :to="`/courses/${course.id}/materials`">Materi</RouterLink><RouterLink class="button button-quiet" :to="`/courses/${course.id}/assignments`">Tugas</RouterLink></template>
                </div>
            </article>
        </section>
        <section v-else class="panel empty"><h2>Belum ada kelas di sini</h2><p>Kelas yang kamu ikuti atau ampu akan muncul di halaman ini.</p><RouterLink v-if="page.user.value?.role === 'mahasiswa'" class="button" to="/courses">Jelajahi katalog</RouterLink></section>
    </PageFrame>
</template>

<style scoped>
.course-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:14px; }
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.pill { display:inline-block; padding:5px 10px; border-radius:99px; background:#f0edff; color:#5744ad; }
.actions { display:flex; flex-wrap:wrap; gap:8px; }
.empty { text-align:center; }
</style>
