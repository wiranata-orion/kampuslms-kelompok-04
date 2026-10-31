<script setup>
import { onMounted, reactive, ref } from 'vue';
import PageFrame from '../../components/PageFrame.vue';
import api from '../../api';

const user = ref(null);
const loading = ref(true);
const error = ref('');

// State untuk formulir reset/ganti kata sandi
const showResetForm = ref(false);
const resetLoading = ref(false);
const resetSuccess = ref('');
const resetError = ref('');
const fieldErrors = ref({});

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

async function loadUserProfile() {
    loading.value = true;
    error.value = '';
    try {
        const response = await api.get('/me');
        user.value = response.data.data;
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal memuat profil pengguna.';
    } finally {
        loading.value = false;
    }
}

function openResetPassword() {
    showResetForm.value = true;
    resetSuccess.value = '';
    resetError.value = '';
    fieldErrors.value = {};
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
}

function cancelResetPassword() {
    showResetForm.value = false;
    resetError.value = '';
    fieldErrors.value = {};
}

async function handleResetPassword() {
    resetError.value = '';
    resetSuccess.value = '';
    fieldErrors.value = {};

    // Validasi frontend awal
    if (!passwordForm.current_password) {
        fieldErrors.value.current_password = ['Kata sandi lama wajib diisi.'];
        return;
    }
    if (!passwordForm.password || passwordForm.password.length < 8) {
        fieldErrors.value.password = ['Kata sandi baru minimal 8 karakter.'];
        return;
    }
    if (passwordForm.password !== passwordForm.password_confirmation) {
        fieldErrors.value.password_confirmation = ['Konfirmasi kata sandi baru tidak cocok.'];
        return;
    }

    resetLoading.value = true;
    try {
        const response = await api.post('/auth/change-password', {
            current_password: passwordForm.current_password,
            password: passwordForm.password,
            password_confirmation: passwordForm.password_confirmation,
        });

        resetSuccess.value = response.data.message || 'Kata sandi berhasil diperbarui.';
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
        showResetForm.value = false;
    } catch (e) {
        if (e.response?.status === 422 && e.response?.data?.errors) {
            fieldErrors.value = e.response.data.errors;
            resetError.value = e.response.data.message || 'Harap periksa kembali input Anda.';
        } else {
            resetError.value = e.response?.data?.message || 'Terjadi kesalahan saat mengganti kata sandi.';
        }
    } finally {
        resetLoading.value = false;
    }
}

onMounted(() => {
    loadUserProfile();
});
</script>

<template>
    <PageFrame title="Pengaturan Akun" subtitle="Kelola profil akun dan keamanan kata sandi Anda" :loading="loading" :error="error">
        <div v-if="user" class="settings-grid">
            <!-- Profil Card -->
            <section class="panel profile-card">
                <div class="profile-header">
                    <div class="avatar-circle">
                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <div>
                        <h2>{{ user.name }}</h2>
                        <span class="pill role-pill">{{ user.role?.toUpperCase() }}</span>
                    </div>
                </div>

                <div class="info-list">
                    <div class="info-item">
                        <span class="label">Email:</span>
                        <span class="value">{{ user.email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">NIM / NIP:</span>
                        <span class="value">{{ user.nim_nip || '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Peran Sistem:</span>
                        <span class="value capitalize">{{ user.role }}</span>
                    </div>
                </div>
            </section>

            <!-- Keamanan & Reset Password Card -->
            <section class="panel security-card">
                <div class="section-title">
                    <div class="icon-wrap">🔒</div>
                    <div>
                        <h3>Keamanan & Kata Sandi</h3>
                        <p class="muted">Perbarui kata sandi akun Anda secara berkala untuk menjaga keamanan.</p>
                    </div>
                </div>

                <!-- Notifikasi Sukses / Error Umum -->
                <p v-if="resetSuccess" class="success-alert" role="status">
                    ✓ {{ resetSuccess }}
                </p>
                <p v-if="resetError" class="error-alert" role="alert">
                    ✕ {{ resetError }}
                </p>

                <!-- Tombol Buka Reset Password -->
                <div v-if="!showResetForm" class="security-action">
                    <p class="desc">Ingin memperbarui kata sandi akun Anda?</p>
                    <button class="button" type="button" @click="openResetPassword">
                        Reset Kata Sandi
                    </button>
                </div>

                <!-- Formulir Input Kata Sandi Lama & Baru -->
                <form v-else class="password-form" @submit.prevent="handleResetPassword">
                    <div class="form-group">
                        <label for="current_password">
                            Kata Sandi Lama <span class="required">*</span>
                        </label>
                        <input
                            id="current_password"
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi saat ini"
                            :class="{ 'has-error': fieldErrors.current_password }"
                            required
                        >
                        <span v-if="fieldErrors.current_password" class="field-error">
                            {{ fieldErrors.current_password[0] }}
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="new_password">
                            Kata Sandi Baru <span class="required">*</span>
                        </label>
                        <input
                            id="new_password"
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            :class="{ 'has-error': fieldErrors.password }"
                            required
                        >
                        <span v-if="fieldErrors.password" class="field-error">
                            {{ fieldErrors.password[0] }}
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation">
                            Konfirmasi Kata Sandi Baru <span class="required">*</span>
                        </label>
                        <input
                            id="new_password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi baru"
                            :class="{ 'has-error': fieldErrors.password_confirmation }"
                            required
                        >
                        <span v-if="fieldErrors.password_confirmation" class="field-error">
                            {{ fieldErrors.password_confirmation[0] }}
                        </span>
                    </div>

                    <div class="form-actions">
                        <button class="button" type="submit" :disabled="resetLoading">
                            {{ resetLoading ? 'Menyimpan…' : 'Simpan Kata Sandi Baru' }}
                        </button>
                        <button class="button button-quiet" type="button" :disabled="resetLoading" @click="cancelResetPassword">
                            Batal
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </PageFrame>
</template>

<style scoped>
.settings-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 22px;
}

@media (min-width: 768px) {
    .settings-grid {
        grid-template-columns: 360px 1fr;
    }
}

.profile-card {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--line, #e7e4ee);
}

.avatar-circle {
    display: grid;
    place-items: center;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff;
    font-size: 1.5rem;
    font-weight: 800;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}

.profile-header h2 {
    margin: 0 0 4px;
    font-size: 1.2rem;
    color: var(--ink, #1f2937);
}

.role-pill {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.92rem;
}

.info-item .label {
    color: var(--muted, #6b7280);
}

.info-item .value {
    font-weight: 600;
    color: var(--ink, #1f2937);
}

.capitalize {
    text-transform: capitalize;
}

.security-card {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.section-title {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--line, #e7e4ee);
}

.section-title .icon-wrap {
    font-size: 1.4rem;
}

.section-title h3 {
    margin: 0 0 4px;
    font-size: 1.15rem;
    color: var(--ink, #1f2937);
}

.security-action {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border-radius: 10px;
    background: #faf8ff;
    border: 1px dashed #dcd5f2;
}

.security-action .desc {
    margin: 0;
    color: #4b5563;
    font-size: 0.94rem;
}

.password-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-width: 500px;
    background: #fafbfc;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--ink, #374151);
}

.form-group .required {
    color: #ef4444;
}

.form-group input {
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    background: #ffffff;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-group input:focus {
    border-color: #6366f1;
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.form-group input.has-error {
    border-color: #ef4444;
}

.field-error {
    font-size: 0.8rem;
    color: #dc2626;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}

.success-alert {
    padding: 12px 16px;
    border-radius: 8px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    font-weight: 500;
    margin: 0;
}

.error-alert {
    padding: 12px 16px;
    border-radius: 8px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    font-weight: 500;
    margin: 0;
}
</style>
