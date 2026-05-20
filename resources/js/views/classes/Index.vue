<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

//deklarasi variabel dan fungsi yang dibutuhkan pada halaman index kelas, seperti items untuk menyimpan data kelas yang didapat dari backend, loading untuk menandakan proses loading data, search untuk menyimpan kata kunci pencarian, filterCat untuk menyimpan kategori yang dipilih pada filter, meta untuk menyimpan informasi paginasi, dan fungsi load untuk mengambil data kelas dari backend dengan parameter pencarian dan filter yang sesuai, serta fungsi remove untuk menghapus kelas tertentu. Selain itu juga terdapat fungsi categoryColor untuk menentukan warna badge kategori pada tampilan kelas berdasarkan kategorinya.
const auth = useAuthStore();
const items = ref([]);
const loading = ref(false);
const search = ref('');
const filterCat = ref('');
const meta = ref({});

//fungsi untuk mengambil data kelas dari backend dengan parameter pencarian dan filter yang sesuai, serta fungsi remove untuk menghapus kelas tertentu. 
//Selain itu juga terdapat fungsi categoryColor untuk menentukan warna badge kategori pada tampilan kelas berdasarkan kategorinya.
async function load() {
    loading.value = true;
    try {
        const params = { per_page: 12 };
        if (search.value) params.search = search.value;
        if (filterCat.value) params.category = filterCat.value;
        const { data } = await axios.get('/classes', { params });
        items.value = data.data;
        meta.value = data.meta;
    } finally { loading.value = false; }
}

//fungsi untuk menghapus kelas tertentu dengan mengirim request delete ke backend, dan jika berhasil maka akan memanggil fungsi load untuk memperbarui daftar kelas yang ditampilkan. Jika terjadi error saat penghapusan, maka akan menampilkan pesan error yang didapat dari response backend atau pesan default 'Gagal hapus'.
async function remove(id) {
    if (!confirm('Yakin hapus kelas ini?')) return;
    try {
        await axios.delete(`/classes/${id}`);
        await load();
    } catch (e) { alert(e.response?.data?.message || 'Gagal hapus'); }
}

const categoryColor = (c) => ({
    cardio: 'bg-rose-500/15 text-rose-300 border border-rose-500/30',
    strength: 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/30',
    flexibility: 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30',
    'mind-body': 'bg-violet-500/15 text-violet-300 border border-violet-500/30',
}[c] || 'bg-slate-700/40 text-slate-300');

//get load sesuai yang sudah dibuat diatas untuk menampilkan 
// data kelas pertama kali saat halaman dimuat
onMounted(load);
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold">🏋️ Manajemen Kelas</h1>
                <p class="text-sm text-slate-400">Katalog kelas fitness yang tersedia</p>
            </div>
            <RouterLink v-if="auth.isAdmin" to="/classes/create" class="btn-primary">+ Tambah Kelas</RouterLink>
        </div>

        <div class="card mb-5 flex flex-wrap gap-3">
            <input v-model="search" @keyup.enter="load" class="input flex-1 min-w-[200px]" placeholder="🔍 Cari nama kelas...">
            <select v-model="filterCat" @change="load" class="input lg:max-w-[200px]">
                <option value="">Semua Kategori</option>
                <option value="cardio">Cardio</option>
                <option value="strength">Strength</option>
                <option value="flexibility">Flexibility</option>
                <option value="mind-body">Mind-Body</option>
            </select>
            <button @click="load" class="btn-secondary">Cari</button>
        </div>

        <div v-if="loading" class="text-center py-12 text-slate-500">Memuat...</div>
        <div v-else-if="items.length === 0" class="text-center py-12 text-slate-500">Tidak ada kelas.</div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="c in items" :key="c.id" class="card-hover flex flex-col group overflow-hidden">
                <div class="aspect-video bg-gradient-to-br from-indigo-900/40 to-slate-800 rounded-xl overflow-hidden mb-3 flex items-center justify-center -mx-1 -mt-1 relative">
                    <img v-if="c.image_url" :src="c.image_url" :alt="c.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span v-else class="text-5xl opacity-40">🏋️</span>
                    <div class="absolute top-2 left-2 flex gap-1.5">
                        <span class="badge" :class="categoryColor(c.category)">{{ c.category }}</span>
                    </div>
                </div>
                <div class="flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-lg leading-tight">{{ c.name }}</h3>
                        <span class="text-xs text-slate-400 shrink-0 mt-1">{{ c.difficulty_level }}</span>
                    </div>
                    <p class="text-sm text-slate-400 line-clamp-2 mb-3">{{ c.description }}</p>
                    <div class="text-xs text-slate-400 flex items-center gap-3 mb-3">
                        <span>⏱️ {{ c.duration_minutes }} mnt</span>
                        <span>👥 {{ c.max_capacity }} orang</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                        <div class="text-lg font-bold text-indigo-400">Rp {{ Number(c.price).toLocaleString('id-ID') }}</div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <RouterLink :to="`/classes/${c.id}`" class="btn-secondary flex-1 text-xs">Detail</RouterLink>
                        <template v-if="auth.isAdmin">
                            <RouterLink :to="`/classes/${c.id}/edit`" class="btn-secondary text-xs">✏️</RouterLink>
                            <button @click="remove(c.id)" class="btn-danger text-xs">🗑</button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="meta.total" class="text-center text-xs text-slate-500 mt-6">
            Menampilkan {{ items.length }} dari {{ meta.total }} kelas
        </div>
    </div>
</template>
