<script setup>
import { RouterLink } from 'vue-router';

defineProps({
    fields: { type: Array, required: true },
    model: { type: Object, required: true },
    submitLabel: { type: String, default: 'Simpan' },
    cancelTo: { type: String, default: '/dashboard' },
    busy: { type: Boolean, default: false },
    showMaterialFields: { type: Boolean, default: false },
});

defineEmits(['submit', 'file-change']);
</script>

<template>
    <form class="panel form-grid" @submit.prevent="$emit('submit')">
        <div
            v-for="field in fields"
            v-show="!showMaterialFields || (field.name === 'external_url' ? model.type === 'link' : ['original_name', 'file_size', 'mime_type'].includes(field.name) ? model.type === 'file' : true)"
            :key="field.name"
            class="field"
            :class="{ 'span-all': field.type === 'textarea' }"
        >
            <label v-if="field.type !== 'checkbox'" :for="field.name">{{ field.label }}</label>
            <textarea v-if="field.type === 'textarea'" :id="field.name" v-model="model[field.name]" :required="field.required" />
            <select v-else-if="field.type === 'select'" :id="field.name" v-model="model[field.name]" :required="field.required">
                <option value="">Pilih {{ field.label.toLowerCase() }}</option>
                <option v-for="option in field.options" :key="option.value" :value="option.value">{{ option.text }}</option>
            </select>
            <label v-else-if="field.type === 'checkbox'" class="check"><input v-model="model[field.name]" type="checkbox">{{ field.label }}</label>
            <input
                v-else-if="field.type === 'file'"
                :id="field.name"
                type="file"
                :required="showMaterialFields && model.type === 'file' && !model.id"
                @change="$emit('file-change', $event)"
            >
            <input
                v-else
                :id="field.name"
                v-model="model[field.name]"
                :type="field.type ?? 'text'"
                :min="field.min"
                :max="field.max"
                :step="field.step"
                :required="field.required || (showMaterialFields && model.type === 'link' && field.name === 'external_url') || (showMaterialFields && model.type === 'file' && ['original_name', 'file_size'].includes(field.name))"
            >
        </div>
        <div class="span-all actions">
            <button class="button" :disabled="busy">{{ submitLabel }}</button>
            <RouterLink class="button button-quiet" :to="cancelTo">Batal</RouterLink>
        </div>
    </form>
</template>

<style scoped>
.panel { padding:22px; margin-bottom:16px; border:1px solid #e7e4ee; border-radius:14px; background:#fff; }
.field { display:flex; flex-direction:column; gap:7px; margin-bottom:14px; }
.field input,.field textarea,select,textarea { width:100%; padding:10px; border:1px solid #d8d4e2; border-radius:8px; }
textarea { min-height:100px; }
.form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 14px; }
.span-all { grid-column:1 / -1; }
.actions { display:flex; flex-wrap:wrap; gap:10px; margin:12px 0; }
.check { display:flex; gap:10px; align-items:center; }
.check input { width:auto; }
@media(max-width:680px) { .form-grid { grid-template-columns:1fr; } }
</style>
