<script setup>
import { Menu, MenuButton, MenuItems } from '@headlessui/vue'
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton as="template">
            <slot name="trigger" />
        </MenuButton>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <MenuItems class="absolute right-0 z-10 mt-1 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                <div class="py-1">
                    <slot />
                </div>
            </MenuItems>
        </Transition>
    </Menu>
</template>
