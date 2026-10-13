<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    PlusIcon,
    UserPlusIcon,
    ArrowRightIcon,
    GiftIcon,
    UserGroupIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = page.props.auth.user;
const primeiroNome = (user.name || user.username || '').split(' ')[0];

defineProps({
    title: String,
    amizadesPendentes: {
        type: Array,
        default: () => [],
    },
    listasAtivas: {
        type: Array,
        default: () => [],
    },
    gruposRecentes: {
        type: Array,
        default: () => [],
    },
    totalAmizades: {
        type: Number,
        default: 0,
    },
});

const formatarData = (data) =>
    data ? new Date(data).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' }) : null;
</script>

<template>
    <Head :title="title" />
    <AppLayout :title="title">
        <div class="space-y-10">
            <!-- Abertura -->
            <section class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="animate-rise-in">
                    <p class="text-sm font-medium text-brand-700">Seu painel</p>
                    <h2 class="mt-1 text-3xl font-semibold text-gray-900 sm:text-4xl">
                        Olá, {{ primeiroNome }}
                    </h2>
                    <p class="mt-2 max-w-prose text-gray-600">
                        {{ listasAtivas.length > 0
                            ? 'Suas listas mais recentes estão logo abaixo.'
                            : 'Comece criando a primeira lista e compartilhe com quem você gosta.' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link :href="route('listas.create')" class="btn-primary">
                        <PlusIcon class="size-4" aria-hidden="true" />
                        Nova lista
                    </Link>
                    <Link :href="route('amizades.index')" class="btn-secondary">
                        <UserPlusIcon class="size-4" aria-hidden="true" />
                        Buscar amigos
                    </Link>
                </div>
            </section>

            <!-- Solicitacoes pendentes -->
            <Link v-if="amizadesPendentes.length > 0" :href="route('amizades.index')"
                class="press group flex items-center justify-between gap-4 rounded-xl border border-brand-200 bg-brand-50 px-5 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex -space-x-2">
                        <img v-for="a in amizadesPendentes.slice(0, 3)" :key="a.usuario_id"
                            :src="a.user?.perfil?.image_url ?? '/img/default_profile.png'" alt=""
                            class="size-9 rounded-full object-cover ring-2 ring-brand-50" />
                    </div>
                    <p class="text-sm text-brand-950">
                        <span class="font-semibold">{{ amizadesPendentes.length }}</span>
                        {{ amizadesPendentes.length === 1 ? 'pessoa quer' : 'pessoas querem' }} ser sua amiga
                    </p>
                </div>
                <span class="flex items-center gap-1 text-sm font-medium text-brand-800">
                    Responder
                    <ArrowRightIcon class="size-4 transition-transform duration-200 ease-out group-hover:translate-x-0.5" aria-hidden="true" />
                </span>
            </Link>

            <div class="grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                <!-- Listas -->
                <section>
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Suas listas</h3>
                        <Link :href="route('listas.index')"
                            class="flex items-center gap-1 text-sm font-medium text-brand-700 hover:text-brand-800">
                            Ver todas
                            <ArrowRightIcon class="size-4" aria-hidden="true" />
                        </Link>
                    </div>

                    <div v-if="listasAtivas.length > 0" class="stagger grid gap-5 sm:grid-cols-2">
                        <Link v-for="(lista, i) in listasAtivas" :key="lista.id" :href="route('listas.show', lista.id)"
                            :style="{ '--i': i }"
                            class="surface-link group overflow-hidden"
                            :class="i === 0 ? 'sm:col-span-2' : ''">
                            <div class="overflow-hidden bg-gray-100" :class="i === 0 ? 'aspect-[21/9]' : 'aspect-[16/9]'">
                                <img :src="lista.image_url || '/img/default_cover.png'" alt=""
                                    class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" />
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <h4 class="font-semibold text-gray-900">{{ lista.nome }}</h4>
                                    <span v-if="lista.grupo"
                                        class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                        {{ lista.grupo.nome }}
                                    </span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-sm text-gray-500">
                                    {{ lista.descricao || 'Sem descrição' }}
                                </p>
                                <p v-if="lista.data_evento" class="mt-3 flex items-center gap-1.5 text-xs font-medium text-gray-600">
                                    <CalendarDaysIcon class="size-4 text-gray-400" aria-hidden="true" />
                                    {{ formatarData(lista.data_evento) }}
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="surface flex flex-col items-center px-6 py-14 text-center">
                        <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                            <GiftIcon class="size-6" aria-hidden="true" />
                        </span>
                        <h4 class="mt-4 font-semibold text-gray-900">Nenhuma lista por aqui</h4>
                        <p class="mt-1 max-w-xs text-sm text-gray-500">
                            Crie uma lista para aniversário, casamento ou qualquer ocasião e adicione os presentes que você quer ganhar.
                        </p>
                        <Link :href="route('listas.create')" class="btn-primary mt-6">Criar primeira lista</Link>
                    </div>
                </section>

                <!-- Lateral: grupos e amigos -->
                <aside class="space-y-8">
                    <section>
                        <div class="mb-5 flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-gray-900">Seus grupos</h3>
                            <Link :href="route('grupos.index')"
                                class="flex items-center gap-1 text-sm font-medium text-brand-700 hover:text-brand-800">
                                Ver todos
                                <ArrowRightIcon class="size-4" aria-hidden="true" />
                            </Link>
                        </div>

                        <ul v-if="gruposRecentes.length > 0" class="surface divide-y divide-gray-100 overflow-hidden">
                            <li v-for="grupo in gruposRecentes" :key="grupo.id">
                                <Link :href="route('grupos.show', grupo.id)"
                                    class="flex items-center gap-3 px-4 py-3 transition-colors duration-150 hover:bg-gray-50">
                                    <img v-if="grupo.image_url" :src="grupo.image_url" alt=""
                                        class="size-10 shrink-0 rounded-md object-cover" />
                                    <span v-else
                                        class="flex size-10 shrink-0 items-center justify-center rounded-md bg-brand-100 text-sm font-semibold text-brand-800">
                                        {{ grupo.nome.substring(0, 2).toUpperCase() }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-gray-900">{{ grupo.nome }}</span>
                                        <span class="block truncate text-xs text-gray-500">{{ grupo.descricao || 'Grupo de amigos' }}</span>
                                    </span>
                                </Link>
                            </li>
                        </ul>

                        <div v-else class="surface px-5 py-8 text-center">
                            <UserGroupIcon class="mx-auto size-6 text-gray-400" aria-hidden="true" />
                            <p class="mt-2 text-sm text-gray-500">Você ainda não participa de nenhum grupo.</p>
                            <Link :href="route('grupos.create')" class="mt-3 inline-block text-sm font-medium text-brand-700 hover:text-brand-800">
                                Criar um grupo
                            </Link>
                        </div>
                    </section>

                    <section class="surface flex items-center justify-between px-5 py-4">
                        <div>
                            <p class="text-3xl font-semibold text-gray-900">{{ totalAmizades }}</p>
                            <p class="text-sm text-gray-500">{{ totalAmizades === 1 ? 'amizade' : 'amizades' }}</p>
                        </div>
                        <Link :href="route('amizades.index')" class="btn-secondary">Ver amigos</Link>
                    </section>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
