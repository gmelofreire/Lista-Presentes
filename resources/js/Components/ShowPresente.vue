<template>
  <div>
    <TransitionRoot as="template" :show="open" appear @after-leave="emit('close')">
      <Dialog class="relative z-50" @close="open = false">
        <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-out duration-150" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-[2px]" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
            <TransitionChild as="template" enter="transform transition ease-drawer duration-300"
              enter-from="translate-x-full" enter-to="translate-x-0"
              leave="transform transition ease-drawer duration-200" leave-from="translate-x-0"
              leave-to="translate-x-full">
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col overflow-y-auto bg-surface shadow-xl">
                  <header class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-surface/90 px-5 py-4 backdrop-blur">
                    <DialogTitle class="text-base font-semibold text-gray-900">Detalhes do presente</DialogTitle>
                    <div class="flex items-center gap-1">
                      <Link :href="route('presentes.edit', presente.id)"
                        class="press rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800" aria-label="Editar presente">
                        <PencilIcon class="size-5" />
                      </Link>
                      <button type="button" class="press rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        @click="open = false">
                        <span class="sr-only">Fechar painel</span>
                        <XMarkIcon class="size-5" aria-hidden="true" />
                      </button>
                    </div>
                  </header>

                  <div class="flex-1 space-y-6 px-5 py-6">
                    <div class="overflow-hidden rounded-xl bg-gray-100">
                      <img v-if="presente.image_url" :src="presente.image_url" :alt="presente.nome"
                        class="max-h-72 w-full object-contain" />
                    </div>

                    <div>
                      <div class="flex items-start justify-between gap-3">
                        <h2 class="text-2xl font-semibold text-gray-900">{{ presente.nome }}</h2>
                        <span v-if="presente.comprado"
                          class="inline-flex shrink-0 items-center gap-1 rounded-full bg-brand-100 px-2.5 py-1 text-xs font-medium text-brand-800">
                          <CheckIcon class="size-3.5" aria-hidden="true" />
                          Comprado
                        </span>
                      </div>
                      <p v-if="presente.preco" class="mt-1 text-lg font-medium text-gray-700">
                        R$ {{ Number(presente.preco).toFixed(2).replace('.', ',') }}
                      </p>
                      <p v-if="presente.descricao" class="mt-3 text-gray-600">{{ presente.descricao }}</p>
                    </div>

                    <div v-if="presente.categorias && presente.categorias.length > 0" class="flex flex-wrap gap-1.5">
                      <span v-for="categoria in presente.categorias" :key="categoria.id"
                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                        :style="{
                          backgroundColor: categoria.hex_cor || '#6B7280',
                          color: isDarkColor(categoria.hex_cor) ? '#FFFFFF' : '#18181b'
                        }">
                        {{ categoria.nome }}
                      </span>
                    </div>

                    <div class="rounded-xl bg-gray-50 px-4 py-3">
                      <div class="flex items-center gap-2">
                        <span class="flex" aria-hidden="true">
                          <StarIcon v-for="n in 5" :key="n" class="size-5"
                            :class="n <= presente.avaliacao ? 'text-amber-400' : 'text-gray-200'" />
                        </span>
                        <span class="text-sm font-medium text-gray-700">{{ avaliacao[presente.avaliacao] || 'Sem avaliação' }}</span>
                      </div>
                    </div>

                    <div v-if="presente.link" class="flex gap-3">
                      <a :href="presente.link" target="_blank" rel="noopener noreferrer" class="btn-primary flex-1">
                        <ArrowRightIcon class="size-5" />
                        Ver na loja
                      </a>
                      <button type="button" class="btn-secondary" @click="showQRCode = true">
                        <QrCodeIcon class="size-5" aria-hidden="true" />
                        QR Code
                      </button>
                    </div>

                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">Anotações</h3>
                      <p class="mt-2 whitespace-pre-line rounded-lg bg-gray-50 p-4 text-sm text-gray-700">
                        {{ presente.anotacoes || 'Sem anotações' }}
                      </p>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- QR Code -->
    <TransitionRoot as="template" :show="showQRCode">
      <Dialog class="relative z-[60]" @close="showQRCode = false">
        <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-out duration-150" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-[2px]" />
        </TransitionChild>
        <div class="fixed inset-0 flex items-center justify-center p-4">
          <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100" leave="ease-out duration-150" leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95">
            <DialogPanel class="w-full max-w-sm rounded-2xl bg-surface p-6 shadow-xl">
              <div class="flex items-center justify-between">
                <DialogTitle class="text-lg font-semibold text-gray-900">QR Code do link</DialogTitle>
                <button type="button" class="press rounded-full p-1.5 text-gray-500 hover:bg-gray-100" @click="showQRCode = false">
                  <span class="sr-only">Fechar</span>
                  <XMarkIcon class="size-5" />
                </button>
              </div>
              <div class="mt-5 flex justify-center">
                <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(presente.link)}`"
                  alt="QR Code do link do presente" class="size-52 rounded-lg" />
              </div>
              <p class="mt-4 text-center text-sm text-gray-500">Aponte a câmera do celular para abrir a loja.</p>
              <a :href="presente.link" target="_blank" rel="noopener noreferrer"
                class="mt-3 block text-center text-sm font-medium text-brand-700 hover:text-brand-800">
                Abrir link
              </a>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon, PencilIcon, QrCodeIcon } from '@heroicons/vue/24/outline'
import { StarIcon, ArrowRightIcon } from '@heroicons/vue/20/solid'
import { Link } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/20/solid'

const open = ref(true)
const showQRCode = ref(false)

const emit = defineEmits(['close'])

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
