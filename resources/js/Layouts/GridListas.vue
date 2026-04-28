<template>
    <div>
        <div class="mb-6">
            <div class="mt-4 text-sm text-gray-600">
                <span>Mostrando {{ listas.data.length }} de {{ listas.total }} listas</span>
            </div>
        </div>

        <!-- Grid View -->
        <ul v-if="viewMode === 'grid'" role="list" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <li v-for="lista in listas.data" :key="lista.id"
                class="col-span-1 rounded-2xl duration-200">
                <div class="shadow-sm group hover:shadow-md rounded-2xl transition-shadow ">
                    <div class="flex w-full items-center justify-between px-5 pt-16 rounded-t-lg bg-cover bg-center bg-no-repeat relative"
                        :style="{ backgroundImage: `url(${lista.image_url})` }">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-t-2xl">
                            <div
                                class="flex text-white justify-end p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <Link :href="route('listas.edit', lista.id)">
                                <PencilIcon class="size-5" />
                                </Link>
                            </div>
                        </div>
                        <div class="flex-1 truncate rounded-lg py-3 relative z-10">
                            <div class="flex items-center space-x-3">
                                <h3 class="truncate text-sm font-bold text-white">{{ lista.nome }}</h3>
                                <!-- <span v-if="lista.status" :class="getStatusClass(lista.status)" class="text-xs px-2 py-1 rounded-full text-slate-700">
                                    {{ lista.status }}
                                </span> -->
                            </div>
                            <p class="mt-1 truncate text-sm text-white">{{ lista.descricao }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="-mt-px flex divide-x divide-gray-200">
                            <Link :href="route('listas.show', lista.id)" class="w-full">
                            <button
                                class="text-center w-full h-12 bg-indigo-500 text-white rounded-b-lg hover:bg-indigo-600 transition-colors">
                                Acessar
                            </button>
                            </Link>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center" v-if="lista.grupo">
                    <Link :href="route('grupos.show', lista.grupo.id)">
                    <div class="inline-block text-black text-center my-3 font-medium py-1 px-4 rounded-full transition-colors" >
                       Grupo da lista: {{ lista.grupo?.nome }}
                    </div>
                    </Link>
                </div>
            </li>
        </ul>

        <!-- List View -->
        <div v-else-if="viewMode === 'list'" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th> -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data do Evento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="lista in listas.data" :key="lista.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img :src="lista.image_url" :alt="lista.nome" class="h-12 w-12 rounded-lg object-cover">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ lista.nome }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ lista.descricao }}</div>
                        </td>
                        <!-- <td class="px-6 py-4 whitespace-nowrap">
                            <span v-if="lista.status" :class="getStatusClass(lista.status)" class="text-xs px-2 py-1 rounded-full">
                                {{ lista.status }}
                            </span>
                        </td> -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ lista.data_evento ? new Date(lista.data_evento).toLocaleDateString('pt-BR') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <Link v-if="lista.grupo" :href="route('grupos.show', lista.grupo.id)" class="text-indigo-600 hover:text-indigo-900">
                                {{ lista.grupo.nome }}
                            </Link>
                            <span v-else>-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('listas.edit', lista.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <PencilIcon class="w-5 h-5" />
                            </Link>
                            <Link :href="route('listas.show', lista.id)" class="text-green-600 hover:text-green-900">
                                Ver
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="listas.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma lista encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">
                Você ainda não criou nenhuma lista.
            </p>
        </div>

        <div v-if="listas.last_page > 1" class="mt-8 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="goToPage(listas.current_page - 1)" :disabled="listas.current_page === 1"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Anterior
                </button>
                <button @click="goToPage(listas.current_page + 1)" :disabled="listas.current_page === listas.last_page"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Próxima
                </button>
            </div>

            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium">{{ listas.from }}</span>
                        a
                        <span class="font-medium">{{ listas.to }}</span>
                        de
                        <span class="font-medium">{{ listas.total }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button @click="goToPage(listas.current_page - 1)" :disabled="listas.current_page === 1"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="sr-only">Anterior</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="[
                            page === listas.current_page
                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                        ]">
                            {{ page }}
                        </button>

                        <button @click="goToPage(listas.current_page + 1)"
                            :disabled="listas.current_page === listas.last_page"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="sr-only">Próxima</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import PencilIcon from '@heroicons/vue/24/outline/PencilIcon'

const props = defineProps({
    listas: {
        type: Object,
        required: true
    },
    viewMode: {
        type: String,
        default: 'grid'
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const getStatusClass = (status) => {
    const classes = {
        'ativa': 'bg-green-100 text-green-800',
        'arquivada': 'bg-gray-100 text-gray-800',
        'concluida': 'bg-blue-100 text-blue-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const visiblePages = computed(() => {
    const pages = []
    const total = props.listas.last_page
    const current = props.listas.current_page

    let start = Math.max(1, current - 2)
    let end = Math.min(total, start + 4)

    if (end - start < 4) {
        start = Math.max(1, end - 4)
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

const goToPage = (page) => {
    if (page >= 1 && page <= props.listas.last_page) {
        router.get(route('listas.index'), {
            page: page,
            search: props.filters.search,
            status: props.filters.status,
            grupo_id: props.filters.grupo_id,
            sort: props.filters.sort,
            direction: props.filters.direction,
        }, {
            preserveState: true,
            replace: true
        })
    }
}
</script>