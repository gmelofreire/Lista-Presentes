<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'
import GridPresentes from '@/Layouts/GridPresentes.vue'
import BotaoVoltar from '@/Components/BotaoVoltar.vue'

const page = usePage()

defineProps({
    title: {
        type: String,
    },
    lista: {
        type: [Array, Object],
    }
})
</script>
<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div>
                            <!-- <BotaoVoltar :rota="route('listas.index')" /> -->
                            <div class="flex w-full h-64 items-center justify-between space-x-6 py-9 rounded-xl bg-cover bg-center bg-no-repeat relative"
                                :style="{ backgroundImage: `url(${lista.image_url})` }">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent rounded-xl flex flex-col justify-end px-8 pb-8">
                                    <div class="text-white">
                                        <h2 class="text-4xl font-bold">{{ lista.nome }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="m-6">
                                <div class="text-2xl font-bold">Descrição</div>
                                <div class="mt-2">{{ lista.descricao }}</div>
                            </div>

                            <GridPresentes />

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
