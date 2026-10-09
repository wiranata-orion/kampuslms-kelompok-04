<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('users');
</script>

<template>
    <PageFrame title="Pengguna kampus" subtitle="Kelola akun admin, dosen, dan mahasiswa." :loading="page.loading.value" :error="page.error.value" :pagination="page.pagination.value" @page="page.goToPage">
        <template #actions><RouterLink class="button" to="/users/create">Tambah pengguna</RouterLink></template>
        <section class="panel">
            <form class="toolbar" @submit.prevent="page.goToPage(1)">
                <input v-model="page.search.value" type="search" placeholder="Cari nama atau email">
                <select v-model="page.role.value"><option value="">Semua peran</option><option value="admin">Admin</option><option value="dosen">Dosen</option><option value="mahasiswa">Mahasiswa</option></select>
                <button class="button">Cari</button><button class="button button-quiet" type="button" @click="page.search.value = ''; page.role.value = ''; page.goToPage(1)">Atur ulang</button>
            </form>
            <div v-if="page.rows.value.length" class="table-wrap"><table><thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>NIM / NIP</th><th>Aksi</th></tr></thead><tbody>
                <tr v-for="item in page.rows.value" :key="item.id"><td><RouterLink :to="`/users/${item.id}`">{{ item.name }}</RouterLink></td><td>{{ item.email }}</td><td>{{ item.role }}</td><td>{{ item.nim_nip || '—' }}</td><td><RouterLink :to="`/users/${item.id}`">Detail</RouterLink> · <RouterLink :to="`/users/${item.id}/edit`">Edit</RouterLink><button v-if="item.id !== page.user.value?.id" class="text-button" @click="page.remove('users', item)">Hapus</button></td></tr>
            </tbody></table></div>
            <div v-else class="empty"><h2>Belum ada pengguna</h2><p>Coba ubah pencarian atau tambahkan akun baru.</p></div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.toolbar { display:grid; grid-template-columns:minmax(180px,1fr) auto auto auto; gap:10px; margin-bottom:16px; }
.toolbar input,.toolbar select { padding:10px; border:1px solid #d8d4e2; border-radius:8px; }
.table-wrap { overflow-x:auto; } table { width:100%; border-collapse:collapse; } td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }
.text-button { margin-left:8px; border:0; background:none; color:#a33; cursor:pointer; }.empty { padding:32px; text-align:center; }
@media(max-width:680px) { .toolbar { grid-template-columns:1fr; } }
</style>
