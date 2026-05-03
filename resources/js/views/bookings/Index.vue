<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const items = ref([]);
const loading = ref(false);
const filterStatus = ref('');
const meta = ref({});

async function load() {
    loading.value = true;
    try {
        const params = { per_page: 12 };
        if (filterStatus.value) params.status = filterStatus.value;
        const { data } = await axios.get('/bookings', { params });
        items.value = data.data;
        meta.value = data.meta;
    } finally { loading.value = false; }
}

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    : '—';

async function cancel(id) {
    const reason = prompt('Alasan pembatalan:', 'Berhalangan hadir');
    if (!reason) return;
    try {
        await axios.delete(`/bookings/${id}`, { data: { cancelled_reason: reason } });
        await load();
    } catch (e) { alert(e.response?.data?.message || 'Gagal batalkan'); }
}

async function changeStatus(b) {
    const status = prompt('Status baru (pending/confirmed/cancelled/completed):', b.status);
    if (!status) return;
    try {
        await axios.put(`/bookings/${b.id}`, { status });
        await load();
    } catch (e) { alert(e.response?.data?.message || 'Gagal update'); }
}

async function markPaid(b) {
    try {
        await axios.put(`/bookings/${b.id}`, { payment_status: 'paid', status: 'confirmed' });
        await load();
    } catch (e) { alert(e.response?.data?.message); }
}

const statusColor = (s) => ({
    pending: 'bg-amber-500/20 text-amber-300 border border-amber-500/30',
    confirmed: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
    completed: 'bg-slate-700/40 text-slate-300 border border-slate-600',
    cancelled: 'bg-rose-500/20 text-rose-300 border border-rose-500/30',
}[s]);
const payColor = (p) => ({
    unpaid: 'bg-rose-500/20 text-rose-300 border border-rose-500/30',
    paid: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
    refunded: 'bg-violet-500/20 text-violet-300 border border-violet-500/30',
}[p]);

onMounted(load);
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold">📋 Manajemen Booking</h1>
                <p class="text-sm text-slate-400">
                    {{ auth.isAdmin ? 'Semua booking di sistem' : auth.isTrainer ? 'Booking pada sesi yang Anda ajar' : 'Riwayat booking Anda' }}
                </p>
            </div>
            <RouterLink v-if="auth.isMember" to="/sessions" class="btn-primary">+ Booking Sesi Baru</RouterLink>
        </div>

        <div class="card mb-5 flex gap-3">
            <select v-model="filterStatus" @change="load" class="input lg:max-w-[200px]">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <div v-if="loading" class="text-center py-12 text-slate-500">Memuat...</div>
        <div v-else-if="items.length === 0" class="text-center py-12 text-slate-500">
            <div class="text-5xl mb-3 opacity-50">📭</div>
            <div>Belum ada booking.</div>
        </div>
        <!-- Mobile cards -->
        <div v-else class="space-y-3 lg:hidden">
            <div v-for="b in items" :key="b.id" class="card-hover" @click="$router.push(`/bookings/${b.id}`)">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold truncate">{{ b.session?.gym_class?.name }}</div>
                        <div class="text-xs text-slate-400 truncate">👨‍🏫 {{ b.session?.trainer?.name }}</div>
                        <div class="font-mono text-[11px] text-indigo-300 mt-1">{{ b.booking_code }}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-lg font-bold text-indigo-400">Rp {{ Number(b.total_price).toLocaleString('id-ID') }}</div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="badge" :class="statusColor(b.status)">{{ b.status }}</span>
                    <span class="badge" :class="payColor(b.payment_status)">{{ b.payment_status }}</span>
                    <span class="text-xs text-slate-400">📅 {{ formatDate(b.session?.session_date) }}</span>
                </div>
                <div v-if="auth.isAdmin || auth.isMember" class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-slate-800" @click.stop>
                    <button v-if="auth.isAdmin && b.payment_status==='unpaid'" @click="markPaid(b)" class="btn-success text-xs">💰 Lunas</button>
                    <button v-if="auth.isAdmin" @click="changeStatus(b)" class="btn-secondary text-xs">🔄 Status</button>
                    <button v-if="(auth.isAdmin || auth.isMember) && b.status !== 'cancelled' && b.status !== 'completed'" @click="cancel(b.id)" class="btn-danger text-xs">❌ Batal</button>
                </div>
            </div>
        </div>
        <!-- Desktop table -->
        <div v-if="!loading && items.length" class="hidden lg:block card overflow-x-auto !p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th v-if="auth.isAdmin || auth.isTrainer">Member</th>
                        <th>Sesi</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Bayar</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in items" :key="b.id">
                        <td><span class="font-mono text-xs text-indigo-300">{{ b.booking_code }}</span></td>
                        <td v-if="auth.isAdmin || auth.isTrainer">
                            <div class="font-medium">{{ b.user?.name }}</div>
                            <div class="text-xs text-slate-400">{{ b.user?.email }}</div>
                        </td>
                        <td>
                            <div class="font-semibold">{{ b.session?.gym_class?.name }}</div>
                            <div class="text-xs text-slate-400">👨‍🏫 {{ b.session?.trainer?.name }}</div>
                        </td>
                        <td class="text-xs text-slate-300">
                            {{ formatDate(b.session?.session_date) }}<br>
                            <span class="text-slate-400">{{ b.session?.start_time?.slice(0,5) }}-{{ b.session?.end_time?.slice(0,5) }}</span>
                        </td>
                        <td class="font-semibold text-indigo-400">Rp {{ Number(b.total_price).toLocaleString('id-ID') }}</td>
                        <td><span class="badge" :class="statusColor(b.status)">{{ b.status }}</span></td>
                        <td><span class="badge" :class="payColor(b.payment_status)">{{ b.payment_status }}</span></td>
                        <td class="text-right">
                            <div class="flex gap-1 justify-end flex-wrap">
                                <RouterLink :to="`/bookings/${b.id}`" class="btn-secondary text-xs">Detail</RouterLink>
                                <button v-if="auth.isAdmin && b.payment_status==='unpaid'" @click="markPaid(b)" class="btn-success text-xs">💰 Lunas</button>
                                <button v-if="auth.isAdmin" @click="changeStatus(b)" class="btn-secondary text-xs">🔄</button>
                                <button v-if="(auth.isAdmin || auth.isMember) && b.status !== 'cancelled' && b.status !== 'completed'" @click="cancel(b.id)" class="btn-danger text-xs">❌</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="meta.total" class="text-center text-xs text-slate-500 mt-6">Total {{ meta.total }} booking</div>
    </div>
</template>
