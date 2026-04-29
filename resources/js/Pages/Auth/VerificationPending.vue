<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    form.post(route('verification.send'), {
        onSuccess: () => {
            resendMessage.value = 'Email reenviado com sucesso!';
            resendDisabled.value = true;
            setTimeout(() => {
                resendDisabled.value = false;
            }, 30000);
        },
        onError: () => {
            resendError.value = 'Erro ao reenviar email. Tente novamente.';
        }
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center">
                    <svg class="h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Verifique seu email
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enviamos um email de verificação para<br>
                    <strong>{{ email }}</strong>
                </p>
            </div>

            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <p class="text-sm text-gray-500 text-center mb-6">
                    Por favor, clique no link enviado ao seu email para verificar sua conta. 
                    Se você não recebeu o email, podemos reenviá-lo.
                </p>

                <form @submit.prevent="resend">
                    <PrimaryButton
                        class="w-full flex justify-center"
                        :class="{ 'opacity-50': resendDisabled }"
                        :disabled="resendDisabled"
                    >
                        {{ resendDisabled ? 'Aguarde 30 segundos...' : 'Reenviar email de verificação' }}
                    </PrimaryButton>
                </form>

                <p v-if="resendMessage" class="mt-3 text-sm text-green-600 text-center">
                    {{ resendMessage }}
                </p>
                <p v-if="resendError" class="mt-3 text-sm text-red-600 text-center">
                    {{ resendError }}
                </p>
            </div>

            <div class="text-center">
                <form method="POST" action="/logout">
                    <input type="hidden" name="_token" :value="$page.props.csrfToken">
                    <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                        Cancelar e fazer login com outra conta
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>