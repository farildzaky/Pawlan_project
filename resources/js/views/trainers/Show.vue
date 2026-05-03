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
        const { data } = await axios.get(`/trainers/${route.params.id}`);
        item.value = data.data;
    } finally { loading.value = false; }
}
async function remove() {
    if (!confirm('Yakin hapus trainer?')) return;
    try { await axios.delete(`/trainers/${item.value.id}`); router.push('/trainers'); }
    catch (e) { alert(e.response?.data?.message); }
}
onMounted(load);
</script>

<template>
    <div v-if="loading" class="text-center py-10 text-slate-500">Memuat...</div>
    <div v-else-if="item">
        <div class="flex items-center gap-3 mb-4">
            <button @click="router.back()" class="btn-ghost">←</button>
            <h1 class="text-2xl font-bold">{{ item.name }}</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card md:col-span-2">
                <div class="flex items-start gap-4 pb-4 border-b border-slate-800">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-rose-500 flex items-center justify-center text-white text-2xl font-bold shrink-0">
                        {{ item.name?.[0]?.toUpperCase() }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ item.name }}</h2>
                        <div class="text-sm text-slate-400">{{ item.specialization }}</div>
                        <div class="text-xs text-amber-400 mt-1">⭐ {{ item.years_experience }} tahun pengalaman</div>
                    </div>
                </div>
                <div class="space-y-4 mt-4">
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Bio</div>
                        <p class="text-slate-200 leading-relaxed">{{ item.bio || '—' }}</p>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Sertifikasi</div>
                        <p class="text-slate-200 leading-relaxed">{{ item.certification || '—' }}</p>
                    </div>
                    <div v-if="item.classes?.length">
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Kelas yang Diajar</div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="c in item.classes" :key="c.id" class="badge border bg-indigo-500/15 text-indigo-300 border-indigo-500/30">
                                🏋️ {{ c.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="card bg-gradient-to-br from-indigo-600/20 to-rose-600/20 border-indigo-500/30">
                    <div class="text-xs text-indigo-300">Tarif per Jam</div>
                    <div class="text-3xl font-bold text-white mt-1">Rp {{ Number(item.hourly_rate).toLocaleString('id-ID') }}</div>
                </div>
                <div class="card text-sm space-y-2.5">
                    <div class="flex justify-between"><span class="text-slate-400">Email</span><span class="font-medium text-slate-200 truncate ml-2">{{ item.email }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Telepon</span><span class="font-medium text-slate-200">{{ item.phone }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Total Sesi</span><span class="font-medium text-slate-200">{{ item.sessions_count }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Status</span>
                        <span class="badge border" :class="item.is_active ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-slate-700/40 text-slate-400'">
                            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
                <div v-if="auth.isAdmin || (auth.isTrainer && auth.user?.trainer_id === item.id)" class="card flex flex-col gap-2">
                    <RouterLink :to="`/trainers/${item.id}/edit`" class="btn-primary">✏️ Edit Profil</RouterLink>
                    <button v-if="auth.isAdmin" @click="remove" class="btn-danger">🗑 Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
