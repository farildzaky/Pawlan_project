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

//fungsi untuk mengambil parameter kelas
async function load() {
    try {
        const { data } = await axios.get(`/classes/${route.params.id}`);
        item.value = data.data;
    } finally { loading.value = false; }
}
async function remove() {
    if (!confirm('Yakin hapus kelas ini?')) return;
    await axios.delete(`/classes/${item.value.id}`);
    router.push('/classes');
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
                <div class="aspect-video bg-gradient-to-br from-indigo-900/40 to-slate-800 rounded-xl overflow-hidden flex items-center justify-center mb-4">
                    <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover">
                    <span v-else class="text-6xl opacity-40">🏋️</span>
                </div>
                <h3 class="font-semibold mb-2 text-slate-200">Deskripsi</h3>
                <p class="text-slate-300 leading-relaxed">{{ item.description }}</p>
            </div>
            <div class="space-y-3">
                <div class="card bg-gradient-to-br from-indigo-600/20 to-rose-600/20 border-indigo-500/30">
                    <div class="text-xs text-indigo-300">Harga per Sesi</div>
                    <div class="text-3xl font-bold text-white mt-1">Rp {{ Number(item.price).toLocaleString('id-ID') }}</div>
                </div>
                <div class="card text-sm space-y-2.5">
                    <div class="flex justify-between"><span class="text-slate-400">Kategori</span><span class="font-medium text-slate-200 capitalize">{{ item.category }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Level</span><span class="font-medium text-slate-200 capitalize">{{ item.difficulty_level }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Durasi</span><span class="font-medium text-slate-200">{{ item.duration_minutes }} menit</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Kapasitas</span><span class="font-medium text-slate-200">{{ item.max_capacity }} orang</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Status</span>
                        <span class="badge border" :class="item.is_active ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-slate-700/40 text-slate-400'">
                            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="flex justify-between"><span class="text-slate-400">Sesi Terjadwal</span><span class="font-medium text-slate-200">{{ item.sessions_count }}</span></div>
                </div>
                <div v-if="item.trainers && item.trainers.length" class="card">
                    <div class="font-semibold mb-3 text-slate-200">👨‍🏫 Trainer Tersedia</div>
                    <div v-for="t in item.trainers" :key="t.id" class="text-sm py-2 border-b border-slate-800 last:border-0">
                        <div class="font-medium text-slate-200">{{ t.name }}</div>
                        <div class="text-xs text-slate-400">{{ t.specialization }}</div>
                    </div>
                </div>
                <div v-if="auth.isAdmin" class="card flex flex-col gap-2">
                    <RouterLink :to="`/classes/${item.id}/edit`" class="btn-primary">✏️ Edit Kelas</RouterLink>
                    <button @click="remove" class="btn-danger">🗑 Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
