<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import api, { getErrorMessage, setToken } from '../../api';

const route = useRoute();
const router = useRouter();
const shell = ref(null);
const dashboard = ref(null);
const courses = ref([]);
const assignments = ref([]);
const submissions = ref([]);
const courseTotal = ref(0);
const loading = ref(true);
const error = ref('');
const sidebarCollapsed = ref(false);
const mobileMenuOpen = ref(false);
const darkMode = ref(localStorage.getItem('laci-dashboard-theme') === 'dark');
const dropdownOpen = ref(false);
const notificationLoading = ref(false);
const notificationError = ref('');
const notifications = ref([]);
const unreadCount = ref(0);
const notificationMenu = ref(null);

const user = computed(() => dashboard.value?.user ?? null);
const stats = computed(() => dashboard.value?.stats ?? {});
const isStudent = computed(() => user.value?.role === 'mahasiswa');
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat pagi';
    if (hour < 15) return 'Selamat siang';
    if (hour < 18) return 'Selamat sore';
    return 'Selamat malam';
});
const today = computed(() => new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
}).format(new Date()));
const pendingAssignments = computed(() => assignments.value.filter((assignment) => (
    isStudent.value
    && new Date(assignment.due_at) >= new Date()
    && !submissions.value.some((submission) => submission.assignment_id === assignment.id)
)));
const upcomingAssignments = computed(() => assignments.value
    .filter((assignment) => assignment.due_at && new Date(assignment.due_at) >= new Date())
    .sort((a, b) => new Date(a.due_at) - new Date(b.due_at))
    .slice(0, 5));
const navigation = computed(() => {
    const links = [{ label: 'Ringkasan', to: '/dashboard', icon: 'home' }];

    links.push({ label: isStudent.value ? 'Kelas saya' : 'Mata kuliah', to: isStudent.value || user.value?.role === 'dosen' ? '/my/courses' : '/courses', icon: 'courses' });
    if (isStudent.value) {
        links.push(
            { label: 'Pengumpulan', to: '/my/submissions', icon: 'submissions' },
            { label: 'Nilai saya', to: '/my/grade-summary', icon: 'grades' },
        );
    } else if (user.value?.role === 'admin') {
        links.push({ label: 'Pengguna', to: '/users', icon: 'users' });
    }
    links.push(
        { label: 'Notifikasi', to: '/notifications', icon: 'notifications' },
        { label: 'Pengaturan', to: '/settings', icon: 'settings' },
    );
    return links;
});
const courseColors = ['lilac', 'blue', 'rose', 'mauve'];

function responseRows(response, label) {
    if (!Array.isArray(response.data?.data)) {
        throw new Error(`Respons ${label} tidak valid.`);
    }
    return response.data.data;
}

async function loadAllPages(path, firstPage, label) {
    const firstRows = responseRows(firstPage, label);
    const lastPage = firstPage.data?.meta?.last_page;
    if (!Number.isInteger(lastPage)) {
        throw new Error(`Respons pagination ${label} tidak valid.`);
    }

    const remainingPages = await Promise.all(
        Array.from({ length: Math.max(0, lastPage - 1) }, (_, index) => (
            api.get(path, { params: { page: index + 2 } })
        )),
    );
    return [
        ...firstRows,
        ...remainingPages.flatMap((response) => responseRows(response, label)),
    ];
}

async function loadCourseAssignments(course) {
    const path = `/courses/${course.id}/assignments`;
    const firstPage = await api.get(path);
    return (await loadAllPages(path, firstPage, `tugas ${course.name}`))
        .map((assignment) => ({ ...assignment, course: assignment.course ?? course }));
}

async function loadDashboard() {
    loading.value = true;
    error.value = '';
    try {
        const [dashboardResponse, coursesResponse, submissionsResponse] = await Promise.all([
            api.get('/dashboard'),
            api.get('/my/courses'),
            api.get('/my/submissions'),
        ]);
        dashboard.value = dashboardResponse.data?.data;
        if (!dashboard.value?.user || !dashboard.value?.stats || typeof dashboard.value.stats !== 'object') {
            throw new Error('Respons ringkasan dashboard tidak valid.');
        }

        [courses.value, submissions.value] = await Promise.all([
            loadAllPages('/my/courses', coursesResponse, 'kelas'),
            loadAllPages('/my/submissions', submissionsResponse, 'pengumpulan'),
        ]);
        courseTotal.value = Number.isInteger(coursesResponse.data?.meta?.total)
            ? coursesResponse.data.meta.total
            : Number(stats.value.total_courses ?? courses.value.length);
        const assignmentGroups = await Promise.all(courses.value.map(loadCourseAssignments));
        assignments.value = assignmentGroups.flat()
            .filter((assignment) => assignment.status === 'published');
    } catch (requestError) {
        error.value = getErrorMessage(requestError);
    } finally {
        loading.value = false;
    }
}

async function fetchNotifications() {
    if (!localStorage.getItem('kampuslms_token')) return;
    notificationLoading.value = true;
    notificationError.value = '';
    try {
        const [countResponse, listResponse] = await Promise.all([
            api.get('/notifications/unread-count'),
            api.get('/notifications', { params: { per_page: 5 } }),
        ]);
        notifications.value = responseRows(listResponse, 'notifikasi');
        unreadCount.value = Number(
            countResponse.data?.data?.unread_count
            ?? countResponse.data?.data?.count
            ?? notifications.value.filter((item) => !item.read_at).length,
        );
    } catch (requestError) {
        notificationError.value = getErrorMessage(requestError);
    } finally {
        notificationLoading.value = false;
    }
}

async function toggleNotifications() {
    dropdownOpen.value = !dropdownOpen.value;
    if (dropdownOpen.value) await fetchNotifications();
}

async function markAsRead(item) {
    if (item.read_at) return;
    try {
        const response = await api.post(`/notifications/${item.id}/read`);
        item.read_at = response.data?.data?.read_at ?? new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        window.dispatchEvent(new CustomEvent('notifications-updated'));
    } catch (requestError) {
        notificationError.value = getErrorMessage(requestError);
    }
}

async function markAllAsRead() {
    try {
        await api.post('/notifications/read-all');
        const readAt = new Date().toISOString();
        notifications.value.forEach((item) => {
            if (!item.read_at) item.read_at = readAt;
        });
        unreadCount.value = 0;
        window.dispatchEvent(new CustomEvent('notifications-updated'));
    } catch (requestError) {
        notificationError.value = getErrorMessage(requestError);
    }
}

async function openNotification(item) {
    if (!item.read_at) await markAsRead(item);
    const targetUrl = item.data?.url;
    dropdownOpen.value = false;
    if (!targetUrl) return;

    const path = targetUrl.replace(/^https?:\/\/[^/]+/, '');
    if (path.startsWith('/')) router.push(path);
    else window.location.href = targetUrl;
}

function closeNotificationMenu(event) {
    if (notificationMenu.value && !notificationMenu.value.contains(event.target)) {
        dropdownOpen.value = false;
    }
}

function handleKeyDown(event) {
    if (event.key === 'Escape') {
        dropdownOpen.value = false;
        mobileMenuOpen.value = false;
    }
}

function handlePointerMove(event) {
    if (!shell.value) return;
    const bounds = shell.value.getBoundingClientRect();
    shell.value.style.setProperty('--pointer-x', `${event.clientX - bounds.left}px`);
    shell.value.style.setProperty('--pointer-y', `${event.clientY - bounds.top}px`);
}

function toggleTheme() {
    darkMode.value = !darkMode.value;
    localStorage.setItem('laci-dashboard-theme', darkMode.value ? 'dark' : 'light');
}

function formatDue(value) {
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

function dueLabel(value) {
    const hours = Math.ceil((new Date(value).getTime() - Date.now()) / 3_600_000);
    if (hours < 24) return hours <= 1 ? 'Segera' : `${hours} jam lagi`;
    const days = Math.ceil(hours / 24);
    return days === 1 ? 'Besok' : `${days} hari lagi`;
}

function formatActivityDate(value) {
    if (!value) return 'Waktu tidak tersedia';
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }).format(new Date(value));
}

function notificationTime(value) {
    if (!value) return '';
    return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }).format(new Date(value));
}

function toggleSidebar() {
    if (window.matchMedia('(max-width: 760px)').matches) {
        mobileMenuOpen.value = !mobileMenuOpen.value;
    } else {
        sidebarCollapsed.value = !sidebarCollapsed.value;
    }
}

async function logout() {
    try {
        await api.post('/auth/logout');
    } finally {
        setToken(null);
        router.push({ name: 'login' });
    }
}

onMounted(() => {
    loadDashboard();
    fetchNotifications();
    document.addEventListener('click', closeNotificationMenu);
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('notifications-updated', fetchNotifications);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeNotificationMenu);
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('notifications-updated', fetchNotifications);
});
</script>

<template>
    <div
        ref="shell"
        class="dashboard-shell"
        :class="{ 'sidebar-collapsed': sidebarCollapsed, 'mobile-menu-open': mobileMenuOpen, 'theme-dark': darkMode }"
        @pointermove="handlePointerMove"
    >
        <aside class="dashboard-sidebar" aria-label="Navigasi utama">
            <RouterLink class="dashboard-brand" to="/dashboard" aria-label="LaciKampus, ringkasan">
                <span class="brand-symbol">L</span>
                <span class="brand-name">laci<span>kampus</span></span>
                <span class="brand-period">.</span>
            </RouterLink>

            <div class="sidebar-caption">RUANG BELAJARMU</div>
            <nav class="sidebar-navigation">
                <RouterLink
                    v-for="item in navigation"
                    :key="item.to"
                    :to="item.to"
                    class="sidebar-link"
                    :class="{ 'is-current': route.path === item.to }"
                    :title="sidebarCollapsed ? item.label : undefined"
                    @click="mobileMenuOpen = false"
                >
                    <svg class="nav-glyph" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path v-if="item.icon === 'home'" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z" />
                        <path v-else-if="item.icon === 'courses'" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21zm0 0V21m4-13h8m-8 4h6" />
                        <path v-else-if="item.icon === 'submissions'" d="M7 3h8l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm8 0v5h5m-8 5 2 2 4-4" />
                        <path v-else-if="item.icon === 'grades'" d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z" />
                        <path v-else-if="item.icon === 'users'" d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20m7-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm7-7.5a4 4 0 0 1 0 7.8m4 8.7v-1.5a4 4 0 0 0-3-3.9" />
                        <path v-else-if="item.icon === 'notifications'" d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 10h18c0-2-3-3-3-10m-8 13h4" />
                        <path v-else d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zm7.4-3.5a7.5 7.5 0 0 0-.1-1.2l1.5-1.1-1.5-2.6-1.8.7a7 7 0 0 0-2.1-1.2L15.1 4h-3l-.3 2.6a7 7 0 0 0-2.1 1.2l-1.8-.7-1.5 2.6 1.5 1.1a7.5 7.5 0 0 0 0 2.4l-1.5 1.1 1.5 2.6 1.8-.7a7 7 0 0 0 2.1 1.2l.3 2.6h3l.3-2.6a7 7 0 0 0 2.1-1.2l1.8.7 1.5-2.6-1.5-1.1a7.5 7.5 0 0 0 .1-1.2z" />
                    </svg>
                    <span class="nav-label">{{ item.label }}</span>
                    <span v-if="item.icon === 'notifications' && unreadCount" class="nav-unread-dot" />
                </RouterLink>
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-note">
                    <span class="note-stars" aria-hidden="true">✦</span>
                    <strong>Pelan-pelan,</strong>
                    <span>yang penting terus bertumbuh.</span>
                </div>
                <button class="sidebar-collapse" type="button" @click="toggleSidebar" :aria-label="sidebarCollapsed ? 'Perluas menu' : 'Ciutkan menu'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 18-6-6 6-6" /></svg>
                    <span>Ciutkan menu</span>
                </button>
                <button class="logout-link" type="button" @click="logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" /></svg>
                    <span>Keluar</span>
                </button>
            </div>
        </aside>

        <div v-if="mobileMenuOpen" class="sidebar-scrim" @click="mobileMenuOpen = false" />

        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="topbar-leading">
                    <button class="icon-button mobile-menu-button" type="button" aria-label="Buka menu" @click="toggleSidebar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </button>
                    <div class="breadcrumb"><span>Ruang belajar</span><span class="breadcrumb-slash">/</span><strong>Ringkasan</strong></div>
                </div>
                <div class="topbar-actions">
                    <span class="topbar-date">{{ today }}</span>
                    <button class="icon-button theme-toggle" type="button" :aria-label="darkMode ? 'Aktifkan tema terang' : 'Aktifkan tema gelap'" @click="toggleTheme">
                        <svg v-if="!darkMode" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5z" /></svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4" /><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" /></svg>
                    </button>

                    <div ref="notificationMenu" class="notification-menu">
                        <button class="icon-button notification-trigger" type="button" aria-label="Notifikasi" :aria-expanded="dropdownOpen" @click.stop="toggleNotifications">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 10h18c0-2-3-3-3-10m-8 13h4" /></svg>
                            <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                        </button>

                        <Transition name="notification-pop">
                            <section v-if="dropdownOpen" class="notification-dropdown" aria-label="Notifikasi terbaru" @click.stop>
                                <header class="notification-heading">
                                    <div><h2>Notifikasi</h2><span v-if="unreadCount" class="notification-count">{{ unreadCount }} belum dibaca</span></div>
                                    <button v-if="unreadCount" type="button" class="text-action" @click="markAllAsRead">Tandai dibaca</button>
                                </header>
                                <p v-if="notificationError" class="notification-error" role="alert">{{ notificationError }}</p>
                                <div v-if="notificationLoading" class="notification-message">Memuat notifikasi…</div>
                                <div v-else-if="!notifications.length && !notificationError" class="notification-message">Belum ada notifikasi.</div>
                                <div v-else class="notification-list">
                                    <button
                                        v-for="item in notifications"
                                        :key="item.id"
                                        type="button"
                                        class="notification-item"
                                        :class="{ 'is-unread': !item.read_at }"
                                        @click="openNotification(item)"
                                    >
                                        <span class="notification-indicator" />
                                        <span class="notification-copy">
                                            <span class="notification-item-top"><strong>{{ item.data?.title ?? item.data?.judul ?? 'Pemberitahuan' }}</strong><time>{{ notificationTime(item.created_at) }}</time></span>
                                            <span class="notification-summary">{{ item.data?.message ?? item.data?.pesan ?? 'Ada pembaruan aktivitas.' }}</span>
                                        </span>
                                    </button>
                                </div>
                                <RouterLink class="all-notifications" to="/notifications" @click="dropdownOpen = false">Lihat semua notifikasi <span aria-hidden="true">↗</span></RouterLink>
                            </section>
                        </Transition>
                    </div>

                    <RouterLink class="profile-chip" to="/settings" :title="user?.name">
                        <span class="profile-avatar">{{ user?.name?.slice(0, 1)?.toUpperCase() ?? 'L' }}</span>
                        <span class="profile-name">{{ user?.name ?? 'Mahasiswa' }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8 10 4 4 4-4" /></svg>
                    </RouterLink>
                </div>
            </header>

            <main class="dashboard-content">
                <div v-if="error" class="dashboard-error" role="alert">
                    <span>{{ error }}</span>
                    <button type="button" @click="loadDashboard">Coba lagi</button>
                </div>

                <div v-if="loading && !dashboard" class="dashboard-loading" role="status">
                    <span class="loading-orbit" />
                    <span>Menyiapkan ruang belajarmu…</span>
                </div>

                <template v-else-if="dashboard">
                    <section class="welcome-banner">
                        <div class="welcome-copy">
                            <div class="welcome-date"><span class="welcome-date-dot" /> {{ today }}</div>
                            <p class="welcome-greeting">{{ greeting }},</p>
                            <h1>{{ user?.name ?? 'Mahasiswa' }}<span class="greeting-comma">.</span></h1>
                            <p class="welcome-subtitle">Satu langkah kecil hari ini, satu langkah lebih dekat ke tujuanmu.</p>
                            <RouterLink class="welcome-link" to="/my/courses">
                                Lanjutkan belajar
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </RouterLink>
                        </div>
                        <div class="welcome-art" aria-hidden="true">
                            <div class="art-orbit orbit-one" /><div class="art-orbit orbit-two" />
                            <div class="art-sun"><span>✦</span></div>
                            <span class="art-sparkle sparkle-one">✦</span><span class="art-sparkle sparkle-two">✳</span>
                            <span class="art-dot dot-one" /><span class="art-dot dot-two" /><span class="art-dot dot-three" />
                            <div class="art-label"><span>hari ini</span><strong>adalah milikmu</strong></div>
                        </div>
                        <span class="welcome-glow" aria-hidden="true" />
                    </section>

                    <section class="overview-section" aria-labelledby="overview-heading">
                        <div class="section-heading">
                            <div><span class="section-kicker">GAMBARAN UMUM</span><h2 id="overview-heading">Perjalanan akademikmu</h2></div>
                            <span class="updated-label"><span /> Data akunmu</span>
                        </div>
                        <div class="overview-grid">
                            <article class="overview-card overview-courses">
                                <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21zm0 0V21m4-13h8m-8 4h6" /></svg></span><span class="stat-arrow">↗</span></div>
                                <strong class="stat-number">{{ courseTotal }}</strong>
                                <span class="stat-label">{{ isStudent ? 'Mata kuliah aktif' : 'Mata kuliah terhubung' }}</span>
                                <span class="stat-footnote">terdaftar di ruang belajarmu</span>
                                <div class="stat-decoration course-stack" aria-hidden="true"><i /><i /><i /></div>
                            </article>
                            <article class="overview-card overview-pending">
                                <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg></span><span class="stat-tag">{{ pendingAssignments.length ? 'Perlu perhatian' : 'Terkendali' }}</span></div>
                                <strong class="stat-number">{{ pendingAssignments.length }}</strong>
                                <span class="stat-label">{{ isStudent ? 'Tugas menunggu' : 'Tenggat mendatang' }}</span>
                                <span class="stat-footnote">{{ isStudent ? 'belum ada pengumpulan' : 'dalam daftar kelasmu' }}</span>
                                <div class="stat-decoration pending-spark" aria-hidden="true">✳</div>
                            </article>
                            <article class="overview-card overview-submissions">
                                <div class="stat-top"><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3h8l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm8 0v5h5m-8 5 2 2 4-4" /></svg></span><span class="stat-tag">tercatat</span></div>
                                <strong class="stat-number">{{ stats.total_submissions ?? submissions.length }}</strong>
                                <span class="stat-label">Pengumpulan tugas</span>
                                <span class="stat-footnote">tersimpan di akunmu</span>
                                <div class="stat-decoration submission-check" aria-hidden="true">✓</div>
                            </article>
                        </div>
                    </section>

                    <div class="dashboard-columns">
                        <section class="courses-section" aria-labelledby="courses-heading">
                            <div class="section-heading compact-heading">
                                <div><span class="section-kicker">KELASMU</span><h2 id="courses-heading">Ruang untuk bertumbuh</h2></div>
                                <RouterLink class="section-link" to="/my/courses">Semua kelas <span aria-hidden="true">→</span></RouterLink>
                            </div>
                            <div v-if="courses.length" class="course-grid">
                                <RouterLink
                                    v-for="(course, index) in courses.slice(0, 4)"
                                    :key="course.id"
                                    :to="`/courses/${course.id}`"
                                    class="course-card"
                                    :class="`course-${courseColors[index % courseColors.length]}`"
                                >
                                    <div class="course-card-top"><span class="course-code">{{ course.code }}</span><span class="course-open" aria-hidden="true">↗</span></div>
                                    <div class="course-monogram">{{ course.name?.slice(0, 1)?.toUpperCase() ?? 'K' }}</div>
                                    <div class="course-card-bottom"><h3>{{ course.name }}</h3><span class="course-lecturer">{{ course.lecturer?.name ?? 'Pengajar belum ditentukan' }}</span></div>
                                    <div class="course-card-meta"><span>{{ course.sks ?? '—' }} SKS</span><span class="course-meta-divider" /><span>{{ course.counts?.assignments ?? '—' }} tugas</span></div>
                                    <span class="course-blob" aria-hidden="true" />
                                </RouterLink>
                            </div>
                            <div v-else class="empty-state course-empty">
                                <span class="empty-symbol">✦</span><h3>Ruang belajarmu menanti</h3>
                                <p>Kelas yang terhubung dengan akunmu akan muncul di sini.</p>
                                <RouterLink v-if="isStudent" to="/courses">Jelajahi mata kuliah <span aria-hidden="true">→</span></RouterLink>
                            </div>
                        </section>

                        <section class="deadlines-section" aria-labelledby="deadlines-heading">
                            <div class="section-heading compact-heading">
                                <div><span class="section-kicker">JANGAN TERLEWAT</span><h2 id="deadlines-heading">Tenggat terdekat</h2></div>
                                <RouterLink v-if="isStudent" class="section-link" to="/my/submissions">Pengumpulan <span aria-hidden="true">→</span></RouterLink>
                            </div>
                            <div v-if="upcomingAssignments.length" class="deadline-list">
                                <RouterLink
                                    v-for="(assignment, index) in upcomingAssignments"
                                    :key="assignment.id"
                                    :to="`/assignments/${assignment.id}`"
                                    class="deadline-item"
                                    :class="{ 'deadline-urgent': new Date(assignment.due_at) - Date.now() < 86_400_000 }"
                                >
                                    <span class="deadline-date" :class="`date-tone-${index % 3}`">
                                        <span>{{ new Date(assignment.due_at).toLocaleDateString('id-ID', { day: '2-digit' }) }}</span>
                                        <small>{{ new Date(assignment.due_at).toLocaleDateString('id-ID', { month: 'short' }) }}</small>
                                    </span>
                                    <span class="deadline-copy"><strong>{{ assignment.title }}</strong><small>{{ assignment.course?.code ?? assignment.course?.name ?? 'Mata kuliah' }}</small></span>
                                    <span class="deadline-time">{{ dueLabel(assignment.due_at) }}</span>
                                </RouterLink>
                            </div>
                            <div v-else class="empty-state deadlines-empty">
                                <span class="empty-symbol">☼</span><h3>Semua punya waktunya</h3><p>Tenggat tugas yang akan datang akan terlihat di sini.</p>
                            </div>
                        </section>
                    </div>

                    <div class="bottom-columns">
                        <section class="activity-section" aria-labelledby="activity-heading">
                            <div class="section-heading compact-heading">
                                <div><span class="section-kicker">LANGKAH TERAKHIR</span><h2 id="activity-heading">Aktivitas terbaru</h2></div>
                                <RouterLink v-if="isStudent" class="section-link" to="/my/submissions">Lihat semua <span aria-hidden="true">→</span></RouterLink>
                            </div>
                            <div v-if="submissions.length" class="activity-list">
                                <RouterLink v-for="submission in submissions.slice(0, 4)" :key="submission.id" :to="`/submissions/${submission.id}`" class="activity-item">
                                    <span class="activity-icon" :class="{ 'activity-graded': submission.grade }">
                                        <svg v-if="submission.grade" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3h8l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm8 0v5h5" /></svg>
                                    </span>
                                    <span class="activity-copy"><strong>{{ submission.assignment?.title ?? 'Pengumpulan tugas' }}</strong><span>{{ submission.assignment?.course?.name ?? 'Mata kuliah' }} · {{ submission.grade ? `Nilai ${submission.grade.score}` : submission.is_late ? 'Dikumpulkan terlambat' : 'Sudah dikumpulkan' }}</span></span>
                                    <time>{{ formatActivityDate(submission.submitted_at) }}</time>
                                </RouterLink>
                            </div>
                            <div v-else class="activity-empty">
                                <span>Belum ada aktivitas pengumpulan.</span>
                                <RouterLink v-if="isStudent" to="/my/courses">Kunjungi kelasmu <span aria-hidden="true">→</span></RouterLink>
                            </div>
                        </section>

                        <section class="shortcuts-section" aria-labelledby="shortcuts-heading">
                            <div class="section-heading compact-heading">
                                <div><span class="section-kicker">MULAI DARI SINI</span><h2 id="shortcuts-heading">Pintasan</h2></div>
                            </div>
                            <div class="shortcut-grid">
                                <RouterLink to="/my/courses" class="shortcut-card shortcut-lilac"><span class="shortcut-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21zm0 0V21m4-13h8" /></svg></span><span><strong>{{ isStudent ? 'Kelas saya' : 'Mata kuliah' }}</strong><small>Masuk ke ruang belajar</small></span><span class="shortcut-arrow">↗</span></RouterLink>
                                <RouterLink v-if="isStudent" to="/my/submissions" class="shortcut-card shortcut-rose"><span class="shortcut-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3h8l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm8 0v5h5m-8 5 2 2 4-4" /></svg></span><span><strong>Pengumpulan</strong><small>Lihat status tugasmu</small></span><span class="shortcut-arrow">↗</span></RouterLink>
                                <RouterLink v-if="!isStudent && user?.role === 'admin'" to="/users" class="shortcut-card shortcut-blue"><span class="shortcut-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20m7-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" /></svg></span><span><strong>Pengguna</strong><small>Kelola akun kampus</small></span><span class="shortcut-arrow">↗</span></RouterLink>
                                <RouterLink v-if="isStudent" to="/my/grade-summary" class="shortcut-card shortcut-blue"><span class="shortcut-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z" /></svg></span><span><strong>Ringkasan nilai</strong><small>Periksa hasil belajarmu</small></span><span class="shortcut-arrow">↗</span></RouterLink>
                            </div>
                        </section>
                    </div>
                    <footer class="dashboard-footer"><span>Ruang belajarmu, caramu bertumbuh.</span><span class="footer-mark">laci<span>kampus</span><b>.</b></span></footer>
                </template>
            </main>
        </div>
    </div>
</template>
