<template>
  <RouterView />
  <router-view></router-view>
</template>

<script setup>
import { RouterView } from 'vue-router'
</script>

<style>
/* Masukin ke sini, WAJIB TANPA atribut 'scoped' */
@tailwind base;
@tailwind components;
@tailwind utilities;
</style>
