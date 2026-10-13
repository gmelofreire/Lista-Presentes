<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CameraIcon, ExclamationTriangleIcon, ArrowPathIcon, CheckCircleIcon, XCircleIcon, KeyIcon } from '@heroicons/vue/24/outline';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import Alert from '@/Components/Alert.vue';
import Modal from '@/Components/Modal.vue';

const page = usePage();
const user = page.props.auth.user;
const fileInput = ref(null);
const selectedFile = ref(null);
const previewUrl = ref(user.perfil.image_url);
const showDesativarModal = ref(false);

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    title: {
        type: String,
    }
});

const hasSuccessMessage = computed(() => {
    return page.props.flash?.status || page.props.status;
});

const hasErrorMessage = computed(() => Object.keys(form.errors).length > 0);

const form = useForm({
    name: user.name,
    email: user.email,
    username: user.username,
    senha_atual: '',
    perfil: {
        telefone: user.perfil?.telefone || '',
        data_nascimento: user.perfil?.data_nascimento || '',
        genero: user.perfil?.genero || '',
    },
    imagem: null,
    confirm_desativar: ''
});

const usernameStatus = ref(null);
const checkingUsername = ref(false);
let debounceTimer = null;

const checkUsername = () => {
    if (form.username.length < 3) {
        usernameStatus.value = null;
        return;
    }
    
    if (form.username === user.username) {
        usernameStatus.value = { available: true, message: 'Seu username atual' };
        return;
    }

    clearTimeout(debounceTimer);
    checkingUsername.value = true;

    debounceTimer = setTimeout(() => {
        fetch(`/api/check-username?username=${encodeURIComponent(form.username)}&exclude_user_id=${user.id}`)
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

const handleImageClick = () => {
    fileInput.value.click();
};

const compressImage = (file, maxSizeMB = 5, quality = 0.8) => {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = () => {
            let { width, height } = img;
            const maxDimension = 1200;
            
            if (width > height && width > maxDimension) {
                height = (height * maxDimension) / width;
                width = maxDimension;
            } else if (height > maxDimension) {
                width = (width * maxDimension) / height;
                height = maxDimension;
            }
            
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);
            canvas.toBlob(resolve, 'image/jpeg', quality);
        };
        
        img.src = URL.createObjectURL(file);
    });
};

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        let processedFile = file;
        
        if (file.size > 5 * 1024 * 1024) {
            processedFile = await compressImage(file, 5, 0.8);
        }
        
        selectedFile.value = processedFile;
        form.imagem = processedFile;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target.result;
        };
        reader.readAsDataURL(processedFile);
    }
};

const submit = () => {
    form.post(route('perfil.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const submitDesativar = () => {
    form.delete(route('perfil.destroy'), {
        onSuccess: () => {
            showDesativarModal.value = false;
        }
    });
};

const emailAlterado = computed(() => form.email !== user.email);
</script>

<template>
    <Head :title="title" />

    <AppLayout :title="title">
        <div class="mx-auto max-w-4xl space-y-8">
            <Alert v-if="hasSuccessMessage" type="success"
                :title="page.props.flash?.status || page.props.status || 'Sucesso'" />
            <Alert v-if="hasErrorMessage" type="danger" title="Não foi possível salvar"
                message="Corrija os campos indicados e tente novamente." />

            <div>
                <h2 class="text-3xl font-semibold text-gray-900">Seu perfil</h2>
                <p class="mt-1 text-gray-600">Como as outras pessoas veem você no Desejário.</p>
            </div>

            <form class="surface p-5 sm:p-8" @submit.prevent="submit">
                <div class="grid gap-8 md:grid-cols-[auto_minmax(0,1fr)] md:gap-10">
                    <!-- Foto -->
                    <div class="flex flex-col items-center gap-3">
                        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange">
                        <button type="button" @click="handleImageClick" class="press group relative size-36 overflow-hidden rounded-full sm:size-44"
                            aria-label="Trocar foto de perfil">
                            <img :src="previewUrl ?? '/img/default_profile.png'" alt="Foto de perfil"
                                class="size-full bg-gray-100 object-cover" />
                            <span class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-zinc-950/50 text-white opacity-0 transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 max-md:opacity-100 max-md:bg-zinc-950/35">
                                <CameraIcon class="size-6" aria-hidden="true" />
                                <span class="text-xs font-medium">Trocar foto</span>
                            </span>
                        </button>
                        <p class="text-xs text-gray-500">JPG ou PNG</p>
                    </div>

                    <!-- Campos -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <InputLabel for="nome" value="Nome" />
                            <TextInput id="nome" type="text" class="mt-1.5 block w-full" v-model="form.name" required autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="username" value="Username" />
                            <div class="relative">
                                <TextInput id="username" type="text" class="mt-1.5 block w-full pr-10" v-model="form.username" required
                                    autocomplete="username" @blur="checkUsername" @input="checkUsername" />
                                <span class="absolute right-3 top-1/2 mt-0.5 -translate-y-1/2">
                                    <ArrowPathIcon v-if="checkingUsername" class="size-5 animate-spin text-gray-400" aria-hidden="true" />
                                    <CheckCircleIcon v-else-if="usernameStatus?.available" class="size-5 text-brand-600" aria-hidden="true" />
                                    <XCircleIcon v-else-if="usernameStatus" class="size-5 text-red-500" aria-hidden="true" />
                                </span>
                            </div>
                            <p v-if="usernameStatus" class="mt-1.5 text-xs" :class="usernameStatus.available ? 'text-brand-700' : 'text-red-600'">
                                {{ usernameStatus.message }}
                            </p>
                            <InputError class="mt-2" :message="form.errors.username" />
                        </div>

                        <div>
                            <InputLabel for="email" value="E-mail" />
                            <TextInput id="email" type="email" class="mt-1.5 block w-full" v-model="form.email" required autocomplete="email" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="telefone" value="Telefone" />
                            <PhoneInput id="telefone" class="mt-1.5 block w-full" v-model="form.perfil.telefone" placeholder="(11) 99999-9999" />
                            <InputError class="mt-2" :message="form.errors['perfil.telefone']" />
                        </div>

                        <div>
                            <InputLabel for="data_nascimento" value="Data de nascimento" />
                            <TextInput id="data_nascimento" type="date" class="mt-1.5 block w-full" v-model="form.perfil.data_nascimento" />
                            <InputError class="mt-2" :message="form.errors['perfil.data_nascimento']" />
                        </div>

                        <div>
                            <InputLabel for="genero" value="Gênero" />
                            <select id="genero" name="genero" class="mt-1.5 block w-full" v-model="form.perfil.genero">
                                <option value="">Prefiro não informar</option>
                                <option value="masculino">Masculino</option>
                                <option value="feminino">Feminino</option>
                                <option value="outro">Outro</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors['perfil.genero']" />
                        </div>

                        <!-- Confirmacao para trocar e-mail -->
                        <div v-if="emailAlterado" class="col-span-full rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <div class="flex items-start gap-3">
                                <KeyIcon class="mt-0.5 size-5 shrink-0 text-amber-600" aria-hidden="true" />
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-amber-900">Confirme sua senha para trocar o e-mail</h3>
                                    <div class="mt-3 max-w-xs">
                                        <InputLabel for="senha_atual" value="Senha atual" />
                                        <TextInput id="senha_atual" type="password" class="mt-1.5 block w-full" v-model="form.senha_atual"
                                            autocomplete="current-password" />
                                        <InputError class="mt-2" :message="form.errors.senha_atual" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end border-t border-gray-100 pt-6">
                    <button type="submit" class="btn-primary min-w-40" :disabled="form.processing">
                        {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
                    </button>
                </div>
            </form>

            <section class="rounded-xl border border-red-200 bg-surface p-5 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Desativar conta</h3>
                        <p class="mt-1 max-w-xl text-sm text-gray-600">
                            Seu perfil e suas listas ficam ocultos. Para reativar, basta entrar de novo.
                        </p>
                    </div>
                    <button type="button" class="btn-danger self-start sm:self-auto" @click="showDesativarModal = true">
                        <ExclamationTriangleIcon class="size-4" aria-hidden="true" />
                        Desativar conta
                    </button>
                </div>
            </section>
        </div>

        <Modal :show="showDesativarModal" max-width="md" @close="showDesativarModal = false">
            <div class="p-6">
                <span class="flex size-12 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <ExclamationTriangleIcon class="size-6" aria-hidden="true" />
                </span>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Desativar sua conta?</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Você poderá reativá-la a qualquer momento fazendo login novamente.
                </p>
                <div class="mt-5">
                    <InputLabel for="confirm_desativar" value="Digite DESATIVAR para confirmar" />
                    <TextInput id="confirm_desativar" type="text" class="mt-1.5 block w-full" v-model="form.confirm_desativar"
                        autocomplete="off" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn-secondary" @click="showDesativarModal = false">Cancelar</button>
                    <button type="button" class="btn-danger" :disabled="form.confirm_desativar !== 'DESATIVAR'" @click="submitDesativar">
                        Desativar conta
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
