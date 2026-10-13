<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GridListas from '@/Layouts/GridListas.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Alert from '@/Components/Alert.vue';
import { computed, ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { MagnifyingGlassIcon } from '@heroicons/vue/20/solid';

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
        <div class="space-y-8">
            <Alert v-if="hasSuccessMessage" type="success"
                :title="page.props.flash?.status || page.props.status || 'Sucesso'" />

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-3xl font-semibold text-gray-900">Minhas listas</h2>
                    <p class="mt-1 text-gray-600">Tudo o que você criou ou foi convidado a acompanhar.</p>
                </div>
                <Link :href="route('listas.create')" class="btn-primary self-start sm:self-auto">
                    <PlusIcon class="size-4" aria-hidden="true" />
                    Nova lista
                </Link>
            </div>

            <form class="surface space-y-3 p-4" @submit.prevent="applyFilters">
                <div class="relative">
                    <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                    <input type="search" v-model="form.search" placeholder="Buscar por nome ou descrição"
                        aria-label="Buscar listas" class="w-full pl-10" />
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                        <select v-model="form.status" aria-label="Status" class="text-sm">
                            <option value="">Todos os status</option>
                            <option value="ativa">Ativa</option>
                            <option value="arquivada">Arquivada</option>
                            <option value="concluida">Concluída</option>
                        </select>
                        <select v-model="form.grupo_id" aria-label="Grupo" class="text-sm">
                            <option value="">Todos os grupos</option>
                            <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">{{ grupo.nome }}</option>
                        </select>
                        <select v-model="form.sort" aria-label="Ordenar por" class="text-sm">
                            <option value="created_at">Data de criação</option>
                            <option value="nome">Nome</option>
                            <option value="data_evento">Data do evento</option>
                        </select>
                        <select v-model="form.direction" aria-label="Direção" class="text-sm">
                            <option value="desc">Decrescente</option>
                            <option value="asc">Crescente</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">Filtrar</button>
                        <button type="button" class="btn-secondary" @click="clearFilters">Limpar</button>
                    </div>
                </div>
            </form>

            <GridListas :listas="listas" :view-mode="viewMode" :filters="filters" />
        </div>
    </AppLayout>
</template>
