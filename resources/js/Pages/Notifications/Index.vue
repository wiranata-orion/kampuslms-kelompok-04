<script setup>
import { computed, ref } from 'vue';
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('notifications');
const filter = ref('all'); // 'all' | 'unread'

const displayedNotifications = computed(() => {
    if (filter.value === 'unread') {
        return page.rows.value.filter((item) => !item.read_at);
    }
    return page.rows.value;
});

const unreadTotal = computed(() => {
    return page.rows.value.filter((item) => !item.read_at).length;
});
</script>

<template>
    <PageFrame 
        title="Notifikasi" 
        subtitle="Kabar terbaru tentang kelas dan aktivitas belajarmu." 
        :loading="page.loading.value" 
        :error="page.error.value" 
        :pagination="page.pagination.value" 
        @page="page.goToPage"
    >
        <template #actions>
            <div class="header-actions">
                <div class="filter-group">
                    <button 
                        class="tab-btn" 
                        :class="{ active: filter === 'all' }" 
                        @click="filter = 'all'"
                    >
                        Semua ({{ page.rows.value.length }})
                    </button>
                    <button 
                        class="tab-btn" 
                        :class="{ active: filter === 'unread' }" 
                        @click="filter = 'unread'"
                    >
                        Belum dibaca ({{ unreadTotal }})
                    </button>
                </div>
                <button 
                    v-if="page.rows.value.some((item) => !item.read_at)" 
                    class="button button-quiet mark-all-btn" 
                    @click="page.readAllNotifications"
                >
                    Tandai semua dibaca
                </button>
            </div>
        </template>

        <section class="panel notif-list-panel">
            <article 
                v-for="item in displayedNotifications" 
                :key="item.id" 
                class="notice-item"
                :class="{ 'is-unread': !item.read_at }"
            >
                <div class="notice-indicator" v-if="!item.read_at"></div>
                <div class="notice-main">
                    <div class="notice-meta-bar">
                        <span class="meta-time">
                            {{ item.created_at ? new Date(item.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '' }}
                        </span>
                        <span v-if="!item.read_at" class="status-badge unread-badge">Belum dibaca</span>
                        <span v-else class="status-badge read-badge">Sudah dibaca</span>
                    </div>

                    <h2 class="notice-title">{{ item.data?.title ?? item.data?.judul ?? 'Pembaruan Kampus' }}</h2>
                    <p class="notice-message">{{ item.data?.message ?? item.data?.pesan ?? item.data?.body ?? 'Ada pembaruan baru untuk akunmu.' }}</p>

                    <div class="notice-actions-row">
                        <a v-if="item.data?.url" :href="item.data.url" class="detail-link">
                            Buka detail &rarr;
                        </a>
                        <button 
                            v-if="!item.read_at" 
                            class="button button-quiet read-single-btn" 
                            @click="page.readNotification(item)"
                        >
                            Tandai sudah dibaca
                        </button>
                    </div>
                </div>
            </article>

            <div v-if="!displayedNotifications.length" class="empty-state">
                <div class="empty-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                </div>
                <h2>{{ filter === 'unread' ? 'Tidak ada notifikasi belum dibaca' : 'Semua tenang' }}</h2>
                <p>{{ filter === 'unread' ? 'Seluruh pesan telah kamu baca.' : 'Notifikasi baru akan muncul di sini saat tersedia pembaruan.' }}</p>
            </div>
        </section>
    </PageFrame>
</template>

<style scoped>
.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-group {
    display: inline-flex;
    background: #edf1f7;
    border-radius: 9px;
    padding: 3px;
    gap: 2px;
}

.tab-btn {
    border: none;
    background: transparent;
    padding: 6px 12px;
    border-radius: 7px;
    font-size: 0.84rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 160ms ease;
}

.tab-btn.active {
    background: #ffffff;
    color: #1e293b;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.notif-list-panel {
    padding: 0;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}

.notice-item {
    position: relative;
    display: flex;
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 160ms ease;
}

.notice-item:last-child {
    border-bottom: none;
}

.notice-item:hover {
    background: #fafbfe;
}

.notice-item.is-unread {
    background: #f8faff;
}

.notice-item.is-unread:hover {
    background: #f0f5ff;
}

.notice-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #3b82f6;
    margin-top: 6px;
    flex-shrink: 0;
}

.notice-main {
    flex: 1;
    min-width: 0;
}

.notice-meta-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
}

.meta-time {
    color: #94a3b8;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 99px;
}

.unread-badge {
    background: #dbeafe;
    color: #1d4ed8;
}

.read-badge {
    background: #f1f5f9;
    color: #64748b;
}

.notice-title {
    margin: 0 0 6px 0;
    color: #1e293b;
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.35;
}

.notice-message {
    margin: 0 0 12px 0;
    color: #475569;
    font-size: 0.92rem;
    line-height: 1.5;
}

.notice-actions-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.detail-link {
    font-size: 0.86rem;
    font-weight: 600;
    color: #3b82f6;
    text-decoration: none;
    transition: color 140ms ease;
}

.detail-link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.read-single-btn {
    font-size: 0.82rem;
    padding: 4px 10px;
}

.empty-state {
    padding: 56px 20px;
    text-align: center;
    color: #64748b;
}

.empty-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    margin-bottom: 14px;
}

.empty-state h2 {
    margin: 0 0 6px 0;
    color: #1e293b;
    font-size: 1.15rem;
}

.empty-state p {
    margin: 0;
    font-size: 0.92rem;
}
</style>
