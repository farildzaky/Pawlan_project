<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const items = ref([]);
const loading = ref(false);
const filterDate = ref('');
const filterStatus = ref('');
const meta = ref({});

async function load() {
    loading.value = true;
    try {
        const params = { per_page: 12 };
        if (filterDate.value) params.date = filterDate.value;
        if (filterStatus.value) params.status = filterStatus.value;
        const { data } = await axios.get('/sessions', { params });
        items.value = data.data;
        meta.value = data.meta;
    } finally { loading.value = false; }
}

async function remove(id) {
    if (!confirm('Yakin batalkan sesi? Semua booking akan dibatalkan otomatis.')) return;
    try { await axios.delete(`/sessions/${id}`); await load(); }
    catch (e) { alert(e.response?.data?.message); }
}

async function bookSession(id) {
    if (!auth.isMember) { alert('Hanya member yang bisa booking'); return; }
    const method = prompt('Metode pembayaran (cash/transfer/ewallet):', 'transfer');
    if (!method) return;
    try {
        await axios.post('/bookings', { session_id: id, payment_method: method });
        alert('Booking berhasil!');
        router.push('/bookings');
    } catch (e) { alert(e.response?.data?.message || 'Gagal booking'); }
}

const statusColor = (s) => ({
    scheduled: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
    ongoing: 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
    completed: 'bg-slate-700/40 text-slate-300 border border-slate-600',
    cancelled: 'bg-rose-500/20 text-rose-300 border border-rose-500/30',
}[s] || 'bg-slate-700/40 text-slate-300');

onMounted(load);
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold">📅 Sesi / Jadwal</h1>
                <p class="text-sm text-slate-400">Sesi latihan terjadwal yang siap di-booking</p>
            </div>
            <RouterLink v-if="auth.isAdmin" to="/sessions/create" class="btn-primary">+ Tambah Sesi</RouterLink>
        </div>

        <div class="card mb-5 flex gap-3 flex-wrap">
            <input type="date" v-model="filterDate" @change="load" class="input lg:max-w-[200px]">
            <select v-model="filterStatus" @change="load" class="input lg:max-w-[200px]">
                <option value="">Semua Status</option>
                <option value="scheduled">Scheduled</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button @click="load" class="btn-secondary">Filter</button>
        </div>

        <div v-if="loading" class="text-center py-12 text-slate-500">Memuat...</div>
        <div v-else-if="items.length === 0" class="text-center py-12 text-slate-500">Belum ada sesi.</div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="s in items" :key="s.id" class="card-hover relative overflow-hidden">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-rose-500 flex flex-col items-center justify-center text-white shrink-0 shadow-lg shadow-indigo-500/30">
                        <div class="text-[10px] uppercase">{{ new Date(s.session_date).toLocaleDateString('id-ID', { month: 'short' }) }}</div>
                        <div class="text-xl font-bold leading-none">{{ new Date(s.session_date).getDate() }}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="badge" :class="statusColor(s.status)">{{ s.status }}</span>
                        <h3 class="font-semibold text-base mt-1.5 leading-tight truncate">{{ s.gym_class?.name }}</h3>
                        <div class="text-xs text-slate-400 truncate">👨‍🏫 {{ s.trainer?.name }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs text-slate-300 mb-3">
                    <div class="flex items-center gap-1.5"><span class="text-slate-500">⏰</span> {{ s.start_time?.slice(0,5) }}-{{ s.end_time?.slice(0,5) }}</div>
                    <div class="flex items-center gap-1.5"><span class="text-slate-500">📍</span> {{ s.location }}</div>
                </div>
                <div class="mb-3">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-400">Slot terisi</span>
                        <span class="text-slate-200 font-medium">{{ s.booked_count }}/{{ s.capacity }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-rose-500 rounded-full" :style="{ width: `${Math.min(100, (s.booked_count/s.capacity)*100)}%` }"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                    <div class="text-lg font-bold text-indigo-400">Rp {{ Number(s.price).toLocaleString('id-ID') }}</div>
                    <span class="text-xs text-emerald-400 font-medium">{{ s.available_slots }} sisa</span>
                </div>
                <div class="flex gap-2 mt-3">
                    <RouterLink :to="`/sessions/${s.id}`" class="btn-secondary flex-1 text-xs">Detail</RouterLink>
                    <button v-if="auth.isMember && s.status === 'scheduled' && s.available_slots > 0" @click="bookSession(s.id)" class="btn-success text-xs">📋 Book</button>
                    <template v-if="auth.isAdmin">
                        <RouterLink :to="`/sessions/${s.id}/edit`" class="btn-secondary text-xs">✏️</RouterLink>
                        <button @click="remove(s.id)" class="btn-danger text-xs">🗑</button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
