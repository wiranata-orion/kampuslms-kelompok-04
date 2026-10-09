<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('user');
</script>

<template>
    <PageFrame :title="page.data.value?.name ?? 'Profil pengguna'" subtitle="Informasi akun kampus." :loading="page.loading.value" :error="page.error.value">
        <template #actions><RouterLink class="button button-quiet" to="/users">Kembali ke pengguna</RouterLink></template>
        <section v-if="page.data.value" class="panel">
            <span class="pill">{{ page.data.value.role }}</span>
            <dl><dt>Email</dt><dd>{{ page.data.value.email }}</dd><dt>NIM / NIP</dt><dd>{{ page.data.value.nim_nip || 'Belum diisi' }}</dd><dt>Bergabung</dt><dd>{{ page.data.value.created_at ? new Date(page.data.value.created_at).toLocaleDateString('id-ID') : '—' }}</dd></dl>
            <div class="actions"><RouterLink class="button" :to="`/users/${page.data.value.id}/edit`">Edit pengguna</RouterLink><button v-if="page.data.value.id !== page.user.value?.id" class="button button-danger" @click="page.remove('users', page.data.value)">Hapus akun</button></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }.pill { padding:5px 10px; border-radius:99px; background:#f0edff; color:#5744ad; }
dl { display:grid; grid-template-columns:180px 1fr; gap:12px; } dd { margin:0; }.actions { display:flex; gap:10px; }
</style>
