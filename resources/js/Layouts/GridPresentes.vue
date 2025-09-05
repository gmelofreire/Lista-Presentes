<script setup>
import { StarIcon } from '@heroicons/vue/20/solid';
import { ref, computed, nextTick, watch } from 'vue';
import ShowPresente from '@/Components/ShowPresente.vue';

const props = defineProps({
    presentes: {
        type: [Array, Object],
        default: () => []
    },
    categorias: {
        type: [Array, Object],
        default: () => []
    }
})

const showModal = ref(false)
const selectedPresente = ref(null)
const modalKey = ref(0)
const searchTerm = ref('')
const sortBy = ref('avaliacao-desc')
const selectedCategoria = ref('')

const openPresenteModal = async (presente) => {
    showModal.value = false
    selectedPresente.value = null

    await nextTick()

    modalKey.value++

    selectedPresente.value = presente
    showModal.value = true
}

const closePresenteModal = () => {
    showModal.value = false
    selectedPresente.value = null
}

const handleModalClose = () => {
    setTimeout(() => {
        showModal.value = false
        selectedPresente.value = null
    }, 100)
}

const avaliacao = {
    1: 'Não quero',
    2: 'Pode ser',
    3: 'Quero',
    4: 'Gostaria de receber',
    5: 'Quero muito'
}

// Função para detectar se uma cor hexadecimal é escura
const isDarkColor = (hexColor) => {
    if (!hexColor) return false
    const hex = hexColor.replace('#', '')
    const r = parseInt(hex.substr(0, 2), 16)
    const g = parseInt(hex.substr(2, 2), 16)
    const b = parseInt(hex.substr(4, 2), 16)
    const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000
    return brightness < 128
}

const currentPage = ref(1)
const itemsPerPage = 15

// Propriedade computada para filtrar e ordenar presentes
const filteredAndSortedPresentes = computed(() => {
    let filtered = props.presentes

    // Aplicar filtro de busca
    if (searchTerm.value) {
        filtered = filtered.filter(presente =>
            presente.nome.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            presente.descricao?.toLowerCase().includes(searchTerm.value.toLowerCase())
        )
    }

    // Aplicar filtro por categoria
    if (selectedCategoria.value) {
        filtered = filtered.filter(presente =>
            presente.categorias && presente.categorias.some(categoria => categoria.id === selectedCategoria.value)
        )
    }

    // Aplicar ordenação
    const sorted = [...filtered].sort((a, b) => {
        switch (sortBy.value) {
            case 'nome-asc':
                return a.nome.localeCompare(b.nome)
            case 'nome-desc':
                return b.nome.localeCompare(a.nome)
            case 'avaliacao-desc': // Mais desejado
                return (b.avaliacao || 0) - (a.avaliacao || 0)
            case 'avaliacao-asc': // Menos desejado
                return (a.avaliacao || 0) - (b.avaliacao || 0)
            case 'created-desc': // Mais recentes
                return new Date(b.created_at || 0) - new Date(a.created_at || 0)
            case 'created-asc': // Mais antigos
                return new Date(a.created_at || 0) - new Date(b.created_at || 0)
            default:
                return 0
        }
    })

    return sorted
})

const totalPages = computed(() => Math.ceil(filteredAndSortedPresentes.value.length / itemsPerPage))

const paginatedPresentes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return filteredAndSortedPresentes.value.slice(start, end)
})

const visiblePages = computed(() => {
    const pages = []
    const total = totalPages.value
    const current = currentPage.value

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

const paginationInfo = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage + 1
    const end = Math.min(currentPage.value * itemsPerPage, filteredAndSortedPresentes.value.length)
    return {
        from: start,
        to: end,
        total: filteredAndSortedPresentes.value.length
    }
})

const resetToFirstPage = () => {
    currentPage.value = 1
}

watch([searchTerm, sortBy, selectedCategoria], resetToFirstPage)

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}
</script>

<template>
    <div class="p-4 sm:p-6">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-4 sm:items-center">
                <!-- Busca -->
                <div class="flex-1 max-w-xl">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input v-model="searchTerm" type="text" placeholder="Buscar presentes..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                    </div>
                </div>

                <!-- Filtros -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2">
                    <!-- Filtro por categoria -->
                    <div class="flex-shrink-0">
                        <select v-model="selectedCategoria"
                            class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todas as categorias</option>
                            <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                {{ categoria.nome }}
                            </option>
                        </select>
                    </div>

                    <!-- Ordenação -->
                    <div class="flex-shrink-0">
                        <select v-model="sortBy"
                            class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="avaliacao-desc">Mais desejado</option>
                            <option value="avaliacao-asc">Menos desejado</option>
                            <option value="nome-asc">Nome (A-Z)</option>
                            <option value="nome-desc">Nome (Z-A)</option>
                            <option value="created-desc">Mais recentes</option>
                            <option value="created-asc">Mais antigos</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <ul role="list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <li v-for="presente in paginatedPresentes" :key="presente.id"
                class="flex flex-col text-center w-full max-w-sm mx-auto sm:mx-0">
                <div class="group relative cursor-pointer" @click="openPresenteModal(presente)">
                    <div class="flex aspect-square w-full items-end justify-between space-x-6 pt-9 rounded-xl bg-cover bg-center bg-no-repeat relative group-hover:opacity-75 transition-opacity duration-200"
                        :style="{ backgroundImage: `url(${presente.image_url})` }">
                        <div
                            class="absolute flex justify-center inset-0 bg-gradient-to-t items-end from-black/60 via-black/5 to-transparent rounded-xl">
                            <div
                                class="flex items-center text-white font-extrabold mb-3 sm:mb-5 text-sm sm:text-base px-2">
                                <span class="truncate">{{ presente.avaliacao }}</span>
                                <StarIcon class="size-3 sm:size-4 ml-1 mr-2 text-yellow-400 flex-shrink-0" />
                                <span class="truncate">{{ avaliacao[presente.avaliacao] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-6 px-2 sm:px-0">
                        <h3 class="mt-1 font-semibold text-gray-900 text-sm sm:text-base line-clamp-2">
                            <span class="hover:text-indigo-600 transition-colors duration-200">
                                {{ presente.nome }}
                            </span>
                        </h3>
                        <p class="mt-1 text-gray-900 font-medium text-sm sm:text-base">R$ {{ presente.preco.toFixed(2)
                            }}</p>
                        <div v-if="presente.comprado" class="mt-2">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Comprado
                            </span>
                        </div>
                        
                        <div v-if="presente.categorias && presente.categorias.length > 0" class="mt-2 flex flex-wrap gap-1 justify-center">
                            <span v-for="categoria in presente.categorias" :key="categoria.id"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                :style="{
                                    backgroundColor: categoria.hex_cor || '#6B7280',
                                    color: isDarkColor(categoria.hex_cor) ? '#FFFFFF' : '#000000'
                                }">
                                {{ categoria.nome }}
                            </span>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Mensagem quando não há presentes -->
        <div v-if="filteredAndSortedPresentes.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
                {{ (searchTerm || selectedCategoria) ? 'Nenhum presente encontrado' : 'Nenhum presente encontrado' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ (searchTerm || selectedCategoria) ? 'Nenhum presente corresponde aos filtros aplicados' : 'Ainda não há presentes nesta lista.' }}
            </p>
            
        </div>

        <!-- Controles de paginação responsivos -->
        <div v-if="totalPages > 1"
            class="mt-8 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
            <!-- Paginação mobile -->
            <div class="flex justify-center w-full sm:hidden">
                <div class="flex items-center space-x-2">
                    <button @click="previousPage" :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        ← Anterior
                    </button>
                    <span class="text-sm text-gray-700 px-2">
                        {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        Próxima →
                    </button>
                </div>
            </div>

            <!-- Paginação desktop -->
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium">{{ paginationInfo.from }}</span>
                        a
                        <span class="font-medium">{{ paginationInfo.to }}</span>
                        de
                        <span class="font-medium">{{ paginationInfo.total }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button @click="previousPage" :disabled="currentPage === 1"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            <span class="sr-only">Anterior</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="[
                            page === currentPage
                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors duration-200'
                        ]">
                            {{ page }}
                        </button>

                        <button @click="nextPage" :disabled="currentPage === totalPages"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
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
        <ShowPresente v-if="showModal && selectedPresente" :key="modalKey" :presente="selectedPresente"
            @close="closePresenteModal" />
    </div>
</template>
