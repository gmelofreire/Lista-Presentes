<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PresenteForm from '@/Components/PresenteForm.vue';

const props = defineProps({
    title: {
        type: String,
    },
    presente: {
        type: [Array, Object],
    },
    categorias: {
        type: [Array, Object],
    }
});
</script>

<template>
    <AppLayout :title="title">
        <PresenteForm 
            mode="edit"
            :title="title"
            :presente="presente"
            :categorias="categorias"
        />
    </AppLayout>
</template>