<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Esqueci a senha" />

    <AuthSplitLayout title="Esqueceu a senha?" subtitle="Informe seu e-mail e enviaremos um link para criar uma nova.">
        <div v-if="status" class="mb-6 rounded-md bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800" role="status">
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail" />
                <TextInput id="email" type="email" class="mt-1.5 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <PrimaryButton class="w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Enviando...' : 'Enviar link de redefinição' }}
            </PrimaryButton>

            <p class="text-center text-sm text-gray-600">
                Lembrou da senha?
                <Link :href="route('login')" class="font-medium text-brand-700 hover:text-brand-800">Entrar</Link>
            </p>
        </form>
    </AuthSplitLayout>
</template>
