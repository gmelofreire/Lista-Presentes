<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GridListas from '@/Layouts/GridListas.vue';
import { Link } from '@inertiajs/vue3';
import Alert from '@/Components/Alert.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
    },
    listas: {
        type: [Array, Object],
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
        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Alert v-if="hasSuccessMessage" type="success"
                    :title="page.props.flash?.status || page.props.status || 'Sucesso'" class="mb-6" />
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Minhas Listas</h2>
                            <Link :href="route('listas.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nova Lista
                            </Link>
                        </div>

                        <GridListas :listas="listas" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>