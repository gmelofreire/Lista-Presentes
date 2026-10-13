<script setup>
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Confirmar senha" />

    <AuthSplitLayout title="Confirme sua senha" subtitle="Esta é uma área protegida. Digite sua senha para continuar.">
        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Senha" />
                <TextInput id="password" type="password" class="mt-1.5 block w-full" v-model="form.password" required
                    autocomplete="current-password" autofocus />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <PrimaryButton class="w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Confirmando...' : 'Confirmar' }}
            </PrimaryButton>
        </form>
    </AuthSplitLayout>
</template>
