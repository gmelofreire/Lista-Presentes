<template>
  <div>
    <TransitionRoot as="template" :show="open">
      <Dialog class="relative z-50" @close="open = false">
        <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0" enter-to=""
          leave="ease-in-out duration-500" leave-from="" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-hidden">
          <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
              <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700"
                enter-from="translate-x-full" enter-to="translate-x-0"
                leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0"
                leave-to="translate-x-full">
                <DialogPanel class="pointer-events-auto relative w-screen max-w-md">
                  <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0" enter-to=""
                    leave="ease-in-out duration-500" leave-from="" leave-to="opacity-0">
                    <div class="absolute top-0 left-0 -ml-8 flex pt-4 pr-2 sm:-ml-10 sm:pr-4">
                      <button type="button"
                        class="relative rounded-md text-gray-300 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        @click="open = false">
                        <span class="absolute -inset-2.5" />
                        <span class="sr-only">Fechar painel</span>
                        <XMarkIcon class="size-6" aria-hidden="true" />
                      </button>
                    </div>
                  </TransitionChild>
                  <div class="relative flex h-full flex-col overflow-y-auto bg-white py-6 shadow-xl">
                    <div class="flex justify-between items-center px-4 sm:px-6">
                      <DialogTitle class="text-xl font-extrabold text-gray-900">Detalhes do Presente</DialogTitle>
                      <Link :href="route('presentes.edit', presente.id)">
                        <PencilIcon class="size-5" />
                        <!-- <PencilSquareIcon class="size-5" /> -->
                      </Link>
                    </div>

                    <div class="relative mt-6 flex-1 px-4 sm:px-6">
                      <div class="flex flex-col items-center">
                        <!-- Imagem -->
                        <img :src="presente.image_url" alt="Foto do presente"
                          class="w-full object-contain max-h-60 bg-gray-100 rounded-xl shadow-md">

                        <!-- Nome -->
                        <div class="mt-4 text-2xl font-bold text-gray-800 text-center">
                          {{ presente.nome }}
                        </div>

                        <!-- Descrição -->
                        <div class="mt-1 text-sm text-gray-500 text-center">
                          {{ presente.descricao }}
                        </div>

                        <div v-if="presente.categorias && presente.categorias.length > 0" class="mt-2 flex flex-wrap gap-1 justify-center">
                            <span v-for="categoria in presente.categorias" :key="categoria.id"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                :style="{
                                    backgroundColor: categoria.hex_cor || '#6B7280',
                                    color: isDarkColor(categoria.hex_cor) ? '#FFFFFF' : '#000000'
                                }">
                                {{ categoria.nome }}
                            </span>
                        </div>

                        <!-- Avaliação -->
                        <div class="flex justify-center mt-4">
                          <span v-for="n in 5" :key="n" class="cursor-pointer">
                            <StarIcon
                              class="size-7 transition-colors duration-200"
                              :class="n <= presente.avaliacao ? 'text-yellow-400' : 'text-gray-300'"
                            />
                          </span>
                        </div>
                        <div class="text-center mt-1 text-sm text-gray-600">
                          {{ avaliacao[presente.avaliacao] || 'Selecione uma avaliação' }}
                        </div>

                        <!-- Botão -->
                        <div class="mb-6 mt-6 w-full flex justify-center gap-3">
                          <a v-if="presente.link" :href="presente.link"
                            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 px-5 py-3 rounded-xl text-white font-semibold shadow-md transition"
                            target="_blank" rel="noopener noreferrer">
                            <ArrowRightIcon class="w-5 h-5" />
                            Ver presente na loja
                          </a>
                          <button v-if="presente.link" @click="showQRCode = true"
                            class="flex items-center gap-2 bg-gray-600 hover:bg-gray-700 px-5 py-3 rounded-xl text-white font-semibold shadow-md transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            QR Code
                          </button>
                        </div>

                        <!-- QR Code Modal -->
                        <div v-if="showQRCode" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showQRCode = false">
                          <div class="bg-white p-6 rounded-xl shadow-xl max-w-sm w-full">
                            <div class="flex justify-between items-center mb-4">
                              <h3 class="text-lg font-bold">QR Code do Link</h3>
                              <button @click="showQRCode = false" class="text-gray-500 hover:text-gray-700">
                                <XMarkIcon class="w-5 h-5" />
                              </button>
                            </div>
                            <div class="flex justify-center mb-4">
                              <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(presente.link)}`" alt="QR Code" class="rounded-lg shadow-md" />
                            </div>
                            <p class="text-xs text-gray-500 text-center mb-4">Escaneie o código com seu celular</p>
                            <a :href="presente.link" target="_blank" class="block text-center text-indigo-600 hover:text-indigo-800 text-sm">
                              Abrir link →
                            </a>
                          </div>
                        </div>

                        <!-- Anotações -->
                        <div class="my-4 w-full">
                          <span class="font-bold flex items-center gap-1">
                            Anotações
                          </span>
                          <div class="mt-2 w-full bg-gray-100 p-4 rounded-lg shadow-sm text-gray-700">
                            {{ presente.anotacoes || 'Sem anotações' }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon, PencilIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'
import { StarIcon, ArrowRightIcon } from '@heroicons/vue/20/solid'
import { Link } from '@inertiajs/vue3';

const open = ref(true)
const showQRCode = ref(false)

const props = defineProps({
  presente: {
    type: [Array, Object],
    default: () => []
  }
})

const isDarkColor = (hexColor) => {
    if (!hexColor) return false
    const hex = hexColor.replace('#', '')
    const r = parseInt(hex.substr(0, 2), 16)
    const g = parseInt(hex.substr(2, 2), 16)
    const b = parseInt(hex.substr(4, 2), 16)
    const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000
    return brightness < 128
}


const avaliacao = {
  1: 'Não quero',
  2: 'Pode ser',
  3: 'Quero',
  4: 'Gostaria de receber',
  5: 'Quero muito'
}
</script>
