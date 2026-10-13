<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import GridPresentes from '@/Layouts/GridPresentes.vue'
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/20/solid';
import { ArrowTopRightOnSquareIcon, CalendarDaysIcon, GiftIcon, UserGroupIcon, UserIcon } from '@heroicons/vue/24/outline';

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

    <div class="min-h-[100dvh] bg-gray-50">
        <header class="border-b border-gray-200/80 bg-surface">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link :href="route('listas.index')" class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-md bg-brand-600 text-white">
                        <GiftIcon class="size-5" aria-hidden="true" />
                    </span>
                    <span class="text-xl font-semibold tracking-tight text-gray-900">Desejário</span>
                </Link>
                <Link :href="route('listas.index')" class="btn-ghost">
                    <ArrowLeftIcon class="size-4" aria-hidden="true" />
                    Voltar às listas
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-6xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
            <div v-if="isPreview" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Você está vendo a lista como as outras pessoas veem.
            </div>

            <!-- Capa -->
            <section class="relative isolate overflow-hidden rounded-2xl bg-gray-200">
                <img :src="lista.image_url || '/img/default_cover.png'" :alt="`Capa da lista ${lista.nome}`"
                    class="h-64 w-full object-cover sm:h-80" />
                <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/75 via-zinc-950/15 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                    <span class="inline-flex rounded-full bg-surface/90 px-2.5 py-1 text-xs font-medium text-gray-800 backdrop-blur">
                        {{ lista.visibilidade === 'publica' ? 'Pública' : 'Privada' }}
                    </span>
                    <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">{{ lista.nome }}</h1>
                </div>
            </section>

            <!-- Descricao e detalhes -->
            <section class="grid gap-8 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Sobre a lista</h2>
                    <p class="mt-3 whitespace-pre-line text-gray-600">{{ lista.descricao || 'Sem descrição' }}</p>
                </div>
                <dl class="surface space-y-4 p-5 text-sm">
                    <div v-if="lista.data_evento" class="flex items-start gap-3">
                        <CalendarDaysIcon class="mt-0.5 size-5 shrink-0 text-gray-400" aria-hidden="true" />
                        <div>
                            <dt class="text-gray-500">Data do evento</dt>
                            <dd class="font-medium text-gray-900">{{ new Date(lista.data_evento).toLocaleDateString('pt-BR') }}</dd>
                        </div>
                    </div>
                    <div v-if="lista.grupo" class="flex items-start gap-3">
                        <UserGroupIcon class="mt-0.5 size-5 shrink-0 text-gray-400" aria-hidden="true" />
                        <div>
                            <dt class="text-gray-500">Grupo</dt>
                            <dd class="font-medium text-gray-900">{{ lista.grupo.nome }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <UserIcon class="mt-0.5 size-5 shrink-0 text-gray-400" aria-hidden="true" />
                        <div>
                            <dt class="text-gray-500">Criada por</dt>
                            <dd class="font-medium text-gray-900">{{ lista.cadastradoPor?.name }}</dd>
                        </div>
                    </div>
                </dl>
            </section>

            <!-- Presentes -->
            <section>
                <h2 class="text-2xl font-semibold text-gray-900">
                    Presentes
                    <span class="ml-1 text-base font-normal text-gray-500">{{ lista.presentes?.length || 0 }}</span>
                </h2>

                <ul v-if="lista.presentes && lista.presentes.length > 0" role="list"
                    class="stagger mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(presente, i) in lista.presentes" :key="presente.id" :style="{ '--i': Math.min(i, 8) }"
                        class="surface flex gap-4 p-4">
                        <div class="size-24 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                            <img v-if="presente.image_url" :src="presente.image_url" :alt="presente.nome"
                                class="size-full object-cover" :class="presente.comprado ? 'opacity-60 saturate-50' : ''" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="line-clamp-2 font-semibold text-gray-900">{{ presente.nome }}</h3>
                            <p v-if="presente.descricao" class="mt-1 line-clamp-2 text-sm text-gray-500">{{ presente.descricao }}</p>
                            <p v-if="presente.preco" class="mt-2 font-medium text-gray-800">
                                R$ {{ Number(presente.preco).toFixed(2).replace('.', ',') }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                <span v-if="presente.comprado"
                                    class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-800">
                                    <CheckIcon class="size-3.5" aria-hidden="true" />
                                    Comprado
                                </span>
                                <a v-if="presente.link" :href="presente.link" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-brand-700 hover:text-brand-800">
                                    Ver na loja
                                    <ArrowTopRightOnSquareIcon class="size-4" aria-hidden="true" />
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>

                <div v-else class="surface mt-6 flex flex-col items-center px-6 py-14 text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                        <GiftIcon class="size-6" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 font-semibold text-gray-900">Nenhum presente ainda</h3>
                    <p class="mt-1 text-sm text-gray-500">Esta lista não tem presentes adicionados.</p>
                </div>
            </section>
        </main>
    </div>
</template>
