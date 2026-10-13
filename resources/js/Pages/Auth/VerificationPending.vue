<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    email: String,
    status: String
});

const form = useForm({});
const resendDisabled = ref(false);
const resendMessage = ref('');
const resendError = ref('');

const resend = () => {
    resendError.value = '';
    resendMessage.value = '';
    form.post(route('verification.send'), {
        onSuccess: () => {
            resendMessage.value = 'E-mail reenviado com sucesso.';
            resendDisabled.value = true;
            setTimeout(() => {
                resendDisabled.value = false;
            }, 30000);
        },
        onError: () => {
            resendError.value = 'Não foi possível reenviar o e-mail. Tente novamente.';
        }
    });
};
</script>

<template>
    <Head title="Verifique seu e-mail" />

    <AuthSplitLayout title="Verifique seu e-mail">
        <p class="-mt-6 mb-6 text-gray-600">
            Enviamos um link de verificação para
            <strong class="break-all font-semibold text-gray-900">{{ email }}</strong>.
            Clique nele para ativar sua conta.
        </p>

        <form class="space-y-4" @submit.prevent="resend">
            <PrimaryButton class="w-full py-3" :disabled="resendDisabled || form.processing">
                {{ resendDisabled ? 'Aguarde 30 segundos para reenviar' : 'Reenviar e-mail de verificação' }}
            </PrimaryButton>
        </form>

        <p v-if="resendMessage" class="mt-4 text-center text-sm text-brand-700" role="status">{{ resendMessage }}</p>
        <p v-if="resendError" class="mt-4 text-center text-sm text-red-600" role="alert">{{ resendError }}</p>

        <Link :href="route('logout')" method="post" as="button" class="btn-ghost mt-6 w-full">
            Cancelar e entrar com outra conta
        </Link>
    </AuthSplitLayout>
</template>
