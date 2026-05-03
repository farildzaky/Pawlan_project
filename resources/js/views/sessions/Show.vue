<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const item = ref(null);
const loading = ref(true);

async function load() {
    try {
        const { data } = await axios.get(`/sessions/${route.params.id}`);
        item.value = data.data;
    } finally { loading.value = false; }
}

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    : '—';

async function bookSession() {
    const method = prompt('Metode pembayaran (cash/transfer/ewallet):', 'transfer');
    if (!method) return;
    try {
        await axios.post('/bookings', { session_id: item.value.id, payment_method: method });
        alert('Booking berhasil!');
        router.push('/bookings');
    } catch (e) { alert(e.response?.data?.message || 'Gagal booking'); }
}

async function remove() {
    if (!confirm('Yakin batalkan sesi?')) return;
    try { await axios.delete(`/sessions/${item.value.id}`); router.push('/sessions'); }
    catch (e) { alert(e.response?.data?.message); }
}

onMounted(load);
</script>

<template>
    <div v-if="loading" class="text-center py-10 text-slate-500">Memuat...</div>
    <div v-else-if="item">
        <div class="flex items-center gap-3 mb-4">
            <button @click="router.back()" class="btn-ghost">←</button>
            <h1 class="text-2xl font-bold truncate">{{ item.gym_class?.name }}</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card md:col-span-2 space-y-4">
                <div class="flex flex-wrap gap-2">
                    <span class="badge border bg-indigo-500/15 text-indigo-300 border-indigo-500/30">{{ item.gym_class?.category }}</span>
                    <span class="badge border bg-emerald-500/20 text-emerald-300 border-emerald-500/30 capitalize">{{ item.status }}</span>
                </div>
                <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-white font-bold">
                        {{ item.trainer?.name?.[0]?.toUpperCase() }}
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Trainer</div>
                        <div class="font-semibold">{{ item.trainer?.name }}</div>
                        <div class="text-xs text-slate-400">{{ item.trainer?.specialization }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">📅 Tanggal</div>
                        <div class="font-semibold mt-1">{{ formatDate(item.session_date) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">⏰ Waktu</div>
                        <div class="font-semibold mt-1">{{ item.start_time?.slice(0,5) }} – {{ item.end_time?.slice(0,5) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">📍 Lokasi</div>
                        <div class="font-semibold mt-1">{{ item.location }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">👥 Slot</div>
                        <div class="font-semibold mt-1">{{ item.booked_count }}/{{ item.capacity }}
                            <span class="text-emerald-400 text-sm">(sisa {{ item.available_slots }})</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Progres Booking</div>
                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-rose-500 rounded-full" :style="{ width: `${Math.min(100, (item.booked_count/item.capacity)*100)}%` }"></div>
                    </div>
                </div>
                <div v-if="item.notes" class="pt-3 border-t border-slate-800">
                    <div class="text-xs text-slate-400 uppercase tracking-wider">Catatan</div>
                    <p class="mt-1 text-slate-200">{{ item.notes }}</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="card bg-gradient-to-br from-indigo-600/20 to-rose-600/20 border-indigo-500/30">
                    <div class="text-xs text-indigo-300">Harga Sesi</div>
                    <div class="text-3xl font-bold text-white mt-1">Rp {{ Number(item.price).toLocaleString('id-ID') }}</div>
                </div>
                <button v-if="auth.isMember && item.status === 'scheduled' && item.available_slots > 0"
                        @click="bookSession" class="btn-success w-full">📋 Book Sesi Ini</button>
                <div v-if="auth.isAdmin" class="card flex flex-col gap-2">
                    <RouterLink :to="`/sessions/${item.id}/edit`" class="btn-primary">✏️ Edit Sesi</RouterLink>
                    <button @click="remove" class="btn-danger">🗑 Batalkan Sesi</button>
                </div>
            </div>
        </div>
    </div>
</template>
