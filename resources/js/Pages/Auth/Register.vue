<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: '',
    termos: false,
});

const usernameStatus = ref(null);
const checkingUsername = ref(false);
let debounceTimer = null;

const checkUsername = () => {
    if (form.username.length < 3) {
        usernameStatus.value = null;
        return;
    }

    clearTimeout(debounceTimer);
    checkingUsername.value = true;

    debounceTimer = setTimeout(() => {
        fetch(`/api/check-username?username=${encodeURIComponent(form.username)}`)
            .then(res => res.json())
            .then(data => {
                usernameStatus.value = data;
                if (!data.available) {
                    form.errors.username = data.message;
                } else {
                    delete form.errors.username;
                }
            })
            .catch(() => {
                usernameStatus.value = null;
            })
            .finally(() => {
                checkingUsername.value = false;
            });
    }, 500);
};

const getUsernameColor = () => {
    if (!usernameStatus.value) return '';
    return usernameStatus.value.available ? 'text-green-600' : 'text-red-600';
};

const getUsernameIcon = () => {
    if (!usernameStatus.value) return '';
    return usernameStatus.value.available ? '✓' : '✗';
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Cadastro" />

    <AuthSplitLayout title="Criar conta" subtitle="Leva menos de um minuto.">
        <form class="space-y-5" @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nome" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1.5 block w-full"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1.5 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel for="username" value="Username" />
                        <div class="relative">
                            <TextInput
                                id="username"
                                type="text"
                                class="mt-1.5 block w-full"
                                v-model="form.username"
                                required
                                autocomplete="username"
                                @blur="checkUsername"
                                @input="checkUsername"
                            />
                            <div v-if="checkingUsername" class="absolute right-3 top-3">
                                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <div v-else-if="usernameStatus" class="absolute right-3 top-3" :class="getUsernameColor()">
                                {{ getUsernameIcon() }}
                            </div>
                        </div>
                        <p v-if="usernameStatus" class="mt-1 text-sm" :class="getUsernameColor()">
                            {{ usernameStatus.message }}
                        </p>
                        <InputError class="mt-2" :message="form.errors.username" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Senha" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1.5 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                        <PasswordStrength :password="form.password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Senha" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1.5 block w-full"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-start">
                        <input
                            id="termos"
                            type="checkbox"
                            v-model="form.termos"
                            class="size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 mt-0.5"
                            required
                        />
                        <label for="termos" class="ml-2 block text-sm text-gray-900">
                            Eu aceito os <a href="/termos" class="text-brand-700 hover:text-brand-800 underline">Termos de Uso</a> e a <a href="/privacidade" class="text-brand-700 hover:text-brand-800 underline">Política de Privacidade</a>
                        </label>
                    </div>
                    <InputError class="mt-2" :message="form.errors.termos" />
                </div>

                <div>
                    <PrimaryButton
                        class="w-full py-3"
                        :disabled="form.processing"
                    >
                        Criar Conta
                    </PrimaryButton>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Já tem uma conta?
                        <Link
                            :href="route('login')"
                            class="font-medium text-brand-700 hover:text-brand-800"
                        >
                            Faça login aqui
                        </Link>
                    </p>
                </div>
            </form>
    </AuthSplitLayout>
</template>
