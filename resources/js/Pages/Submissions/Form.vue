<script setup>
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('submission-form');
</script>

<template>
    <PageFrame title="Perbarui pengumpulan" :subtitle="page.data.value?.assignment?.title" :loading="page.loading.value" :error="page.error.value">
        <p v-if="page.data.value" class="current-file">File saat ini: <strong>{{ page.data.value.original_name }}</strong> · dikumpulkan {{ page.data.value.submitted_at ? new Date(page.data.value.submitted_at).toLocaleString('id-ID') : '—' }}</p>
        <ResourceForm :fields="page.fields.value" :model="page.form" :busy="page.loading.value" :cancel-to="`/submissions/${page.data.value?.id}`" submit-label="Simpan pengumpulan" @submit="page.submit" @file-change="page.setFile" />
        <p class="hint">Biarkan kosong untuk tetap menggunakan file saat ini. Maksimum 10 MB.</p>
    </PageFrame>
</template>

<style scoped>.current-file,.hint { color:#666; }.hint { margin-top:-8px; }</style>
