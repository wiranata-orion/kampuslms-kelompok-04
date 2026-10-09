<template>
    <div class="app-shell">
        <header v-if="user" class="topbar">
            <RouterLink class="brand" to="/dashboard"><span class="brand-mark">K</span><span>KampusLMS</span></RouterLink>
            <nav class="nav-row">
                <RouterLink to="/dashboard">Dashboard</RouterLink>
                <RouterLink v-if="user.role !== 'admin'" to="/my/courses">Kelas saya</RouterLink>
                <RouterLink to="/courses">Mata kuliah</RouterLink>
                <RouterLink v-if="user.role === 'admin'" to="/users">Pengguna</RouterLink>
                <RouterLink v-if="user.role === 'mahasiswa'" to="/my/submissions">Pengumpulan saya</RouterLink>
                <RouterLink to="/notifications">Notifikasi</RouterLink>
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

<style>
:root { font-family: Inter, ui-sans-serif, system-ui, sans-serif; color: #24213b; background: #f7f6fb; }
* { box-sizing: border-box; }
body { margin: 0; min-width: 320px; min-height: 100vh; }
a { color: #6656bd; text-decoration: none; }
.app-shell { min-height: 100vh; }
.topbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px; padding: 14px max(20px, calc((100vw - 1160px) / 2)); border-bottom: 1px solid #e8e5f0; background: #fff; }
.brand, .nav-row { display: flex; align-items: center; gap: 12px; }
.brand { color: inherit; font-size: 1.1rem; font-weight: 800; }
.brand-mark { display: grid; width: 36px; aspect-ratio: 1; place-items: center; border-radius: 12px; background: #e9e4ff; color: #6552bd; }
.nav-row { flex-wrap: wrap; }
.nav-row a { padding: 8px 10px; border-radius: 8px; color: #514d66; }
.nav-row a.router-link-active { background: #f0edff; color: #5744ad; }
.page-wrap { width: min(1160px, calc(100% - 32px)); margin: 32px auto; }
.button { padding: 9px 14px; border: 0; border-radius: 9px; background: #6552bd; color: white; cursor: pointer; font-weight: 700; }
.button-quiet { border: 1px solid #dedbe8; background: #fff; color: #514d66; }
.error { padding: 12px 14px; border-radius: 9px; background: #fff0f0; color: #ae3549; }
</style>