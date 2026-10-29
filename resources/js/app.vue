<template>
    <div class="app-shell">
        <header v-if="user" class="topbar">
            <RouterLink class="brand" to="/dashboard"><span class="brand-mark">K</span><span>KampusLMS</span></RouterLink>
            <nav class="nav-row">
                <RouterLink to="/dashboard">Dashboard</RouterLink>
                <RouterLink v-if="user.role !== 'admin'" to="/my/courses">Kelas saya</RouterLink>
                <RouterLink v-if="user.role === 'admin'"to="/courses">Mata kuliah</RouterLink>
                <RouterLink v-if="user.role === 'admin'" to="/users">Pengguna</RouterLink>
                <RouterLink v-if="user.role === 'mahasiswa'" to="/my/submissions">Pengumpulan saya</RouterLink>
                <RouterLink to="/notifications">Notifikasi</RouterLink>
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
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import api, { setToken } from './api';

const router = useRouter();
const user = ref(null);
const authError = ref('');

async function loadUser() {
    try {
        const { data } = await api.get('/me');
        user.value = data.data;
        authError.value = '';
    } catch {
        setToken(null);
        user.value = null;
        if (router.currentRoute.value.name !== 'login') router.replace({ name: 'login' });
    }
}

async function logout() {
    try {
        await api.post('/auth/logout');
    } finally {
        setToken(null);
        user.value = null;
        router.push({ name: 'login' });
    }
}

onMounted(() => {
    if (localStorage.getItem('kampuslms_token')) loadUser();
});

watch(() => router.currentRoute.value.fullPath, () => {
    if (localStorage.getItem('kampuslms_token') && !user.value) loadUser();
});
</script>
