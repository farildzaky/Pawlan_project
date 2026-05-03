<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    name: '', description: '', category: 'cardio', difficulty_level: 'beginner',
    duration_minutes: 45, max_capacity: 15, price: 75000, is_active: true,
});
const imageFile = ref(null);
const previewUrl = ref(null);
const errors = ref({});
const loading = ref(false);
const submitting = ref(false);
const error = ref('');

async function load() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await axios.get(`/classes/${route.params.id}`);
        const c = data.data;
        form.value = {
            name: c.name, description: c.description, category: c.category,
            difficulty_level: c.difficulty_level, duration_minutes: c.duration_minutes,
            max_capacity: c.max_capacity, price: c.price, is_active: c.is_active,
        };
        previewUrl.value = c.image_url;
    } finally { loading.value = false; }
}

function pickImage(e) {
    const f = e.target.files?.[0];
    if (!f) return;
    if (f.size > 50 * 1024 * 1024) { alert('Ukuran maksimal 50 MB'); return; }
    imageFile.value = f;
    previewUrl.value = URL.createObjectURL(f);
}

async function submit() {
    submitting.value = true;
    error.value = '';
    errors.value = {};

    const fd = new FormData();
    Object.entries(form.value).forEach(([k, v]) => {
        if (v === null || v === undefined) return;
        fd.append(k, typeof v === 'boolean' ? (v ? 1 : 0) : v);
    });
    if (imageFile.value) fd.append('image', imageFile.value);

    try {
        if (isEdit.value) {
            fd.append('_method', 'PUT');
            await axios.post(`/classes/${route.params.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        } else {
            await axios.post('/classes', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        }
        router.push('/classes');
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal menyimpan';
        errors.value = e.response?.data?.errors || {};
    } finally { submitting.value = false; }
}

onMounted(load);
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-4">
            <button @click="router.back()" class="btn-ghost">←</button>
            <h1 class="text-2xl font-bold">{{ isEdit ? 'Edit Kelas' : 'Tambah Kelas' }}</h1>
        </div>

        <form @submit.prevent="submit" class="card space-y-4">
            <div v-if="error" class="bg-rose-50 text-rose-700 px-3 py-2 rounded text-sm">{{ error }}</div>

            <div>
                <label class="label">Foto Kelas</label>
                <div class="flex items-start gap-4 flex-col sm:flex-row">
                    <div class="w-full sm:w-40 h-28 bg-gradient-to-br from-indigo-900/40 to-slate-800 border border-slate-700 rounded-xl overflow-hidden flex items-center justify-center">
                        <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover">
                        <span v-else class="text-4xl opacity-40">📸</span>
                    </div>
                    <div class="flex-1 w-full">
                        <input type="file" accept="image/jpeg,image/png,image/jpg" @change="pickImage" class="text-sm text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-600/20 file:text-indigo-300 file:cursor-pointer file:hover:bg-indigo-600/30">
                        <p class="text-xs text-slate-400 mt-2">JPG / JPEG / PNG. Maksimal 50 MB.</p>
                        <p v-if="errors.image" class="text-xs text-rose-400 mt-1">{{ errors.image[0] }}</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="label">Nama Kelas</label>
                <input v-model="form.name" required class="input">
                <p v-if="errors.name" class="text-xs text-rose-600 mt-1">{{ errors.name[0] }}</p>
            </div>

            <div>
                <label class="label">Deskripsi</label>
                <textarea v-model="form.description" required rows="3" class="input"></textarea>
                <p v-if="errors.description" class="text-xs text-rose-600 mt-1">{{ errors.description[0] }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Kategori</label>
                    <select v-model="form.category" class="input">
                        <option value="cardio">Cardio</option>
                        <option value="strength">Strength</option>
                        <option value="flexibility">Flexibility</option>
                        <option value="mind-body">Mind-Body</option>
                    </select>
                </div>
                <div>
                    <label class="label">Level</label>
                    <select v-model="form.difficulty_level" class="input">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="label">Durasi (menit)</label>
                    <input v-model.number="form.duration_minutes" type="number" min="15" max="180" class="input">
                </div>
                <div>
                    <label class="label">Kapasitas</label>
                    <input v-model.number="form.max_capacity" type="number" min="1" class="input">
                </div>
                <div>
                    <label class="label">Harga (Rp)</label>
                    <input v-model.number="form.price" type="number" min="0" class="input">
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox">
                    <span class="text-sm">Aktif</span>
                </label>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="router.push('/classes')" class="btn-secondary">Batal</button>
                <button :disabled="submitting" type="submit" class="btn-primary">
                    {{ submitting ? 'Menyimpan...' : (isEdit ? 'Update' : 'Simpan') }}
                </button>
            </div>
        </form>
    </div>
</template>
