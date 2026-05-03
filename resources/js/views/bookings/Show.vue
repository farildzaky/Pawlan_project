<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const item = ref(null);
const loading = ref(true);

async function load() {
    try {
        const { data } = await axios.get(`/bookings/${route.params.id}`);
        item.value = data.data;
    } finally { loading.value = false; }
}

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    : '—';
const formatDateTime = (d) => d
    ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '—';

async function cancel() {
    const reason = prompt('Alasan pembatalan:', 'Berhalangan');
    if (!reason) return;
    try {
        await axios.delete(`/bookings/${item.value.id}`, { data: { cancelled_reason: reason } });
        router.push('/bookings');
    } catch (e) { alert(e.response?.data?.message); }
}

onMounted(load);
</script>

<template>
    <div v-if="loading" class="text-center py-10 text-slate-500">Memuat...</div>
    <div v-else-if="item">
        <div class="flex items-center gap-3 mb-4 flex-wrap">
            <button @click="router.back()" class="btn-ghost">←</button>
            <h1 class="text-2xl font-bold">Detail Booking</h1>
            <span class="font-mono text-sm bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 px-2 py-1 rounded">{{ item.booking_code }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card md:col-span-2 space-y-4">
                <div>
                    <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Kelas</div>
                    <div class="font-bold text-lg">{{ item.session?.gym_class?.name }}</div>
                    <p class="text-sm text-slate-400 mt-1">{{ item.session?.gym_class?.description }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-800">
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">👨‍🏫 Trainer</div>
                        <div class="font-semibold mt-1">{{ item.session?.trainer?.name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">📅 Tanggal</div>
                        <div class="font-semibold mt-1">{{ formatDate(item.session?.session_date) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">⏰ Waktu</div>
                        <div class="font-semibold mt-1">{{ item.session?.start_time?.slice(0,5) }}-{{ item.session?.end_time?.slice(0,5) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider">📍 Lokasi</div>
                        <div class="font-semibold mt-1">{{ item.session?.location }}</div>
                    </div>
                </div>
                <div v-if="item.notes" class="pt-3 border-t border-slate-800">
                    <div class="text-xs text-slate-400 uppercase tracking-wider">Catatan Member</div>
                    <p class="mt-1 text-slate-200">{{ item.notes }}</p>
                </div>
                <div v-if="item.cancelled_reason" class="bg-rose-500/10 border border-rose-500/30 px-4 py-3 rounded-xl">
                    <div class="text-xs text-rose-300 uppercase tracking-wider font-semibold">⚠️ Alasan Pembatalan</div>
                    <p class="text-sm text-rose-200 mt-1">{{ item.cancelled_reason }}</p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="card bg-gradient-to-br from-indigo-600/20 to-rose-600/20 border-indigo-500/30">
                    <div class="text-xs text-indigo-300">Total Bayar</div>
                    <div class="text-3xl font-bold text-white mt-1">Rp {{ Number(item.total_price).toLocaleString('id-ID') }}</div>
                </div>
                <div class="card text-sm space-y-2.5">
                    <div class="flex justify-between"><span class="text-slate-400">Status</span><span class="font-semibold text-slate-200 capitalize">{{ item.status }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Pembayaran</span><span class="font-semibold text-slate-200 capitalize">{{ item.payment_status }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Metode</span><span class="font-medium text-slate-200">{{ item.payment_method || '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Member</span><span class="font-medium text-slate-200">{{ item.user?.name }}</span></div>
                    <div class="flex justify-between text-xs pt-2 border-t border-slate-800"><span class="text-slate-400">Dibuat</span><span class="text-slate-300">{{ formatDateTime(item.booking_date) }}</span></div>
                </div>
                <button v-if="(auth.isAdmin || auth.isMember) && item.status !== 'cancelled' && item.status !== 'completed'"
                        @click="cancel" class="btn-danger w-full">❌ Batalkan Booking</button>
            </div>
        </div>
    </div>
</template>
