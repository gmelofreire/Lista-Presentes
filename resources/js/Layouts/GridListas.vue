<template>
    <div>
        <p class="mb-5 text-sm text-gray-500">
            {{ listas.total }} {{ listas.total === 1 ? 'lista' : 'listas' }}
        </p>

        <!-- Grid View -->
        <ul v-if="viewMode === 'grid' && listas.data.length > 0" role="list"
            class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <li v-for="(lista, i) in listas.data" :key="lista.id" :style="{ '--i': Math.min(i, 8) }">
                <div class="surface-link group relative flex h-full flex-col overflow-hidden">
                    <Link :href="route('listas.show', lista.id)" class="block">
                        <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                            <img :src="lista.image_url || '/img/default_cover.png'" :alt="`Capa da lista ${lista.nome}`"
                                class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" />
                        </div>
                        <div class="p-5 pb-4">
                            <h3 class="truncate font-semibold text-gray-900">{{ lista.nome }}</h3>
                            <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-sm text-gray-500">
                                {{ lista.descricao || 'Sem descrição' }}
                            </p>
                        </div>
                    </Link>
                    <div class="mt-auto flex items-center justify-between gap-3 px-5 pb-5">
                        <Link v-if="lista.grupo" :href="route('grupos.show', lista.grupo.id)"
                            class="truncate rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-200">
                            {{ lista.grupo.nome }}
                        </Link>
                        <span v-else class="text-xs text-gray-400">Lista pessoal</span>
                        <span v-if="lista.data_evento" class="shrink-0 text-xs font-medium text-gray-600">
                            {{ new Date(lista.data_evento).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' }) }}
                        </span>
                    </div>
                    <Link :href="route('listas.edit', lista.id)"
                        class="press absolute right-3 top-3 rounded-full bg-surface/90 p-2 text-gray-700 opacity-0 shadow-sm backdrop-blur transition-opacity duration-150 hover:bg-surface focus-visible:opacity-100 group-hover:opacity-100 max-md:opacity-100"
                        aria-label="Editar lista">
                        <PencilIcon class="size-4" />
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
                <tbody class="bg-surface divide-y divide-gray-200">
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

        <div v-if="listas.data.length === 0" class="surface flex flex-col items-center px-6 py-16 text-center">
            <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                <GiftIcon class="size-6" aria-hidden="true" />
            </span>
            <h3 class="mt-4 font-semibold text-gray-900">Nenhuma lista encontrada</h3>
            <p class="mt-1 max-w-xs text-sm text-gray-500">
                Ajuste os filtros ou crie uma lista nova para começar.
            </p>
            <Link :href="route('listas.create')" class="btn-primary mt-6">Nova lista</Link>
        </div>

        <div v-if="listas.last_page > 1" class="mt-8 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="goToPage(listas.current_page - 1)" :disabled="listas.current_page === 1"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-surface hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Anterior
                </button>
                <button @click="goToPage(listas.current_page + 1)" :disabled="listas.current_page === listas.last_page"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-surface hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
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
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-surface text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
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
                                : 'bg-surface border-gray-300 text-gray-500 hover:bg-gray-50',
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                        ]">
                            {{ page }}
                        </button>

                        <button @click="goToPage(listas.current_page + 1)"
                            :disabled="listas.current_page === listas.last_page"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-surface text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
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
import GiftIcon from '@heroicons/vue/24/outline/GiftIcon'

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