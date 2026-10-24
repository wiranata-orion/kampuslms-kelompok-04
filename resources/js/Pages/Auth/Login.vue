<script setup>
import { usePageData } from '../../composables/usePageData';
import ResourceForm from '../../components/ResourceForm.vue';
import { useRoute } from 'vue-router';

const page = usePageData('login');
const route = useRoute();
</script>

<template>
    <section class="login-card">
        <span class="eyebrow">KampusLMS</span>
        <h1>Masuk akun</h1>
        <p>Gunakan email kampus untuk melanjutkan.</p>
        <p v-if="route.query.passwordReset === 'success'" class="success" role="status">
            Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.
        </p>
        <p v-if="page.error.value" class="error" role="alert">{{ page.error.value }}</p>
        <ResourceForm
            :fields="page.fields.value"
            :model="page.form"
            :busy="page.loading.value"
            submit-label="Masuk ke KampusLMS"
            @submit="page.login"
        />
        <div class="auth-footer">
            <router-link to="/forgot-password" class="reset-link">
                Lupa kata sandi?
            </router-link>
        </div>
    </section>
</template>

<style scoped>
.login-card { width:min(480px,100%); margin:8vh auto; padding:28px; border:1px solid #e7e4ee; border-radius:16px; background:#fff; }
.eyebrow { color:#7163bb; font-size:.78rem; font-weight:800; text-transform:uppercase; }
.error { padding:12px; border-radius:8px; background:#fff0f0; color:#ae3549; }
.success { padding:12px; border-radius:8px; background:#effaf2; color:#216e3a; }
</style>
