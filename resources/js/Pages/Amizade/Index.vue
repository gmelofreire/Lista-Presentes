<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Alert from '@/Components/Alert.vue';
import { computed, ref, watch } from 'vue';
import { 
    MagnifyingGlassIcon, 
    UserPlusIcon, 
    EyeIcon, 
    UserMinusIcon,
    CheckIcon,
    XMarkIcon,
    UsersIcon
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

const hasSuccessMessage = computed(() => page.props.flash?.status || page.props.status);

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
        <div class="space-y-8">
            <Alert v-if="hasSuccessMessage" type="success"
                :title="page.props.flash?.status || page.props.status || 'Sucesso'" />

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-3xl font-semibold text-gray-900">Amigos</h2>
                    <p class="mt-1 text-gray-600">Encontre pessoas e veja as listas de quem você acompanha.</p>
                </div>
                <button type="button" @click="showRequestsModal = true" class="btn-secondary relative self-start sm:self-auto">
                    <UserPlusIcon class="size-5" aria-hidden="true" />
                    Solicitações
                    <span v-if="amizadesPendentes.length > 0"
                        class="absolute -right-2 -top-2 flex size-5 items-center justify-center rounded-full bg-brand-600 text-[11px] font-semibold text-white ring-2 ring-gray-50">
                        {{ amizadesPendentes.length }}
                    </span>
                </button>
            </div>

            <!-- Busca global -->
            <div class="relative z-20 max-w-2xl">
                <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                <input v-model="searchQuery" type="search" autocomplete="off" aria-label="Buscar pessoas"
                    class="block w-full py-3 pl-10 pr-10" placeholder="Buscar pessoas por nome ou @username" />
                <button v-if="searchQuery" type="button" @click="clearSearch"
                    class="press absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <span class="sr-only">Limpar busca</span>
                    <XMarkIcon class="size-4" />
                </button>

                <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-out duration-100"
                    leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="searchQuery.length >= 2 && (globalUsers.length > 0 || isSearching)"
                        class="absolute left-0 right-0 top-full mt-2 overflow-hidden rounded-xl border border-gray-200 bg-surface shadow-lg">
                        <p v-if="isSearching" class="px-4 py-4 text-center text-sm text-gray-500">Buscando...</p>
                        <ul v-else class="divide-y divide-gray-100">
                            <li v-for="u in globalUsers" :key="u.id">
                                <Link :href="route('amizades.show', u.id)"
                                    class="flex items-center gap-3 px-4 py-3 transition-colors duration-150 hover:bg-gray-50">
                                    <img :src="u.avatar ?? '/img/default_profile.png'" alt=""
                                        class="size-10 rounded-full bg-gray-100 object-cover" />
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-gray-900">{{ u.name }}</span>
                                        <span class="block truncate text-xs text-gray-500">@{{ u.username }}</span>
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </Transition>
                <p v-if="searchQuery.length >= 2 && !isSearching && globalUsers.length === 0"
                    class="mt-2 text-sm text-gray-500">Ninguém encontrado com esse nome.</p>
            </div>

            <!-- Meus amigos -->
            <section>
                <h3 class="mb-5 text-xl font-semibold text-gray-900">
                    Seus amigos
                    <span class="ml-1 text-base font-normal text-gray-500">{{ friendsData.length }}</span>
                </h3>

                <ul v-if="filteredFriends.length > 0" role="list"
                    class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <li v-for="(friend, i) in filteredFriends" :key="friend.id" :style="{ '--i': Math.min(i, 8) }">
                        <div class="surface-link flex h-full flex-col overflow-hidden">
                            <Link :href="route('amizades.show', friend.id)" class="flex flex-1 flex-col items-center px-5 pb-5 pt-6 text-center">
                                <img :src="friend.perfil?.image_url ?? '/img/default_profile.png'" :alt="friend.name"
                                    class="size-24 rounded-full bg-gray-100 object-cover ring-4 ring-gray-50" />
                                <h4 class="mt-4 w-full truncate font-semibold text-gray-900">{{ friend.name }}</h4>
                                <p class="w-full truncate text-sm text-gray-500">@{{ friend.username }}</p>
                                <p v-if="friend.grupos?.length" class="mt-3 text-xs font-medium text-brand-700">
                                    {{ friend.grupos.length }} {{ friend.grupos.length === 1 ? 'grupo em comum' : 'grupos em comum' }}
                                </p>
                            </Link>
                            <div class="grid grid-cols-2 border-t border-gray-100">
                                <Link :href="route('amizades.show', friend.id)"
                                    class="flex items-center justify-center gap-2 border-r border-gray-100 py-3 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-50">
                                    <EyeIcon class="size-4" aria-hidden="true" />
                                    Perfil
                                </Link>
                                <button type="button" @click="removeFriend(friend)"
                                    class="flex items-center justify-center gap-2 py-3 text-sm font-medium text-gray-500 transition-colors duration-150 hover:bg-red-50 hover:text-red-600">
                                    <UserMinusIcon class="size-4" aria-hidden="true" />
                                    Remover
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>

                <div v-else class="surface flex flex-col items-center px-6 py-16 text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                        <UsersIcon class="size-6" aria-hidden="true" />
                    </span>
                    <h4 class="mt-4 font-semibold text-gray-900">Você ainda não tem amigos por aqui</h4>
                    <p class="mt-1 max-w-xs text-sm text-gray-500">Busque alguém pelo nome ou @username e envie uma solicitação.</p>
                </div>
            </section>
        </div>

        <!-- Solicitacoes -->
        <Modal :show="showRequestsModal" max-width="md" @close="showRequestsModal = false">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Solicitações de amizade</h2>
                    <button type="button" class="press rounded-full p-1.5 text-gray-500 hover:bg-gray-100" @click="showRequestsModal = false">
                        <span class="sr-only">Fechar</span>
                        <XMarkIcon class="size-5" />
                    </button>
                </div>

                <div v-if="amizadesPendentes.length === 0" class="py-10 text-center">
                    <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <UserPlusIcon class="size-6" aria-hidden="true" />
                    </span>
                    <p class="mt-3 text-sm text-gray-500">Nenhuma solicitação pendente.</p>
                </div>

                <ul v-else class="mt-4 max-h-[60vh] divide-y divide-gray-100 overflow-y-auto">
                    <li v-for="request in amizadesPendentes" :key="request.usuario_id" class="flex items-center gap-3 py-3">
                        <Link :href="route('amizades.show', request.user.id)" class="flex min-w-0 flex-1 items-center gap-3">
                            <img :src="request.user.perfil?.image_url ?? '/img/default_profile.png'" alt=""
                                class="size-12 shrink-0 rounded-full bg-gray-100 object-cover" />
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-gray-900">{{ request.user.name }}</span>
                                <span class="block truncate text-xs text-gray-500">@{{ request.user.username }}</span>
                            </span>
                        </Link>
                        <button type="button" @click="acceptRequest(request.user.id)"
                            class="press rounded-lg bg-brand-600 p-2 text-white hover:bg-brand-700" title="Aceitar">
                            <span class="sr-only">Aceitar</span>
                            <CheckIcon class="size-5" />
                        </button>
                        <button type="button" @click="rejectRequest(request.user.id)"
                            class="press rounded-lg border border-gray-300 bg-surface p-2 text-gray-600 hover:bg-gray-50" title="Recusar">
                            <span class="sr-only">Recusar</span>
                            <XMarkIcon class="size-5" />
                        </button>
                    </li>
                </ul>
            </div>
        </Modal>
    </AppLayout>
</template>
