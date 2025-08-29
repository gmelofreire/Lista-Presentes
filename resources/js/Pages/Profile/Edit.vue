<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { PencilIcon } from '@heroicons/vue/24/outline';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import Alert from '@/Components/Alert.vue';

const page = usePage();
const user = page.props.user;
const fileInput = ref(null);
const selectedFile = ref(null);
const previewUrl = ref(user.perfil.image_url);

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
    password: '',
    password_confirmation: '',
    perfil: {
        telefone: user.perfil.telefone,
        data_nascimento: user.perfil.data_nascimento,
        genero: user.perfil.genero,
    },
    imagem: null
});

const handleImageClick = () => {
    fileInput.value.click();
};


const compressImage = (file, maxSizeMB = 5, quality = 0.8) => {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = () => {
            // Calcular novo tamanho mantendo proporção
            let { width, height } = img;
            const maxDimension = 1200; // pixels
            
            if (width > height && width > maxDimension) {
                height = (height * maxDimension) / width;
                width = maxDimension;
            } else if (height > maxDimension) {
                width = (width * maxDimension) / height;
                height = maxDimension;
            }
            
            canvas.width = width;
            canvas.height = height;
            
            // Desenhar imagem redimensionada
            ctx.drawImage(img, 0, 0, width, height);
            
            // Converter para blob com qualidade especificada
            canvas.toBlob(resolve, 'image/jpeg', quality);
        };
        
        img.src = URL.createObjectURL(file);
    });
};

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        console.log('Arquivo original:', (file.size / 1024 / 1024).toFixed(2), 'MB');
        
        let processedFile = file;
        
        // Comprimir se for maior que 5MB
        if (file.size > 5 * 1024 * 1024) {
            console.log('Comprimindo imagem...');
            processedFile = await compressImage(file, 5, 0.8);
            console.log('Arquivo comprimido:', (processedFile.size / 1024 / 1024).toFixed(2), 'MB');
        }
        
        selectedFile.value = processedFile;
        form.imagem = processedFile;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target.result;
            console.log('Preview criado');
        };
        reader.readAsDataURL(processedFile);
    }
};

const submit = () => {
    form.post(route('perfil.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Perfil atualizado com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao atualizar perfil:', errors);
        }
    });
};
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
                        <div class="flex lg:justify-start">
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

                        <!-- Campos do formulário -->
                        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="sm:col-span-1">
                                <InputLabel for="nome" value="Nome" />
                                <TextInput id="nome" type="text" class="mt-1 block w-full" v-model="form.name" required
                                    autofocus autocomplete="nome" />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="sm:col-span-1">
                                <InputLabel for="username" value="Username" />
                                <TextInput id="username" type="text" class="mt-1 block w-full" v-model="form.username"
                                    required autofocus autocomplete="username" />
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
                                    v-model="form.perfil.data_nascimento" required autofocus />
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

                    <!-- Botão de salvar -->
                    <div class="flex justify-center mt-8 sm:mt-12">
                        <button type="button"
                            class="rounded-md w-full sm:w-auto sm:min-w-44 bg-indigo-600 px-6 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors duration-200"
                            @click="submit">
                            Salvar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
