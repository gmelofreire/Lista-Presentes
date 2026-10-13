<script setup>
import { Link } from '@inertiajs/vue3'
import PencilIcon from '@heroicons/vue/24/outline/PencilIcon'
import { GiftIcon } from '@heroicons/vue/24/outline'

defineProps({
    listas: {
        type: Object,
        required: true
    },
    grupoId: {
        type: String,
        default: null
    }
})
</script>

<template>
    <div>
        <ul v-if="(listas?.data?.length || 0) > 0" role="list"
            class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <li v-for="(lista, i) in listas.data" :key="lista.id" :style="{ '--i': Math.min(i, 8) }">
                <div class="surface-link group relative flex h-full flex-col overflow-hidden">
                    <Link :href="route('listas.show', lista.id)" class="block">
                        <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                            <img :src="lista.image_url || '/img/default_cover.png'" :alt="`Capa da lista ${lista.nome}`"
                                class="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]" />
                        </div>
                        <div class="p-5">
                            <h3 class="truncate font-semibold text-gray-900">{{ lista.nome }}</h3>
                            <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-sm text-gray-500">
                                {{ lista.descricao || 'Sem descrição' }}
                            </p>
                            <p v-if="lista.data_evento" class="mt-3 text-xs font-medium text-gray-600">
                                {{ new Date(lista.data_evento).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' }) }}
                            </p>
                        </div>
                    </Link>
                    <Link :href="route('listas.edit', lista.id)"
                        class="press absolute right-3 top-3 rounded-full bg-surface/90 p-2 text-gray-700 opacity-0 shadow-sm backdrop-blur hover:bg-surface focus-visible:opacity-100 group-hover:opacity-100 max-md:opacity-100"
                        aria-label="Editar lista">
                        <PencilIcon class="size-4" />
                    </Link>
                </div>
            </li>
        </ul>

        <div v-else class="surface flex flex-col items-center px-6 py-14 text-center">
            <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                <GiftIcon class="size-6" aria-hidden="true" />
            </span>
            <h3 class="mt-4 font-semibold text-gray-900">Este grupo ainda não tem listas</h3>
            <p class="mt-1 max-w-xs text-sm text-gray-500">
                Crie a primeira lista e os membros são avisados por e-mail.
            </p>
            <Link v-if="grupoId" :href="route('listas.create', { grupo_id: grupoId })" class="btn-primary mt-6">Nova lista</Link>
        </div>
    </div>
</template>
