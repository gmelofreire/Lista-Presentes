<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GridListas from '@/Layouts/GridListas.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Alert from '@/Components/Alert.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    title: {
        type: String,
    },
    listas: {
        type: [Array, Object],
    },
    grupos: {
        type: [Array, Object],
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage();
const viewMode = ref('grid');

const hasSuccessMessage = computed(() => {
    return page.props.flash?.status || page.props.status;
});

const form = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
    grupo_id: props.filters.grupo_id || '',
    sort: props.filters.sort || 'created_at',
    direction: props.filters.direction || 'desc',
})

const applyFilters = () => {
    form.get(route('listas.index'), {
        preserveState: true,
        preserveScroll: true,
    })
}

const toggleView = (mode) => {
    viewMode.value = mode;
}

const clearFilters = () => {
    form.search = '';
    form.status = '';
    form.grupo_id = '';
    form.sort = 'created_at';
    form.direction = 'desc';
    applyFilters();
}
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
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Minhas Listas</h2>
                            <Link :href="route('listas.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nova Lista
                            </Link>
                        </div>

                        <div class="mb-6 space-y-3">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        v-model="form.search"
                                        placeholder="Buscar listas..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @keyup.enter="applyFilters"
                                    />
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                                <div class="flex gap-2 flex-wrap">

                                    <select v-model="form.status" class="rounded-md border-gray-300 shadow-sm">
                                        <option value="">Todos os status</option>
                                        <option value="ativa">Ativa</option>
                                        <option value="arquivada">Arquivada</option>
                                        <option value="concluida">Concluída</option>
                                    </select>

                                    <select v-model="form.grupo_id" class="rounded-md border-gray-300 shadow-sm">
                                        <option value="">Todos os grupos</option>
                                        <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                                            {{ grupo.nome }}
                                        </option>
                                    </select>
                                    
                                    <select v-model="form.sort" class="rounded-md border-gray-300 shadow-sm">
                                        <option value="created_at">Data de criação</option>
                                        <option value="nome">Nome</option>
                                        <option value="data_evento">Data do evento</option>
                                    </select>
                                    
                                    <select v-model="form.direction" class="rounded-md border-gray-300 shadow-sm">
                                        <option value="desc">Decrescente</option>
                                        <option value="asc">Crescente</option>
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        Filtrar
                                    </button>
                                    
                                    <button @click="clearFilters" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                        Limpar
                                    </button>
                                </div>
                            </div>
                            
                            <!-- <div class="flex justify-end">
                                <div class="flex gap-2">
                                    <button 
                                        @click="toggleView('grid')"
                                        class="p-2 rounded"
                                        :class="viewMode === 'grid' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        @click="toggleView('list')"
                                        class="p-2 rounded"
                                        :class="viewMode === 'list' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div> -->
                        </div>

                        <GridListas :listas="listas" :view-mode="viewMode" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>