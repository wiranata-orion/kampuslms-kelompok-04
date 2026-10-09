<script setup>
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('grade');
</script>

<template>
    <PageFrame :title="page.user.value?.role === 'mahasiswa' ? 'Nilai saya' : (page.data.value?.grade ? 'Perbarui nilai' : 'Beri nilai')" :subtitle="page.data.value?.assignment?.title" :loading="page.loading.value" :error="page.error.value">
        <section v-if="page.data.value && page.user.value?.role === 'mahasiswa'" class="panel">
            <template v-if="page.data.value.grade"><span class="eyebrow">Nilai akhir</span><strong class="score">{{ page.data.value.grade.score }} <small>/ {{ page.data.value.assignment?.max_score }}</small></strong><p>Dinilai {{ page.data.value.grade.graded_at ? new Date(page.data.value.grade.graded_at).toLocaleString('id-ID') : '' }} oleh {{ page.data.value.grade.grader?.name ?? 'Dosen' }}.</p><hr><h2>Umpan balik</h2><p class="feedback">{{ page.data.value.grade.feedback || 'Belum ada umpan balik.' }}</p></template>
            <div v-else class="empty"><h2>Nilai belum tersedia</h2><p>Dosen belum memberikan penilaian untuk tugas ini.</p></div>
            <RouterLink class="button button-quiet" :to="`/submissions/${page.data.value.id}`">Kembali ke pengumpulan</RouterLink>
        </section>
        <template v-else-if="page.data.value && page.user.value?.role === 'dosen'">
            <section class="panel"><p>{{ page.data.value.student?.name ?? 'Mahasiswa' }} · {{ page.data.value.assignment?.title }}</p><button class="button button-quiet" @click="page.download(`/submissions/${page.data.value.id}/download`, page.data.value.original_name)">Unduh {{ page.data.value.original_name }}</button></section>
            <ResourceForm :fields="page.fields.value" :model="page.form" :busy="page.loading.value" :cancel-to="`/submissions/${page.data.value.id}`" submit-label="Simpan nilai" @submit="page.submit" />
        </template>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; margin-bottom:16px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }.eyebrow { color:#7163bb; font-weight:800; text-transform:uppercase; }.score { display:block; margin:14px 0; font-size:3rem; }.score small { color:#777; font-size:1rem; font-weight:400; }.feedback { white-space:pre-line; }.empty { padding:24px; text-align:center; }
</style>
