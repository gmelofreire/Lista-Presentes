<template>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Buscar Usuários</h2>
        <div class="relative">
            <div class="relative">
                <input ref="searchInput" v-model="searchQuery" @keydown="handleKeydown"
                    @focus="showSuggestions = suggestions.length > 0" type="text"
                    placeholder="Digite o nome ou username do usuário..."
                    class="w-full px-4 py-3 pl-10 pr-4 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors" />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div v-if="isLoading" class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="w-5 h-5 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Dropdown de sugestões -->
            <div v-if="showSuggestions && suggestions.length > 0"
                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                @click.stop>
                <Link v-for="(user, index) in suggestions" :key="user.id" 
                    :href="route('amizades.show', user.id)"
                    :class="[
                        'flex items-center px-4 py-3 cursor-pointer transition-colors block',
                        selectedIndex === index ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50']"
                    @click="selectUser(user)">
                    <div class="flex-shrink-0 w-10 h-10 mr-3">
                        <img v-if="user.avatar" :src="user.avatar" :alt="user.name"
                            class="w-10 h-10 rounded-full object-cover" />
                        <div v-else class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                        <p class="text-sm text-gray-500 truncate">@{{ user.username }}</p>
                    </div>
                </Link>
            </div>

            <!-- Mensagem quando não há resultados -->
            <div v-if="showSuggestions && suggestions.length === 0 && searchQuery.length >= 2 && !isLoading"
                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg">
                <div class="px-4 py-3 text-sm text-gray-500 text-center">
                    Nenhum usuário encontrado
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

// Props
defineProps({
    title: {
        type: String,
        default: 'Buscar Usuários'
    },
    placeholder: {
        type: String,
        default: 'Digite o nome ou username do usuário...'
    }
});

// Emits
const emit = defineEmits(['user-selected']);

// Estado da busca
const searchQuery = ref('');
const suggestions = ref([]);
const isLoading = ref(false);
const showSuggestions = ref(false);
const selectedIndex = ref(-1);
const searchInput = ref(null);

// Debounce timer
let debounceTimer = null;

// Função de busca
const searchUsers = async (query) => {
    if (query.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get('/api/usuarios/buscar', {
            params: { q: query }
        });
        suggestions.value = response.data;
        showSuggestions.value = true;
        selectedIndex.value = -1;
    } catch (error) {
        console.error('Erro ao buscar usuários:', error);
        suggestions.value = [];
    } finally {
        isLoading.value = false;
    }
};

// Watch para busca com debounce
watch(searchQuery, (newQuery) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        searchUsers(newQuery);
    }, 300);
});

// Handlers de teclado
const handleKeydown = (event) => {
    if (!showSuggestions.value || suggestions.value.length === 0) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            selectedIndex.value = Math.min(selectedIndex.value + 1, suggestions.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (selectedIndex.value >= 0) {
                selectUser(suggestions.value[selectedIndex.value]);
            }
            break;
        case 'Escape':
            showSuggestions.value = false;
            selectedIndex.value = -1;
            break;
    }
};

// Selecionar usuário
const selectUser = (user) => {
    emit('user-selected', user);
    searchQuery.value = '';
    suggestions.value = [];
    showSuggestions.value = false;
    selectedIndex.value = -1;
};

// Fechar sugestões ao clicar fora
const handleClickOutside = () => {
    showSuggestions.value = false;
    selectedIndex.value = -1;
};

// Expor métodos para o componente pai
defineExpose({
    clearSearch: () => {
        searchQuery.value = '';
        suggestions.value = [];
        showSuggestions.value = false;
        selectedIndex.value = -1;
    },
    focusInput: () => {
        searchInput.value?.focus();
    }
});
</script>