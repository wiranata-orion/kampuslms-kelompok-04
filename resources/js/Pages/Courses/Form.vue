<script setup>
import { computed } from 'vue';
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('course-form');
const title = computed(() => page.mode.value === 'edit' ? 'Edit mata kuliah' : 'Buat mata kuliah');
const cancelTo = computed(() => page.mode.value === 'edit' ? `/courses/${page.data.value?.id ?? ''}` : '/courses');
</script>

<template>
    <PageFrame :title="title" subtitle="Lengkapi identitas kelas dan tentukan dosen pengampunya." :loading="page.loading.value" :error="page.error.value">
        <ResourceForm :fields="page.fields.value" :model="page.form" :busy="page.loading.value" :cancel-to="cancelTo" submit-label="Simpan mata kuliah" @submit="page.submit" />
    </PageFrame>
</template>
