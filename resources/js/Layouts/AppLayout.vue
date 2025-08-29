<template>
  <div class="h-screen flex flex-col">
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog class="relative z-50 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0"
          enter-to="" leave="transition-opacity ease-linear duration-300" leave-from="" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-900/80" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild as="template" enter="transition ease-in-out duration-300 transform"
            enter-from="-translate-x-full" enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0"
            leave-to="-translate-x-full">
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
              <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to=""
                leave="ease-in-out duration-300" leave-from="" leave-to="opacity-0">
                <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                  <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              <div class="relative flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4">
                <div class="relative flex h-16 shrink-0 items-center gap-3">
                  <GiftIcon class="size-8 text-indigo-600" />
                  <span class="text-lg font-bold mt-1 text-gray-900">Desejário</span>
                </div>
                <nav class="relative flex flex-1 flex-col">
                  <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                      <ul role="list" class="-mx-2 space-y-1">
                        <li v-for="item in navigation" :key="item.name">
                          <a :href="item.href"
                            :class="[item.current ? 'bg-gray-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600', 'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold']">
                            <component :is="item.icon"
                              :class="[item.current ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600', 'size-6 shrink-0']"
                              aria-hidden="true" />
                            {{ item.name }}
                          </a>
                        </li>
                      </ul>
                    </li>
                    <li class="mt-auto">
                      <a href="#"
                        class="group -mx-2 flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold text-gray-700 hover:bg-gray-50 hover:text-indigo-600">
                        <Cog6ToothIcon class="size-6 shrink-0 text-gray-400 group-hover:text-indigo-600"
                          aria-hidden="true" />
                        Settings
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <div class="hidden bg-gray-900 lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4">
        <div class="flex h-16 shrink-0 items-center gap-3">
          <GiftIcon class="size-8 text-indigo-600" />
          <span class="text-lg font-bold mt-1 text-gray-900">Desejário</span>
        </div>
        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul role="list" class="-mx-2 space-y-1">
                <li v-for="item in navigation" :key="item.name">
                  <a :href="item.href"
                    :class="[item.current ? 'bg-gray-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600', 'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold']">
                    <component :is="item.icon"
                      :class="[item.current ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600', 'size-6 shrink-0']"
                      aria-hidden="true" />
                    {{ item.name }}
                  </a>
                </li>
              </ul>
            </li>
            <li class="mt-auto">
              <a href="#"
                class="group -mx-2 flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold text-gray-700 hover:bg-gray-50 hover:text-indigo-600">
                <Cog6ToothIcon class="size-6 shrink-0 text-gray-400 group-hover:text-indigo-600" aria-hidden="true" />
                Settings
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <div class="lg:pl-72 flex flex-col h-full">
      <div
        class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-xs sm:gap-x-6 sm:px-6 lg:px-8">
        <button type="button" class="-m-2.5 p-2.5 text-gray-700 hover:text-gray-900 lg:hidden"
          @click="sidebarOpen = true">
          <span class="sr-only">Open sidebar</span>
          <Bars3Icon class="size-6" aria-hidden="true" />
        </button>

        <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true" />
        <span class="text-black text-xl font-bold">{{ title }}</span>
        <div class="flex flex-1 justify-end gap-x-4 self-stretch lg:gap-x-6">
          <div class="flex items-center gap-x-4 lg:gap-x-6">
            <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
              <span class="sr-only">View notifications</span>
              <BellIcon class="size-6" aria-hidden="true" />
            </button>

            <!-- Separator -->
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" aria-hidden="true" />

            <!-- Profile dropdown -->
            <Menu as="div" class="relative">
              <MenuButton class="relative flex items-center">
                <span class="absolute -inset-1.5" />
                <span class="sr-only">Open user menu</span>
                <img :src="user.perfil.image_url ?? '/img/default_profile.png'" alt="Foto de perfil"
                  class="size-8 rounded-full bg-gray-50 outline -outline-offset-1 outline-black/5 object-cover" />
                <span class="hidden lg:flex lg:items-center">
                  <span class="ml-4 text-sm/6 font-semibold text-gray-900" aria-hidden="true">{{ user.username }}</span>
                  <ChevronDownIcon class="ml-2 size-5 text-gray-400" aria-hidden="true" />
                </span>
              </MenuButton>
              <transition enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95" enter-to-class="transform scale-100"
                leave-active-class="transition ease-in duration-75" leave-from-class="transform scale-100"
                leave-to-class="transform opacity-0 scale-95">
                <MenuItems
                  class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg outline-1 outline-gray-900/5">
                  <MenuItem v-slot="{ active }">
                  <a :href="route('perfil.edit')"
                    :class="[active ? 'bg-gray-50 outline-hidden' : '', 'block px-3 py-1 text-sm/6 text-gray-900']">
                    Seu perfil
                  </a>
                  </MenuItem>
                  <MenuItem v-slot="{ active }">
                    <Link :href="route('logout')" method="post">
                    <button
                      :class="[active ? 'bg-gray-50 outline-hidden' : '', 'block w-full text-left px-3 py-1 text-sm/6 text-gray-900']">
                      Sair
                    </button>
                    </Link>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
      </div>

      <main class="flex-1 py-10 bg-gray-50">
        <div class="px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
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
  Cog6ToothIcon,
  GiftTopIcon,
  GiftIcon,
  HomeIcon,
  TagIcon,
  UserGroupIcon,
  UsersIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/20/solid'
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
  },
})

const user = usePage().props.user;

const sidebarOpen = ref(false)

const navigation = [
  { name: 'Página inicial', href: route('dashboard'), icon: HomeIcon, current: true },
  { name: 'Listas de presentes', href: route('listas.index'), icon: GiftTopIcon, current: false },
  { name: 'Grupos', href: '#', icon: UserGroupIcon, current: false },
  { name: 'Categorias', href: '#', icon: TagIcon, current: false },
  { name: 'Amigo Secreto', href: '#', icon: UsersIcon, current: false },
]
</script>