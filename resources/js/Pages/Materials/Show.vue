<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('material');
</script>

<template>
    <PageFrame :title="page.data.value?.title ?? 'Materi'" :subtitle="`${page.data.value?.course?.code ?? ''} · ${page.data.value?.uploader?.name ?? 'Dosen'}`" :loading="page.loading.value" :error="page.error.value">
        <section v-if="page.data.value" class="panel">
            <span class="pill">{{ page.data.value.type === 'file' ? 'File pembelajaran' : 'Tautan eksternal' }}</span>
            <p class="description">{{ page.data.value.description || 'Tidak ada deskripsi tambahan.' }}</p>
            <dl v-if="page.data.value.type === 'file'" class="file-metadata"><div><dt>Nama berkas</dt><dd>{{ page.data.value.original_name || 'Belum diisi' }}</dd></div><div><dt>Ukuran</dt><dd>{{ page.data.value.file_size ? `${Number(page.data.value.file_size).toLocaleString('id-ID')} byte` : 'Belum diisi' }}</dd></div><div><dt>Tipe konten</dt><dd>{{ page.data.value.mime_type || 'Belum diisi' }}</dd></div></dl>
            <div class="actions">
                <button v-if="page.data.value.download_available" class="button" @click="page.download(`/materials/${page.data.value.id}/download`, page.data.value.original_name || 'materi')">Unduh {{ page.data.value.original_name || 'file materi' }}</button>
                <a v-else-if="page.data.value.external_url" class="button" :href="page.data.value.external_url" target="_blank" rel="noopener noreferrer">Buka tautan materi</a>
            </div>
            <div v-if="page.data.value.permissions?.can_update || page.data.value.permissions?.can_delete" class="actions"><RouterLink v-if="page.data.value.permissions?.can_update" class="button button-quiet" :to="`/materials/${page.data.value.id}/edit`">Edit materi</RouterLink><button v-if="page.data.value.permissions?.can_delete" class="button button-danger" @click="page.remove('materials', page.data.value)">Hapus</button></div>
            <RouterLink class="button button-quiet" :to="`/courses/${page.data.value.course_id}/materials`">Kembali ke materi</RouterLink>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.description { white-space:pre-line; }.pill { padding:5px 10px; border-radius:99px; background:#f0edff; color:#5744ad; }
.actions { display:flex; flex-wrap:wrap; gap:10px; margin:16px 0; }
</style>
