<script setup>
import { RouterLink } from 'vue-router';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    keepContentWhileLoading: { type: Boolean, default: false },
    error: { type: String, default: '' },
    pagination: { type: Object, default: null },
    paginationGroups: { type: Array, default: () => [] },
});

defineEmits(['page', 'page-group']);
</script>

<template>
    <section>
        <header class="page-heading">
            <div><span class="eyebrow">KampusLMS</span><h1>{{ title }}</h1><p v-if="subtitle" class="muted">{{ subtitle }}</p></div>
            <div class="actions"><slot name="actions" /></div>
        </header>
        <p v-if="error" class="error" role="alert">{{ error }}</p>
        <p v-if="loading && !keepContentWhileLoading" class="panel" role="status">Memuat data…</p>
        <slot v-if="!loading || keepContentWhileLoading" />
        <nav v-if="pagination" class="pagination" aria-label="Pagination">
            <button class="button button-quiet" :disabled="loading || pagination.current_page <= 1" @click="$emit('page', pagination.current_page - 1)">Sebelumnya</button>
            <span aria-current="page">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }} · {{ pagination.total }} data</span>
            <button class="button button-quiet" :disabled="loading || pagination.current_page >= pagination.last_page" @click="$emit('page', pagination.current_page + 1)">Selanjutnya</button>
        </nav>
        <template v-for="group in paginationGroups" :key="group.key">
            <nav class="pagination" :aria-label="`Pagination ${group.label}`">
                <button class="button button-quiet" :disabled="group.loading || group.pagination.current_page <= 1" @click="$emit('page-group', group.pagination.current_page - 1, group.key)">Sebelumnya</button>
                <span aria-current="page">{{ group.label }} · Halaman {{ group.pagination.current_page }} dari {{ group.pagination.last_page }} · {{ group.pagination.total }} data</span>
                <button class="button button-quiet" :disabled="group.loading || group.pagination.current_page >= group.pagination.last_page" @click="$emit('page-group', group.pagination.current_page + 1, group.key)">Selanjutnya</button>
            </nav>
        </template>
    </section>
</template>

<style scoped>
.page-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin:0 0 20px; }
.eyebrow { color:#7163bb; font-size:.78rem; font-weight:800; text-transform:uppercase; }
h1 { margin:6px 0; }
.muted { color:#777; }
.panel { padding:22px; margin-bottom:16px; border:1px solid #e7e4ee; border-radius:14px; background:white; }
.error { padding:12px 14px; border-radius:9px; background:#fff0f0; color:#ae3549; }
.actions { display:flex; flex-wrap:wrap; gap:10px; }
.pagination { display:flex; align-items:center; justify-content:center; gap:14px; margin:18px 0; }
@media (max-width:680px) { .page-heading { align-items:flex-start; flex-direction:column; } .pagination { flex-wrap:wrap; } }
</style>
