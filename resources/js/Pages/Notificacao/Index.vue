<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import axios from 'axios';
import {
    BellIcon,
    CheckIcon,
    GiftIcon,
    UserGroupIcon,
    UserPlusIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    title: {
        type: String,
        default: 'Notificações',
    },
    notificacoes: {
        type: Array,
        default: () => [],
    },
    naoLidas: {
        type: Number,
        default: 0,
    },
});

const itens = ref(props.notificacoes.map((n) => ({ ...n })));
const marcandoTodas = ref(false);

const totalNaoLidas = computed(() => itens.value.filter((n) => !n.lido).length);

const iconePorTipo = (tipo) => {
    if (tipo === 'solicitacao_amizade') return UserPlusIcon;
    if (tipo === 'amizade_aceita') return UserGroupIcon;
    if (tipo === 'nova_lista') return GiftIcon;
    return BellIcon;
};

const rtf = new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' });

const quandoFoi = (data) => {
    const segundos = Math.round((new Date(data).getTime() - Date.now()) / 1000);
    const abs = Math.abs(segundos);
    if (abs < 60) return 'agora mesmo';
    if (abs < 3600) return rtf.format(Math.round(segundos / 60), 'minute');
    if (abs < 86400) return rtf.format(Math.round(segundos / 3600), 'hour');
    if (abs < 86400 * 30) return rtf.format(Math.round(segundos / 86400), 'day');
    return new Date(data).toLocaleDateString('pt-BR');
};

const abrir = async (notificacao) => {
    try {
        if (!notificacao.lido) {
            await axios.post(`/notificacoes/${notificacao.id}/marcar-lido`);
            notificacao.lido = true;
        }
    } catch (error) {
        console.error('Erro ao marcar notificação', error);
    }
    if (notificacao.link) {
        window.location.href = notificacao.link;
    }
};

const marcarTodas = async () => {
    marcandoTodas.value = true;
    try {
        await axios.post('/notificacoes/marcar-todas-lido');
        itens.value.forEach((n) => (n.lido = true));
    } catch (error) {
        console.error('Erro ao marcar notificações', error);
    } finally {
        marcandoTodas.value = false;
    }
};
</script>

<template>
    <Head :title="title" />
    <AppLayout :title="title">
        <div class="mx-auto max-w-3xl space-y-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-3xl font-semibold text-gray-900">Notificações</h2>
                    <p class="mt-1 text-gray-600">
                        {{ totalNaoLidas > 0
                            ? `${totalNaoLidas} ${totalNaoLidas === 1 ? 'não lida' : 'não lidas'}`
                            : 'Você está em dia.' }}
                    </p>
                </div>
                <button v-if="totalNaoLidas > 0" type="button" class="btn-secondary self-start sm:self-auto"
                    :disabled="marcandoTodas" @click="marcarTodas">
                    <CheckIcon class="size-4" aria-hidden="true" />
                    Marcar todas como lidas
                </button>
            </div>

            <ul v-if="itens.length > 0" role="list" class="surface divide-y divide-gray-100 overflow-hidden">
                <li v-for="notificacao in itens" :key="notificacao.id">
                    <button type="button" @click="abrir(notificacao)"
                        class="flex w-full items-start gap-4 px-5 py-4 text-left transition-colors duration-150 hover:bg-gray-50"
                        :class="notificacao.lido ? '' : 'bg-brand-50/60'">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full"
                            :class="notificacao.lido ? 'bg-gray-100 text-gray-500' : 'bg-brand-100 text-brand-700'">
                            <component :is="iconePorTipo(notificacao.tipo)" class="size-5" aria-hidden="true" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-baseline justify-between gap-3">
                                <span class="truncate text-sm text-gray-900"
                                    :class="notificacao.lido ? 'font-medium' : 'font-semibold'">
                                    {{ notificacao.titulo }}
                                </span>
                                <time :datetime="notificacao.created_at" class="shrink-0 text-xs text-gray-400">
                                    {{ quandoFoi(notificacao.created_at) }}
                                </time>
                            </span>
                            <span class="mt-0.5 block text-sm text-gray-600">{{ notificacao.mensagem }}</span>
                            <span v-if="!notificacao.lido" class="sr-only">Não lida</span>
                        </span>
                    </button>
                </li>
            </ul>

            <div v-else class="surface flex flex-col items-center px-6 py-16 text-center">
                <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                    <BellIcon class="size-6" aria-hidden="true" />
                </span>
                <h3 class="mt-4 font-semibold text-gray-900">Nenhuma notificação por enquanto</h3>
                <p class="mt-1 max-w-xs text-sm text-gray-500">
                    Pedidos de amizade, aceites e novas listas dos seus grupos aparecem aqui.
                </p>
            </div>

            <p v-if="itens.length >= 20" class="text-center text-sm text-gray-400">Mostrando as 20 mais recentes.</p>
        </div>
    </AppLayout>
</template>
