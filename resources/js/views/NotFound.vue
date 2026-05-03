<script setup>
import { RouterLink } from 'vue-router';
</script>

<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <div class="text-6xl">🤷‍♂️</div>
            <h1 class="text-2xl font-bold mt-4">Halaman tidak ditemukan</h1>
            <RouterLink to="/dashboard" class="btn-primary mt-4 inline-flex">Kembali ke Dashboard</RouterLink>
        </div>
    </div>
</template>
