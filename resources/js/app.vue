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
