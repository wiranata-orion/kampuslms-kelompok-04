<script setup>
import { computed } from 'vue';
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('material-form');
const title = computed(() => page.mode.value === 'edit' ? 'Edit materi' : 'Tambah materi');
const cancelTo = computed(() => page.mode.value === 'edit' ? `/materials/${page.data.value?.id ?? ''}` : `/courses/${page.data.value?.course?.id ?? ''}/materials`);
</script>

<template>
    <PageFrame :title="title" :subtitle="page.data.value?.course?.name ?? page.data.value?.course?.code" :loading="page.loading.value" :error="page.error.value">
        <ResourceForm :fields="page.fields.value" :model="page.form" :busy="page.loading.value" :cancel-to="cancelTo" submit-label="Simpan materi" show-material-fields @submit="page.submit" />
        <p class="hint">Isi metadata berkas secara manual; berkas tidak diunggah.</p>
    </PageFrame>
</template>

<style scoped>
.hint { margin-top: -8px; }
</style>
