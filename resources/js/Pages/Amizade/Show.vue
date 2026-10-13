<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';
import { computed } from 'vue';
import { Carousel, Slide, Navigation } from 'vue3-carousel'
import 'vue3-carousel/dist/carousel.css';
import { 
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
        type: [Array, Object],
        default: () => []
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
        <div class="space-y-8">
            <Alert v-if="hasSuccessMessage" type="success"
                :title="page.props.flash?.status || page.props.status || 'Sucesso'" />

            <!-- Perfil -->
            <section class="surface p-6 sm:p-8">
                <div class="flex flex-col items-center gap-6 text-center md:flex-row md:items-start md:text-left">
                    <img :src="amigo?.perfil?.image_url ?? '/img/default_profile.png'" alt="Foto de perfil"
                        class="size-32 shrink-0 rounded-full bg-gray-100 object-cover ring-4 ring-gray-50" />

                    <div class="min-w-0 flex-1">
                        <h2 class="text-3xl font-semibold text-gray-900">{{ amigo?.name }}</h2>
                        <p class="mt-1 text-lg font-medium text-brand-700">@{{ amigo?.username }}</p>
                        <p class="mt-3 max-w-xl text-gray-600">
                            {{ amigo?.perfil?.biografia || 'Sem biografia por enquanto.' }}
                        </p>
                    </div>

                    <div v-if="amigo.id !== currentUser.id" class="flex w-full flex-col gap-2 md:w-auto md:min-w-[11rem]">
                        <!-- Sem relacao -->
                        <Link v-if="!amizade" :href="route('amizade.store', amigo?.id)" method="post" as="button" class="btn-primary">
                            <UserPlusIcon class="size-5" aria-hidden="true" />
                            Adicionar amigo
                        </Link>

                        <!-- Pendente -->
                        <template v-else-if="amizade.status === 'pendente'">
                            <Link v-if="amizade.usuario_id == currentUser.id" :href="route('amizades.destroy', amigo?.id)"
                                method="delete" as="button" class="btn-secondary">
                                <ClockIcon class="size-5" aria-hidden="true" />
                                Cancelar solicitação
                            </Link>
                            <template v-else>
                                <Link :href="route('amizades.update', amigo?.id)" method="put" as="button" class="btn-primary">
                                    <CheckIcon class="size-5" aria-hidden="true" />
                                    Aceitar
                                </Link>
                                <Link :href="route('amizades.destroy', amigo?.id)" method="delete" as="button" class="btn-secondary">
                                    <XMarkIcon class="size-5" aria-hidden="true" />
                                    Recusar
                                </Link>
                            </template>
                        </template>

                        <!-- Amigos -->
                        <Link v-else-if="amizade.status === 'aceito'" :href="route('amizades.destroy', amigo?.id)"
                            method="delete" as="button" class="btn-secondary group"
                            :on-before="() => confirm(`Desfazer a amizade com ${amigo?.name}?`)">
                            <CheckIcon class="size-5 text-brand-700" aria-hidden="true" />
                            <span class="group-hover:hidden">Amigos</span>
                            <span class="hidden group-hover:inline">Desfazer amizade</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Grupos em comum -->
            <section v-if="grupos && grupos.length > 0">
                <h3 class="mb-5 text-xl font-semibold text-gray-900">Grupos em comum</h3>

                <Carousel v-bind="carouselSettings" :breakpoints="breakpoints" class="grupos-carousel pb-4">
                    <Slide v-for="grupo in grupos" :key="grupo.id">
                        <Link :href="route('grupos.show', grupo.id)" class="block size-full px-2">
                            <div class="surface-link group flex h-full w-full flex-col overflow-hidden text-left">
                                <div class="aspect-video overflow-hidden bg-brand-100">
                                    <img v-if="grupo.image_url" :src="grupo.image_url" :alt="`Imagem do grupo ${grupo.nome}`"
                                        class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" />
                                </div>
                                <div class="p-4">
                                    <p class="truncate font-semibold text-gray-900">{{ grupo.nome }}</p>
                                    <p class="mt-1 line-clamp-1 text-sm text-gray-500">{{ grupo.descricao || 'Grupo' }}</p>
                                </div>
                            </div>
                        </Link>
                    </Slide>

                    <template #addons>
                        <Navigation />
                    </template>
                </Carousel>
            </section>

            <section v-else-if="amizade?.status === 'aceito'" class="surface px-6 py-10 text-center">
                <p class="text-sm text-gray-500">Vocês ainda não participam do mesmo grupo.</p>
            </section>
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
    background: rgb(var(--surface));
    color: rgb(var(--gray-700));
    border: 1px solid rgb(var(--gray-200));
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
    background: rgb(var(--gray-50));
    border-color: #1a7f56;
    color: #1a7f56;
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