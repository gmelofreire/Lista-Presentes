<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { PlusIcon, MagnifyingGlassIcon, TagIcon, PencilIcon } from '@heroicons/vue/24/outline';

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

const isDark = (hex) => {
    if (!hex || hex.length < 7) return false;
    const h = hex.replace('#', '');
    const r = parseInt(h.substr(0, 2), 16), g = parseInt(h.substr(2, 2), 16), b = parseInt(h.substr(4, 2), 16);
    return (0.299 * r + 0.587 * g + 0.114 * b) / 255 < 0.5;
};

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
        <div class="space-y-8">
            <Alert v-if="hasSuccessMessage" type="success"
                :title="page.props.flash?.status || page.props.status || 'Sucesso'" />

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-3xl font-semibold text-gray-900">Categorias</h2>
                    <p class="mt-1 text-gray-600">Organize os presentes por tipo e cor para achar tudo mais rápido.</p>
                </div>
                <Link :href="route('categorias.create')" class="btn-primary self-start sm:self-auto">
                    <PlusIcon class="size-4" aria-hidden="true" />
                    Nova categoria
                </Link>
            </div>

            <div v-if="(categorias?.length || 0) > 0" class="relative max-w-md">
                <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                <input v-model="searchTerm" type="search" placeholder="Buscar por nome ou descrição"
                    aria-label="Buscar categorias" class="block w-full pl-10" />
            </div>

            <ul v-if="filteredCategorias.length > 0" role="list"
                class="stagger grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <li v-for="(categoria, i) in filteredCategorias" :key="categoria.id" :style="{ '--i': Math.min(i, 8) }">
                    <Link :href="route('categorias.edit', categoria)" class="surface-link group flex h-full items-center gap-4 p-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl text-lg font-semibold"
                            :style="{ backgroundColor: categoria.hex_cor, color: isDark(categoria.hex_cor) ? '#ffffff' : '#18181b' }"
                            aria-hidden="true">
                            {{ categoria.nome?.charAt(0)?.toUpperCase() }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-semibold text-gray-900">{{ categoria.nome }}</span>
                            <span class="block truncate text-sm text-gray-500">{{ categoria.descricao || 'Sem descrição' }}</span>
                            <span class="mt-0.5 block font-mono text-xs uppercase text-gray-400">{{ categoria.hex_cor }}</span>
                        </span>
                        <PencilIcon class="size-4 shrink-0 text-gray-300 transition-colors duration-150 group-hover:text-gray-600" aria-hidden="true" />
                        <span class="sr-only">Editar {{ categoria.nome }}</span>
                    </Link>
                </li>
            </ul>

            <div v-else class="surface flex flex-col items-center px-6 py-16 text-center">
                <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                    <TagIcon class="size-6" aria-hidden="true" />
                </span>
                <h3 class="mt-4 font-semibold text-gray-900">
                    {{ searchTerm ? 'Nenhuma categoria encontrada' : 'Você ainda não tem categorias' }}
                </h3>
                <p class="mt-1 max-w-xs text-sm text-gray-500">
                    {{ searchTerm ? 'Nada corresponde à busca.' : 'Crie categorias como Casa, Livros ou Viagem e use nos seus presentes.' }}
                </p>
                <button v-if="searchTerm" type="button" class="btn-secondary mt-6" @click="searchTerm = ''">Limpar busca</button>
                <Link v-else :href="route('categorias.create')" class="btn-primary mt-6">Criar categoria</Link>
            </div>
        </div>
    </AppLayout>
</template>
