<template>
  <div class="flex min-h-[100dvh] flex-col">
    <!-- Sidebar mobile -->
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog class="relative z-50 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild as="template" enter="transition-opacity ease-out duration-200" enter-from="opacity-0"
          enter-to="opacity-100" leave="transition-opacity ease-out duration-150" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-[2px]" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild as="template" enter="transition ease-drawer duration-300 transform"
            enter-from="-translate-x-full" enter-to="translate-x-0"
            leave="transition ease-drawer duration-200 transform" leave-from="translate-x-0"
            leave-to="-translate-x-full">
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
              <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-out duration-150" leave-from="opacity-100" leave-to="opacity-0">
                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                  <button type="button" class="press -m-2.5 rounded-full p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Fechar menu</span>
                    <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              <div class="relative flex grow flex-col gap-y-6 overflow-y-auto bg-surface px-5 pb-5">
                <Link :href="route('dashboard')" class="flex h-16 shrink-0 items-center gap-2.5">
                  <span class="flex size-9 items-center justify-center rounded-md bg-brand-600 text-white">
                    <GiftIcon class="size-5" aria-hidden="true" />
                  </span>
                  <span class="text-xl font-semibold tracking-tight text-gray-900">Desejário</span>
                </Link>
                <nav class="flex flex-1 flex-col">
                  <ul role="list" class="flex flex-1 flex-col gap-y-1">
                    <li v-for="item in navigation" :key="item.name">
                      <component :is="item.disabled ? 'span' : Link" :href="item.disabled ? undefined : item.href"
                        :class="[navItemClass(item), item.disabled ? 'cursor-not-allowed text-gray-400' : '']">
                        <component :is="item.icon" :class="[item.current ? 'text-brand-700' : 'text-gray-400 group-hover:text-gray-600', 'size-5 shrink-0']" aria-hidden="true" />
                        {{ item.name }}
                        <span v-if="item.disabled" class="ml-auto rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Em breve</span>
                      </component>
                    </li>
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Sidebar desktop -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-64 lg:flex-col">
      <div class="flex grow flex-col gap-y-6 overflow-y-auto border-r border-gray-200/80 bg-surface px-5 pb-5">
        <Link :href="route('dashboard')" class="flex h-16 shrink-0 items-center gap-2.5">
          <span class="flex size-9 items-center justify-center rounded-md bg-brand-600 text-white">
            <GiftIcon class="size-5" aria-hidden="true" />
          </span>
          <span class="text-xl font-semibold tracking-tight text-gray-900">Desejário</span>
        </Link>
        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-1">
            <li v-for="item in navigation" :key="item.name">
              <component :is="item.disabled ? 'span' : Link" :href="item.disabled ? undefined : item.href"
                :class="[navItemClass(item), item.disabled ? 'cursor-not-allowed text-gray-400' : '']">
                <component :is="item.icon" :class="[item.current ? 'text-brand-700' : 'text-gray-400 group-hover:text-gray-600', 'size-5 shrink-0']" aria-hidden="true" />
                {{ item.name }}
                <span v-if="item.disabled" class="ml-auto rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Em breve</span>
              </component>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <div class="flex min-h-[100dvh] flex-col lg:pl-64">
      <header
        class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200/80 bg-surface/85 px-4 backdrop-blur sm:px-6 lg:px-8">
        <button type="button" class="press -m-2.5 rounded-md p-2.5 text-gray-700 hover:text-gray-900 lg:hidden"
          @click="sidebarOpen = true">
          <span class="sr-only">Abrir menu</span>
          <Bars3Icon class="size-6" aria-hidden="true" />
        </button>

        <h1 class="truncate text-lg font-semibold tracking-tight text-gray-900">{{ title }}</h1>

        <div class="flex flex-1 items-center justify-end gap-x-3">
          <ThemeToggle />

          <!-- Notificacoes -->
          <div class="relative">
            <button type="button" class="press relative rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
              :aria-expanded="showNotifications" @click="showNotifications = !showNotifications">
              <span class="sr-only">Ver notificações</span>
              <BellIcon class="size-6" aria-hidden="true" />
              <span v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex size-5 items-center justify-center rounded-full bg-brand-600 text-[11px] font-semibold text-white ring-2 ring-surface">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </button>

            <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-out duration-100"
              leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
              <div v-if="showNotifications"
                class="absolute right-0 top-full z-50 mt-2 w-[min(24rem,calc(100vw-2rem))] origin-top-right rounded-xl border border-gray-200 bg-surface p-2 shadow-lg">
                <div class="flex items-center justify-between px-3 pb-2 pt-2">
                  <h3 class="text-sm font-semibold text-gray-900">Notificações</h3>
                  <button @click="showNotifications = false" class="press rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <span class="sr-only">Fechar</span>
                    <XMarkIcon class="size-4" />
                  </button>
                </div>
                <div v-if="notificationsList.length === 0" class="px-3 py-8 text-center text-sm text-gray-500">
                  Nada por aqui ainda. Os avisos de amizades e novas listas aparecem neste lugar.
                </div>
                <div v-else class="max-h-72 space-y-0.5 overflow-y-auto">
                  <button v-for="notif in notificationsList" :key="notif.id" type="button"
                    class="block w-full rounded-lg px-3 py-2.5 text-left transition-colors duration-150 hover:bg-gray-50"
                    :class="!notif.lido ? 'bg-brand-50/70' : ''" @click="marcarLidaENavegar(notif)">
                    <p class="text-sm font-medium text-gray-900">{{ notif.titulo }}</p>
                    <p class="mt-0.5 text-sm text-gray-500">{{ notif.mensagem }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ new Date(notif.created_at).toLocaleDateString('pt-BR') }}</p>
                  </button>
                </div>
                <div class="mt-1 border-t border-gray-100 pt-1">
                  <Link :href="route('notificacoes.index')" class="block rounded-lg px-3 py-2 text-center text-sm font-medium text-brand-700 hover:bg-gray-50"
                    @click="showNotifications = false">
                    Ver todas
                  </Link>
                </div>
              </div>
            </Transition>
          </div>

          <div class="hidden h-6 w-px bg-gray-200 sm:block" aria-hidden="true" />

          <!-- Menu do usuario -->
          <Menu as="div" class="relative">
            <MenuButton class="press flex items-center gap-2.5 rounded-full py-1 pl-1 pr-2 hover:bg-gray-100">
              <span class="sr-only">Abrir menu do usuário</span>
              <img :src="user.perfil.image_url ?? '/img/default_profile.png'" alt="Foto de perfil"
                class="size-8 rounded-full bg-gray-100 object-cover ring-1 ring-gray-900/5" />
              <span class="hidden items-center sm:flex">
                <span class="text-sm font-medium text-gray-900" aria-hidden="true">{{ user.username }}</span>
                <ChevronDownIcon class="ml-1 size-5 text-gray-400" aria-hidden="true" />
              </span>
            </MenuButton>
            <transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-out duration-100"
              leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
              <MenuItems
                class="absolute right-0 z-50 mt-2 w-44 origin-top-right rounded-xl border border-gray-200 bg-surface p-1.5 shadow-lg focus:outline-none">
                <MenuItem v-slot="{ active }">
                  <Link :href="route('perfil.edit')"
                    :class="[active ? 'bg-gray-100' : '', 'block rounded-md px-3 py-2 text-sm text-gray-800']">
                    Seu perfil
                  </Link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <Link :href="route('logout')" method="post" as="button"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full rounded-md px-3 py-2 text-left text-sm text-gray-800']">
                    Sair
                  </Link>
                </MenuItem>
              </MenuItems>
            </transition>
          </Menu>
        </div>
      </header>

      <main class="flex-1 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import {
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import {
  Bars3Icon,
  BellIcon,
  GiftTopIcon,
  GiftIcon,
  HomeIcon,
  TagIcon,
  UserGroupIcon,
  UsersIcon,
  XMarkIcon,
  LockClosedIcon,
} from '@heroicons/vue/24/outline'
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/20/solid'
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
  title: {
    type: String,
  },
})

const user = usePage().props.auth.user;

const sidebarOpen = ref(false)
const showNotifications = ref(false)
const unreadCount = ref(0)
const notificationsList = ref([])

const loadNotifications = async () => {
  try {
    const [naoLidas, recentes] = await Promise.all([
      axios.get('/notificacoes/nao-lidas'),
      axios.get('/notificacoes/recentes')
    ]);
    unreadCount.value = naoLidas.data.count;
    notificationsList.value = recentes.data.notificacoes || [];
  } catch (error) {
    console.error('Erro ao carregar notificações', error);
  }
};

const marcarLidaENavegar = async (notif) => {
  try {
    if (!notif.lido) {
      await axios.post(`/notificacoes/${notif.id}/marcar-lido`);
    }
    showNotifications.value = false;
    window.location.href = notif.link;
  } catch (error) {
    console.error('Erro ao marcar notificação', error);
  }
};

loadNotifications();

const navItemClass = (item) => [
  'group flex items-center gap-x-3 rounded-md px-3 py-2.5 text-sm font-medium press',
  item.current ? 'bg-brand-50 text-brand-800' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
]

const navigation = [
  { name: 'Página inicial', href: route('dashboard'), icon: HomeIcon, current: route().current('dashboard') },
  { name: 'Amizades', href: route('amizades.index'), icon: UsersIcon, current: route().current('amizades.*') },
  { name: 'Listas de presentes', href: route('listas.index'), icon: GiftTopIcon, current: route().current('listas.*') },
  { name: 'Grupos', href: route('grupos.index'), icon: UserGroupIcon, current: route().current('grupos.*') },
  { name: 'Categorias', href: route('categorias.index'), icon: TagIcon, current: route().current('categorias.*') },
  { name: 'Amigo Secreto', href: '#', icon: LockClosedIcon, current: false, disabled: true },
]
</script>