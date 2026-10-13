<script setup>
import { Link } from '@inertiajs/vue3';
import { UsersIcon } from '@heroicons/vue/24/outline';

defineProps({
    amizades: {
        type: [Array, Object],
        default: () => [],
    },
})

const MAX_GRUPOS = 3
</script>

<template>
    <ul v-if="amizades.length > 0" role="list"
        class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <li v-for="(amizade, i) in amizades" :key="amizade.id" :style="{ '--i': Math.min(i, 8) }">
            <Link :href="route('amizades.show', amizade.id)"
                class="surface-link flex h-full flex-col items-center px-5 pb-5 pt-6 text-center">
                <img :src="amizade.perfil?.image_url ?? '/img/default_profile.png'" :alt="`Foto de ${amizade.name}`"
                    class="size-24 rounded-full bg-gray-100 object-cover ring-4 ring-gray-50" />

                <h3 class="mt-4 w-full truncate font-semibold text-gray-900">{{ amizade.name }}</h3>
                <p class="w-full truncate text-sm text-gray-500">@{{ amizade.username }}</p>

                <div v-if="amizade.grupos?.length" class="mt-4 flex flex-wrap justify-center gap-1.5">
                    <span v-for="grupo in amizade.grupos.slice(0, MAX_GRUPOS)" :key="grupo.id"
                        class="max-w-full truncate rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-800">
                        {{ grupo.nome }}
                    </span>
                    <span v-if="amizade.grupos.length > MAX_GRUPOS"
                        class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                        +{{ amizade.grupos.length - MAX_GRUPOS }}
                    </span>
                </div>
            </Link>
        </li>
    </ul>

    <div v-else class="surface flex flex-col items-center px-6 py-16 text-center">
        <span class="flex size-12 items-center justify-center rounded-full bg-brand-50 text-brand-700">
            <UsersIcon class="size-6" aria-hidden="true" />
        </span>
        <h3 class="mt-4 font-semibold text-gray-900">Nenhuma amizade por aqui</h3>
        <p class="mt-1 max-w-sm text-sm text-gray-500">
            Busque pessoas pelo nome ou @username para compartilhar suas listas de presentes.
        </p>
    </div>
</template>
