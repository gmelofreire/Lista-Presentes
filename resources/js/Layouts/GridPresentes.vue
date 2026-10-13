<script setup>
import { StarIcon, CheckIcon, MagnifyingGlassIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';
import { GiftIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
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
    },
    listaId: {
        type: String,
        default: null
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
    <div>
        <!-- Barra de busca e filtros -->
        <div v-if="presentes.length > 0" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1 sm:max-w-md">
                <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                <input v-model="searchTerm" type="search" placeholder="Buscar presentes" aria-label="Buscar presentes"
                    class="block w-full pl-10 text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex">
                <select v-model="selectedCategoria" aria-label="Categoria" class="text-sm">
                    <option value="">Todas as categorias</option>
                    <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                        {{ categoria.nome }}
                    </option>
                </select>
                <select v-model="sortBy" aria-label="Ordenar" class="text-sm">
                    <option value="avaliacao-desc">Mais desejado</option>
                    <option value="avaliacao-asc">Menos desejado</option>
                    <option value="nome-asc">Nome (A-Z)</option>
                    <option value="nome-desc">Nome (Z-A)</option>
                    <option value="created-desc">Mais recentes</option>
                    <option value="created-asc">Mais antigos</option>
                </select>
            </div>
        </div>

        <ul v-if="paginatedPresentes.length > 0" role="list"
            class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <li v-for="(presente, i) in paginatedPresentes" :key="presente.id" :style="{ '--i': Math.min(i, 8) }">
                <button type="button" @click="openPresenteModal(presente)"
                    class="surface-link group flex h-full w-full flex-col overflow-hidden text-left">
                    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                        <img v-if="presente.image_url" :src="presente.image_url" :alt="presente.nome"
                            class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]"
                            :class="presente.comprado ? 'opacity-60 saturate-50' : ''" />
                        <div v-else class="flex size-full items-center justify-center text-gray-300">
                            <GiftIcon class="size-10" aria-hidden="true" />
                        </div>
                        <span v-if="presente.comprado"
                            class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-brand-600 px-2.5 py-1 text-xs font-medium text-white shadow-sm">
                            <CheckIcon class="size-3.5" aria-hidden="true" />
                            Comprado
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="line-clamp-2 font-semibold text-gray-900">{{ presente.nome }}</h3>
                        <p class="mt-1 text-sm font-medium text-gray-700">
                            {{ presente.preco ? 'R$ ' + Number(presente.preco).toFixed(2).replace('.', ',') : 'Sem preço' }}
                        </p>

                        <div class="mt-3 flex items-center gap-1.5 text-sm text-gray-600" v-if="presente.avaliacao">
                            <span class="flex" aria-hidden="true">
                                <StarIcon v-for="n in 5" :key="n" class="size-4"
                                    :class="n <= presente.avaliacao ? 'text-amber-400' : 'text-gray-200'" />
                            </span>
                            <span class="truncate">{{ avaliacao[presente.avaliacao] }}</span>
                        </div>

                        <div v-if="presente.categorias && presente.categorias.length > 0" class="mt-3 flex flex-wrap gap-1.5">
                            <span v-for="categoria in presente.categorias" :key="categoria.id"
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :style="{
                                    backgroundColor: categoria.hex_cor || '#6B7280',
                                    color: isDarkColor(categoria.hex_cor) ? '#FFFFFF' : '#18181b'
                                }">
                                {{ categoria.nome }}
                            </span>
                        </div>
                    </div>
                </button>
            </li>
        </ul>

        <!-- Estado vazio -->
        <div v-else class="surface flex flex-col items-center px-6 py-16 text-center">
            <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                <GiftIcon class="size-6" aria-hidden="true" />
            </span>
            <template v-if="searchTerm || selectedCategoria">
                <h3 class="mt-4 font-semibold text-gray-900">Nenhum presente encontrado</h3>
                <p class="mt-1 max-w-xs text-sm text-gray-500">Nada corresponde à busca ou ao filtro aplicado.</p>
                <button type="button" class="btn-secondary mt-6" @click="searchTerm = ''; selectedCategoria = ''">
                    Limpar filtros
                </button>
            </template>
            <template v-else>
                <h3 class="mt-4 font-semibold text-gray-900">Esta lista ainda está vazia</h3>
                <p class="mt-1 max-w-xs text-sm text-gray-500">
                    Adicione o primeiro presente. Dá para colar o link da loja e preencher os dados automaticamente.
                </p>
                <Link v-if="listaId" :href="route('presentes.create', { lista_id: listaId })" class="btn-primary mt-6">
                    Adicionar presente
                </Link>
            </template>
        </div>

        <!-- Paginacao -->
        <nav v-if="totalPages > 1" class="mt-8 flex items-center justify-between gap-4" aria-label="Paginação">
            <p class="hidden text-sm text-gray-500 sm:block">
                {{ paginationInfo.from }} a {{ paginationInfo.to }} de {{ paginationInfo.total }}
            </p>
            <div class="flex items-center gap-1 max-sm:mx-auto">
                <button type="button" @click="previousPage" :disabled="currentPage === 1" class="btn-ghost px-2.5" aria-label="Página anterior">
                    <ChevronLeftIcon class="size-5" />
                </button>
                <button v-for="page in visiblePages" :key="page" type="button" @click="goToPage(page)"
                    :aria-current="page === currentPage ? 'page' : undefined"
                    :class="page === currentPage ? 'bg-brand-600 text-white hover:bg-brand-600' : 'btn-ghost'"
                    class="press min-w-10 rounded-md px-3 py-2 text-sm font-medium">
                    {{ page }}
                </button>
                <button type="button" @click="nextPage" :disabled="currentPage === totalPages" class="btn-ghost px-2.5" aria-label="Próxima página">
                    <ChevronRightIcon class="size-5" />
                </button>
            </div>
        </nav>

        <ShowPresente v-if="showModal && selectedPresente" :key="modalKey" :presente="selectedPresente"
            @close="closePresenteModal" />
    </div>
</template>
