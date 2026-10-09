<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('notifications');
</script>

<template>
    <PageFrame title="Notifikasi" subtitle="Kabar terbaru tentang kelas dan aktivitas belajarmu." :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <template #actions><button v-if="page.rows.value.some((item) => !item.read_at)" class="button button-quiet" @click="page.readAllNotifications">Tandai semua dibaca</button></template>
        <section class="panel">
            <article v-for="item in page.rows.value" :key="item.id" class="notice">
                <div><span class="meta">{{ item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '' }}</span><h2>{{ item.data?.title ?? 'Pembaruan kampus' }}</h2><p>{{ item.data?.message ?? item.data?.body ?? 'Ada pembaruan baru untuk akunmu.' }}</p><a v-if="item.data?.url" :href="item.data.url">Lihat detail</a></div>
                <button v-if="!item.read_at" class="button button-quiet" @click="page.readNotification(item)">Tandai dibaca</button><span v-else class="pill">Dibaca</span>
            </article>
            <div v-if="!page.rows.value.length" class="empty"><h2>Semua tenang</h2><p>Notifikasi baru akan muncul di sini.</p></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }.notice { display:flex; justify-content:space-between; gap:16px; padding:16px 0; border-bottom:1px solid #eee; }
.meta { color:#777; font-size:.85rem; }.pill { padding:5px 10px; border-radius:99px; background:#eee; }.empty { padding:32px; text-align:center; }
</style>
