<script setup>
import { computed } from 'vue';
import PageFrame from '../../components/PageFrame.vue';
import ResourceForm from '../../components/ResourceForm.vue';
import { usePageData } from '../../composables/usePageData';

const page = usePageData('user-form');
const title = computed(() => page.mode.value === 'edit' ? 'Edit pengguna' : 'Tambah pengguna');
const cancelTo = computed(() => page.mode.value === 'edit' ? `/users/${page.data.value?.id ?? ''}` : '/users');
</script>

<template>
    <PageFrame :title="title" :loading="page.loading.value" :error="page.error.value">
        <ResourceForm :fields="page.fields.value" :model="page.form" :busy="page.loading.value" :cancel-to="cancelTo" submit-label="Simpan pengguna" @submit="page.submit" />
    </PageFrame>
</template>
