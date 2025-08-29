<script setup>
import { StarIcon } from '@heroicons/vue/20/solid';
import { ref, computed } from 'vue';

const presentes = [
    { id: 3, nome: 'Camisa Alpha', descricao: 'Jaqueta Bomber com Gola Alta Azul', preco: 149.90, link: 'https://www.google.com', anotacoes: 'Tamanho P', comprado: false, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_pi/2024/12/27/a8/173526220372ca6d041732c2db75fe8cea10383ffa_thumbnail_900x.webp' },
    { id: 1, nome: 'Camiseta Literalmente Eu', descricao: 'Camiseta Overboxy em algodão com efeito puff com estampa lettering marrom', preco: 49.90, link: 'https://www.google.com', anotacoes: 'Tamanho M', comprado: false, avaliacao: 1, image_url: 'https://img.ltwebstatic.com/images3_spmp/2025/02/19/13/17399655119ea6231aea475801e6dba56877afff0b_thumbnail_900x.webp' },
    { id: 2, nome: 'Camisa 2026', descricao: 'Camisa em Sarja com Botões e Bolsos Marrom', preco: 199.90, link: 'https://www.google.com', anotacoes: 'Tamanho GG', comprado: true, avaliacao: 4, image_url: 'https://img.ltwebstatic.com/v4/j/spmp/2025/08/11/c7/1754910487c47761350aacef8ffe18ed3eb889aedb_thumbnail_900x.webp' },
    { id: 4, nome: 'Camiseta Rato', descricao: 'Suéter em Algodão com Textura Variada Azul', preco: 109.90, link: 'https://www.google.com', anotacoes: 'Tamanho M', comprado: true, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_spmp/2025/03/06/bf/1741269218c337bfd35c7a776f2b10d234783de9b9_thumbnail_900x.webp' },
    { id: 5, nome: 'Camisa Ídolo', descricao: 'Camisa do homem mais gato da Bahia', preco: 180.00, link: 'https://www.google.com', anotacoes: 'Tamanho 42', comprado: false, avaliacao: 3, image_url: 'https://img.ltwebstatic.com/v4/j/spmp/2025/03/30/5e/1743268885809cdd3882e2187eea4b5017ccc654b8.webp' },
    { id: 6, nome: 'Camisa.', descricao: 'Jaqueta jeans com botões metálicos', preco: 150.50, link: 'https://www.google.com', anotacoes: 'Tamanho G', comprado: false, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_pi/2024/12/18/5d/17345169777dbf072fb7e2895cdd5125f0874b2a12_thumbnail_900x.webp' },
    { id: 3, nome: 'Camisa Alpha', descricao: 'Jaqueta Bomber com Gola Alta Azul', preco: 149.90, link: 'https://www.google.com', anotacoes: 'Tamanho P', comprado: false, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_pi/2024/12/27/a8/173526220372ca6d041732c2db75fe8cea10383ffa_thumbnail_900x.webp' },
    { id: 1, nome: 'Camiseta Literalmente Eu', descricao: 'Camiseta Overboxy em algodão com efeito puff com estampa lettering marrom', preco: 49.90, link: 'https://www.google.com', anotacoes: 'Tamanho M', comprado: false, avaliacao: 1, image_url: 'https://img.ltwebstatic.com/images3_spmp/2025/02/19/13/17399655119ea6231aea475801e6dba56877afff0b_thumbnail_900x.webp' },
    { id: 2, nome: 'Camisa 2026', descricao: 'Camisa em Sarja com Botões e Bolsos Marrom', preco: 199.90, link: 'https://www.google.com', anotacoes: 'Tamanho GG', comprado: true, avaliacao: 4, image_url: 'https://img.ltwebstatic.com/v4/j/spmp/2025/08/11/c7/1754910487c47761350aacef8ffe18ed3eb889aedb_thumbnail_900x.webp' },
    { id: 4, nome: 'Camiseta Rato', descricao: 'Suéter em Algodão com Textura Variada Azul', preco: 109.90, link: 'https://www.google.com', anotacoes: 'Tamanho M', comprado: true, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_spmp/2025/03/06/bf/1741269218c337bfd35c7a776f2b10d234783de9b9_thumbnail_900x.webp' },
    { id: 5, nome: 'Camisa Ídolo', descricao: 'Camisa do homem mais gato da Bahia', preco: 180.00, link: 'https://www.google.com', anotacoes: 'Tamanho 42', comprado: false, avaliacao: 3, image_url: 'https://img.ltwebstatic.com/v4/j/spmp/2025/03/30/5e/1743268885809cdd3882e2187eea4b5017ccc654b8.webp' },
    { id: 6, nome: 'Camisa.', descricao: 'Jaqueta jeans com botões metálicos', preco: 150.50, link: 'https://www.google.com', anotacoes: 'Tamanho G', comprado: false, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_pi/2024/12/18/5d/17345169777dbf072fb7e2895cdd5125f0874b2a12_thumbnail_900x.webp' },
    { id: 3, nome: 'Camisa Alpha', descricao: 'Jaqueta Bomber com Gola Alta Azul', preco: 149.90, link: 'https://www.google.com', anotacoes: 'Tamanho P', comprado: false, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_pi/2024/12/27/a8/173526220372ca6d041732c2db75fe8cea10383ffa_thumbnail_900x.webp' },
    { id: 1, nome: 'Camiseta Literalmente Eu', descricao: 'Camiseta Overboxy em algodão com efeito puff com estampa lettering marrom', preco: 49.90, link: 'https://www.google.com', anotacoes: 'Tamanho M', comprado: false, avaliacao: 1, image_url: 'https://img.ltwebstatic.com/images3_spmp/2025/02/19/13/17399655119ea6231aea475801e6dba56877afff0b_thumbnail_900x.webp' },
    { id: 2, nome: 'Camisa 2026', descricao: 'Camisa em Sarja com Botões e Bolsos Marrom', preco: 199.90, link: 'https://www.google.com', anotacoes: 'Tamanho GG', comprado: true, avaliacao: 4, image_url: 'https://img.ltwebstatic.com/v4/j/spmp/2025/08/11/c7/1754910487c47761350aacef8ffe18ed3eb889aedb_thumbnail_900x.webp' },
    { id: 4, nome: 'Camiseta Rato', descricao: 'Suéter em Algodão com Textura Variada Azul', preco: 109.90, link: 'https://www.google.com', anotacoes: 'Tamanho M', comprado: true, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_spmp/2025/03/06/bf/1741269218c337bfd35c7a776f2b10d234783de9b9_thumbnail_900x.webp' },
    { id: 5, nome: 'Camisa Ídolo', descricao: 'Camisa do homem mais gato da Bahia', preco: 180.00, link: 'https://www.google.com', anotacoes: 'Tamanho 42', comprado: false, avaliacao: 3, image_url: 'https://img.ltwebstatic.com/v4/j/spmp/2025/03/30/5e/1743268885809cdd3882e2187eea4b5017ccc654b8.webp' },
    { id: 6, nome: 'Camisa.', descricao: 'Jaqueta jeans com botões metálicos', preco: 150.50, link: 'https://www.google.com', anotacoes: 'Tamanho G', comprado: false, avaliacao: 5, image_url: 'https://img.ltwebstatic.com/images3_pi/2024/12/18/5d/17345169777dbf072fb7e2895cdd5125f0874b2a12_thumbnail_900x.webp' },
];

const avaliacao = {
    1: 'Não quero',
    2: 'Pode ser',
    3: 'Quero',
    4: 'Gostaria de receber',
    5: 'Quero muito'
}

// Estado da paginação
const currentPage = ref(1)
const itemsPerPage = 15

// Computed properties para paginação
const totalPages = computed(() => Math.ceil(presentes.length / itemsPerPage))

const paginatedPresentes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return presentes.slice(start, end)
})

const visiblePages = computed(() => {
    const pages = []
    const total = totalPages.value
    const current = currentPage.value

    // Mostrar até 5 páginas por vez
    let start = Math.max(1, current - 2)
    let end = Math.min(total, start + 4)

    // Ajustar se estivermos no final
    if (end - start < 4) {
        start = Math.max(1, end - 4)
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
})

const paginationInfo = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage + 1
    const end = Math.min(currentPage.value * itemsPerPage, presentes.length)
    return {
        from: start,
        to: end,
        total: presentes.length
    }
})

// Funções de navegação
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}
</script>

<template>
    <div class="p-4 sm:p-6">
        <div class="mb-4 text-sm text-gray-600 text-center sm:text-left">
            <span>Mostrando {{ paginationInfo.from }} a {{ paginationInfo.to }} de {{ paginationInfo.total }} presentes</span>
        </div>

        <ul role="list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <li v-for="presente in paginatedPresentes" :key="presente.id" 
                class="flex flex-col text-center w-full max-w-sm mx-auto sm:mx-0">
                <div class="group relative">
                    <div class="flex aspect-square w-full items-end justify-between space-x-6 pt-9 rounded-xl bg-cover bg-center bg-no-repeat relative group-hover:opacity-75 transition-opacity duration-200"
                        :style="{ backgroundImage: `url(${presente.image_url})` }">
                        <div
                            class="absolute flex justify-center inset-0 bg-gradient-to-t items-end from-black/60 via-black/5 to-transparent rounded-xl">
                            <div class="flex items-center text-white font-extrabold mb-3 sm:mb-5 text-sm sm:text-base px-2">
                                <span class="truncate">{{ presente.avaliacao }}</span>
                                <StarIcon class="size-3 sm:size-4 ml-1 mr-2 text-yellow-400 flex-shrink-0" /> 
                                <span class="truncate">{{ avaliacao[presente.avaliacao] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-6 px-2 sm:px-0">
                        <h3 class="mt-1 font-semibold text-gray-900 text-sm sm:text-base line-clamp-2">
                            <a class="hover:text-indigo-600 transition-colors duration-200">
                                <span class="absolute inset-0" />
                                {{ presente.nome }}
                            </a>
                        </h3>
                        <p class="mt-1 text-gray-900 font-medium text-sm sm:text-base">R$ {{ presente.preco.toFixed(2) }}</p>
                        <div v-if="presente.comprado" class="mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Comprado
                            </span>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Mensagem quando não há presentes -->
        <div v-if="presentes.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum presente encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Ainda não há presentes nesta lista.</p>
        </div>

        <!-- Controles de paginação responsivos -->
        <div v-if="totalPages > 1" class="mt-8 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
            <!-- Paginação mobile -->
            <div class="flex justify-center w-full sm:hidden">
                <div class="flex items-center space-x-2">
                    <button @click="previousPage" :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        ← Anterior
                    </button>
                    <span class="text-sm text-gray-700 px-2">
                        {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        Próxima →
                    </button>
                </div>
            </div>

            <!-- Paginação desktop -->
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        <span class="font-medium">{{ paginationInfo.from }}</span>
                        a
                        <span class="font-medium">{{ paginationInfo.to }}</span>
                        de
                        <span class="font-medium">{{ paginationInfo.total }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button @click="previousPage" :disabled="currentPage === 1"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            <span class="sr-only">Anterior</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="[
                            page === currentPage
                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors duration-200'
                        ]">
                            {{ page }}
                        </button>

                        <button @click="nextPage" :disabled="currentPage === totalPages"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            <span class="sr-only">Próxima</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>