<script setup>
import { computed } from 'vue';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <Head title="Verificar e-mail" />

    <AuthSplitLayout title="Confirme seu e-mail"
        subtitle="Enviamos um link para o e-mail do cadastro. Clique nele para ativar sua conta.">
        <div v-if="verificationLinkSent" class="mb-6 rounded-md bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800" role="status">
            Enviamos um novo link de verificação para o e-mail informado no cadastro.
        </div>

        <p class="mb-6 text-sm text-gray-600">Não chegou? Veja a caixa de spam ou peça um novo envio.</p>

        <form class="space-y-4" @submit.prevent="submit">
            <PrimaryButton class="w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Enviando...' : 'Reenviar e-mail de verificação' }}
            </PrimaryButton>

            <Link :href="route('logout')" method="post" as="button" class="btn-ghost w-full">
                Sair
            </Link>
        </form>
    </AuthSplitLayout>
</template>
