<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Redefinir senha" />

    <AuthSplitLayout title="Criar nova senha" subtitle="Escolha uma senha forte que você ainda não usou.">
        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail" />
                <TextInput id="email" type="email" class="mt-1.5 block w-full" v-model="form.email" required
                    autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Nova senha" />
                <TextInput id="password" type="password" class="mt-1.5 block w-full" v-model="form.password" required
                    autofocus autocomplete="new-password" />
                <PasswordStrength :password="form.password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar nova senha" />
                <TextInput id="password_confirmation" type="password" class="mt-1.5 block w-full"
                    v-model="form.password_confirmation" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton class="w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Salvando...' : 'Redefinir senha' }}
            </PrimaryButton>

            <p class="text-center text-sm text-gray-600">
                Lembrou da senha?
                <Link :href="route('login')" class="font-medium text-brand-700 hover:text-brand-800">Entrar</Link>
            </p>
        </form>
    </AuthSplitLayout>
</template>
