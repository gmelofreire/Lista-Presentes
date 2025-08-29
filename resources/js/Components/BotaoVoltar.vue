<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/20/solid';
import { Link } from '@inertiajs/vue3';

defineProps({
    rota: {
        type: String,
    }
})
</script>

<template>
    <Link :href="rota">
        <button class="bg-blue-500 text-white px-4 py-2 rounded-md flex items-center gap-2">
            <ArrowLeftIcon class="w-5 h-5" />
            Voltar
        </button>
    </Link>
</template>