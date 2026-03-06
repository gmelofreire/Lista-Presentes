<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { computed, ref, watch } from 'vue';
import { 
    MagnifyingGlassIcon, 
    UserPlusIcon, 
    EyeIcon, 
    UserMinusIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    title: {
        type: String,
    },
    amizades: {
        type: [Array, Object],
    },
    amizadesPendentes: {
        type: [Array, Object],
        default: () => [],
    },
})

const page = usePage();

const showRequestsModal = ref(false);

const friendsData = computed(() => {
    if (Array.isArray(props.amizades)) return props.amizades;
    return props.amizades?.data || [];
});

const searchQuery = ref('');
const globalUsers = ref([]);
const isSearching = ref(false);
let debounceTimer = null;

const filteredFriends = computed(() => friendsData.value);

const performGlobalSearch = async () => {
    if (searchQuery.value.length < 2) {
        globalUsers.value = [];
        return;
    }
    
    isSearching.value = true;
    try {
        const response = await axios.get(route('usuarios.buscar'), {
            params: { q: searchQuery.value }
        });
        
        // Exibe todos os usuários encontrados (amigos ou não), limitado a 5
        globalUsers.value = response.data.slice(0, 5);
        
    } catch (error) {
        console.error('Erro ao buscar usuários:', error);
    } finally {
        isSearching.value = false;
    }
};

watch(searchQuery, (newVal) => {
    if (!newVal) {
        globalUsers.value = [];
    }
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        performGlobalSearch();
    }, 500);
});

const clearSearch = () => {
    searchQuery.value = '';
    globalUsers.value = [];
};

const removeFriend = (friend) => {
    if (confirm(`Tem certeza que deseja remover ${friend.name}?`)) {
        router.delete(route('amizades.destroy', friend.id));
    }
};

const acceptRequest = (userId) => {
    router.put(route('amizades.update', userId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: close modal if empty
        }
    });
};

const rejectRequest = (userId) => {
    if (confirm('Tem certeza que deseja recusar esta solicitação?')) {
        router.delete(route('amizades.destroy', userId), {
            preserveScroll: true
        });
    }
};
</script>

<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Alert v-if="hasSuccessMessage" type="success"
                    :title="page.props.flash?.status || page.props.status || 'Sucesso'" class="mb-6" />
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Gerenciar Amigos</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Encontre amigos e gerencie suas solicitações de amizade.</p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="showRequestsModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 relative">
                            <UserPlusIcon class="w-5 h-5" />
                            Solicitações
                            <span v-if="amizadesPendentes.length > 0" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-slate-900">
                                {{ amizadesPendentes.length }}
                            </span>
                        </button>
                    </div>
                </div>
                
                <div class="relative max-w-2xl z-30">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5" />
                        <input v-model="searchQuery" 
                               class="w-full pl-10 pr-10 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none text-slate-800 dark:text-slate-100 shadow-sm transition-all" 
                               placeholder="Buscar usuários no sistema (nome ou @username)..." 
                               type="text"
                               autocomplete="off"
                        />
                        <button v-if="searchQuery" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer">
                            <span class="sr-only">Limpar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Dropdown de Resultados -->
                    <div v-if="searchQuery && (globalUsers.length > 0 || isSearching)" 
                         class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                        
                        <div v-if="isSearching" class="p-4 text-center text-sm text-slate-500">
                            <span class="animate-pulse flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Buscando...
                            </span>
                        </div>
                        
                        <div v-else>
                            <Link v-for="user in globalUsers" 
                                  :key="user.id"
                                  :href="route('amizades.show', user.id)"
                                  class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b last:border-0 border-slate-100 dark:border-slate-700/50 group">
                                <img :src="user.avatar ?? '/img/default_profile.png'" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700" />
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-800 dark:text-white text-sm group-hover:text-indigo-600 transition-colors">{{ user.name }}</span>
                                    <span class="text-xs text-slate-500">@{{ user.username }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
                
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    Você tem {{ friendsData.length }} amigos
                </p>
            </div>
            
            <!-- Meus Amigos (Lista Fixa) -->
            <div v-if="filteredFriends.length > 0" class="mb-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="friend in filteredFriends" :key="friend.id" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
                        <div class="p-6 text-center">
                            <img :src="friend.perfil?.image_url ?? '/img/default_profile.png'" :alt="friend.name" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-slate-50 dark:border-slate-800 group-hover:scale-105 transition-transform object-cover"/>
                            <h4 class="font-bold text-slate-800 dark:text-white text-lg">{{ friend.name }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">@{{ friend.username }}</p>
                            <!-- Status omitted as data is not available -->
                        </div>
                        <div class="grid grid-cols-2 border-t border-slate-200 dark:border-slate-800">
                            <Link :href="route('amizades.show', friend.id)" class="flex items-center justify-center gap-2 py-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 border-r border-slate-200 dark:border-slate-800">
                                <EyeIcon class="w-4 h-4" />
                                Perfil
                            </Link>
                            <button @click="removeFriend(friend)" class="flex items-center justify-center gap-2 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10">
                                <UserMinusIcon class="w-4 h-4" />
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12">
                 <p class="text-slate-500 dark:text-slate-400">Você ainda não tem amigos.</p>
            </div>
        </div>

        <!-- Modal de Solicitações -->
        <Modal :show="showRequestsModal" @close="showRequestsModal = false">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <UserPlusIcon class="w-6 h-6 text-indigo-600" />
                        Solicitações de Amizade
                    </h2>
                    <button @click="showRequestsModal = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>
                
                <div v-if="amizadesPendentes.length === 0" class="text-center py-8">
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                        <UserPlusIcon class="w-8 h-8 text-slate-400" />
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Nenhuma solicitação pendente.</p>
                </div>
                
                <div v-else class="space-y-3 max-h-[60vh] overflow-y-auto pr-1 custom-scrollbar">
                    <div v-for="request in amizadesPendentes" :key="request.id" 
                         class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl hover:shadow-sm transition-shadow">
                        
                        <Link :href="route('amizades.show', request.user.id)" class="flex items-center gap-3 flex-1 min-w-0 group">
                            <img :src="request.user.perfil?.image_url ?? '/img/default_profile.png'" 
                                 class="w-12 h-12 rounded-full object-cover border-2 border-slate-100 dark:border-slate-700 group-hover:border-indigo-100 transition-colors" />
                            <div class="truncate">
                                <h4 class="font-bold text-slate-800 dark:text-white group-hover:text-indigo-600 transition-colors truncate">
                                    {{ request.user.name }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">@{{ request.user.username }}</p>
                            </div>
                        </Link>
                        
                        <div class="flex items-center gap-2 ml-3">
                            <button @click="acceptRequest(request.user.id)" 
                                    class="p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg transition-colors"
                                    title="Aceitar">
                                <CheckIcon class="w-5 h-5" />
                            </button>
                            <button @click="rejectRequest(request.user.id)" 
                                    class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors"
                                    title="Recusar">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>