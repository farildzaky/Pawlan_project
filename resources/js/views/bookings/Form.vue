<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const sessions = ref([]);
const form = ref({ session_id: '', payment_method: 'transfer', notes: '' });
const error = ref('');
const submitting = ref(false);

async function loadSessions() {
    const { data } = await axios.get('/sessions', { params: { available: 1, status: 'scheduled', per_page: 100 } });
    sessions.value = data.data;
    if (route.query.session_id) form.value.session_id = Number(route.query.session_id);
}

async function submit() {
    submitting.value = true;
    error.value = '';
    try {
        await axios.post('/bookings', form.value);
        router.push('/bookings');
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal booking';
    } finally { submitting.value = false; }
}

onMounted(loadSessions);
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-4">
            <button @click="router.back()" class="btn-ghost">←</button>
            <h1 class="text-2xl font-bold">Booking Sesi Baru</h1>
        </div>
        <form @submit.prevent="submit" class="card space-y-4">
            <div v-if="error" class="bg-rose-50 text-rose-700 px-3 py-2 rounded text-sm">{{ error }}</div>

            <div>
                <label class="label">Pilih Sesi</label>
                <select v-model="form.session_id" required class="input">
                    <option value="">— pilih sesi —</option>
                    <option v-for="s in sessions" :key="s.id" :value="s.id">
                        {{ s.gym_class?.name }} · {{ s.session_date }} {{ s.start_time }}-{{ s.end_time }} · {{ s.trainer?.name }}
                        (sisa {{ s.available_slots }})
                    </option>
                </select>
            </div>

            <div>
                <label class="label">Metode Pembayaran</label>
                <select v-model="form.payment_method" required class="input">
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
            </div>

            <div>
                <label class="label">Catatan (opsional)</label>
                <textarea v-model="form.notes" rows="2" class="input"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="router.push('/bookings')" class="btn-secondary">Batal</button>
                <button :disabled="submitting" type="submit" class="btn-primary">
                    {{ submitting ? 'Memproses...' : 'Booking' }}
                </button>
            </div>
        </form>
    </div>
</template>
