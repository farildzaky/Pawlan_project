<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const stats = ref({ classes: 0, trainers: 0, sessions: 0, bookings: 0 });
const recentSessions = ref([]);
const recentBookings = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const [c, t, s, b] = await Promise.all([
            axios.get('/classes?per_page=1'),
            axios.get('/trainers?per_page=1'),
            axios.get('/sessions?per_page=4&status=scheduled'),
            auth.isAuthenticated ? axios.get('/bookings?per_page=4') : { data: { data: [], meta: { total: 0 } } },
        ]);
        stats.value = {
            classes: c.data.meta.total,
            trainers: t.data.meta.total,
            sessions: s.data.meta.total,
            bookings: b.data.meta.total,
        };
        recentSessions.value = s.data.data;
        recentBookings.value = b.data.data;
    } catch (e) { /* ignore */ }
    loading.value = false;
});

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 11) return 'Selamat pagi';
    if (h < 15) return 'Selamat siang';
    if (h < 19) return 'Selamat sore';
    return 'Selamat malam';
});

const statCards = computed(() => [
    { label: 'Total Kelas', value: stats.value.classes, icon: '🏋️', gradient: 'from-indigo-500 to-blue-500', glow: 'shadow-indigo-500/20', to: '/classes' },
    { label: 'Total Trainer', value: stats.value.trainers, icon: '👨‍🏫', gradient: 'from-rose-500 to-pink-500', glow: 'shadow-rose-500/20', to: '/trainers' },
    { label: 'Sesi Terjadwal', value: stats.value.sessions, icon: '📅', gradient: 'from-amber-500 to-orange-500', glow: 'shadow-amber-500/20', to: '/sessions' },
    { label: 'Booking', value: stats.value.bookings, icon: '📋', gradient: 'from-emerald-500 to-teal-500', glow: 'shadow-emerald-500/20', to: '/bookings' },
]);

const accessRules = computed(() => {
    if (auth.isAdmin) return [
        { ok: true, text: 'CRUD penuh: Kelas, Trainer, Sesi' },
        { ok: true, text: 'Kelola seluruh booking & status pembayaran' },
        { ok: true, text: 'Upload gambar kelas & data master' },
    ];
    if (auth.isTrainer) return [
        { ok: true, text: 'Lihat sesi yang Anda ajar & daftar peserta' },
        { ok: true, text: 'Update profil pribadi (bio, kontak)' },
        { ok: false, text: 'Tidak bisa CRUD master kelas/sesi/booking' },
    ];
    return [
        { ok: true, text: 'Booking sesi yang tersedia' },
        { ok: true, text: 'Lihat & batalkan booking pribadi (≥ 24 jam sebelum sesi)' },
        { ok: false, text: 'Tidak bisa edit master kelas/trainer/sesi' },
    ];
});

const statusColor = (s) => ({
    pending: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
    confirmed: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
    cancelled: 'bg-rose-500/20 text-rose-300 border-rose-500/30',
    completed: 'bg-slate-700/40 text-slate-300 border-slate-600',
    scheduled: 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
}[s] || 'bg-slate-700/40 text-slate-300');

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
</script>

<template>
    <div class="space-y-6">
        <!-- Hero -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-rose-600 p-6 lg:p-8">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><path d=%22M0 30h60M30 0v60%22 stroke=%22white%22 stroke-opacity=%220.05%22/></svg>')] opacity-50"></div>
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            <div class="relative">
                <div class="text-sm text-white/80 mb-1">{{ greeting }} 👋</div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ auth.user?.name }}</h1>
                <p class="text-white/80 mt-2 text-sm lg:text-base">
                    Anda login sebagai
                    <span class="font-semibold capitalize bg-white/20 backdrop-blur px-2 py-0.5 rounded">{{ auth.role }}</span>.
                    Kelola gym dengan mudah dari satu dashboard.
                </p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <RouterLink v-if="auth.isMember" to="/sessions" class="px-4 py-2 bg-white text-slate-900 rounded-lg font-semibold text-sm hover:bg-slate-100 transition">
                        📋 Booking Sesi
                    </RouterLink>
                    <RouterLink v-if="auth.isAdmin" to="/classes/create" class="px-4 py-2 bg-white text-slate-900 rounded-lg font-semibold text-sm hover:bg-slate-100 transition">
                        + Tambah Kelas
                    </RouterLink>
                    <RouterLink v-if="auth.isAdmin" to="/sessions/create" class="px-4 py-2 bg-white/20 backdrop-blur border border-white/40 text-white rounded-lg font-semibold text-sm hover:bg-white/30 transition">
                        📅 Buat Sesi
                    </RouterLink>
                    <RouterLink v-if="auth.isTrainer" to="/sessions" class="px-4 py-2 bg-white text-slate-900 rounded-lg font-semibold text-sm hover:bg-slate-100 transition">
                        📅 Lihat Jadwal Saya
                    </RouterLink>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <RouterLink
                v-for="card in statCards" :key="card.label" :to="card.to"
                class="group relative overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-5 hover:border-indigo-500/50 transition shadow-lg"
                :class="card.glow"
            >
                <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full opacity-20 blur-2xl group-hover:opacity-30 transition" :class="`bg-gradient-to-br ${card.gradient}`"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shadow-lg" :class="`bg-gradient-to-br ${card.gradient} ${card.glow}`">
                            {{ card.icon }}
                        </div>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-300 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div class="text-3xl font-bold text-white">{{ loading ? '…' : card.value }}</div>
                    <div class="text-xs text-slate-400 mt-1">{{ card.label }}</div>
                </div>
            </RouterLink>
        </div>

        <!-- Two-column content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Recent Sessions -->
            <div class="card lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-lg">📅 Sesi Terdekat</h3>
                        <p class="text-xs text-slate-400">Sesi yang dijadwalkan & masih buka untuk booking</p>
                    </div>
                    <RouterLink to="/sessions" class="text-xs text-indigo-400 hover:text-indigo-300">Lihat semua →</RouterLink>
                </div>
                <div v-if="loading" class="text-center py-6 text-slate-500 text-sm">Memuat...</div>
                <div v-else-if="recentSessions.length === 0" class="text-center py-6 text-slate-500 text-sm">Belum ada sesi terjadwal.</div>
                <div v-else class="space-y-2">
                    <RouterLink
                        v-for="s in recentSessions" :key="s.id" :to="`/sessions/${s.id}`"
                        class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/30 border border-slate-800 hover:border-indigo-500/40 hover:bg-slate-800/60 transition"
                    >
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-rose-500 flex flex-col items-center justify-center text-white shrink-0">
                            <div class="text-[10px] uppercase">{{ new Date(s.session_date).toLocaleDateString('id-ID', { month: 'short' }) }}</div>
                            <div class="text-base font-bold leading-none">{{ new Date(s.session_date).getDate() }}</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold truncate">{{ s.gym_class?.name }}</div>
                            <div class="text-xs text-slate-400 truncate">
                                ⏰ {{ s.start_time?.slice(0,5) }}-{{ s.end_time?.slice(0,5) }} · 📍 {{ s.location }}
                            </div>
                            <div class="text-xs text-slate-500 truncate">👨‍🏫 {{ s.trainer?.name }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs text-slate-500">sisa slot</div>
                            <div class="text-lg font-bold text-emerald-400">{{ s.available_slots }}</div>
                        </div>
                    </RouterLink>
                </div>
            </div>

            <!-- Access Rules -->
            <div class="card">
                <h3 class="font-semibold text-lg mb-1">🔐 Hak Akses Anda</h3>
                <p class="text-xs text-slate-400 mb-4">Berdasarkan role: <span class="capitalize text-slate-200">{{ auth.role }}</span></p>
                <div class="space-y-2">
                    <div v-for="(r, i) in accessRules" :key="i" class="flex items-start gap-2 text-sm">
                        <span :class="r.ok ? 'text-emerald-400' : 'text-rose-400'" class="shrink-0">{{ r.ok ? '✓' : '✗' }}</span>
                        <span class="text-slate-300">{{ r.text }}</span>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-800 text-xs text-slate-500">
                    💡 Pengaturan role hanya bisa diubah oleh admin lewat database.
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div v-if="recentBookings.length" class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-lg">📋 Booking Terbaru</h3>
                    <p class="text-xs text-slate-400">{{ auth.isAdmin ? 'Semua booking di sistem' : auth.isTrainer ? 'Booking pada sesi yang Anda ajar' : 'Booking yang Anda buat' }}</p>
                </div>
                <RouterLink to="/bookings" class="text-xs text-indigo-400 hover:text-indigo-300">Lihat semua →</RouterLink>
            </div>
            <div class="overflow-x-auto -mx-5">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th class="hidden sm:table-cell">Sesi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in recentBookings" :key="b.id" @click="$router.push(`/bookings/${b.id}`)" class="cursor-pointer">
                            <td><span class="font-mono text-xs text-indigo-300">{{ b.booking_code }}</span></td>
                            <td class="hidden sm:table-cell">
                                <div class="font-medium">{{ b.session?.gym_class?.name }}</div>
                                <div class="text-xs text-slate-500">{{ b.session?.trainer?.name }}</div>
                            </td>
                            <td class="text-xs text-slate-400">{{ formatDate(b.session?.session_date) }}</td>
                            <td><span class="badge border" :class="statusColor(b.status)">{{ b.status }}</span></td>
                            <td class="text-right font-semibold">Rp {{ Number(b.total_price).toLocaleString('id-ID') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
