<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const items = ref([]);
const loading = ref(false);
const search = ref('');
const meta = ref({});

async function load() {
    loading.value = true;
    try {
        const params = { per_page: 12 };
        if (search.value) params.search = search.value;
        const { data } = await axios.get('/trainers', { params });
        items.value = data.data;
        meta.value = data.meta;
    } finally { loading.value = false; }
}
async function remove(id) {
    if (!confirm('Yakin hapus trainer ini?')) return;
    try { await axios.delete(`/trainers/${id}`); await load(); }
    catch (e) { alert(e.response?.data?.message || 'Gagal hapus'); }
}
onMounted(load);
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold">👨‍🏫 Manajemen Trainer</h1>
                <p class="text-sm text-slate-400">Daftar instruktur & coach</p>
            </div>
            <RouterLink v-if="auth.isAdmin" to="/trainers/create" class="btn-primary">+ Tambah Trainer</RouterLink>
        </div>

        <div class="card mb-5 flex gap-3">
            <input v-model="search" @keyup.enter="load" class="input flex-1" placeholder="🔍 Cari nama/email trainer...">
            <button @click="load" class="btn-secondary">Cari</button>
        </div>

        <div v-if="loading" class="text-center py-12 text-slate-500">Memuat...</div>
        <div v-else-if="items.length === 0" class="text-center py-12 text-slate-500">Belum ada trainer.</div>
        <!-- Mobile cards -->
        <div v-else class="grid sm:grid-cols-2 lg:hidden gap-4">
            <div v-for="t in items" :key="t.id" class="card-hover">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-white font-bold shrink-0">
                        {{ t.name?.[0]?.toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold truncate">{{ t.name }}</div>
                        <div class="text-xs text-slate-400 truncate">{{ t.email }}</div>
                        <span class="badge border mt-1" :class="t.is_active ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-slate-700/40 text-slate-400'">
                            {{ t.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                    <div><div class="text-xs text-slate-400">Spesialisasi</div><div class="text-slate-200">{{ t.specialization }}</div></div>
                    <div><div class="text-xs text-slate-400">Pengalaman</div><div class="text-slate-200">{{ t.years_experience }} thn</div></div>
                    <div><div class="text-xs text-slate-400">Tarif/jam</div><div class="text-indigo-400 font-semibold">Rp {{ Number(t.hourly_rate).toLocaleString('id-ID') }}</div></div>
                    <div><div class="text-xs text-slate-400">Kelas</div><div class="text-slate-200">{{ t.classes_count }} kelas</div></div>
                </div>
                <div class="flex gap-2 mt-4">
                    <RouterLink :to="`/trainers/${t.id}`" class="btn-secondary flex-1 text-xs">Detail</RouterLink>
                    <RouterLink v-if="auth.isAdmin || (auth.isTrainer && auth.user?.trainer_id === t.id)" :to="`/trainers/${t.id}/edit`" class="btn-secondary text-xs">✏️</RouterLink>
                    <button v-if="auth.isAdmin" @click="remove(t.id)" class="btn-danger text-xs">🗑</button>
                </div>
            </div>
        </div>
        <!-- Desktop table -->
        <div v-if="!loading && items.length" class="hidden lg:block card overflow-x-auto !p-0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Trainer</th>
                        <th>Spesialisasi</th>
                        <th>Pengalaman</th>
                        <th>Tarif/jam</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in items" :key="t.id">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-white font-bold shrink-0 text-sm">
                                    {{ t.name?.[0]?.toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-100">{{ t.name }}</div>
                                    <div class="text-xs text-slate-400">{{ t.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ t.specialization }}</td>
                        <td>{{ t.years_experience }} thn</td>
                        <td class="text-indigo-400 font-semibold">Rp {{ Number(t.hourly_rate).toLocaleString('id-ID') }}</td>
                        <td>{{ t.classes_count }}</td>
                        <td>
                            <span class="badge border" :class="t.is_active ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-slate-700/40 text-slate-400'">
                                {{ t.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="flex gap-1 justify-end">
                                <RouterLink :to="`/trainers/${t.id}`" class="btn-secondary text-xs">Detail</RouterLink>
                                <RouterLink v-if="auth.isAdmin || (auth.isTrainer && auth.user?.trainer_id === t.id)" :to="`/trainers/${t.id}/edit`" class="btn-secondary text-xs">✏️</RouterLink>
                                <button v-if="auth.isAdmin" @click="remove(t.id)" class="btn-danger text-xs">🗑</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="meta.total" class="text-center text-xs text-slate-500 mt-6">Total {{ meta.total }} trainer</div>
    </div>
</template>
