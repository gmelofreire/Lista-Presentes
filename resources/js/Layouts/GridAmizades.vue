<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    amizades: {
        type: [Array, Object],
    },
})

const coresGrupos = [
    { bg: 'bg-red-200', text: 'text-red-600' },
    { bg: 'bg-blue-200', text: 'text-blue-600' },
    { bg: 'bg-green-200', text: 'text-green-600' },
    { bg: 'bg-yellow-200', text: 'text-yellow-600' },
    { bg: 'bg-purple-200', text: 'text-purple-600' },
    { bg: 'bg-pink-200', text: 'text-pink-600' },
    { bg: 'bg-indigo-200', text: 'text-indigo-600' },
    { bg: 'bg-orange-200', text: 'text-orange-600' },
    { bg: 'bg-teal-200', text: 'text-teal-600' },
    { bg: 'bg-cyan-200', text: 'text-cyan-600' },
    { bg: 'bg-lime-200', text: 'text-lime-600' },
    { bg: 'bg-emerald-200', text: 'text-emerald-600' },
]

const getCorGrupo = (grupoId) => {
    let hash = 0;
    for (let i = 0; i < grupoId.length; i++) {
        const char = grupoId.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    const index = Math.abs(hash) % coresGrupos.length;
    return coresGrupos[index];
}
</script>
<template>
    <div class="grid md:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-5" v-if="amizades.length > 0">
        <div v-for="amizade in amizades" :key="amizade.id"
            class="bg-white rounded-lg shadow-md flex flex-col justify-between">
            <div class="p-4">
                <img :src="amizade.perfil?.image_url ?? '/img/default_profile.png'" alt="imagem de perfil"
                    class=" size-36 rounded-full object-cover mx-auto">
                <div class="mt-3 text-center">
                    <h3 class="text-lg font-bold">{{ amizade.name }}</h3>
                    <p class="text-sm text-gray-500">@{{ amizade.username }}</p>
                    <div class="mt-4">
                        <span v-if="amizade.grupos?.length" v-for="grupo in amizade.grupos.slice(0, 5)" :key="grupo.id"
                            :class="`inline-block px-2 py-1 text-sm mr-2 mb-1 rounded-full whitespace-nowrap ${getCorGrupo(grupo.id).bg} ${getCorGrupo(grupo.id).text}`">
                            {{ grupo.nome }}
                        </span>
                        <span v-if="amizade.grupos?.length > 5"
                            class="inline-block px-2 py-1 text-sm mr-2 mb-1 rounded-full whitespace-nowrap bg-gray-200 text-gray-600">
                            ...
                        </span>
                    </div>
                </div>
            </div>
            <div>
                <div class="-mt-px flex divide-x divide-gray-200">
                    <Link :href="route('amizades.show', amizade.id)" class="w-full">
                    <button
                        class="text-center w-full h-12 bg-indigo-500 text-white rounded-b-lg hover:bg-indigo-600 transition-colors">
                        Acessar
                    </button>
                    </Link>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="flex flex-col items-center justify-center py-16 px-4">
        <div class="text-center max-w-md mx-auto">
            <!-- Ícone de amizades -->
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            
            <!-- Título -->
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Nenhuma amizade encontrada
            </h3>
            
            <!-- Descrição -->
            <p class="text-gray-500 mb-8 leading-relaxed">
                Você ainda não possui amigos na plataforma. Comece a conectar-se com outras pessoas para compartilhar suas listas de presentes!
            </p>
        </div>
    </div>
</template>
