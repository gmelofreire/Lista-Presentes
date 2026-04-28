<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import GridPresentes from '@/Layouts/GridPresentes.vue'
import { ArrowLeftIcon } from '@heroicons/vue/20/solid';

interface Lista {
    id: string
    nome: string
    descricao: string
    image_url: string
    visibilidade: string
    data_evento: string | null
    grupo: { id: string; nome: string } | null
    cadastradoPor: { name: string; username: string; perfil: { avatar: string } }
    presentes: any[]
}

const props = defineProps<{
    title: string
    lista: Lista
    isPreview?: boolean
}>()
</script>
<template>

    <Head :title="title" />

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 py-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex w-full h-96 items-center justify-between space-x-6 py-9 rounded-xl bg-cover bg-center bg-no-repeat relative"
                        :style="{ backgroundImage: `url(${lista.image_url})` }">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-xl flex flex-col justify-end px-8 pb-8">

                            <Link v-if="isPreview" :href="route('listas.index')" class="absolute top-6 left-6">
                                <span class="flex items-center justify-center size-10 rounded-full shadow-lg 
                                     bg-indigo-500 text-white hover:scale-110 hover:shadow-xl transition-all duration-300">
                                    <ArrowLeftIcon class="w-5 h-5" />
                                </span>
                            </Link>
                            <Link v-else :href="route('listas.index')" class="absolute top-6 left-6">
                                <span class="flex items-center justify-center size-10 rounded-full shadow-lg 
                                     bg-indigo-500 text-white hover:scale-110 hover:shadow-xl transition-all duration-300">
                                    <ArrowLeftIcon class="w-5 h-5" />
                                </span>
                            </Link>

                            <div class="text-white">
                                <div class="flex items-center gap-3 mb-2">
                                    <span :class="lista.visibilidade === 'publica' ? 'bg-green-500' : 'bg-yellow-500'" class="text-xs px-2 py-1 rounded">
                                        {{ lista.visibilidade === 'publica' ? 'Pública' : 'Privada' }}
                                    </span>
                                    <span v-if="isPreview" class="bg-red-500 text-xs px-2 py-1 rounded">
                                        Preview
                                    </span>
                                </div>
                                <h2 class="text-4xl font-bold">{{ lista.nome }}</h2>
                            </div>
                        </div>
                    </div>

                    <div class="m-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="col-span-2">
                            <div class="text-2xl font-bold mb-2">Descrição</div>
                            <div>{{ lista.descricao || 'Sem descrição' }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-lg font-semibold mb-3">Informações do Evento</div>
                            <div class="space-y-2 text-sm">
                                <div v-if="lista.data_evento">
                                    <span class="text-gray-500">Data do evento:</span>
                                    <span class="ml-2 font-medium">{{ new Date(lista.data_evento).toLocaleDateString('pt-BR') }}</span>
                                </div>
                                <div v-if="lista.grupo">
                                    <span class="text-gray-500">Grupo:</span>
                                    <span class="ml-2 font-medium">{{ lista.grupo.nome }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Criado por:</span>
                                    <span class="ml-2 font-medium">{{ lista.cadastradoPor?.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-6">
                        <div class="text-2xl font-bold mb-4">Presentes ({{ lista.presentes?.length || 0 }})</div>
                        
                        <div v-if="lista.presentes && lista.presentes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="presente in lista.presentes" :key="presente.id" 
                                class="border rounded-lg p-4 hover:shadow-lg transition-shadow"
                                :class="presente.comprado ? 'bg-green-50 border-green-200' : 'bg-white'">
                                <div class="flex items-start gap-3">
                                    <img v-if="presente.image_url" :src="presente.image_url" :alt="presente.nome" class="w-20 h-20 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-lg">{{ presente.nome }}</h4>
                                        <p v-if="presente.descricao" class="text-sm text-gray-600 mt-1">{{ presente.descricao }}</p>
                                        <p v-if="presente.preco" class="text-lg font-bold text-indigo-600 mt-2">
                                            R$ {{ Number(presente.preco).toFixed(2) }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span v-if="presente.comprado" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                                Comprado
                                            </span>
                                            <span v-if="presente.link" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                <a :href="presente.link" target="_blank" class="flex items-center gap-1">
                                                    Ver produto
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum presente ainda</h3>
                            <p class="mt-1 text-sm text-gray-500">Esta lista não tem presentes adicionados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>