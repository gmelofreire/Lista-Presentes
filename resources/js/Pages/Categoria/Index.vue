<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
    },
    categorias: {
        type: [Array, Object],
    }
})

const page = usePage();

// Estado reativo para filtro
const searchTerm = ref('');

const hasSuccessMessage = computed(() => {
    return page.props.flash?.status || page.props.status;
});

// Propriedade computada para filtrar categorias
const filteredCategorias = computed(() => {
    let filtered = props.categorias || [];

    // Aplicar filtro de busca por nome
    if (searchTerm.value) {
        filtered = filtered.filter(categoria =>
            categoria.nome.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            (categoria.descricao && categoria.descricao.toLowerCase().includes(searchTerm.value.toLowerCase()))
        );
    }

    return filtered;
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
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900">Categorias</h1>
                                <p class="mt-2 text-sm text-gray-700">Uma lista de todas as categorias da sua conta incluindo nome, descrição, cor e ações.</p>
                            </div>
                            <Link :href="route('categorias.create')">
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Adicionar categoria
                                </button>
                            </Link>
                        </div>

                        <!-- Filtros -->
                        <div class="mb-6">
                            <!-- Busca por nome -->
                            <div class="w-full">
                                <input v-model="searchTerm" type="text" placeholder="Buscar por nome ou descrição..."
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Tabela de Categorias -->
                        <div class="mt-8 flow-root">
                            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-300">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nome</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Descrição</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Cor</th>
                                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                                        <span class="sr-only">Editar</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white">
                                                <tr v-for="categoria in filteredCategorias" :key="categoria.id">
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                        {{ categoria.nome }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                        {{ categoria.descricao || '-' }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                        <div class="flex items-center">
                                                            <div class="h-4 w-4 rounded-full mr-2" :style="{ backgroundColor: categoria.hex_cor }"></div>
                                                            <span class="text-xs font-mono">{{ categoria.hex_cor }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                        <Link :href="route('categorias.edit', categoria)">
                                                            <button type="button" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                                        </Link>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Mensagem quando não há categorias -->
                                                <tr v-if="filteredCategorias.length === 0">
                                                    <td colspan="4" class="px-6 py-14 text-center text-sm text-gray-500">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                                                            {{ searchTerm ? 'Nenhuma categoria encontrada' : 'Nenhuma categoria' }}
                                                        </h3>
                                                        <p class="mt-1 text-sm text-gray-500">
                                                            {{ searchTerm ? 'Nenhuma categoria corresponde ao filtro aplicado' : 'Comece criando uma nova categoria.' }}
                                                        </p>
                                                        <div v-if="searchTerm" class="mt-4 flex flex-wrap gap-2 justify-center">
                                                            <button @click="searchTerm = ''"
                                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                                                Limpar busca
                                                            </button>
                                                        </div>
                                                        <div v-else class="mt-6">
                                                            <Link :href="route('categorias.create')">
                                                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                                    </svg>
                                                                    Nova categoria
                                                                </button>
                                                            </Link>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>