<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';
import { computed } from 'vue';
import { Carousel, Slide, Navigation } from 'vue3-carousel'
import 'vue3-carousel/dist/carousel.css';
import { 
    ChevronRightIcon, 
    UserPlusIcon, 
    ChatBubbleLeftIcon, 
    UserIcon, 
    SparklesIcon,
    CheckIcon,
    ClockIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

defineProps({
    title: {
        type: String,
        default: 'Detalhes da Amizade'
    },
    amigo: {
        type: Object,
        default: () => ({})
    },
    amizade: {
        type: Object,
        default: () => ({})
    },
    grupos: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage();

const currentUser = computed(() => page.props.auth?.user || {});

const hasSuccessMessage = computed(() => {
    return page.props.flash?.status || page.props.status;
});

// Configurações responsivas do carrossel
const carouselSettings = computed(() => {
    return {
        itemsToShow: 1.5,
        snapAlign: 'start',
        wrapAround: false,
        transition: 500,
        mouseDrag: true,
        touchDrag: true,
        gap: 24,
    };
});

// Configurações de breakpoints para responsividade
const breakpoints = computed(() => ({
    640: {
        itemsToShow: 2.5,
        snapAlign: 'start',
    },
    768: {
        itemsToShow: 3.5,
        snapAlign: 'start',
    },
    1024: {
        itemsToShow: 4.5, // Mostrar mais itens para ficar parecido com o design horizontal
        snapAlign: 'start',
    }
}));

</script>

<template>

    <Head :title="title" />
    <AppLayout :title="title">

            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <Alert v-if="hasSuccessMessage" type="success"
                    :title="page.props.flash?.status || page.props.status || 'Sucesso'" class="mb-6" />

                <!-- Profile Header Card -->
                <div class="bg-white border border-gray-200 rounded-xl p-8 mb-8 shadow-sm">
                    <div class="flex flex-col md:flex-row gap-8 items-center md:items-start text-center md:text-left">
                        <!-- Large Profile Avatar -->
                        <div class="relative shrink-0">
                            <img :src="amigo?.perfil?.image_url" 
                                 class="size-32 rounded-full border-4 border-indigo-50 shadow-lg object-cover bg-gray-100" 
                                 alt="Foto de perfil">
                        </div>

                        <!-- Profile Info -->
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="flex flex-col gap-1 mb-4">
                                <h1 class="text-gray-900 text-3xl font-bold leading-tight tracking-tight">{{ amigo?.name }}</h1>
                                <p class="text-indigo-600 font-medium text-lg">@{{ amigo?.username }}</p>
                            </div>
                            <p class="text-gray-500 text-base font-normal leading-relaxed max-w-xl">
                                {{ amigo?.perfil?.biografia || 'Sem biografia disponível.' }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-row md:flex-col gap-3 w-full md:w-auto mt-4 md:mt-0">
                            <!-- Caso 1: Sem amizade (Botão Seguir) -->
                            <Link :href="route('amizade.store', amigo?.id)" method="post" as="button"
                                v-if="!amizade"
                                class="flex flex-1 md:min-w-[160px] cursor-pointer items-center justify-center rounded-lg h-11 px-6 py-2 bg-indigo-600 text-white text-sm font-bold tracking-wide hover:bg-indigo-700 transition-all shadow-md gap-2">
                                <UserPlusIcon class="size-5" />
                                <span class="truncate">Seguir</span>
                            </Link>

                            <!-- Caso 2: Amizade Pendente -->
                            <template v-else-if="amizade.status === 'pendente'">
                                <!-- Subcaso 2.1: Eu enviei a solicitação -->
                                <Link :href="route('amizades.destroy', amigo?.id)" method="delete" as="button"
                                    v-if="amizade.usuario_id == currentUser.id"
                                    class="flex flex-1 md:min-w-[160px] cursor-pointer items-center justify-center rounded-lg h-11 px-6 py-2 bg-gray-100 text-gray-700 text-sm font-bold tracking-wide hover:bg-gray-200 transition-all border border-gray-200 gap-2">
                                    <ClockIcon class="size-5" />
                                    <span class="truncate">Solicitação Enviada</span>
                                </Link>

                                <!-- Subcaso 2.2: Eu recebi a solicitação (Botões Aceitar/Recusar) -->
                                <div v-else class="flex flex-col gap-2 w-full">
                                    <Link :href="route('amizades.update', amigo?.id)" method="put" as="button"
                                        class="flex flex-1 md:min-w-[160px] cursor-pointer items-center justify-center rounded-lg h-11 px-6 py-2 bg-indigo-600 text-white text-sm font-bold tracking-wide hover:bg-indigo-700 transition-all shadow-md gap-2">
                                        <CheckIcon class="size-5" />
                                        <span class="truncate">Aceitar</span>
                                    </Link>
                                    <Link :href="route('amizades.destroy', amigo?.id)" method="delete" as="button"
                                        class="flex flex-1 md:min-w-[160px] cursor-pointer items-center justify-center rounded-lg h-11 px-6 py-2 bg-red-100 text-red-700 text-sm font-bold tracking-wide hover:bg-red-200 transition-all border border-red-200 gap-2">
                                        <XMarkIcon class="size-5" />
                                        <span class="truncate">Recusar</span>
                                    </Link>
                                </div>
                            </template>

                            <!-- Caso 3: Amizade Aceita -->
                            <Link :href="route('amizades.destroy', amigo?.id)" method="delete" as="button"
                                v-else-if="amizade.status === 'aceito'"
                                class="flex flex-1 md:min-w-[160px] cursor-pointer items-center justify-center rounded-lg h-11 px-6 py-2 bg-green-600 text-white text-sm font-bold tracking-wide hover:bg-green-700 transition-all shadow-md gap-2">
                                <CheckIcon class="size-5" />
                                <span class="truncate">Amigos</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Action Panel / Privacy Notice -->
                <!-- <div class="mb-8" v-if="!amizade || amizade.status !== 'aceito'">
                    <div class="flex flex-1 flex-col items-start justify-between gap-4 rounded-xl border border-dashed border-gray-300 bg-gray-50/50 p-6 md:flex-row md:items-center">
                        <div class="flex flex-col gap-1">
                            <p class="text-gray-900 text-base font-bold leading-tight">Quer ver a lista de desejos?</p>
                            <p class="text-gray-500 text-sm font-normal leading-normal">
                                {{ amigo?.name }} compartilha seus desejos apenas com amigos confirmados. Envie uma solicitação!
                            </p>
                        </div>
                        <Link :href="route('amizade.store', amigo?.id)" method="post" as="button" v-if="!amizade"
                             class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-9 px-5 bg-indigo-50 text-indigo-600 text-sm font-bold hover:bg-indigo-100 transition-all">
                            <span class="truncate">Solicitar Amizade</span>
                        </Link>
                    </div>
                </div> -->

                <!-- Section: Grupos em Comum -->
                <div class="mb-12" v-if="amigo?.grupos && amigo.grupos.length > 0">
                    <div class="flex justify-between items-center px-2 mb-6">
                        <h2 class="text-gray-900 text-xl font-bold leading-tight tracking-tight">Grupos em Comum</h2>

                        <span class="text-indigo-600 text-sm font-bold hover:underline cursor-pointer">Ver todos</span>
                    </div>

                    <!-- Horizontal Carousel adaptado para o estilo do design -->
                    <Carousel v-bind="carouselSettings" :breakpoints="breakpoints" class="grupos-carousel pb-4">
                        <Slide v-for="grupo in grupos" :key="grupo.id">
                            <Link :href="route('grupos.show', grupo.id)" class="w-full h-full px-2 block">
                                <div class="flex h-full w-full flex-col gap-4 text-center rounded-xl bg-white border border-gray-200 p-5 shrink-0 hover:shadow-lg hover:border-indigo-200 transition-all duration-300 group cursor-pointer">
                                    <div class="bg-center bg-no-repeat aspect-video bg-cover rounded-xl flex flex-col self-center w-full border border-gray-100 group-hover:opacity-90 transition-opacity" 
                                         :style="{ backgroundImage: `url(${grupo.image_url})` }">
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <p class="text-gray-900 text-sm font-bold leading-normal truncate w-full group-hover:text-indigo-600 transition-colors">
                                            {{ grupo.nome }}
                                        </p>
                                        <p class="text-gray-500 text-xs font-normal leading-normal mt-1">
                                            {{ grupo.descricao || 'Grupo' }}
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        </Slide>
                        
                        <template #addons>
                            <Navigation />
                        </template>
                    </Carousel>
                </div>

                <!-- Footer Section Info -->
                <!-- <div class="border-t border-gray-200 pt-8 pb-12 flex flex-col items-center gap-4 text-center">
                    <SparklesIcon class="size-10 text-gray-400" />
                    <p class="text-gray-500 text-sm italic">
                        "O melhor presente é aquele que é compartilhado."
                    </p>
                </div> -->

            </div>
    </AppLayout>
</template>

<style scoped>
/* Estilos personalizados para o vue-carousel */
.grupos-carousel {
    position: relative;
}

/* Container do carrossel */
:deep(.carousel__viewport) {
    overflow: visible; /* Permitir ver as sombras */
    padding-bottom: 20px; /* Espaço para sombra */
}

:deep(.carousel__track) {
    display: flex;
    align-items: stretch; /* Cards com mesma altura */
}

/* Slides */
:deep(.carousel__slide) {
    padding: 0;
    flex-shrink: 0;
    height: auto; /* Altura automática baseada no conteúdo */
}

/* Estilização dos botões de navegação */
:deep(.carousel__prev),
:deep(.carousel__next) {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
    z-index: 10;
}

:deep(.carousel__prev):hover,
:deep(.carousel__next):hover {
    background: #f9fafb;
    border-color: #6366f1;
    color: #6366f1;
}

:deep(.carousel__prev svg),
:deep(.carousel__next svg) {
    width: 20px;
    height: 20px;
}

/* Ajustes de posição dos botões */
:deep(.carousel__prev) {
    left: -20px;
}

:deep(.carousel__next) {
    right: -20px;
}

@media (max-width: 768px) {
    :deep(.carousel__prev) {
        left: 0px;
    }
    :deep(.carousel__next) {
        right: 0px;
    }
}
</style>