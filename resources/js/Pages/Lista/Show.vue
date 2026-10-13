<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage, Link } from '@inertiajs/vue3'
import Alert from '@/Components/Alert.vue'
import GridPresentes from '@/Layouts/GridPresentes.vue'
import PencilIcon from '@heroicons/vue/24/outline/PencilIcon'
import { ArrowLeftIcon } from '@heroicons/vue/20/solid';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue'

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

const mensagem = computed(() => page.props.flash?.status)

defineProps<{
    title?: string
    lista: Lista
    categorias?: Categoria[]
}>()
</script>
<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div class="space-y-8">
            <Alert v-if="mensagem" type="success" :title="mensagem" />

            <!-- Capa -->
            <section class="relative isolate overflow-hidden rounded-2xl bg-gray-200">
                <img :src="lista.image_url || '/img/default_cover.png'" :alt="`Capa da lista ${lista.nome}`"
                    class="h-64 w-full object-cover sm:h-80" />
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/75 via-zinc-950/15 to-transparent"></div>

                <Link :href="route('listas.index')"
                    class="press absolute left-4 top-4 flex size-10 items-center justify-center rounded-full bg-surface/90 text-gray-800 shadow-sm backdrop-blur hover:bg-surface"
                    aria-label="Voltar para as listas">
                    <ArrowLeftIcon class="size-5" />
                </Link>
                <Link :href="route('listas.edit', lista.id)"
                    class="press absolute right-4 top-4 flex size-10 items-center justify-center rounded-full bg-surface/90 text-gray-800 shadow-sm backdrop-blur hover:bg-surface"
                    aria-label="Editar lista">
                    <PencilIcon class="size-5" />
                </Link>

                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                    <h2 class="text-3xl font-semibold text-white sm:text-4xl">{{ lista.nome }}</h2>
                    <p v-if="lista.descricao" class="mt-2 max-w-2xl text-white/85 line-clamp-2">{{ lista.descricao }}</p>
                </div>
            </section>

            <!-- Presentes -->
            <section>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-900">Presentes</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ lista.presentes.length }} {{ lista.presentes.length === 1 ? 'item' : 'itens' }} na lista
                        </p>
                    </div>
                    <Link :href="route('presentes.create', { lista_id: lista.id })" class="btn-primary self-start sm:self-auto">
                        <PlusIcon class="size-4" aria-hidden="true" />
                        Adicionar presente
                    </Link>
                </div>

                <GridPresentes class="mt-6" :presentes="lista.presentes" :categorias="categorias" :lista-id="lista.id" />
            </section>
        </div>
    </AppLayout>
</template>
