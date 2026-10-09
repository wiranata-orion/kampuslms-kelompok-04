<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
</script>

<template>
    <section class="panel">
        <span class="eyebrow">Akses terbatas</span><h1>403</h1><h2>Kamu belum bisa masuk ke halaman ini</h2>
        <p>{{ route.query.message || 'Halaman ini hanya tersedia untuk akun dengan izin yang sesuai.' }}</p>
        <RouterLink class="button" to="/dashboard">Kembali ke beranda</RouterLink>
    </section>
</template>

<style scoped>
.panel { max-width:680px; margin:8vh auto; padding:clamp(28px,7vw,64px); border:1px solid #e7e4ee; border-radius:16px; background:#fff; text-align:center; }
.eyebrow { color:#7163bb; font-weight:800; text-transform:uppercase; }.panel h1 { color:#b93850; font-size:clamp(4rem,12vw,7rem); }
</style>
