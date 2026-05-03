<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { RouterLink, RouterView, useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const sidebarOpen = ref(true);
const isMobile = ref(false);

function checkMobile() {
    isMobile.value = window.innerWidth < 1024;
    sidebarOpen.value = !isMobile.value;
}

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
onBeforeUnmount(() => window.removeEventListener('resize', checkMobile));

watch(() => route.fullPath, () => {
    if (isMobile.value) sidebarOpen.value = false;
});

const nav = computed(() => [
    { to: '/dashboard', label: 'Dashboard', icon: '🏠' },
    { to: '/classes', label: 'Kelas', icon: '🏋️' },
    { to: '/trainers', label: 'Trainer', icon: '👨‍🏫' },
    { to: '/sessions', label: 'Sesi/Jadwal', icon: '📅' },
    { to: '/bookings', label: 'Booking', icon: '📋' },
]);

async function logout() {
    await auth.logout();
    router.push({ name: 'landing' });
}

const roleBadge = computed(() => {
    const map = {
        admin: { label: 'Admin', cls: 'bg-rose-500/20 text-rose-300 border-rose-500/30' },
        trainer: { label: 'Trainer', cls: 'bg-amber-500/20 text-amber-300 border-amber-500/30' },
        member: { label: 'Member', cls: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' },
    };
    return map[auth.role] || { label: auth.role, cls: 'bg-slate-500/20 text-slate-300' };
});

const pageTitle = computed(() => {
    const map = {
        dashboard: 'Dashboard',
        'classes.index': 'Kelas',
        'classes.create': 'Tambah Kelas',
        'classes.show': 'Detail Kelas',
        'classes.edit': 'Edit Kelas',
        'trainers.index': 'Trainer',
        'trainers.create': 'Tambah Trainer',
        'trainers.show': 'Detail Trainer',
        'trainers.edit': 'Edit Trainer',
        'sessions.index': 'Sesi / Jadwal',
        'sessions.create': 'Tambah Sesi',
        'sessions.show': 'Detail Sesi',
        'sessions.edit': 'Edit Sesi',
        'bookings.index': 'Booking',
        'bookings.create': 'Booking Baru',
        'bookings.show': 'Detail Booking',
    };
    return map[route.name] || 'FitFlow';
});
</script>

<template>
    <div class="relative min-h-screen bg-slate-950 text-slate-100 overflow-x-hidden">
        <!-- Background glow effects -->
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/3 -right-40 w-96 h-96 bg-rose-600/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Mobile Backdrop -->
        <transition
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen && isMobile"
                @click="sidebarOpen = false"
                class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-30 lg:hidden"
            ></div>
        </transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-0 left-0 h-screen w-72 z-40 flex flex-col bg-slate-900/95 backdrop-blur-xl border-r border-slate-800 shadow-2xl shadow-black/40',
                'transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo -->
            <div class="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
                <RouterLink to="/" class="flex items-center gap-2 group">
                    <span class="text-2xl group-hover:scale-110 transition-transform">💪</span>
                    <div>
                        <div class="text-lg font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent">FitFlow</div>
                        <div class="text-[10px] text-slate-500 -mt-1 uppercase tracking-wider">Gym Platform</div>
                    </div>
                </RouterLink>
                <button
                    v-if="isMobile"
                    @click="sidebarOpen = false"
                    class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition lg:hidden"
                    aria-label="Close menu"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- User card -->
            <div class="px-4 py-4 border-b border-slate-800">
                <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-slate-800/50 border border-slate-800">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-white font-bold shrink-0">
                        {{ auth.user?.name?.[0]?.toUpperCase() || '?' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-slate-100 truncate">{{ auth.user?.name }}</div>
                        <span class="badge border" :class="roleBadge.cls">{{ roleBadge.label }}</span>
                    </div>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Menu Utama</div>
                <RouterLink
                    v-for="item in nav" :key="item.to" :to="item.to"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white text-sm transition group"
                    active-class="bg-gradient-to-r from-indigo-600/20 to-rose-600/20 text-white border border-indigo-500/30"
                >
                    <span class="text-lg group-hover:scale-110 transition-transform">{{ item.icon }}</span>
                    <span class="font-medium">{{ item.label }}</span>
                </RouterLink>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-800 space-y-2">
                <RouterLink to="/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-slate-200 text-sm transition">
                    <span>🏡</span>
                    <span>Halaman Utama</span>
                </RouterLink>
                <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-rose-400 hover:bg-rose-500/10 text-sm transition">
                    <span>🚪</span>
                    <span class="font-medium">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div
            :class="[
                'min-h-screen transition-[padding] duration-300 ease-in-out',
                sidebarOpen && !isMobile ? 'lg:pl-72' : 'pl-0',
            ]"
        >
            <!-- Topbar -->
            <header class="sticky top-0 z-20 bg-slate-950/70 backdrop-blur-xl border-b border-slate-800">
                <div class="flex items-center justify-between px-4 lg:px-6 py-3">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition"
                            aria-label="Toggle sidebar"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h1 class="text-lg font-semibold text-slate-100">{{ pageTitle }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-slate-400">Online</span>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-white text-sm font-bold">
                            {{ auth.user?.name?.[0]?.toUpperCase() || '?' }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 lg:p-6">
                <div class="max-w-7xl mx-auto">
                    <RouterView v-slot="{ Component }">
                        <transition
                            enter-active-class="transition duration-200 ease-out"
                            leave-active-class="transition duration-150 ease-in"
                            enter-from-class="opacity-0 translate-y-2"
                            leave-to-class="opacity-0"
                            mode="out-in"
                        >
                            <component :is="Component" />
                        </transition>
                    </RouterView>
                </div>
            </main>
        </div>
    </div>
</template>
