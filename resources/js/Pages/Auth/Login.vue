<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Entrar" />

    <AuthSplitLayout title="Entrar" subtitle="Acesse suas listas e acompanhe seus amigos.">
        <div v-if="status" class="mb-6 rounded-md bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800" role="status">
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail ou username" />
                <TextInput id="email" type="text" class="mt-1.5 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Senha" />
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-sm font-medium text-brand-700 hover:text-brand-800">
                        Esqueceu a senha?
                    </Link>
                </div>
                <TextInput id="password" type="password" class="mt-1.5 block w-full" v-model="form.password" required
                    autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <PrimaryButton class="w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Entrando...' : 'Entrar' }}
            </PrimaryButton>

            <p class="text-center text-sm text-gray-600">
                Ainda não tem conta?
                <Link :href="route('register')" class="font-medium text-brand-700 hover:text-brand-800">
                    Criar conta
                </Link>
            </p>
        </form>
    </AuthSplitLayout>
</template>
