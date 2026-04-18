<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const props = defineProps({
    to: String,
    icon: Object,
    label: String,
    badge: { type: Number, default: 0 },
})

const route = useRoute()

const isActive = computed(() =>
    route.path === props.to ||
    (props.to !== '/' && route.path.startsWith(props.to + '/'))
)
</script>

<template>
    <RouterLink :to="to" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-150"
        :class="isActive
            ? 'bg-[#E5FAF1] text-[#065F46] font-semibold border-r-[8px] border-[#065F46]'
            : 'text-slate-400 hover:bg-[#E5FAF1] hover:text-[#065F46]'">
        <component :is="icon" class="w-5 h-5 shrink-0" />

        <span class="flex-1 truncate">
            {{ label }}
        </span>

        <span v-if="badge > 0"
            class="bg-red-500 text-white text-[10px] font-bold min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full">
            {{ badge > 99 ? '99+' : badge }}
        </span>
    </RouterLink>
</template>