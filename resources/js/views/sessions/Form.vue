<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = ref({
    class_id: '', trainer_id: '', session_date: '', start_time: '', end_time: '',
    location: '', capacity: null, price: null, notes: '',
});
const allClasses = ref([]);
const trainersForClass = ref([]);
const errors = ref({});
const error = ref('');
const submitting = ref(false);

async function loadClasses() {
    const { data } = await axios.get('/classes?per_page=100');
    allClasses.value = data.data;
}
async function loadTrainersForClass(classId) {
    if (!classId) { trainersForClass.value = []; return; }
    const { data } = await axios.get(`/classes/${classId}`);
    trainersForClass.value = data.data.trainers || [];
}
async function loadSession() {
    if (!isEdit.value) return;
    const { data } = await axios.get(`/sessions/${route.params.id}`);
    const s = data.data;
    form.value = {
        class_id: s.class_id, trainer_id: s.trainer_id,
        session_date: s.session_date, start_time: s.start_time?.slice(0, 5),
        end_time: s.end_time?.slice(0, 5), location: s.location,
        capacity: s.capacity, price: s.price, notes: s.notes,
    };
    await loadTrainersForClass(s.class_id);
}

watch(() => form.value.class_id, async (val) => {
    await loadTrainersForClass(val);
    if (!isEdit.value) form.value.trainer_id = '';
});

async function submit() {
    submitting.value = true;
    error.value = '';
    errors.value = {};
    try {
        const payload = { ...form.value };
        if (!payload.capacity) delete payload.capacity;
        if (!payload.price) delete payload.price;
        if (isEdit.value) await axios.put(`/sessions/${route.params.id}`, payload);
        else await axios.post('/sessions', payload);
        router.push('/sessions');
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal menyimpan';
        errors.value = e.response?.data?.errors || {};
    } finally { submitting.value = false; }
}

onMounted(async () => {
    await loadClasses();
    await loadSession();
});
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-4">
            <button @click="router.back()" class="btn-ghost">←</button>
            <h1 class="text-2xl font-bold">{{ isEdit ? 'Edit Sesi' : 'Tambah Sesi' }}</h1>
        </div>

        <form @submit.prevent="submit" class="card space-y-4">
            <div v-if="error" class="bg-rose-50 text-rose-700 px-3 py-2 rounded text-sm">{{ error }}</div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Kelas</label>
                    <select v-model="form.class_id" required class="input">
                        <option value="">— Pilih kelas —</option>
                        <option v-for="c in allClasses" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="label">Trainer</label>
                    <select v-model="form.trainer_id" required class="input" :disabled="!form.class_id">
                        <option value="">— Pilih trainer —</option>
                        <option v-for="t in trainersForClass" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                    <p v-if="form.class_id && trainersForClass.length === 0" class="text-xs text-amber-600 mt-1">
                        Tidak ada trainer terdaftar untuk kelas ini.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="label">Tanggal</label>
                    <input v-model="form.session_date" type="date" required class="input">
                </div>
                <div>
                    <label class="label">Mulai</label>
                    <input v-model="form.start_time" type="time" required class="input">
                </div>
                <div>
                    <label class="label">Selesai</label>
                    <input v-model="form.end_time" type="time" required class="input">
                </div>
            </div>

            <div>
                <label class="label">Lokasi/Ruangan</label>
                <input v-model="form.location" required class="input" placeholder="Studio A">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Kapasitas (opsional)</label>
                    <input v-model.number="form.capacity" type="number" min="1" class="input" placeholder="Default dari kelas">
                </div>
                <div>
                    <label class="label">Harga (opsional)</label>
                    <input v-model.number="form.price" type="number" min="0" class="input" placeholder="Default dari kelas">
                </div>
            </div>

            <div>
                <label class="label">Catatan</label>
                <textarea v-model="form.notes" rows="2" class="input"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="router.push('/sessions')" class="btn-secondary">Batal</button>
                <button :disabled="submitting" type="submit" class="btn-primary">
                    {{ submitting ? 'Menyimpan...' : (isEdit ? 'Update' : 'Simpan') }}
                </button>
            </div>
        </form>
    </div>
</template>
