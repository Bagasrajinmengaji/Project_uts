<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import BlankLayout from '@/layouts/blank.vue';

const route = useRoute();

const currentLayout = computed(() => {
    return route.meta.layout || BlankLayout;
});
</script>

<template>
    <component :is="currentLayout">
        <router-view :key="route.fullPath" />
    </component>
</template>

<style>
/* Global resets & Sneat fonts */
@import url('https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&display=swap');

body {
    font-family:
        'Public Sans',
        -apple-system,
        BlinkMacSystemFont,
        'Segoe UI',
        Roboto,
        Helvetica,
        Arial,
        sans-serif !important;
    margin: 0;
    padding: 0;
}

.cursor-pointer {
    cursor: pointer;
}
</style>
