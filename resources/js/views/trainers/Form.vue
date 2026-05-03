<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    name: '', email: '', phone: '', specialization: '',
    years_experience: 0, certification: '', bio: '', hourly_rate: 100000,
    is_active: true, class_ids: [],
});
const allClasses = ref([]);
const errors = ref({});
const error = ref('');
const submitting = ref(false);

async function loadClasses() {
    const { data } = await axios.get('/classes?per_page=100');
    allClasses.value = data.data;
}
async function loadTrainer() {
    if (!isEdit.value) return;
    const { data } = await axios.get(`/trainers/${route.params.id}`);
    const t = data.data;
    form.value = {
        name: t.name, email: t.email, phone: t.phone, specialization: t.specialization,
        years_experience: t.years_experience, certification: t.certification, bio: t.bio,
        hourly_rate: t.hourly_rate, is_active: t.is_active,
        class_ids: (t.classes || []).map(c => c.id),
    };
}

async function submit() {
    submitting.value = true;
    error.value = '';
    errors.value = {};
    try {
        const payload = { ...form.value };
        // Trainer hanya boleh kirim field tertentu (di backend juga divalidasi)
        if (auth.isTrainer && !auth.isAdmin) {
            const allowed = { phone: payload.phone, bio: payload.bio, certification: payload.certification };
            await axios.put(`/trainers/${route.params.id}`, allowed);
        } else if (isEdit.value) {
            await axios.put(`/trainers/${route.params.id}`, payload);
        } else {
            await axios.post('/trainers', payload);
        }
        router.push('/trainers');
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal menyimpan';
        errors.value = e.response?.data?.errors || {};
    } finally { submitting.value = false; }
}

const trainerOnly = computed(() => auth.isTrainer && !auth.isAdmin);

onMounted(async () => {
    await Promise.all([loadClasses(), loadTrainer()]);
});
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-4">
            <button @click="router.back()" class="btn-ghost">←</button>
            <h1 class="text-2xl font-bold">{{ isEdit ? 'Edit Trainer' : 'Tambah Trainer' }}</h1>
        </div>

        <form @submit.prevent="submit" class="card space-y-4">
            <div v-if="error" class="bg-rose-50 text-rose-700 px-3 py-2 rounded text-sm">{{ error }}</div>
            <div v-if="trainerOnly" class="bg-amber-50 text-amber-700 px-3 py-2 rounded text-sm">
                Sebagai trainer, Anda hanya bisa update No. Telepon, Bio, dan Sertifikasi.
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Nama</label>
                    <input v-model="form.name" required :disabled="trainerOnly" class="input">
                    <p v-if="errors.name" class="text-xs text-rose-600 mt-1">{{ errors.name[0] }}</p>
                </div>
                <div>
                    <label class="label">Email</label>
                    <input v-model="form.email" type="email" required :disabled="trainerOnly" class="input">
                    <p v-if="errors.email" class="text-xs text-rose-600 mt-1">{{ errors.email[0] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Telepon/WA</label>
                    <input v-model="form.phone" required class="input">
                </div>
                <div>
                    <label class="label">Spesialisasi</label>
                    <input v-model="form.specialization" required :disabled="trainerOnly" class="input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Pengalaman (tahun)</label>
                    <input v-model.number="form.years_experience" type="number" min="0" :disabled="trainerOnly" class="input">
                </div>
                <div>
                    <label class="label">Tarif/jam (Rp)</label>
                    <input v-model.number="form.hourly_rate" type="number" min="0" :disabled="trainerOnly" class="input">
                </div>
            </div>

            <div>
                <label class="label">Sertifikasi</label>
                <textarea v-model="form.certification" rows="2" class="input"></textarea>
            </div>

            <div>
                <label class="label">Bio</label>
                <textarea v-model="form.bio" rows="3" class="input"></textarea>
            </div>

            <div v-if="!trainerOnly">
                <label class="label">Kelas yang Dapat Diajar</label>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3">
                    <label v-for="c in allClasses" :key="c.id" class="flex items-center gap-2 text-sm">
                        <input type="checkbox" :value="c.id" v-model="form.class_ids">
                        {{ c.name }} <span class="text-xs text-slate-400">({{ c.category }})</span>
                    </label>
                </div>
            </div>

            <div v-if="!trainerOnly">
                <label class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox">
                    <span class="text-sm">Aktif</span>
                </label>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="router.push('/trainers')" class="btn-secondary">Batal</button>
                <button :disabled="submitting" type="submit" class="btn-primary">
                    {{ submitting ? 'Menyimpan...' : (isEdit ? 'Update' : 'Simpan') }}
                </button>
            </div>
        </form>
    </div>
</template>
