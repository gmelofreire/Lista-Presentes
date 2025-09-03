<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed } from 'vue';
import { StarIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    title: {
        type: String,
    },
    lista_id: {
        type: String,
    }
})

const form = useForm({
    nome: '',
    descricao: '',
    preco: '',
    link: '',
    image_url: '',
    anotacoes: '',
    lista_id: props.lista_id,
    avaliacao: 0,
})


const intensidadesDesejo = [
    'Quase não quero',
    'Seria legal ter',
    'Gostaria bastante',
    'Quero muito',
    'Preciso desse presente',
];

const imagePreview = ref(null);
const hoverRating = ref(0);

const hasErrorMessage = computed(() => {
    return Object.keys(form.errors).length > 0;
});

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type.startsWith('image/')) {
            console.log('Arquivo original:', (file.size / 1024 / 1024).toFixed(2), 'MB');

            let processedFile = file;

            if (file.size > 5 * 1024 * 1024) {
                console.log('Comprimindo imagem...');
                processedFile = await compressImage(file, 5, 0.8);
                console.log('Arquivo comprimido:', (processedFile.size / 1024 / 1024).toFixed(2), 'MB');
            }

            form.image_url = processedFile;

            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.value = e.target.result;
            };
            reader.readAsDataURL(processedFile);
        } else {
            alert('Por favor, selecione apenas arquivos de imagem.');
            event.target.value = '';
        }
    }
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

const removeImage = () => {
    imagePreview.value = null;
    form.image_url = null;
    document.getElementById('image_url').value = '';
};

const submit = () => {
    form.post(route('presentes.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Lista salva com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao salvar lista:', errors);
        }
    });
};

const getStarColor = (starNumber) => {
    const activeRating = hoverRating.value || form.avaliacao;
    return starNumber <= activeRating ? 'text-yellow-400' : 'text-gray-300';
};
</script>

<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <Alert v-if="hasErrorMessage" type="danger" title="Erro ao salvar perfil"
                            message="Por favor, corrija os erros abaixo e tente novamente." class="mb-6" />
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Novo Presente</h2>
                        </div>
                        <div class="mt-6">
                            <form>
                                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <div class="col-span-full">
                                        <InputLabel for="image_url" value="Foto do Presente" />
                                        <div v-if="imagePreview" class="mt-4">
                                            <div class="relative">
                                                <img :src="imagePreview" alt="Preview da imagem de capa"
                                                    class="w-full h-56 object-contain    rounded-lg border-2 border-gray-200 shadow-sm">
                                                <button type="button" @click="removeImage"
                                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                    ×
                                                </button>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-2">Preview da imagem selecionada</p>
                                        </div>
                                        <div class="mt-2">
                                            <input type="file" name="image_url" id="image_url" accept="image/*"
                                                @change="handleFileChange"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.image_url" />
                                    </div>
                                    <div class="sm:col-span-1">
                                        <InputLabel for="nome" value="Nome do Presente" :required="true" />
                                        <TextInput id="nome" type="text" class="mt-1 block w-full" v-model="form.nome"
                                            required autofocus autocomplete="nome" />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>
                                    <div class="sm:col-span-1">
                                        <InputLabel for="preco" value="Preço"/>
                                        <TextInput id="preco" type="number" class="mt-1 block w-full"
                                            v-model="form.preco" autofocus autocomplete="preco" />
                                        <InputError class="mt-2" :message="form.errors.preco" />
                                    </div>
                                    <div class="col-span-full">
                                        <InputLabel for="descricao" value="Descrição" />
                                        <textarea id="descricao" name="descricao" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            v-model="form.descricao"
                                            placeholder="Descreva sua lista de presentes..."></textarea>
                                        <InputError class="mt-2" :message="form.errors.descricao" />
                                    </div>
                                    <div class="col-span-full">
                                        <InputLabel for="link" value="Link" />
                                        <TextInput id="link" type="text" class="mt-1 block w-full" v-model="form.link"
                                            autofocus autocomplete="link" />
                                        <InputError class="mt-2" :message="form.errors.link" />
                                    </div>
                                    <div class="col-span-full">
                                        <InputLabel for="anotacoes" value="Anotações" />
                                        <textarea id="anotacoes" name="anotacoes" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            v-model="form.anotacoes"
                                            placeholder="Adicione anotações sobre o presente..."></textarea>
                                        <InputError class="mt-2" :message="form.errors.anotacoes" />
                                    </div>
                                    <div class="col-span-full">
                                        <InputLabel for="anotacoes" value="Avaliação" />
                                        <div class="flex justify-center">
                                            <span v-for="n in 5" :key="n" class="cursor-pointer"
                                                @click="form.avaliacao = n" 
                                                @mouseenter="hoverRating = n"
                                                @mouseleave="hoverRating = 0">
                                                <StarIcon :class="[
                                                    'size-7 transition-colors duration-200',
                                                    getStarColor(n)
                                                ]" />
                                            </span>
                                        </div>
                                        <div class="text-center mt-2">
                                            {{ intensidadesDesejo[(hoverRating || form.avaliacao) - 1] || 'Selecione uma avaliação' }}
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.anotacoes" />
                                    </div>                                    
                                    <div class="col-span-full">
                                        <div class="flex justify-end">
                                            <button @click="submit" type="button"
                                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                Salvar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

</template>
