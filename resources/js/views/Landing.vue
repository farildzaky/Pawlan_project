<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const featuredClasses = ref([]);
const featuredTrainers = ref([]);
const upcomingSessions = ref([]);
const stats = ref({ classes: 0, trainers: 0, sessions: 0 });
const loading = ref(true);

const ctaPrimary = computed(() => auth.isAuthenticated
    ? { to: '/dashboard', label: '🚀 Ke Dashboard' }
    : { to: '/register', label: '🎯 Mulai Gratis' });

onMounted(async () => {
    try {
        const [c, t, s] = await Promise.all([
            axios.get('/classes', { params: { per_page: 6, is_active: 1 } }),
            axios.get('/trainers', { params: { per_page: 4, is_active: 1 } }),
            axios.get('/sessions', { params: { per_page: 4, status: 'scheduled', available: 1 } }),
        ]);
        featuredClasses.value = c.data.data;
        featuredTrainers.value = t.data.data;
        upcomingSessions.value = s.data.data;
        stats.value = {
            classes: c.data.meta.total,
            trainers: t.data.meta.total,
            sessions: s.data.meta.total,
        };
    } catch (e) { /* tampilkan landing tanpa data jika API error */ }
    loading.value = false;
});

const categoryColor = (c) => ({
    cardio: 'bg-rose-500/10 text-rose-300 border-rose-500/30',
    strength: 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30',
    flexibility: 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
    'mind-body': 'bg-violet-500/10 text-violet-300 border-violet-500/30',
}[c] || 'bg-slate-500/10 text-slate-300');

const features = [
    { icon: '🏋️', title: 'Kelas Beragam', desc: 'Cardio, Strength, Yoga, HIIT — pilih sesuai goal kamu.' },
    { icon: '👨‍🏫', title: 'Trainer Bersertifikat', desc: 'Dipandu coach berpengalaman dengan sertifikasi resmi.' },
    { icon: '📅', title: 'Jadwal Fleksibel', desc: 'Booking sesi sesuai jadwal kamu, real-time slot tersedia.' },
    { icon: '🔒', title: 'Aman & Modern', desc: 'Autentikasi JWT, role-based access, data terlindungi.' },
    { icon: '💳', title: 'Bayar Mudah', desc: 'Cash, transfer, atau e-wallet — semua diterima.' },
    { icon: '📱', title: 'Akses Multi-device', desc: 'Web responsive, bisa diakses dari HP/laptop.' },
];
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 overflow-x-hidden">
        <!-- Navbar -->
        <header class="fixed top-0 inset-x-0 z-40 backdrop-blur-md bg-slate-950/70 border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">💪</span>
                    <span class="text-xl font-bold tracking-tight">FitFlow</span>
                    <span class="text-xs text-slate-400 ml-2 hidden sm:inline">Gym Management</span>
                </div>
                <nav class="hidden md:flex items-center gap-6 text-sm text-slate-300">
                    <a href="#features" class="hover:text-white transition">Fitur</a>
                    <a href="#classes" class="hover:text-white transition">Kelas</a>
                    <a href="#trainers" class="hover:text-white transition">Trainer</a>
                    <a href="#schedule" class="hover:text-white transition">Jadwal</a>
                </nav>
                <div class="flex items-center gap-2">
                    <RouterLink v-if="!auth.isAuthenticated" to="/login" class="text-sm text-slate-300 hover:text-white px-3 py-2">Login</RouterLink>
                    <RouterLink :to="ctaPrimary.to" class="text-sm bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-semibold transition">
                        {{ auth.isAuthenticated ? 'Dashboard' : 'Daftar' }}
                    </RouterLink>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative pt-32 pb-20 px-6 overflow-hidden">
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
                <div class="absolute top-40 right-1/4 w-96 h-96 bg-rose-600/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl"></div>
            </div>
            <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs mb-6">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Platform Manajemen Gym Terlengkap 2026
                    </div>
                    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight tracking-tight">
                        Latih Tubuh,
                        <span class="bg-gradient-to-r from-indigo-400 via-rose-400 to-amber-400 bg-clip-text text-transparent">
                            Kelola Gym
                        </span>
                        Tanpa Ribet.
                    </h1>
                    <p class="text-lg text-slate-300 mt-6 max-w-xl">
                        FitFlow adalah platform manajemen pusat kebugaran yang memudahkan member booking sesi,
                        trainer mengatur jadwal, dan admin mengelola operasional — semua dalam satu sistem.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <RouterLink :to="ctaPrimary.to" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-lg font-semibold text-base transition shadow-lg shadow-indigo-600/30">
                            {{ ctaPrimary.label }}
                        </RouterLink>
                        <a href="#classes" class="px-6 py-3 border border-slate-700 hover:bg-slate-800 rounded-lg font-semibold text-base transition">
                            Lihat Kelas →
                        </a>
                    </div>
                    <div class="mt-10 flex flex-wrap gap-8 text-sm">
                        <div>
                            <div class="text-2xl font-bold text-white">{{ stats.classes }}+</div>
                            <div class="text-slate-400">Kelas Aktif</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ stats.trainers }}+</div>
                            <div class="text-slate-400">Trainer Pro</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-white">{{ stats.sessions }}+</div>
                            <div class="text-slate-400">Sesi Tersedia</div>
                        </div>
                    </div>
                </div>

                <!-- Hero illustration card -->
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-rose-600 rounded-3xl rotate-3 opacity-30 blur-xl"></div>
                    <div class="relative bg-slate-900/80 backdrop-blur border border-slate-800 rounded-3xl p-8 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-rose-500 rounded-xl flex items-center justify-center text-2xl">🔥</div>
                                <div>
                                    <div class="font-semibold">HIIT Burn Session</div>
                                    <div class="text-xs text-slate-400">Hari ini · 07:00</div>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-emerald-500/20 text-emerald-300">Live</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">👨‍🏫 Trainer</span>
                                <span>Andi Pratama</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">📍 Lokasi</span>
                                <span>Studio A</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">👥 Slot</span>
                                <span>12/15 terisi</span>
                            </div>
                            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-rose-500 rounded-full" style="width: 80%"></div>
                            </div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-slate-800 flex items-center justify-between">
                            <div>
                                <div class="text-xs text-slate-400">Harga</div>
                                <div class="text-2xl font-bold">Rp 75.000</div>
                            </div>
                            <button class="px-4 py-2 bg-white text-slate-900 rounded-lg font-semibold text-sm hover:bg-slate-100">Book Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-20 px-6 bg-slate-900/50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold">Kenapa Pilih FitFlow?</h2>
                    <p class="text-slate-400 mt-3 max-w-2xl mx-auto">
                        Dirancang untuk gym modern. Mendukung 3 role pengguna: Admin, Trainer, dan Member.
                    </p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="(f, i) in features" :key="i" class="group p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-900/80 transition">
                        <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">{{ f.icon }}</div>
                        <h3 class="font-semibold text-lg mb-2">{{ f.title }}</h3>
                        <p class="text-sm text-slate-400">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Classes -->
        <section id="classes" class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <h2 class="text-4xl font-bold">Kelas Unggulan</h2>
                        <p class="text-slate-400 mt-2">Pilih kelas yang sesuai dengan goal kebugaranmu.</p>
                    </div>
                    <RouterLink to="/login" class="hidden md:inline text-sm text-indigo-400 hover:text-indigo-300">
                        Lihat semua →
                    </RouterLink>
                </div>
                <div v-if="loading" class="text-center py-10 text-slate-500">Memuat...</div>
                <div v-else-if="featuredClasses.length === 0" class="text-center py-10 text-slate-500">Belum ada kelas tersedia.</div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="c in featuredClasses" :key="c.id" class="group rounded-2xl overflow-hidden bg-slate-900 border border-slate-800 hover:border-indigo-500/50 transition">
                        <div class="aspect-video bg-gradient-to-br from-indigo-900 to-slate-800 flex items-center justify-center relative overflow-hidden">
                            <img v-if="c.image_url" :src="c.image_url" :alt="c.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            <span v-else class="text-6xl opacity-50">🏋️</span>
                            <div class="absolute top-3 left-3 flex gap-2">
                                <span class="text-xs px-2 py-1 rounded-full border" :class="categoryColor(c.category)">{{ c.category }}</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-lg">{{ c.name }}</h3>
                                <span class="text-xs text-slate-400">{{ c.difficulty_level }}</span>
                            </div>
                            <p class="text-sm text-slate-400 line-clamp-2 mb-4">{{ c.description }}</p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">⏱️ {{ c.duration_minutes }} mnt</span>
                                <span class="text-xl font-bold text-indigo-400">Rp {{ Number(c.price).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trainers -->
        <section id="trainers" class="py-20 px-6 bg-slate-900/50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold">Trainer Berpengalaman</h2>
                    <p class="text-slate-400 mt-3">Belajar dari para coach terbaik yang siap memandu progres kamu.</p>
                </div>
                <div v-if="loading" class="text-center py-10 text-slate-500">Memuat...</div>
                <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div v-for="t in featuredTrainers" :key="t.id" class="rounded-2xl bg-slate-900 border border-slate-800 p-5 text-center hover:border-rose-500/50 transition">
                        <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-3xl mb-3">
                            🥋
                        </div>
                        <h3 class="font-semibold">{{ t.name }}</h3>
                        <div class="text-xs text-slate-400 mt-1">{{ t.specialization }}</div>
                        <div class="text-xs text-amber-400 mt-2">⭐ {{ t.years_experience }} tahun pengalaman</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Upcoming Sessions -->
        <section id="schedule" class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold">Jadwal Mendatang</h2>
                    <p class="text-slate-400 mt-3">Sesi yang masih buka untuk booking. Daftar sekarang sebelum penuh!</p>
                </div>
                <div v-if="loading" class="text-center py-10 text-slate-500">Memuat...</div>
                <div v-else-if="upcomingSessions.length === 0" class="text-center py-10 text-slate-500">Tidak ada sesi tersedia saat ini.</div>
                <div v-else class="grid md:grid-cols-2 gap-4 max-w-4xl mx-auto">
                    <div v-for="s in upcomingSessions" :key="s.id" class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-4 hover:border-emerald-500/50 transition">
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-emerald-500 to-indigo-500 flex flex-col items-center justify-center text-white shrink-0">
                            <div class="text-xs">{{ new Date(s.session_date).toLocaleDateString('id-ID', { month: 'short' }) }}</div>
                            <div class="text-xl font-bold">{{ new Date(s.session_date).getDate() }}</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold truncate">{{ s.gym_class?.name }}</div>
                            <div class="text-xs text-slate-400">{{ s.start_time?.slice(0,5) }}-{{ s.end_time?.slice(0,5) }} · {{ s.location }}</div>
                            <div class="text-xs text-slate-500 mt-1">👨‍🏫 {{ s.trainer?.name }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs text-slate-400">sisa</div>
                            <div class="text-xl font-bold text-emerald-400">{{ s.available_slots }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20 px-6">
            <div class="max-w-4xl mx-auto rounded-3xl bg-gradient-to-br from-indigo-600 via-rose-600 to-amber-500 p-12 text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative">
                    <h2 class="text-4xl md:text-5xl font-extrabold">Siap Mulai Latihan?</h2>
                    <p class="text-white/90 mt-4 text-lg">
                        Daftar sekarang dan dapatkan akses ke semua kelas. Gratis registrasi member.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <RouterLink v-if="!auth.isAuthenticated" to="/register" class="px-8 py-3 bg-white text-slate-900 rounded-lg font-bold hover:bg-slate-100 transition">
                            🎯 Daftar Sekarang
                        </RouterLink>
                        <RouterLink v-else to="/dashboard" class="px-8 py-3 bg-white text-slate-900 rounded-lg font-bold hover:bg-slate-100 transition">
                            🚀 Ke Dashboard
                        </RouterLink>
                        <RouterLink v-if="!auth.isAuthenticated" to="/login" class="px-8 py-3 bg-white/20 backdrop-blur border border-white/40 text-white rounded-lg font-bold hover:bg-white/30 transition">
                            Login
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-800 py-10 px-6 text-center text-sm text-slate-500">
            <div class="flex items-center justify-center gap-2 mb-3">
                <span class="text-xl">💪</span>
                <span class="font-bold text-slate-300">FitFlow</span>
            </div>
            <p>Gym Management Platform · Built with Laravel + Vue.js + JWT</p>
            <p class="mt-2">© 2026 FitFlow. All rights reserved.</p>
        </footer>
    </div>
</template>
