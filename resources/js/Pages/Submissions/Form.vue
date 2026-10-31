<script setup>
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('submission-form');
</script>

<template>
    <PageFrame title="Perbarui pengumpulan" :subtitle="page.data.value?.assignment?.title" :loading="page.loading.value" :error="page.error.value">
        <div v-if="page.data.value?.is_graded || page.data.value?.grade" class="graded-block">
            <p class="blocked-msg">🔒 <strong>Pengumpulan telah dinilai oleh dosen.</strong> Anda tidak dapat mengubah atau mengunggah ulang tugas yang sudah dinilai.</p>
            <RouterLink class="button" :to="`/submissions/${page.data.value?.id}`">Kembali ke Detail Pengumpulan</RouterLink>
        </div>
        <template v-else>
            <p v-if="page.data.value" class="current-file">File saat ini: <strong>{{ page.data.value.original_name }}</strong> · dikumpulkan {{ page.data.value.submitted_at ? new Date(page.data.value.submitted_at).toLocaleString('id-ID') : '—' }}</p>
            <ResourceForm :fields="page.fields.value" :model="page.form" :field-errors="page.fieldErrors" :busy="page.loading.value" :cancel-to="`/submissions/${page.data.value?.id}`" submit-label="Simpan pengumpulan" @submit="page.submit" @file-change="page.setFile" />
            <p class="hint">Biarkan kosong untuk tetap menggunakan file saat ini. Maksimum 5 MB.</p>
        </template>
    </PageFrame>
</template>

<style scoped>
.current-file,.hint { color:#666; }.hint { margin-top:-8px; }
.graded-block { padding: 22px; background: #fff; border: 1px solid #e7e4ee; border-radius: 14px; display: flex; flex-direction: column; gap: 16px; align-items: flex-start; }
.blocked-msg { margin: 0; padding: 12px 16px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 0.95rem; width: 100%; box-sizing: border-box; }
</style>
