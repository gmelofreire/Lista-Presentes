<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed } from 'vue';
import GridListasGrupo from '@/Layouts/GridListasGrupo.vue';
import Modal from '@/Components/Modal.vue';
import { TrashIcon, UserMinusIcon, UserPlusIcon, StarIcon, XMarkIcon, CheckIcon, PlusIcon } from '@heroicons/vue/20/solid';
import { UserGroupIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

defineProps({
    title: {
        type: String,
    },
    grupo: {
        type: [Array, Object],
    }
})

const page = usePage();
const user = page.props.auth.user;
const grupo = page.props.grupo || {};

const amigos = computed(() => page.props.amigos || []);

const hasSuccessMessage = computed(() => {
    return page.props.flash?.status || page.props.status;
});

const listasForGrid = computed(() => {
    const listas = grupo.listas || [];
    return {
        data: listas,
        total: listas.length,
        current_page: 1,
        last_page: 1,
        from: 1,
        to: listas.length
    };
});

const currentUserMember = computed(() => {
    return grupo.integrantes?.find(m => m.id === user.id);
});

const isAdmin = computed(() => {
    return currentUserMember.value?.pivot?.role === 'admin' || currentUserMember.value?.id === grupo.cadastrado_por;
});

const isOwner = computed(() => {
    return user.id === grupo.cadastrado_por;
});

const showInviteModal = ref(false);
const showMembersModal = ref(false);
const selectedFriends = ref([]);
const searchTerm = ref('');

const filteredFriends = computed(() => {
    const friendsList = amigos.value;
    if (!searchTerm.value) return friendsList;
    const term = searchTerm.value.toLowerCase();
    return friendsList.filter(amigo => 
        (amigo.name && amigo.name.toLowerCase().includes(term)) || 
        (amigo.perfil && amigo.perfil.username && amigo.perfil.username.toLowerCase().includes(term))
    );
});

const toggleFriend = (friendId) => {
    const index = selectedFriends.value.indexOf(friendId);
    if (index > -1) {
        selectedFriends.value.splice(index, 1);
    } else {
        selectedFriends.value.push(friendId);
    }
};

const isSelected = (friendId) => {
    return selectedFriends.value.includes(friendId);
};

const sendInvitations = async () => {
    if (selectedFriends.value.length === 0) {
        alert('Selecione pelo menos um amigo para convidar.');
        return;
    }
    
    try {
        const response = await axios.post(`/grupos/${grupo.id}/invite-friends`, {
            friend_ids: selectedFriends.value
        });
        
        if (response.data.success) {
            alert(response.data.message);
            showInviteModal.value = false;
            selectedFriends.value = [];
            window.location.reload();
        } else {
            alert(response.data.message || 'Erro ao convidar');
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Erro ao convidar');
    }
};

const leaveGroup = async () => {
    if (confirm('Tem certeza que deseja sair deste grupo?')) {
        try {
            const response = await axios.post(`/grupos/${grupo.id}/leave`);
            alert(response.data.message || 'Você saiu do grupo!');
            window.location.href = route('grupos.index');
        } catch (error) {
            alert(error.response?.data?.message || error.response?.data?.error || 'Erro ao sair do grupo');
        }
    }
};

const removeMember = async (memberId, memberName) => {
    if (confirm(`Remover ${memberName} do grupo?`)) {
        try {
            const response = await axios.post(`/grupos/${grupo.id}/remove-member`, { member_id: memberId });
            alert(response.data.message || 'Membro removido!');
            window.location.reload();
        } catch (error) {
            alert(error.response?.data?.message || error.response?.data?.error || 'Erro ao remover membro');
        }
    }
};

const makeAdmin = async (memberId, memberName) => {
    if (confirm(`Tornar ${memberName} administrador do grupo? Você perderá o status de admin.`)) {
        try {
            const response = await axios.post(`/grupos/${grupo.id}/make-admin`, { member_id: memberId });
            alert(response.data.message || 'Admin atualizado!');
            window.location.reload();
        } catch (error) {
            alert(error.response?.data?.message || error.response?.data?.error || 'Erro ao promover');
        }
    }
};

const deleteGroup = async () => {
    if (confirm('EXCLUIR este grupo? Todos os dados serão perdidos.')) {
        try {
            await axios.delete(`/grupos/${grupo.id}`);
            router.visit(route('grupos.index'));
        } catch (error) {
            alert(error.response?.data?.message || error.response?.data?.error || 'Erro ao excluir grupo');
        }
    }
};
</script>

<template>
    <Head :title="title || 'Grupo'" />
    <AppLayout :title="title || 'Grupo'">
        <div v-if="grupo.id" class="space-y-8">
            <Alert v-if="hasSuccessMessage" type="success"
                :title="page.props.flash?.status || page.props.status || 'Sucesso'" />

            <!-- Cabecalho do grupo -->
            <section class="surface overflow-hidden">
                <div v-if="grupo.banner_url || grupo.image_url" class="h-40 bg-gray-100 sm:h-56">
                    <img :src="grupo.banner_url || grupo.image_url" :alt="`Imagem do grupo ${grupo.nome}`"
                        class="size-full object-cover" />
                </div>
                <div v-else class="flex h-28 items-center justify-center bg-brand-100 text-brand-700 sm:h-36">
                    <UserGroupIcon class="size-10" aria-hidden="true" />
                </div>

                <div class="space-y-5 p-5 sm:p-8">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-3xl font-semibold text-gray-900">{{ grupo.nome }}</h2>
                            <p v-if="grupo.descricao" class="mt-2 max-w-2xl whitespace-pre-wrap text-gray-600">{{ grupo.descricao }}</p>

                            <button type="button" class="press mt-4 inline-flex items-center gap-3 rounded-full py-1 pr-3 hover:bg-gray-50"
                                @click="showMembersModal = true">
                                <span class="flex -space-x-2">
                                    <template v-for="m in (grupo.integrantes || []).slice(0, 4)" :key="m.id">
                                        <img v-if="m.perfil?.image_url" :src="m.perfil.image_url" alt=""
                                            class="size-8 rounded-full object-cover ring-2 ring-surface" />
                                        <span v-else
                                            class="flex size-8 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-800 ring-2 ring-surface">
                                            {{ m.name ? m.name.charAt(0).toUpperCase() : '?' }}
                                        </span>
                                    </template>
                                </span>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ grupo.integrantes ? grupo.integrantes.length : 0 }}
                                    {{ (grupo.integrantes?.length || 0) === 1 ? 'membro' : 'membros' }}
                                </span>
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            <Link :href="route('listas.create', { grupo_id: grupo.id })" class="btn-primary">
                                <PlusIcon class="size-4" aria-hidden="true" />
                                Nova lista
                            </Link>
                            <button v-if="isAdmin" type="button" @click="showInviteModal = true" class="btn-secondary">
                                <UserPlusIcon class="size-4" aria-hidden="true" />
                                Convidar
                            </button>
                            <button v-if="!isOwner" type="button" @click="leaveGroup" class="btn-ghost">
                                <UserMinusIcon class="size-4" aria-hidden="true" />
                                Sair
                            </button>
                            <button v-if="isOwner" type="button" @click="deleteGroup" class="btn-ghost text-red-600 hover:bg-red-50 hover:text-red-700">
                                <TrashIcon class="size-4" aria-hidden="true" />
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h3 class="mb-5 text-2xl font-semibold text-gray-900">Listas do grupo</h3>
                <GridListasGrupo :listas="listasForGrid" :grupo-id="grupo.id" />
            </section>
        </div>
    </AppLayout>

    <!-- Convidar amigos -->
    <Modal :show="showInviteModal" max-width="md" @close="showInviteModal = false">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Convidar amigos</h3>
                <button type="button" class="press rounded-full p-1.5 text-gray-500 hover:bg-gray-100" @click="showInviteModal = false">
                    <span class="sr-only">Fechar</span>
                    <XMarkIcon class="size-5" />
                </button>
            </div>

            <input v-model="searchTerm" type="search" placeholder="Buscar por nome ou @username"
                aria-label="Buscar amigos" class="mt-4 block w-full" />

            <ul class="mt-4 max-h-72 space-y-1 overflow-y-auto">
                <li v-if="filteredFriends.length === 0" class="py-8 text-center text-sm text-gray-500">
                    Nenhum amigo disponível para convidar.
                </li>
                <li v-for="amigo in filteredFriends" :key="amigo.id">
                    <button type="button" @click="toggleFriend(amigo.id)"
                        class="press flex w-full items-center gap-3 rounded-lg border p-3 text-left"
                        :class="isSelected(amigo.id) ? 'border-brand-600 bg-brand-50' : 'border-transparent hover:bg-gray-50'">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-800">
                            {{ amigo.name ? amigo.name.charAt(0).toUpperCase() : '?' }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-gray-900">{{ amigo.name }}</span>
                            <span class="block truncate text-xs text-gray-500">@{{ amigo.username }}</span>
                        </span>
                        <CheckIcon v-if="isSelected(amigo.id)" class="size-5 text-brand-700" aria-hidden="true" />
                    </button>
                </li>
            </ul>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="btn-secondary" @click="showInviteModal = false">Cancelar</button>
                <button type="button" class="btn-primary" :disabled="selectedFriends.length === 0" @click="sendInvitations">
                    Adicionar{{ selectedFriends.length ? ` (${selectedFriends.length})` : '' }}
                </button>
            </div>
        </div>
    </Modal>

    <!-- Membros -->
    <Modal :show="showMembersModal" max-width="md" @close="showMembersModal = false">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Membros</h3>
                <button type="button" class="press rounded-full p-1.5 text-gray-500 hover:bg-gray-100" @click="showMembersModal = false">
                    <span class="sr-only">Fechar</span>
                    <XMarkIcon class="size-5" />
                </button>
            </div>

            <ul class="mt-4 max-h-96 divide-y divide-gray-100 overflow-y-auto">
                <li v-for="membro in grupo.integrantes" :key="membro.id" class="flex items-center gap-3 py-3">
                    <img v-if="membro.perfil?.image_url" :src="membro.perfil.image_url" alt=""
                        class="size-10 shrink-0 rounded-full object-cover" />
                    <span v-else
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-800">
                        {{ membro.name ? membro.name.charAt(0).toUpperCase() : '?' }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium text-gray-900">{{ membro.name }}</span>
                        <span class="block truncate text-xs text-gray-500">@{{ membro.username }}</span>
                    </span>

                    <span v-if="membro.id === grupo.cadastrado_por" class="rounded-full bg-brand-100 px-2.5 py-1 text-xs font-medium text-brand-800">Proprietário</span>
                    <span v-else-if="membro.pivot && membro.pivot.role === 'admin'" class="rounded-full bg-zinc-900 px-2.5 py-1 text-xs font-medium text-white">Admin</span>
                    <span v-else class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">Membro</span>

                    <template v-if="isAdmin && membro.id !== grupo.cadastrado_por && membro.id !== user.id">
                        <button type="button" @click="makeAdmin(membro.id, membro.name)"
                            class="press rounded-full p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-800" title="Tornar admin">
                            <span class="sr-only">Tornar {{ membro.name }} admin</span>
                            <StarIcon class="size-4" />
                        </button>
                        <button type="button" @click="removeMember(membro.id, membro.name)"
                            class="press rounded-full p-1.5 text-gray-500 hover:bg-red-50 hover:text-red-600" title="Remover">
                            <span class="sr-only">Remover {{ membro.name }}</span>
                            <UserMinusIcon class="size-4" />
                        </button>
                    </template>
                </li>
            </ul>

            <div class="mt-6 flex justify-end">
                <button type="button" class="btn-secondary" @click="showMembersModal = false">Fechar</button>
            </div>
        </div>
    </Modal>
</template>
