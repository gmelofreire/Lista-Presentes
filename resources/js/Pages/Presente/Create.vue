<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { StarIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    title: {
        type: String,
    },
    lista_id: {
        type: String,
    },
    categorias: {
        type: [Array, Object],
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
    categoria_ids: [],
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
const showCategorias = ref(false);

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

const toggleCategoria = (categoriaId) => {
    const index = form.categoria_ids.indexOf(categoriaId);
    if (index > -1) {
        form.categoria_ids.splice(index, 1);
    } else {
        form.categoria_ids.push(categoriaId);
    }
};

const isCategoriaSelected = (categoriaId) => {
    return form.categoria_ids.includes(categoriaId);
};

const isDarkColor = (hexColor) => {
    if (!hexColor || hexColor.length < 6) return false;
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance < 0.5;
};

// Fechar dropdown ao clicar fora
const handleClickOutside = (event) => {
    const dropdown = document.querySelector('.categoria-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        showCategorias.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
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

                                        <!-- Área de Upload -->
                                        <div v-if="!imagePreview"
                                            class=" mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg cursor-pointer border-gray-300 hover:border-indigo-500 hover:bg-indigo-50 transition"
                                            @click="$refs.fileInput.click()">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor"
                                                    fill="none" viewBox="0 0 48 48">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M28 8H12a4 4 0 00-4 4v24a4 4 0 004 4h24a4 4 0 004-4V20l-12-12z" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600 justify-center">
                                                    <span
                                                        class="relative font-medium text-indigo-600 hover:text-indigo-500">
                                                        Clique para enviar
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500">PNG, JPG até 5MB</p>
                                            </div>
                                        </div>

                                        <!-- Input real escondido -->
                                        <input type="file" ref="fileInput" id="image_url" name="image_url"
                                            accept="image/*" class="hidden" @change="handleFileChange" />

                                        <!-- Preview -->
                                        <div v-if="imagePreview" class="mt-4 relative">
                                            <img :src="imagePreview" alt="Preview da imagem"
                                                class="w-full h-56 object-contain rounded-lg border shadow-sm" />
                                            <button type="button" @click="removeImage"
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                                ×
                                            </button>
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
                                        <InputLabel for="preco" value="Preço" />
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
                                        <InputLabel for="categoria_ids" value="Categorias" />

                                        <div class="mt-2 relative categoria-dropdown">
                                            <button type="button" @click="showCategorias = !showCategorias"
                                                class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 text-left shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-2 flex-wrap">
                                                        <span v-if="form.categoria_ids.length === 0"
                                                            class="text-gray-500">
                                                            Selecione as categorias
                                                        </span>
                                                        <div v-else class="flex items-center space-x-1 flex-wrap">
                                                            <div v-for="categoriaId in form.categoria_ids"
                                                                :key="categoriaId"
                                                                class="inline-flex items-center space-x-1 bg-gray-100 rounded-full px-2 py-1 text-xs">
                                                                <div class="w-3 h-3 rounded-full border border-white shadow-sm"
                                                                    :style="{ backgroundColor: categorias.find(c => c.id === categoriaId)?.hex_cor || '#6B7280' }">
                                                                </div>
                                                                <span class="text-gray-700">
                                                                    {{categorias.find(c => c.id === categoriaId)?.nome
                                                                    }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                                        :class="{ 'rotate-180': showCategorias }" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>
                                            </button>

                                            <!-- Dropdown de opções -->
                                            <div v-show="showCategorias"
                                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                                @click.stop>
                                                <div v-if="!categorias || categorias.length === 0"
                                                    class="px-3 py-2 text-gray-500 text-sm">
                                                    Nenhuma categoria disponível
                                                </div>
                                                <div v-else class="py-1">
                                                    <div v-for="categoria in categorias" :key="categoria.id"
                                                        @click="toggleCategoria(categoria.id)"
                                                        class="flex items-center space-x-3 px-3 py-2 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
                                                        :class="{ 'bg-indigo-50': isCategoriaSelected(categoria.id) }">
                                                        <!-- Checkbox visual -->
                                                        <div class="flex-shrink-0">
                                                            <div class="w-4 h-4 border-2 rounded flex items-center justify-center transition-all duration-150"
                                                                :class="isCategoriaSelected(categoria.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300'">
                                                                <svg v-if="isCategoriaSelected(categoria.id)"
                                                                    class="w-3 h-3 text-white" fill="currentColor"
                                                                    viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                        clip-rule="evenodd"></path>
                                                                </svg>
                                                            </div>
                                                        </div>

                                                        <!-- Círculo colorido -->
                                                        <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm flex items-center justify-center"
                                                            :style="{ backgroundColor: categoria.hex_cor || '#6B7280' }">
                                                            <span class="text-xs font-semibold"
                                                                :class="isDarkColor(categoria.hex_cor) ? 'text-white' : 'text-gray-800'">
                                                                {{ categoria.nome?.charAt(0)?.toUpperCase() }}
                                                            </span>
                                                        </div>

                                                        <!-- Nome da categoria -->
                                                        <div class="flex-1">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ categoria.nome }}
                                                            </div>
                                                            <div v-if="categoria.descricao"
                                                                class="text-xs text-gray-500 truncate">
                                                                {{ categoria.descricao }}
                                                            </div>
                                                        </div>

                                                        <!-- Código da cor -->
                                                        <div class="text-xs text-gray-400 font-mono">
                                                            {{ categoria.hex_cor }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <InputError class="mt-2" :message="form.errors.categoria_ids" />
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
                                                @click="form.avaliacao = n" @mouseenter="hoverRating = n"
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
