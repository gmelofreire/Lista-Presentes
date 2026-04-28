<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed } from 'vue';
import GridListasGrupo from '@/Layouts/GridListasGrupo.vue';
import { TrashIcon, UserMinusIcon, UserPlusIcon, StarIcon, XMarkIcon } from '@heroicons/vue/20/solid';
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

const { router } = usePage();

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
        <div v-if="grupo.id">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Alert v-if="hasSuccessMessage" type="success"
                    :title="page.props.flash?.status || page.props.status || 'Sucesso'" class="mb-6" />
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ grupo.nome }}</h2>
                            </div>
                            
                            <div class="flex gap-2">
                                <button v-if="isAdmin" @click="showInviteModal = true"
                                    class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                                    <UserPlusIcon class="w-4 h-4 mr-1" />
                                    Convidar Amigo
                                </button>
                                
                                <button @click="showMembersModal = true"
                                    class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                                    <UserPlusIcon class="w-4 h-4 mr-1" />
                                    Membros ({{ grupo.integrantes ? grupo.integrantes.length : 0 }})
                                </button>
                                
                                <button v-if="!isOwner" @click="leaveGroup"
                                    class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">
                                    <UserMinusIcon class="w-4 h-4 mr-1" />
                                    Sair
                                </button>
                                
                                <button v-if="isOwner" @click="deleteGroup"
                                    class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
                                    <TrashIcon class="w-4 h-4 mr-1" />
                                    Excluir
                                </button>
                                
                                <Link
                                    :href="route('listas.create', { grupo_id: grupo.id })"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Nova Lista
                                </Link>
                            </div>
                        </div>

                        <div v-if="grupo.descricao" class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Descrição</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ grupo.descricao }}</p>
                        </div>

                        <GridListasGrupo :listas="listasForGrid" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <Teleport to="body">
        <div v-if="showInviteModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showInviteModal = false">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-500/75" @click="showInviteModal = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Convidar Amigo</h3>
                        <button @click="showInviteModal = false" class="text-gray-400 hover:text-gray-600">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    
                    <div class="mb-4">
                        <input v-model="searchTerm" type="text" placeholder="Buscar por nome ou @username..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div v-if="filteredFriends.length === 0" class="text-center py-4 text-gray-500">
                            Nenhum amigo disponível para convidar.
                        </div>
                        <div v-else v-for="amigo in filteredFriends" :key="amigo.id"
                            @click="toggleFriend(amigo.id)"
                            class="flex items-center p-3 rounded-lg cursor-pointer transition-colors"
                            :class="isSelected(amigo.id) ? 'bg-indigo-50 border-2 border-indigo-500' : 'bg-gray-50 border-2 border-transparent hover:bg-gray-100'">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                <span class="text-sm font-medium text-indigo-600">{{ amigo.name ? amigo.name.charAt(0) : '?' }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">{{ amigo.name }}</div>
                                <div class="text-xs text-gray-500">@{{ amigo.username }}</div>
                            </div>
                            <div v-if="isSelected(amigo.id)" class="text-indigo-600">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex justify-end gap-2">
                        <button @click="showInviteModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button @click="sendInvitations"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                            :disabled="selectedFriends.length === 0">
                            Adicionar ({{ selectedFriends.length }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

    <Teleport to="body">
        <div v-if="showMembersModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showMembersModal = false">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-500/75" @click="showMembersModal = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Membros do Grupo</h3>
                        <button @click="showMembersModal = false" class="text-gray-400 hover:text-gray-600">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <div v-for="membro in grupo.integrantes" :key="membro.id" 
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                    <span class="text-sm font-medium text-indigo-600">{{ membro.name ? membro.name.charAt(0) : '?' }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium">{{ membro.name }}</div>
                                    <div class="text-xs text-gray-500">@{{ membro.username }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span v-if="membro.id === grupo.cadastrado_por" class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Proprietário</span>
                                <span v-else-if="membro.pivot && membro.pivot.role === 'admin'" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Admin</span>
                                <span v-else class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Membro</span>
                                
                                <button v-if="isAdmin && membro.id !== grupo.cadastrado_por && membro.id !== user.id" 
                                    @click="makeAdmin(membro.id, membro.name)"
                                    class="p-1 text-blue-600 hover:text-blue-800" title="Tornar admin">
                                    <StarIcon class="w-4 h-4" />
                                </button>
                                <button v-if="isAdmin && membro.id !== grupo.cadastrado_por && membro.id !== user.id" 
                                    @click="removeMember(membro.id, membro.name)"
                                    class="p-1 text-red-600 hover:text-red-800" title="Remover">
                                    <UserMinusIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button @click="showMembersModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>