<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    PlusIcon, 
    UserPlusIcon, 
    UserGroupIcon, 
    GiftIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = page.props.auth.user;

defineProps({
    title: String,
    amizadesPendentes: {
        type: Array,
        default: () => []
    },
    listasAtivas: {
        type: Array,
        default: () => []
    },
    gruposRecentes: {
        type: Array,
        default: () => []
    },
});
</script>

<template>
    <Head :title="title" />
    <AppLayout :title="title">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Welcome Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Olá, {{ user.name }}! 👋</h2>
                            <p class="text-gray-600 mt-1">Bem-vindo ao seu painel de presentes.</p>
                        </div>
                        <div class="flex gap-3">
                            <Link :href="route('listas.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Nova Lista
                            </Link>
                            <Link :href="route('amizades.index')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <UserPlusIcon class="w-4 h-4 mr-2" />
                                Buscar Amigos
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests Alert (if any) -->
                <div v-if="amizadesPendentes.length > 0" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <UserPlusIcon class="h-5 w-5 text-yellow-400" aria-hidden="true" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Você tem <span class="font-bold">{{ amizadesPendentes.length }}</span> solicitações de amizade pendentes.
                                <Link :href="route('amizades.index')" class="font-medium underline hover:text-yellow-600 ml-1">Ver solicitações</Link>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- Recent Lists -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center px-1">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                <GiftIcon class="w-5 h-5 text-indigo-500" />
                                Suas Listas Recentes
                            </h3>
                            <Link :href="route('listas.index')" class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
                                Ver todas <ArrowRightIcon class="w-3 h-3 ml-1" />
                            </Link>
                        </div>

                        <div v-if="listasAtivas.length > 0" class="grid gap-4">
                            <Link v-for="lista in listasAtivas" :key="lista.id" :href="route('listas.show', lista.id)" 
                                class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow group">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ lista.nome }}</h4>
                                        <p class="text-sm text-gray-500 mt-1 line-clamp-1">{{ lista.descricao || 'Sem descrição' }}</p>
                                    </div>
                                    <span v-if="lista.grupo" class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                        {{ lista.grupo.nome }}
                                    </span>
                                </div>
                                <div class="mt-3 flex items-center text-xs text-gray-400">
                                    <span>Criada em {{ new Date(lista.created_at).toLocaleDateString() }}</span>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="bg-white border border-gray-200 rounded-lg p-8 text-center text-gray-500">
                            <p>Nenhuma lista ativa encontrada.</p>
                            <Link :href="route('listas.create')" class="text-indigo-600 hover:underline mt-2 inline-block text-sm">Criar primeira lista</Link>
                        </div>
                    </div>

                    <!-- Recent Groups -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center px-1">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                <UserGroupIcon class="w-5 h-5 text-emerald-500" />
                                Seus Grupos
                            </h3>
                            <Link :href="route('grupos.index')" class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center">
                                Ver todos <ArrowRightIcon class="w-3 h-3 ml-1" />
                            </Link>
                        </div>

                        <div v-if="gruposRecentes.length > 0" class="grid gap-4">
                            <Link v-for="grupo in gruposRecentes" :key="grupo.id" :href="route('grupos.show', grupo.id)"
                                class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold shrink-0">
                                        {{ grupo.nome.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ grupo.nome }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ grupo.descricao || 'Grupo de amigos' }}</p>
                                    </div>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="bg-white border border-gray-200 rounded-lg p-8 text-center text-gray-500">
                            <p>Você não participa de nenhum grupo.</p>
                            <Link :href="route('grupos.create')" class="text-indigo-600 hover:underline mt-2 inline-block text-sm">Criar novo grupo</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
