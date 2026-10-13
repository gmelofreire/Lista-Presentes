<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Alert from '@/Components/Alert.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import {
    ArrowRightIcon,
    BellAlertIcon,
    GiftIcon,
    LinkIcon,
    TagIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

const mensagem = usePage().props.flash?.status;

// Revela o bloco quando entra na tela. Sem IntersectionObserver, o conteudo ja nasce visivel.
const vReveal = {
    mounted(el) {
        if (!('IntersectionObserver' in window)) return;
        el.classList.add('reveal');
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    el.classList.add('is-visible');
                    observer.disconnect();
                }
            },
            { threshold: 0.15 },
        );
        observer.observe(el);
    },
};

const passos = [
    {
        titulo: 'Crie a lista',
        texto: 'Dê um nome, escolha a capa e a data do evento. Pode ser pública para os amigos ou privada.',
    },
    {
        titulo: 'Adicione os presentes',
        texto: 'Cole o link da loja e o Desejário preenche nome, preço e foto. Marque o quanto você quer cada item.',
    },
    {
        titulo: 'Compartilhe com quem importa',
        texto: 'Convide amigos pelo @username ou reúna a família num grupo. Todo mundo vê a mesma lista.',
    },
];

const ocasioes = ['Aniversário', 'Casamento', 'Chá de bebê', 'Formatura', 'Natal', 'Amigo oculto'];

const perguntas = [
    {
        pergunta: 'Quem consegue ver a minha lista?',
        resposta:
            'Você escolhe ao criar: pública, para amigos e membros do grupo, ou privada, só para quem você convidar. Dá para mudar depois.',
    },
    {
        pergunta: 'Posso colocar presentes de qualquer loja?',
        resposta:
            'Sim. Cole o link e o Desejário tenta preencher os dados sozinho. Em alguns sites nem tudo vem completo, aí você ajusta à mão.',
    },
    {
        pergunta: 'Como adiciono meus amigos?',
        resposta:
            'Busque pelo nome ou @username, envie a solicitação e pronto. Quando a pessoa aceita, vocês passam a ver as listas um do outro.',
    },
    {
        pergunta: 'Como evito ganhar o mesmo presente duas vezes?',
        resposta:
            'Quem comprou marca o item como comprado, e ele aparece diferente na lista para todos os convidados.',
    },
];

const anoAtual = new Date().getFullYear();
</script>

<template>
    <Head title="Listas de presentes para quem você gosta" />

    <div class="min-h-[100dvh] bg-gray-50 text-gray-900">
        <!-- Navegacao -->
        <header class="sticky top-0 z-30 border-b border-gray-200/70 bg-gray-50/85 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2.5" aria-label="Desejário, página inicial">
                    <span class="flex size-9 items-center justify-center rounded-md bg-brand-600 text-white">
                        <GiftIcon class="size-5" aria-hidden="true" />
                    </span>
                    <span class="text-xl font-semibold tracking-tight">Desejário</span>
                </Link>

                <nav class="flex items-center gap-1 sm:gap-2" aria-label="Principal">
                    <a href="#como-funciona" class="btn-ghost hidden md:inline-flex">Como funciona</a>
                    <a href="#recursos" class="btn-ghost hidden md:inline-flex">Recursos</a>
                    <a href="#duvidas" class="btn-ghost hidden md:inline-flex">Dúvidas</a>
                    <ThemeToggle />
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn-primary">Abrir painel</Link>
                    </template>
                    <template v-else>
                        <Link v-if="canLogin" :href="route('login')" class="btn-ghost">Entrar</Link>
                        <Link v-if="canRegister" :href="route('register')" class="btn-primary">Criar conta</Link>
                    </template>
                </nav>
            </div>
        </header>

        <main>
            <div v-if="mensagem" class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                <Alert type="success" :title="mensagem" :auto-dismiss="10000" />
            </div>

            <!-- Hero: assimetrico, texto a esquerda, fotos a direita -->
            <section class="mx-auto grid max-w-7xl items-center gap-10 px-4 pb-16 pt-10 sm:px-6 md:pt-14 lg:min-h-[calc(100dvh-4rem)] lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)] lg:gap-16 lg:px-8 lg:pb-12 lg:pt-12">
                <div class="animate-rise-in">
                    <h1 class="text-4xl font-semibold leading-[1.05] sm:text-5xl lg:text-6xl">
                        Ganhe o que você realmente quer
                    </h1>
                    <p class="mt-6 max-w-md text-lg text-gray-600">
                        Monte sua lista, compartilhe com amigos e família e pare de receber o que já tem.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <Link v-if="!$page.props.auth.user && canRegister" :href="route('register')" class="btn-primary px-6 py-3 text-base">
                            Criar conta
                            <ArrowRightIcon class="size-4" aria-hidden="true" />
                        </Link>
                        <Link v-else-if="$page.props.auth.user" :href="route('dashboard')" class="btn-primary px-6 py-3 text-base">
                            Abrir painel
                            <ArrowRightIcon class="size-4" aria-hidden="true" />
                        </Link>
                        <Link v-if="!$page.props.auth.user && canLogin" :href="route('login')" class="btn-secondary px-6 py-3 text-base">Entrar</Link>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl animate-rise-in lg:max-w-none" style="animation-delay: 120ms">
                    <img src="https://picsum.photos/seed/desejario-presentes-hero/900/1000" alt=""
                        width="900" height="1000" fetchpriority="high"
                        class="aspect-[4/4.2] w-full rounded-2xl bg-gray-200 object-cover shadow-lg lg:ml-auto lg:w-[88%]" />
                    <img src="https://picsum.photos/seed/desejario-mesa-festa/600/700" alt=""
                        width="600" height="700" loading="lazy"
                        class="absolute -bottom-6 left-0 hidden aspect-[6/7] w-[38%] rounded-2xl border-4 border-gray-50 bg-gray-200 object-cover shadow-lg sm:block" />
                </div>
            </section>

            <!-- Como funciona: titulo fixo a esquerda, passos a direita -->
            <section id="como-funciona" class="scroll-mt-20 border-t border-gray-200/70 bg-surface">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] lg:gap-20 lg:px-8 lg:py-28">
                    <div v-reveal class="lg:sticky lg:top-28 lg:self-start">
                        <h2 class="text-3xl font-semibold sm:text-4xl">Pronta em poucos minutos</h2>
                        <p class="mt-4 max-w-sm text-gray-600">Três passos e a lista já está nas mãos de quem vai presentear.</p>
                    </div>

                    <ol class="space-y-10">
                        <li v-for="(passo, i) in passos" :key="passo.titulo" v-reveal
                            class="flex gap-6" :style="{ transitionDelay: `${i * 60}ms` }">
                            <span class="w-10 shrink-0 text-4xl font-semibold leading-none text-brand-600 tabular-nums" aria-hidden="true">{{ i + 1 }}</span>
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-xl font-semibold">{{ passo.titulo }}</h3>
                                <p class="mt-2 max-w-lg text-gray-600">{{ passo.texto }}</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </section>

            <!-- Recursos: bento de 5 celulas -->
            <section id="recursos" class="scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                    <h2 v-reveal class="max-w-2xl text-3xl font-semibold sm:text-4xl">Tudo para organizar os presentes num lugar só</h2>

                    <div class="mt-12 grid grid-cols-1 gap-4 md:grid-cols-12">
                        <!-- Listas com capa: imagem -->
                        <article v-reveal class="relative min-h-72 overflow-hidden rounded-2xl bg-zinc-900 md:col-span-7">
                            <img src="https://picsum.photos/seed/desejario-lista-capa/1000/700" alt="" loading="lazy"
                                width="1000" height="700" class="absolute inset-0 size-full object-cover opacity-80" />
                            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/85 via-zinc-950/30 to-transparent"></div>
                            <div class="relative flex h-full min-h-72 flex-col justify-end p-6 sm:p-8">
                                <GiftIcon class="size-6 text-white/80" aria-hidden="true" />
                                <h3 class="mt-3 text-2xl font-semibold text-white">Listas com a sua cara</h3>
                                <p class="mt-2 max-w-md text-white/80">Capa, descrição e data do evento. Cada lista conta para quem é e para quando.</p>
                            </div>
                        </article>

                        <!-- Link da loja: acento -->
                        <article v-reveal class="rounded-2xl bg-brand-700 p-6 text-white sm:p-8 md:col-span-5" style="transition-delay: 60ms">
                            <LinkIcon class="size-6 text-[#b0e6c9]" aria-hidden="true" />
                            <h3 class="mt-3 text-2xl font-semibold">Cole o link, a gente preenche</h3>
                            <p class="mt-2 text-[#d7f3e3]">Nome, preço e foto vêm da página da loja. Você só confere e salva.</p>
                        </article>

                        <!-- Grupos -->
                        <article v-reveal class="rounded-2xl border border-gray-200 bg-surface p-6 sm:p-8 md:col-span-5">
                            <UserGroupIcon class="size-6 text-brand-700" aria-hidden="true" />
                            <h3 class="mt-3 text-2xl font-semibold">Grupos para a família e a turma</h3>
                            <p class="mt-2 text-gray-600">Todo mundo do grupo enxerga as listas e é avisado quando uma nova aparece.</p>
                        </article>

                        <!-- Categorias: componente real -->
                        <article v-reveal class="rounded-2xl bg-brand-50 p-6 sm:p-8 md:col-span-3" style="transition-delay: 60ms">
                            <TagIcon class="size-6 text-brand-700" aria-hidden="true" />
                            <h3 class="mt-3 text-2xl font-semibold">Categorias coloridas</h3>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="rounded-full bg-[#1A7F56] px-3 py-1 text-sm font-medium text-white">Casa</span>
                                <span class="rounded-full bg-[#2563EB] px-3 py-1 text-sm font-medium text-white">Livros</span>
                                <span class="rounded-full bg-[#EA580C] px-3 py-1 text-sm font-medium text-white">Viagem</span>
                            </div>
                        </article>

                        <!-- Notificacoes -->
                        <article v-reveal class="rounded-2xl border border-gray-200 bg-surface p-6 sm:p-8 md:col-span-4" style="transition-delay: 120ms">
                            <BellAlertIcon class="size-6 text-brand-700" aria-hidden="true" />
                            <h3 class="mt-3 text-2xl font-semibold">Avisos na hora certa</h3>
                            <p class="mt-2 text-gray-600">Pedidos de amizade e listas novas chegam no sino do painel.</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Ocasioes: faixa de imagem com texto grande -->
            <section class="relative isolate overflow-hidden bg-brand-950">
                <img src="https://picsum.photos/seed/desejario-ocasioes/1600/700" alt="" loading="lazy"
                    width="1600" height="700" class="absolute inset-0 -z-10 size-full object-cover opacity-30" />
                <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                    <p v-reveal class="max-w-3xl text-3xl font-semibold leading-tight text-white sm:text-5xl">
                        Para toda ocasião em que alguém pergunta o que você quer ganhar
                    </p>
                    <ul v-reveal class="mt-10 flex flex-wrap gap-3" aria-label="Ocasiões">
                        <li v-for="o in ocasioes" :key="o"
                            class="rounded-full border border-white/25 px-4 py-2 text-sm font-medium text-white">
                            {{ o }}
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Duvidas -->
            <section id="duvidas" class="scroll-mt-20 bg-surface">
                <div class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:py-28">
                    <h2 v-reveal class="text-3xl font-semibold sm:text-4xl">Dúvidas frequentes</h2>

                    <div class="mt-10 divide-y divide-gray-200 border-y border-gray-200">
                        <details v-for="item in perguntas" :key="item.pergunta" class="group py-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-medium text-gray-900 [&::-webkit-details-marker]:hidden">
                                {{ item.pergunta }}
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition-transform duration-200 ease-out group-open:rotate-45" aria-hidden="true">+</span>
                            </summary>
                            <p class="mt-3 max-w-2xl text-gray-600">{{ item.resposta }}</p>
                        </details>
                    </div>
                </div>
            </section>

            <!-- Fechamento -->
            <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                <div v-reveal class="flex flex-col items-start justify-between gap-8 rounded-2xl bg-brand-600 p-8 sm:p-12 lg:flex-row lg:items-center">
                    <h2 class="max-w-xl text-3xl font-semibold text-white sm:text-4xl">Comece sua primeira lista hoje</h2>
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="press inline-flex items-center gap-2 whitespace-nowrap rounded-md bg-zinc-50 px-6 py-3 text-base font-medium text-[#14513a] shadow-sm hover:bg-white">
                        Abrir painel
                        <ArrowRightIcon class="size-4" aria-hidden="true" />
                    </Link>
                    <Link v-else-if="canRegister" :href="route('register')"
                        class="press inline-flex items-center gap-2 whitespace-nowrap rounded-md bg-zinc-50 px-6 py-3 text-base font-medium text-[#14513a] shadow-sm hover:bg-white">
                        Criar conta
                        <ArrowRightIcon class="size-4" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-200/70">
            <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-4 px-4 py-8 text-sm text-gray-500 sm:flex-row sm:items-center sm:px-6 lg:px-8">
                <span class="flex items-center gap-2 font-medium text-gray-700">
                    <GiftIcon class="size-4 text-brand-700" aria-hidden="true" />
                    Desejário
                </span>
                <span>© {{ anoAtual }} Desejário</span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Entrada ao rolar: so opacidade e deslocamento curto, sem layout */
.reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 500ms cubic-bezier(0.23, 1, 0.32, 1),
        transform 500ms cubic-bezier(0.23, 1, 0.32, 1);
}

.reveal.is-visible {
    opacity: 1;
    transform: none;
}

@media (prefers-reduced-motion: reduce) {
    .reveal {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
