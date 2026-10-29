<script setup>
import PageFrame from '../../components/PageFrame.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('grade-summary');
</script>

<template>
    <PageFrame
        title="Rekap nilai"
        :subtitle="page.user.value?.role === 'mahasiswa' ? 'Daftar nilai tugas yang telah dipublikasikan.' : `Nilai tugas pada ${page.data.value?.name ?? 'mata kuliah'}.`"
        :loading="page.loading.value"
        :error="page.error.value"
        :pagination="page.pagination.value"
        @page="page.goToPage"
    >
        <section class="panel">
            <div v-if="page.rows.value.length" class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th v-if="page.user.value?.role !== 'mahasiswa'">Mahasiswa</th>
                            <th v-if="page.user.value?.role === 'mahasiswa'">Mata kuliah</th>
                            <th>Tugas</th>
                            <th>Nilai</th>
                            <th>Umpan balik</th>
                            <th v-if="page.user.value?.role !== 'mahasiswa'">Publikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="grade in page.rows.value" :key="grade.id">
                            <td v-if="page.user.value?.role !== 'mahasiswa'">{{ grade.student?.name ?? 'Mahasiswa' }}</td>
                            <td v-if="page.user.value?.role === 'mahasiswa'">{{ grade.course?.name }}</td>
                            <td>{{ grade.assignment?.title }}</td>
                            <td>{{ grade.score }} / {{ grade.assignment?.max_score }}</td>
                            <td>{{ grade.feedback || '—' }}</td>
                            <td v-if="page.user.value?.role !== 'mahasiswa'">{{ grade.is_published ? 'Dipublikasikan' : 'Draft' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="empty">
                <h2>Belum ada nilai</h2>
                <p>{{ page.user.value?.role === 'mahasiswa' ? 'Nilai akan tampil setelah dosen memublikasikannya.' : 'Belum ada submission yang dinilai pada mata kuliah ini.' }}</p>
            </div>
        </section>
    </PageFrame>
</template>

<style scoped>
.panel { padding:22px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
td,th { padding:10px; border-bottom:1px solid #eee; text-align:left; }
.empty { padding:32px; text-align:center; color:#6b6879; }
</style>
