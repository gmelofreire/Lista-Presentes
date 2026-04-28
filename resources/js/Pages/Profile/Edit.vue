<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { PencilIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
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
    confirm_desativar: false
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
        <div class="w-full">
            <div class="mx-auto max-w-7xl space-y-4 sm:space-y-6">
                <Alert 
                    v-if="hasSuccessMessage"
                    type="success" 
                    :title="page.props.flash?.status || page.props.status || 'Sucesso'"
                    class="mb-6"
                />
                <Alert 
                    v-if="hasErrorMessage"
                    type="danger" 
                    title="Erro ao salvar perfil"
                    message="Por favor, corrija os erros abaixo e tente novamente."
                    class="mb-6"
                />
                <div class="bg-white p-4 sm:p-6 lg:p-8 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg sm:text-xl text-center font-medium text-gray-900">
                            Informações Pessoais
                        </h2>
                    </header>

                    <div class="mt-6 sm:mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                        <div class="flex lg:justify-center">
                            <div class="flex flex-col items-center lg:items-start">
                                <div class="relative inline-block mx-auto">
                                    <input ref="fileInput" type="file" accept="image/*" class="hidden"
                                        @change="handleFileChange">

                                    <div class="relative size-32 sm:size-40 lg:size-52 rounded-full overflow-hidden cursor-pointer group"
                                        @click="handleImageClick">
                                        <img :src="previewUrl ?? '/img/default_profile.png'" alt="Foto de perfil"
                                            class="w-full h-full rounded-full bg-blue-200 object-cover transition-all duration-300 group-hover:blur-sm">
                                        <div
                                            class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-full">
                                            <PencilIcon
                                                class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 text-white mb-1 sm:mb-2" />
                                            <span class="text-white text-xs sm:text-sm font-medium">Editar foto</span>
                                        </div>
                                    </div>
                                </div>

                                <p
                                    class="mt-3 text-xs sm:text-sm text-gray-500 text-center lg:text-left max-w-32 sm:max-w-40 lg:max-w-52">
                                    Clique para editar foto.<br>
                                    <span class="text-gray-400">Máx: 10MB</span>
                                </p>
                            </div>
                        </div>

                        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="sm:col-span-1">
                                <InputLabel for="nome" value="Nome" />
                                <TextInput id="nome" type="text" class="mt-1 block w-full" v-model="form.name" required
                                    autofocus autocomplete="nome" />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel for="username" value="Username" />
                                <div class="relative">
                                    <TextInput id="username" type="text" class="mt-1 block w-full" v-model="form.username"
                                        required autofocus autocomplete="username"
                                        @blur="checkUsername" @input="checkUsername" />
                                    <div v-if="checkingUsername" class="absolute right-3 top-3">
                                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div v-else-if="usernameStatus" class="absolute right-3 top-3" 
                                        :class="usernameStatus.available ? 'text-green-500' : 'text-red-500'">
                                        {{ usernameStatus.available ? '✓' : '✗' }}
                                    </div>
                                </div>
                                <p v-if="usernameStatus" class="mt-1 text-xs" :class="usernameStatus.available ? 'text-green-600' : 'text-red-600'">
                                    {{ usernameStatus.message }}
                                </p>
                                <InputError class="mt-2" :message="form.errors.username" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel for="email" value="Email" />
                                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                                    required autofocus autocomplete="email" />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel for="telefone" value="Telefone" />
                                <PhoneInput id="telefone" class="mt-1 block w-full" v-model="form.perfil.telefone"
                                    placeholder="(11) 99999-9999" />
                                <InputError class="mt-2" :message="form.errors['perfil.telefone']" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel for="data_nascimento" value="Data de Nascimento" />
                                <TextInput id="data_nascimento" type="date" class="mt-1 block w-full"
                                    v-model="form.perfil.data_nascimento" />
                                <InputError class="mt-2" :message="form.errors['perfil.data_nascimento']" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel for="genero" value="Gênero" />
                                <select id="genero" name="genero"
                                    class="rounded-md mt-1 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full"
                                    v-model="form.perfil.genero">
                                    <option value="masculino">Masculino</option>
                                    <option value="feminino">Feminino</option>
                                    <option value="outro">Outro</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors['perfil.genero']" />
                            </div>
                        </div>
                    </div>

                    <div v-if="emailAlterado" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Alteração de Email
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Para alterar o email, confirme sua senha atual:</p>
                                </div>
                                <div class="mt-4">
                                    <InputLabel for="senha_atual" value="Senha Atual" />
                                    <TextInput id="senha_atual" type="password" class="mt-1 block w-full sm:w-1/2" 
                                        v-model="form.senha_atual" />
                                    <InputError class="mt-2" :message="form.errors.senha_atual" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center mt-8 sm:mt-12">
                        <button type="button"
                            class="rounded-md w-full sm:w-auto sm:min-w-44 bg-indigo-600 px-6 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors duration-200"
                            @click="submit">
                            Salvar
                        </button>
                    </div>
                </div>

                <div class="bg-white p-4 sm:p-6 lg:p-8 shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg sm:text-xl text-center font-medium text-gray-900">
                            Zona de Perigo
                        </h2>
                    </header>
                    
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-600 mb-4">
                            Desativar sua conta temporariamente. Você pode reativar a qualquer momento fazendo login novamente.
                        </p>
                        <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150"
                            @click="showDesativarModal = true">
                            <ExclamationTriangleIcon class="w-4 h-4 mr-2" />
                            Desativar Minha Conta
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showDesativarModal" @close="showDesativarModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Desativar Conta
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Tem certeza que deseja desativar sua conta? Você poderá reativar a qualquer momento fazendo login novamente.
                </p>
                <div class="mt-4">
                    <InputLabel for="confirm_desativar" value="Digite 'DESATIVAR' para confirmar" class="mb-2" />
                    <TextInput id="confirm_desativar" type="text" class="mt-1 block w-full" 
                        v-model="form.confirm_desativar" />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        @click="showDesativarModal = false">
                        Cancelar
                    </button>
                    <button type="button" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-500 disabled:opacity-50"
                        :disabled="form.confirm_desativar !== 'DESATIVAR'"
                        @click="submitDesativar">
                        Desativar Conta
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>