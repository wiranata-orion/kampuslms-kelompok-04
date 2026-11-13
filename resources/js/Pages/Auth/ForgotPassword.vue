<script setup>
import { RouterLink } from 'vue-router';
</script>

<template>
    <section class="reset-card">
        <span class="eyebrow">KampusLMS</span>
        <h1>Atur ulang kata sandi</h1>
        <p>Pengaturan ulang kata sandi harus dilakukan melalui tautan verifikasi yang dikirim ke email terdaftar.</p>
        <p class="notice" role="status">Fitur pengaturan ulang langsung dengan NIM/NIP atau email telah dinonaktifkan demi keamanan akun.</p>
        <div class="actions">
            <RouterLink class="button" to="/login">Kembali ke login</RouterLink>
        </div>
    </section>
</template>

<style scoped>
.reset-card { width:min(480px,100%); margin:8vh auto; padding:28px; border:1px solid #e7e4ee; border-radius:16px; background:#fff; }
.eyebrow { color:#7163bb; font-size:.78rem; font-weight:800; text-transform:uppercase; }
.form-grid { display:grid; gap:14px; }
.field { display:flex; flex-direction:column; gap:7px; }
.field input { width:100%; padding:10px; border:1px solid #d8d4e2; border-radius:8px; }
.actions { display:flex; flex-wrap:wrap; gap:10px; }
.notice { padding:12px; border-radius:8px; background:#fff8e6; color:#785512; }
.error { padding:12px; border-radius:8px; background:#fff0f0; color:#ae3549; }
</style>
