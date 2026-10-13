<script setup>
import { Link } from '@inertiajs/vue3';
import { GiftIcon } from '@heroicons/vue/24/outline';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div class="grid min-h-[100dvh] lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        <div class="flex flex-col px-5 py-8 sm:px-10 lg:px-16">
            <div class="flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-md bg-brand-600 text-white">
                        <GiftIcon class="size-5" aria-hidden="true" />
                    </span>
                    <span class="text-xl font-semibold tracking-tight text-gray-900">Desejário</span>
                </Link>
                <ThemeToggle />
            </div>

            <div class="flex flex-1 items-center py-10">
                <div class="mx-auto w-full max-w-sm animate-rise-in">
                    <h1 class="text-3xl font-semibold text-gray-900">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-2 text-gray-600">{{ subtitle }}</p>
                    <div class="mt-8">
                        <slot />
                    </div>
                </div>
            </div>
        </div>

        <aside class="relative hidden overflow-hidden bg-brand-900 lg:flex lg:flex-col lg:justify-end lg:p-16" aria-hidden="true">
            <div class="absolute -right-24 -top-24 size-[28rem] rounded-full bg-brand-800"></div>
            <div class="absolute -bottom-32 -left-16 size-[22rem] rounded-full bg-brand-700/60"></div>
            <div class="relative max-w-md">
                <p class="text-4xl font-semibold leading-tight tracking-tight text-white xl:text-5xl">
                    Ganhe o que você realmente quer.
                </p>
                <p class="mt-5 text-lg text-[#d7f3e3]">
                    Monte listas, convide amigos e acabe com os presentes repetidos.
                </p>
            </div>
        </aside>
    </div>
</template>
