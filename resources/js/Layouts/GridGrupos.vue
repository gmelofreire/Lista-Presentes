<template>
    <div>
        <form class="mb-6 flex flex-col gap-2 sm:flex-row" @submit.prevent="performSearch">
            <div class="relative flex-1 sm:max-w-md">
                <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                <input v-model="searchTerm" type="search" placeholder="Buscar por nome ou descrição"
                    aria-label="Buscar grupos" class="block w-full pl-10" />
            </div>
            <div class="flex gap-2">
                <button type="submit" :disabled="isSearching" class="btn-primary">
                    {{ isSearching ? 'Buscando...' : 'Buscar' }}
                </button>
                <button v-if="hasActiveSearch || searchTerm" type="button" class="btn-secondary" @click="clearSearch">Limpar</button>
            </div>
        </form>

        <p class="mb-5 text-sm text-gray-500">
            <template v-if="hasActiveSearch">Resultados para "{{ filters.search }}": </template>
            {{ grupos?.total || 0 }} {{ (grupos?.total || 0) === 1 ? 'grupo' : 'grupos' }}
        </p>

        <ul v-if="(grupos?.data?.length || 0) > 0" role="list"
            class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <li v-for="(grupo, i) in grupos.data" :key="grupo.id" :style="{ '--i': Math.min(i, 8) }">
                <div class="surface-link group relative flex h-full flex-col overflow-hidden">
                    <Link :href="route('grupos.show', grupo.id)" class="block">
                        <div class="aspect-[16/9] overflow-hidden bg-brand-100">
                            <img v-if="grupo.image_url" :src="grupo.image_url" :alt="`Imagem do grupo ${grupo.nome}`"
                                class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" />
                            <div v-else class="flex size-full items-center justify-center text-brand-700">
                                <UserGroupIcon class="size-10" aria-hidden="true" />
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="truncate font-semibold text-gray-900">{{ grupo.nome }}</h3>
                            <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-sm text-gray-500">
                                {{ grupo.descricao || 'Sem descrição' }}
                            </p>
                        </div>
                    </Link>
                    <Link :href="route('grupos.edit', grupo.id)"
                        class="press absolute right-3 top-3 rounded-full bg-surface/90 p-2 text-gray-700 opacity-0 shadow-sm backdrop-blur hover:bg-surface focus-visible:opacity-100 group-hover:opacity-100 max-md:opacity-100"
                        aria-label="Editar grupo">
                        <PencilIcon class="size-4" />
                    </Link>
                </div>
            </li>
        </ul>

        <div v-else class="surface flex flex-col items-center px-6 py-16 text-center">
            <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                <UserGroupIcon class="size-6" aria-hidden="true" />
            </span>
            <h3 class="mt-4 font-semibold text-gray-900">
                {{ hasActiveSearch ? 'Nenhum grupo encontrado' : 'Você ainda não tem grupos' }}
            </h3>
            <p class="mt-1 max-w-xs text-sm text-gray-500">
                {{ hasActiveSearch ? 'Tente outro termo ou limpe a busca.' : 'Crie um grupo para reunir amigos e compartilhar listas com todos de uma vez.' }}
            </p>
            <Link v-if="!hasActiveSearch" :href="route('grupos.create')" class="btn-primary mt-6">Criar grupo</Link>
        </div>

        <nav v-if="(grupos?.last_page || 0) > 1" class="mt-8 flex items-center justify-between gap-4" aria-label="Paginação">
            <p class="hidden text-sm text-gray-500 sm:block">
                {{ grupos?.from || 0 }} a {{ grupos?.to || 0 }} de {{ grupos?.total || 0 }}
            </p>
            <div class="flex items-center gap-1 max-sm:mx-auto">
                <button type="button" class="btn-ghost px-3" :disabled="(grupos?.current_page || 1) === 1"
                    @click="goToPage((grupos?.current_page || 1) - 1)">Anterior</button>
                <button v-for="page in visiblePages" :key="page" type="button" @click="goToPage(page)"
                    :aria-current="page === grupos?.current_page ? 'page' : undefined"
                    :class="page === grupos?.current_page ? 'bg-brand-600 text-white hover:bg-brand-600' : 'btn-ghost'"
                    class="press min-w-10 rounded-md px-3 py-2 text-sm font-medium">
                    {{ page }}
                </button>
                <button type="button" class="btn-ghost px-3"
                    :disabled="(grupos?.current_page || 1) === (grupos?.last_page || 1)"
                    @click="goToPage((grupos?.current_page || 1) + 1)">Próxima</button>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import PencilIcon from '@heroicons/vue/24/outline/PencilIcon'
import { MagnifyingGlassIcon, UserGroupIcon } from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
    grupos: {
        type: [Object, Array],
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

// Estado reativo
const searchTerm = ref(props.filters?.search || '')
const isSearching = ref(false)

// Computed properties
const hasActiveSearch = computed(() => {
    return props.filters?.search && props.filters.search.length > 0
})

const visiblePages = computed(() => {
    const pages = []
    const total = props.grupos?.last_page || 1
    const current = props.grupos?.current_page || 1

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

    router.get(route('grupos.index'), {
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

    router.get(route('grupos.index'), {}, {
        preserveState: true,
        replace: true
    })
}

const goToPage = (page) => {
    if (page >= 1 && page <= (props.grupos?.last_page || 1)) {
        router.get(route('grupos.index'), {
            search: props.filters?.search || '',
            page: page
        }, {
            preserveState: true,
            replace: true
        })
    }
}
</script>