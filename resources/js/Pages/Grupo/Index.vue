<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { PlusIcon } from '@heroicons/vue/24/outline';
import Alert from '@/Components/Alert.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GridGrupos from '@/Layouts/GridGrupos.vue';

defineProps({
    title: {
        type: String,
    },
    grupos: {
        type: [Array, Object],
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage();

const hasSuccessMessage = computed(() => {
    return page.props.flash?.status || page.props.status;
});
</script>

<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div class="space-y-8">
            <Alert v-if="hasSuccessMessage" type="success"
                :title="page.props.flash?.status || page.props.status || 'Sucesso'" />

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-3xl font-semibold text-gray-900">Meus grupos</h2>
                    <p class="mt-1 text-gray-600">Família, amigos e turmas que compartilham listas com você.</p>
                </div>
                <Link :href="route('grupos.create')" class="btn-primary self-start sm:self-auto">
                    <PlusIcon class="size-4" aria-hidden="true" />
                    Novo grupo
                </Link>
            </div>

            <GridGrupos :grupos="grupos" :filters="filters" />
        </div>
    </AppLayout>
</template>
