<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center">
                    <svg class="h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Lista de Presentes
                </h2>
            </div>
            
            <div v-if="status" class="mb-4 text-sm font-medium text-green-600 text-center">
                {{ status }}
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput 
                            id="email" 
                            type="email" 
                            class="mt-1 block w-full" 
                            v-model="form.email" 
                            required 
                            autofocus 
                            autocomplete="username" 
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Senha" />
                        <TextInput 
                            id="password" 
                            type="password" 
                            class="mt-1 block w-full" 
                            v-model="form.password" 
                            required 
                            autocomplete="current-password" 
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                </div>

                <div class="flex items-center justify-between">

                    <div class="text-sm">
                        <Link 
                            v-if="canResetPassword" 
                            :href="route('password.request')"
                            class="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Esqueceu sua senha?
                        </Link>
                    </div>
                </div>

                <div>
                    <PrimaryButton 
                        class="group relative w-full flex justify-center" 
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Entrar
                    </PrimaryButton>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Não tem uma conta?
                        <Link 
                            :href="route('register')"
                            class="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Cadastre-se aqui
                        </Link>
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>
