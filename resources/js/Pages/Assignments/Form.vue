<script setup>
import { computed } from 'vue';
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('assignment-form');
const title = computed(() => page.mode.value === 'edit' ? 'Edit tugas' : 'Buat tugas');
const cancelTo = computed(() => page.mode.value === 'edit' ? `/assignments/${page.data.value?.id ?? ''}` : `/courses/${page.data.value?.course?.id ?? ''}`);
</script>

<template>
    <PageFrame :title="title" :subtitle="page.data.value?.course?.name" :loading="page.loading.value" :error="page.error.value">
        <ResourceForm :fields="page.fields.value" :model="page.form" :busy="page.loading.value" :cancel-to="cancelTo" submit-label="Simpan tugas" @submit="page.submit" />
    </PageFrame>
</template>
