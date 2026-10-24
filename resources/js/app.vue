<template>
    <div class="app-shell" @click="handleDocumentClick">
        <header v-if="user" class="topbar">
            <RouterLink class="brand" to="/dashboard"><span class="brand-mark">K</span><span>KampusLMS</span></RouterLink>
            <nav class="nav-row">
                <RouterLink to="/dashboard">Dashboard</RouterLink>
                <RouterLink v-if="user.role !== 'admin'" to="/my/courses">Kelas saya</RouterLink>
                <RouterLink v-if="user.role === 'admin'" to="/courses">Mata kuliah</RouterLink>
                <RouterLink v-if="user.role === 'admin'" to="/users">Pengguna</RouterLink>
                <RouterLink v-if="user.role === 'mahasiswa'" to="/my/submissions">Pengumpulan saya</RouterLink>
                <RouterLink to="/settings">Pengaturan</RouterLink>

                <!-- Lonceng Notifikasi & Dropdown Ringkas -->
                <div class="notif-menu-container" ref="notifDropdownRef">
                    <button 
                        class="notif-bell-btn" 
                        type="button" 
                        @click.stop="toggleDropdown" 
                        :aria-expanded="dropdownOpen"
                        title="Notifikasi"
                    >
                        <svg class="bell-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg>
                        <span v-if="unreadCount > 0" class="notif-badge">
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
                    </button>

                    <!-- Dropdown Notifikasi -->
                    <transition name="dropdown-anim">
                        <div v-if="dropdownOpen" class="notif-dropdown" @click.stop>
                            <div class="notif-dropdown-header">
                                <div class="notif-title-area">
                                    <span class="notif-title">Notifikasi</span>
                                    <span v-if="unreadCount > 0" class="notif-counter-tag">{{ unreadCount }} baru</span>
                                </div>
                                <button 
                                    v-if="unreadCount > 0" 
                                    class="notif-mark-all-btn" 
                                    type="button" 
                                    @click="markAllAsRead"
                                >
                                    Tandai semua dibaca
                                </button>
                            </div>

                            <div class="notif-dropdown-body">
                                <div v-if="loadingNotifs" class="notif-loading">
                                    Memuat notifikasi...
                                </div>
                                <div v-else-if="!recentNotifications.length" class="notif-empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="empty-icon">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                    </svg>
                                    <p>Tidak ada notifikasi baru.</p>
                                </div>
                                <div v-else class="notif-items-list">
                                    <article 
                                        v-for="item in recentNotifications" 
                                        :key="item.id" 
                                        class="notif-card"
                                        :class="{ 'is-unread': !item.read_at }"
                                        @click="handleNotificationClick(item)"
                                    >
                                        <div class="notif-card-indicator" v-if="!item.read_at"></div>
                                        <div class="notif-card-content">
                                            <div class="notif-card-top">
                                                <h4 class="notif-card-title">{{ item.data?.title ?? item.data?.judul ?? 'Pemberitahuan' }}</h4>
                                                <span class="notif-card-time">{{ formatTime(item.created_at) }}</span>
                                            </div>
                                            <p class="notif-card-message">{{ item.data?.message ?? item.data?.pesan ?? 'Ada pembaruan aktivitas.' }}</p>
                                            <div class="notif-card-bottom" v-if="!item.read_at">
                                                <button 
                                                    class="notif-item-read-action" 
                                                    type="button" 
                                                    @click.stop="markAsRead(item)"
                                                >
                                                    Tandai dibaca
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            </div>

                            <div class="notif-dropdown-footer">
                                <RouterLink to="/notifications" class="notif-see-all-link" @click="dropdownOpen = false">
                                    Lihat semua notifikasi &rarr;
                                </RouterLink>
                            </div>
                        </div>
                    </transition>
                </div>

                <button class="button button-quiet" type="button" @click="logout">Keluar</button>
            </nav>
        </header>
        <main class="page-wrap">
            <p v-if="authError" class="error" role="alert">{{ authError }}</p>
            <RouterView @authenticated="loadUser" />
        </main>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import api, { setToken } from './api';

const router = useRouter();
const user = ref(null);
const authError = ref('');

const notifDropdownRef = ref(null);
const dropdownOpen = ref(false);
const loadingNotifs = ref(false);
const unreadCount = ref(0);
const recentNotifications = ref([]);

async function loadUser() {
    try {
        const { data } = await api.get('/me');
        user.value = data.data;
        authError.value = '';
        fetchNotifications();
    } catch {
        setToken(null);
        user.value = null;
        if (router.currentRoute.value.name !== 'login') router.replace({ name: 'login' });
    }
}

async function fetchNotifications() {
    if (!localStorage.getItem('kampuslms_token')) return;
    try {
        const [countRes, listRes] = await Promise.all([
            api.get('/notifications/unread-count').catch(() => null),
            api.get('/notifications', { params: { per_page: 5 } }).catch(() => null),
        ]);

        if (countRes?.data?.data) {
            unreadCount.value = countRes.data.data.unread_count ?? countRes.data.data.count ?? 0;
        }

        if (listRes?.data?.data) {
            recentNotifications.value = listRes.data.data;
            if (!countRes?.data?.data) {
                unreadCount.value = recentNotifications.value.filter((n) => !n.read_at).length;
            }
        }
    } catch {
        // silent fail
    }
}

function toggleDropdown() {
    dropdownOpen.value = !dropdownOpen.value;
    if (dropdownOpen.value) {
        fetchNotifications();
    }
}

function handleDocumentClick(e) {
    if (notifDropdownRef.value && !notifDropdownRef.value.contains(e.target)) {
        dropdownOpen.value = false;
    }
}

function handleKeyDown(e) {
    if (e.key === 'Escape') dropdownOpen.value = false;
}

async function markAsRead(item) {
    try {
        await api.post(`/notifications/${item.id}/read`);
        item.read_at = new Date().toISOString();
        if (unreadCount.value > 0) unreadCount.value--;
        window.dispatchEvent(new CustomEvent('notifications-updated'));
    } catch (e) {
        console.error(e);
    }
}

async function markAllAsRead() {
    try {
        await api.post('/notifications/read-all');
        recentNotifications.value.forEach((n) => {
            n.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
        window.dispatchEvent(new CustomEvent('notifications-updated'));
    } catch (e) {
        console.error(e);
    }
}

function handleNotificationClick(item) {
    if (!item.read_at) {
        markAsRead(item);
    }
    const targetUrl = item.data?.url;
    if (targetUrl) {
        const path = targetUrl.replace(/^https?:\/\/[^\/]+/, '');
        if (path.startsWith('/')) {
            router.push(path);
        } else {
            window.location.href = targetUrl;
        }
    }
    dropdownOpen.value = false;
}

function formatTime(isoString) {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

async function logout() {
    try {
        await api.post('/auth/logout');
    } finally {
        setToken(null);
        user.value = null;
        dropdownOpen.value = false;
        router.push({ name: 'login' });
    }
}

onMounted(() => {
    if (localStorage.getItem('kampuslms_token')) loadUser();
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('notifications-updated', fetchNotifications);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('notifications-updated', fetchNotifications);
});

watch(() => router.currentRoute.value.fullPath, () => {
    dropdownOpen.value = false;
    if (localStorage.getItem('kampuslms_token')) {
        if (!user.value) loadUser();
        else fetchNotifications();
    }
});
</script>

<style scoped>
.notif-menu-container {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.notif-bell-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    padding: 0;
    border: 1px solid #e0e5f2;
    border-radius: 9px;
    background: #ffffff;
    color: #4b5563;
    cursor: pointer;
    transition: all 180ms ease;
}

.notif-bell-btn:hover {
    background: #f4f6fc;
    color: #3154c5;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.bell-svg {
    transition: transform 180ms ease;
}

.notif-bell-btn:hover .bell-svg {
    transform: rotate(12deg);
}

.notif-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 99px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 700;
    line-height: 1;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
    border: 2px solid #ffffff;
}

.notif-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 340px;
    max-width: 90vw;
    border: 1px solid #e5e9f2;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(14px);
    box-shadow: 0 16px 40px rgba(30, 41, 59, 0.12), 0 2px 6px rgba(30, 41, 59, 0.04);
    z-index: 100;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.notif-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #edf1f7;
    background: #fafbfe;
}

.notif-title-area {
    display: flex;
    align-items: center;
    gap: 8px;
}

.notif-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}

.notif-counter-tag {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 99px;
    background: #fee2e2;
    color: #b91c1c;
}

.notif-mark-all-btn {
    background: none;
    border: none;
    padding: 0;
    font-size: 0.78rem;
    font-weight: 600;
    color: #3b82f6;
    cursor: pointer;
    transition: color 140ms ease;
}

.notif-mark-all-btn:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.notif-dropdown-body {
    max-height: 360px;
    overflow-y: auto;
}

.notif-loading {
    padding: 24px 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 0.88rem;
}

.notif-empty-state {
    padding: 32px 16px;
    text-align: center;
    color: #94a3b8;
}

.notif-empty-state .empty-icon {
    margin-bottom: 8px;
    stroke: #cbd5e1;
}

.notif-empty-state p {
    margin: 0;
    font-size: 0.86rem;
}

.notif-items-list {
    display: flex;
    flex-direction: column;
}

.notif-card {
    position: relative;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f4f9;
    cursor: pointer;
    transition: background 140ms ease;
    display: flex;
    gap: 10px;
}

.notif-card:last-child {
    border-bottom: none;
}

.notif-card:hover {
    background: #f8faff;
}

.notif-card.is-unread {
    background: #f0f5ff;
}

.notif-card.is-unread:hover {
    background: #e6efff;
}

.notif-card-indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #3b82f6;
    margin-top: 6px;
    flex-shrink: 0;
}

.notif-card-content {
    flex: 1;
    min-width: 0;
}

.notif-card-top {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}

.notif-card-title {
    margin: 0;
    font-size: 0.86rem;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notif-card-time {
    font-size: 0.72rem;
    color: #94a3b8;
    flex-shrink: 0;
}

.notif-card-message {
    margin: 0 0 6px 0;
    font-size: 0.8rem;
    color: #475569;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notif-card-bottom {
    display: flex;
    justify-content: flex-end;
}

.notif-item-read-action {
    background: none;
    border: none;
    padding: 0;
    font-size: 0.74rem;
    font-weight: 600;
    color: #3b82f6;
    cursor: pointer;
}

.notif-item-read-action:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.notif-dropdown-footer {
    padding: 10px 16px;
    border-top: 1px solid #edf1f7;
    background: #fafbfe;
    text-align: center;
}

.notif-see-all-link {
    font-size: 0.82rem;
    font-weight: 600;
    color: #3b82f6;
    text-decoration: none;
}

.notif-see-all-link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.dropdown-anim-enter-active,
.dropdown-anim-leave-active {
    transition: opacity 180ms ease, transform 180ms cubic-bezier(0.16, 1, 0.3, 1);
}

.dropdown-anim-enter-from,
.dropdown-anim-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
}
</style>
