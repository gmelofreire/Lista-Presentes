<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Alert from '@/Components/Alert.vue';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    title: {
        type: String,
    },
    categoria: {
        type: [Object, Array],
    }
})

const form = useForm({
    nome: props.categoria.nome,
    descricao: props.categoria.descricao,
    hex_cor: props.categoria.hex_cor,
})

const recentColors = ref(JSON.parse(localStorage.getItem('recentColors') || '[]'));

const hasErrorMessage = computed(() => {
    return Object.keys(form.errors).length > 0;
});

const isDarkColor = (hexColor) => {
    if (!hexColor || hexColor.length < 6) return false;
    const hex = hexColor.replace('#', '');

    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);

    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance < 0.5;
};

const isValidHex = (hex) => {
    return /^#[0-9A-Fa-f]{6}$/.test(hex);
};

const addToRecentColors = (color) => {
    if (!isValidHex(color)) return;

    const recent = recentColors.value.filter(c => c !== color);
    recent.unshift(color);
    recentColors.value = recent.slice(0, 10); // Manter apenas 10 cores recentes

    localStorage.setItem('recentColors', JSON.stringify(recentColors.value));
};

const generateRandomColor = () => {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    form.hex_cor = color;
    addToRecentColors(color);
};

watch(() => form.hex_cor, (newColor) => {
    if (isValidHex(newColor)) {
        addToRecentColors(newColor);
    }
});

const submit = () => {
    form.put(route('categorias.update', props.categoria.id), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Categoria salva com sucesso!');
        },
        onError: (errors) => {
            console.log('Erro ao salvar categoria:', errors);
        }
    });
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
                            <h2 class="text-2xl font-bold text-gray-900">Editar Categoria</h2>
                        </div>
                        <div class="mt-6">
                            <form>
                                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <div class="sm:col-span-1">
                                        <InputLabel for="nome" value="Nome da Categoria" :required="true" />
                                        <TextInput id="nome" type="text" class="mt-1 block w-full" v-model="form.nome"
                                            required autofocus autocomplete="nome" />
                                        <InputError class="mt-2" :message="form.errors.nome" />
                                    </div>
                                    <!-- <div class="sm:col-span-1">
                                        <InputLabel for="hex_cor" value="Cor" :required="true" />
                                        <input id="hex_cor" type="color" v-model="form.hex_cor"
                                            class="w-1/2 h-10 mt-1 p-1 border-0 rounded cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                        <InputError class="mt-2" :message="form.errors.hex_cor" />
                                    </div> -->
                                    <div class="col-span-full">
                                        <InputLabel for="descricao" value="Descrição" />
                                        <textarea id="descricao" name="descricao" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            v-model="form.descricao" placeholder="Descreva a categoria..."></textarea>
                                        <InputError class="mt-2" :message="form.errors.descricao" />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <InputLabel for="hex_cor" value="Cor da Categoria" :required="true" />
                                        <div class="mt-3 space-y-4">

                                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                                <div class="flex items-center space-x-4">
                                                    <!-- Preview Grande -->
                                                    <div class="w-20 h-20 rounded-xl border-2 border-white shadow-lg flex items-center justify-center transition-all duration-300"
                                                        :style="{ backgroundColor: form.hex_cor }">
                                                        <span class="text-sm font-semibold" :class="{
                                                            'text-white': isDarkColor(form.hex_cor),
                                                            'text-gray-800': !isDarkColor(form.hex_cor)
                                                        }">Aa</span>
                                                    </div>

                                                    <div class="space-y-1">
                                                        <div class="flex items-center space-x-2">
                                                            <input id="hex_cor" type="color" v-model="form.hex_cor"
                                                                class="w-8 h-8 border-0 rounded cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                                            <input type="text" v-model="form.hex_cor"
                                                                class="w-28 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono uppercase"
                                                                placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$"
                                                                @input="$event.target.value = $event.target.value.toUpperCase()" />
                                                        </div>
                                                        <p class="text-xs text-gray-500">Clique no quadrado ou digite o
                                                            código</p>
                                                    </div>
                                                </div>

                                                <div class="flex space-x-2">
                                                    <button type="button" @click="generateRandomColor"
                                                        class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                                                        🎲 Aleatória
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="form.hex_cor && !isValidHex(form.hex_cor)"
                                            class="mt-2 text-sm text-red-600">
                                            ⚠️ Formato inválido. Use o formato #RRGGBB (ex: #FF5733)
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.hex_cor" />
                                    </div>

                                    <div class="col-span-full">
                                        <div class="flex justify-between">
                                            <Link :href="route('categorias.index')">
                                            <button
                                                class="border bg-white text-black px-4 py-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                Cancelar
                                            </button>
                                            </Link>
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
