<template>
    <div>
        <div class="mb-6">
            <div class="flex gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input v-model="searchTerm" @keyup.enter="performSearch" type="text"
                        placeholder="Buscar listas por nome ou descrição..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>

                <button @click="performSearch" :disabled="isSearching"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg v-if="isSearching" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    {{ isSearching ? 'Buscando...' : 'Buscar' }}
                </button>

                <button @click="clearSearch"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Limpar
                </button>
            </div>

            <div v-if="hasActiveSearch" class="mt-2 flex items-center text-sm text-indigo-600">
                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z" />
                </svg>
                Buscando por: "{{ filters.search }}"
            </div>

            <div class="mt-4 text-sm text-gray-600">
                <span>Mostrando {{ listas.data.length }} de {{ listas.total }} listas</span>
            </div>
        </div>

        <ul role="list" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <li v-for="lista in listas.data" :key="lista.id"
                class="col-span-1 divide-y divide-gray-200 rounded-2xl bg-blue-50 shadow-sm group hover:shadow-md transition-shadow duration-200">
                <div class="flex w-full items-center justify-between space-x-6 pt-9 rounded-t-lg bg-cover bg-center bg-no-repeat relative"
                    :style="{ backgroundImage: `url(${lista.image_url})` }">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-t-lg">
                            <div class="flex text-white justify-end p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <Link :href="route('listas.edit', lista.id)">
                                    <PencilIcon class="size-5" />
                                </Link>
                            </div>
                    </div>
                    <div class="flex-1 truncate rounded-lg py-3 relative z-10">
                        <div class="flex items-center space-x-3">
                            <h3 class="truncate text-sm font-bold text-white">{{ lista.nome }}</h3>
                        </div>
                        <p class="mt-1 truncate text-sm text-white">{{ lista.descricao }}</p>
                    </div>
                </div>
                <div>
                    <div class="-mt-px flex divide-x divide-gray-200">
                        <Link :href="route('listas.show', lista.id)" class="w-full">
                        <button
                            class="text-center w-full h-12 bg-indigo-400 text-white rounded-b-lg hover:bg-indigo-500 transition-colors">
                            Acessar
                        </button>
                        </Link>
                    </div>
                </div>
            </li>
        </ul>

        <div v-if="listas.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma lista encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ hasActiveSearch ? 'Tente ajustar o termo de busca ou limpar os filtros.' : 'Você ainda não criou nenhuma lista.' }}
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

// Props
const props = defineProps({
    listas: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

// Estado reativo
const searchTerm = ref(props.filters.search || '')
const isSearching = ref(false)

// Computed properties
const hasActiveSearch = computed(() => {
    return props.filters.search && props.filters.search.length > 0
})

const visiblePages = computed(() => {
    const pages = []
    const total = props.listas.last_page
    const current = props.listas.current_page

    // Mostrar até 5 páginas por vez
    let start = Math.max(1, current - 2)
    let end = Math.min(total, start + 4)

    // Ajustar se estivermos no final
    if (end - start < 4) {
        start = Math.max(1, end - 4)
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

// Funções
const performSearch = () => {
    if (isSearching.value) return

    isSearching.value = true

    router.get(route('listas.index'), {
        search: searchTerm.value.trim(),
        page: 1 // Resetar para primeira página ao buscar
    }, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            isSearching.value = false
        }
    })
}

const clearSearch = () => {
    searchTerm.value = ''

    router.get(route('listas.index'), {}, {
        preserveState: true,
        replace: true
    })
}

const goToPage = (page) => {
    if (page >= 1 && page <= props.listas.last_page) {
        router.get(route('listas.index'), {
            search: props.filters.search,
            page: page
        }, {
            preserveState: true,
            replace: true
        })
    }
}
</script>