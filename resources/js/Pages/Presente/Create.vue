<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PresenteForm from '@/Components/PresenteForm.vue';

const props = defineProps({
    title: {
        type: String,
    },
    lista_id: {
        type: String,
    },
    categorias: {
        type: [Array, Object],
    }
});
</script>

<template>
    <AppLayout :title="title">
        <PresenteForm 
            mode="create"
            :title="title"
            :lista_id="lista_id"
            :categorias="categorias"
        />
    </AppLayout>
</template>