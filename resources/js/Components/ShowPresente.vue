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
                        <div class="mb-6 mt-6 w-full flex justify-center">
                          <a :href="presente.link"
                            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 px-5 py-3 rounded-xl text-white font-semibold shadow-md transition"
                            target="_blank" rel="noopener noreferrer">
                            <ArrowRightIcon class="w-5 h-5" />
                            Ver presente na loja
                          </a>
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

const props = defineProps({
  presente: {
    type: [Array, Object],
    default: () => []
  }
})

const avaliacao = {
  1: 'Não quero',
  2: 'Pode ser',
  3: 'Quero',
  4: 'Gostaria de receber',
  5: 'Quero muito'
}
</script>
