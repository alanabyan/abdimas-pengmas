<template>
    <span :class="['badge', `badge-${cfg.key}`, size === 'lg' ? 'badge-lg' : '']">
        <span class="badge-dot"></span>
        {{ cfg.label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: { type: String, default: '' },
    size: { type: String, default: 'sm' },
})

const MAP = {
    'Menunggu': { key: 'menunggu', label: 'Menunggu' },
    'Aktif': { key: 'aktif', label: 'Aktif' },
    'Selesai': { key: 'selesai', label: 'Selesai' },
    'Batal': { key: 'batal', label: 'Batal' },
}

const cfg = computed(() => MAP[props.status] ?? { key: 'default', label: props.status })
</script>

<style scoped>
.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.badge-lg {
    padding: 6px 14px;
    font-size: 13px;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.badge-menunggu {
    background: #fef3c7;
    color: #d97706;
}

.badge-menunggu .badge-dot {
    animation: pulse 2s infinite;
}

.badge-aktif {
    background: #dcfce7;
    color: #16a34a;
}

.badge-aktif .badge-dot {
    animation: pulse 2s infinite;
}

.badge-selesai {
    background: #ede9fe;
    color: #7c3aed;
}

.badge-batal {
    background: #fee2e2;
    color: #dc2626;
}

.badge-default {
    background: #f1f4f9;
    color: #7a8499;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: .3;
    }
}
</style>