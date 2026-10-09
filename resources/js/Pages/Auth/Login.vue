<script setup>
import { usePageData } from '../../composables/usePageData';
import ResourceForm from '../../components/ResourceForm.vue';

const page = usePageData('login');
</script>

<template>
    <section class="login-card">
        <span class="eyebrow">KampusLMS</span>
        <h1>Masuk akun</h1>
        <p>Gunakan email kampus untuk melanjutkan.</p>
        <p v-if="page.error.value" class="error" role="alert">{{ page.error.value }}</p>
        <ResourceForm
            :fields="page.fields.value"
            :model="page.form"
            :busy="page.loading.value"
            submit-label="Masuk ke KampusLMS"
            @submit="page.login"
        />
    </section>
</template>

<style scoped>
.login-card { width:min(480px,100%); margin:8vh auto; padding:28px; border:1px solid #e7e4ee; border-radius:16px; background:#fff; }
.eyebrow { color:#7163bb; font-size:.78rem; font-weight:800; text-transform:uppercase; }
.error { padding:12px; border-radius:8px; background:#fff0f0; color:#ae3549; }
</style>
