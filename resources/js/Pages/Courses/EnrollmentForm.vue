<script setup>
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('enrollment-form');
</script>

<template>
    <PageFrame title="Daftarkan mahasiswa" :subtitle="page.data.value?.name" :loading="page.loading.value" :error="page.error.value">
        <section v-if="!page.candidates.value.length && !page.loading.value" class="panel">Semua mahasiswa sudah terdaftar di kelas ini.</section>
        <ResourceForm v-else :fields="page.fields.value" :model="page.form" :busy="page.loading.value" :cancel-to="`/courses/${page.data.value?.id}/enrollments`" submit-label="Daftarkan mahasiswa" @submit="page.submit" />
    </PageFrame>
</template>

<style scoped>.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }</style>
