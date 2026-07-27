<script setup>
import { MoreHorizontal } from 'lucide-vue-next'
import Dropdown from '@/Components/UI/Dropdown.vue'
import Button from '@/Components/Button.vue'
</script>

<template>
    <Dropdown>
        <template #trigger>
            <Button variant="ghost" size="sm" aria-label="Actions">
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </template>
        <slot />
    </Dropdown>
</template>
