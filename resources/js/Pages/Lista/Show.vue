<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage, Link } from '@inertiajs/vue3'
import GridPresentes from '@/Layouts/GridPresentes.vue'
import BotaoVoltar from '@/Components/BotaoVoltar.vue'
import PencilIcon from '@heroicons/vue/24/outline/PencilIcon'

interface Lista {
    id: string
    nome: string
    descricao: string
    image_url: string
    presentes: any[]
}

interface Categoria {
    id: string
    nome: string
    hex_cor: string
}

const page = usePage()

defineProps<{
    title?: string
    lista: Lista
    categorias?: Categoria[]
}>()
</script>
<template>

    <Head :title="title" />
    <AppLayout :title="title">

        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- <div class="mb-4">
                            <BotaoVoltar :rota="route('listas.index')" />
                        </div> -->

                        <div class="flex w-full h-64 items-center justify-between space-x-6 py-9 rounded-xl bg-cover bg-center bg-no-repeat relative"
                            :style="{ backgroundImage: `url(${lista.image_url})` }">

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-xl flex flex-col justify-end px-8 pb-8">

                                <!-- Botão Editar -->
                                <Link :href="route('listas.edit', lista.id)">
                                <span class="absolute top-6 right-6 flex items-center justify-center size-10 rounded-full shadow-lg 
                                     bg-[radial-gradient(circle_at_center,theme(colors.indigo.500),theme(colors.indigo.400))]
                                     text-white hover:scale-110 hover:shadow-xl transition-all duration-300">
                                    <PencilIcon class="size-5" />
                                </span>
                                </Link>

                                <!-- Nome da Lista -->
                                <div class="text-white">
                                    <h2 class="text-4xl font-bold">{{ lista.nome }}</h2>
                                </div>
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div class="m-6">
                            <div class="text-2xl font-bold">Descrição</div>
                            <div class="mt-2">{{ lista.descricao }}</div>
                        </div>

                        <!-- Presentes -->
                        <div class="flex justify-between mx-6">
                            <div class="text-2xl font-bold">Presentes</div>
                            <Link :href="route('presentes.create', { lista_id: lista.id })">
                            <button class="bg-blue-500 text-white px-4 py-2 rounded-md">Adicionar Presente</button>
                            </Link>
                        </div>

                        <GridPresentes :presentes="lista.presentes" :categorias="categorias" />

                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
