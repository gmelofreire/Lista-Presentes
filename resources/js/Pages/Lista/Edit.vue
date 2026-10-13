<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const visibilidades = [
    { valor: 'publica', nome: 'Pública', texto: 'Amigos e membros do grupo conseguem ver a lista.' },
    { valor: 'privada', nome: 'Privada', texto: 'Só quem você convidar enxerga os presentes.' },
];


const props = defineProps({
    title: {
        type: String,
    },
    lista: {
        type: [Object, Array],
    },
    grupos: {
        type: Array,
    },
})

const form = useForm({
    nome: props.lista.nome || '',
    descricao: props.lista.descricao || '',
    visibilidade: props.lista.visibilidade || '',
    data_evento: props.lista.data_evento || '',
    grupo_id: props.lista.grupo_id || '',
    image_url: props.lista.image_url || null,
})

const submit = () => {
    form.post(route('listas.update', props.lista.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Lista editada com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao editar lista:', errors);
        }
    });
};

const hasErrorMessage = computed(() => Object.keys(form.errors).length > 0);
const imagePreview = ref(props.lista.image_url || null);

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

const removeImage = () => {
    imagePreview.value = null;
    form.image_url = null;
    document.getElementById('image_url').value = '';
};

const compressImage = (file, maxSizeMB = 5, quality = 0.8) => {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = () => {
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

            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob(resolve, 'image/jpeg', quality);
        };

        img.src = URL.createObjectURL(file);
    });
};
</script>

<template>

    <Head :title="title" />
    <AppLayout :title="title">
        <div class="mx-auto max-w-3xl">
            <Alert v-if="hasErrorMessage" type="danger" title="Não foi possível salvar"
                message="Corrija os campos indicados e tente novamente." class="mb-6" />

            <div class="mb-8">
                <h2 class="text-3xl font-semibold text-gray-900">Editar lista</h2>
                <p class="mt-1 text-gray-600">Atualize capa, descrição e quem pode ver.</p>
            </div>

            <div class="surface p-5 sm:p-8">
                <form class="space-y-8" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <!-- Capa -->
                        <div class="col-span-full">
                            <InputLabel for="image_url" value="Capa da lista" />

                            <button v-if="!imagePreview" type="button"
                                class="press mt-2 flex w-full flex-col items-center rounded-xl border-2 border-dashed border-gray-300 px-6 py-10 hover:border-brand-500 hover:bg-brand-50/50"
                                @click="$refs.fileInput.click()">
                                <PhotoIcon class="size-10 text-gray-400" aria-hidden="true" />
                                <span class="mt-2 text-sm font-medium text-brand-700">Escolher imagem</span>
                                <span class="mt-1 text-xs text-gray-500">PNG ou JPG, até 5MB</span>
                            </button>

                            <input type="file" ref="fileInput" id="image_url" name="image_url" accept="image/*"
                                class="hidden" @change="handleFileChange" />

                            <div v-if="imagePreview" class="relative mt-2 overflow-hidden rounded-xl">
                                <img :src="imagePreview" alt="Pré-visualização da capa" class="aspect-[16/7] w-full object-cover" />
                                <button type="button" @click="removeImage"
                                    class="press absolute right-3 top-3 flex size-8 items-center justify-center rounded-full bg-surface/90 text-gray-700 shadow-sm backdrop-blur hover:bg-surface">
                                    <XMarkIcon class="size-4" aria-hidden="true" />
                                    <span class="sr-only">Remover imagem</span>
                                </button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.image_url" />
                        </div>

                        <div>
                            <InputLabel for="nome" value="Nome da lista" :required="true" />
                            <TextInput id="nome" type="text" class="mt-1.5 block w-full" v-model="form.nome" required
                                autofocus autocomplete="off" />
                            <InputError class="mt-2" :message="form.errors.nome" />
                        </div>

                        <div>
                            <InputLabel for="grupo_id" value="Grupo" />
                            <select id="grupo_id" class="mt-1.5 block w-full" v-model="form.grupo_id">
                                <option value="">Selecione</option>
                                <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">{{ grupo.nome }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.grupo_id" />
                        </div>

                        <div class="col-span-full">
                            <InputLabel for="descricao" value="Descrição" />
                            <textarea id="descricao" name="descricao" rows="3" class="mt-1.5 block w-full"
                                v-model="form.descricao"
                                placeholder="Ocasião, tamanhos, dicas para quem vai presentear"></textarea>
                            <InputError class="mt-2" :message="form.errors.descricao" />
                        </div>

                        <fieldset class="col-span-full">
                            <legend class="block text-sm font-medium text-gray-800">
                                Visibilidade <span class="text-red-500">*</span>
                            </legend>
                            <div class="mt-1.5 grid gap-3 sm:grid-cols-2">
                                <label v-for="op in visibilidades" :key="op.valor"
                                    class="press flex cursor-pointer items-start gap-3 rounded-xl border p-4 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50"
                                    :class="form.visibilidade === op.valor ? 'border-brand-600 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="visibilidade" :value="op.valor" v-model="form.visibilidade"
                                        class="mt-0.5" />
                                    <span>
                                        <span class="block text-sm font-medium text-gray-900">{{ op.nome }}</span>
                                        <span class="mt-0.5 block text-sm text-gray-500">{{ op.texto }}</span>
                                    </span>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.visibilidade" />
                        </fieldset>

                        <div>
                            <InputLabel for="data_evento" value="Data do evento" hint="Se houver" />
                            <TextInput id="data_evento" type="date" class="mt-1.5 block w-full" v-model="form.data_evento"
                                autocomplete="off" />
                            <InputError class="mt-2" :message="form.errors.data_evento" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-6">
                        <Link :href="route('listas.show', lista.id)" class="btn-secondary">Cancelar</Link>
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
