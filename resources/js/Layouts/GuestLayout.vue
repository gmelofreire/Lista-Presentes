<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-[100dvh] flex-col items-center bg-gray-50 px-4 pt-8 sm:justify-center sm:pt-0">
        <Link href="/">
            <ApplicationLogo class="size-16 fill-current text-brand-700" />
        </Link>

        <div class="surface mt-6 w-full px-6 py-6 sm:max-w-md">
            <slot />
        </div>
    </div>
</template>
