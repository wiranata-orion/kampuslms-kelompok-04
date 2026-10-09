<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import api, { getErrorMessage } from '../../api';

const router = useRouter();
const identifier = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const stage = ref('identifier');
const busy = ref(false);
const error = ref('');

async function checkUser() {
    busy.value = true;
    error.value = '';

    try {
        await api.post('/auth/check-user', { identifier: identifier.value.trim() });
        stage.value = 'password';
    } catch (requestError) {
        error.value = getErrorMessage(requestError);
    } finally {
        busy.value = false;
    }
}

async function resetPassword() {
    busy.value = true;
    error.value = '';

    try {
        await api.post('/auth/reset-password', {
            identifier: identifier.value.trim(),
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        await router.push({ name: 'login', query: { passwordReset: 'success' } });
    } catch (requestError) {
        error.value = getErrorMessage(requestError);
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <section class="reset-card">
        <span class="eyebrow">KampusLMS</span>
        <template v-if="stage === 'identifier'">
            <h1>Cari akun Anda</h1>
            <p>Masukkan NIM/NIP atau email yang terdaftar untuk melanjutkan.</p>
            <form class="form-grid" @submit.prevent="checkUser">
                <div class="field">
                    <label for="identifier">NIM/NIP atau email</label>
                    <input
                        id="identifier"
                        v-model="identifier"
                        type="text"
                        autocomplete="username"
                        required
                    >
                </div>
                <p v-if="error" class="error" role="alert">{{ error }}</p>
                <div class="actions">
                    <button class="button" :disabled="busy">
                        {{ busy ? 'Mencari akun...' : 'Lanjutkan' }}
                    </button>
                    <RouterLink class="button button-quiet" to="/login">Kembali ke login</RouterLink>
                </div>
            </form>
        </template>

        <template v-else>
            <h1>Ganti kata sandi</h1>
            <p>Masukkan kata sandi baru untuk akun <strong>{{ identifier }}</strong>.</p>
            <form class="form-grid" @submit.prevent="resetPassword">
                <div class="field">
                    <label for="password">Kata sandi baru</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>
                <div class="field">
                    <label for="password-confirmation">Konfirmasi kata sandi baru</label>
                    <input
                        id="password-confirmation"
                        v-model="passwordConfirmation"
                        type="password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>
                <p v-if="error" class="error" role="alert">{{ error }}</p>
                <div class="actions">
                    <button class="button" :disabled="busy">
                        {{ busy ? 'Menyimpan...' : 'Simpan kata sandi' }}
                    </button>
                    <button class="button button-quiet" type="button" :disabled="busy" @click="stage = 'identifier'; error = ''">
                        Ganti NIM/email
                    </button>
                </div>
            </form>
        </template>
    </section>
</template>

<style scoped>
.reset-card { width:min(480px,100%); margin:8vh auto; padding:28px; border:1px solid #e7e4ee; border-radius:16px; background:#fff; }
.eyebrow { color:#7163bb; font-size:.78rem; font-weight:800; text-transform:uppercase; }
.form-grid { display:grid; gap:14px; }
.field { display:flex; flex-direction:column; gap:7px; }
.field input { width:100%; padding:10px; border:1px solid #d8d4e2; border-radius:8px; }
.actions { display:flex; flex-wrap:wrap; gap:10px; }
.error { padding:12px; border-radius:8px; background:#fff0f0; color:#ae3549; }
</style>
