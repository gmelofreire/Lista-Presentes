<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    title: {
        type: String
    }
})
</script>

<template>
    <Head :title="title" />
    <AppLayout :title="title">
        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Você está logado 😲
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
