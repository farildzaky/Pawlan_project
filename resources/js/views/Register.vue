<script setup>
import { ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const form = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' });
const errors = ref({});
const loading = ref(false);
const error = ref('');
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

async function submit() {
    loading.value = true;
    error.value = '';
    errors.value = {};
    try {
        await auth.register(form.value);
        router.push('/dashboard');
    } catch (e) {
        error.value = e.response?.data?.message || 'Registrasi gagal.';
        errors.value = e.response?.data?.errors || {};
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-950 text-slate-100 p-4 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-rose-600/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>

        <div class="w-full max-w-md relative z-10">
            <RouterLink to="/" class="block text-center mb-6 group">
                <div class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent inline-flex items-center gap-2">
                    <span class="group-hover:scale-110 transition-transform">💪</span>
                    FitFlow
                </div>
                <div class="text-sm text-slate-400 mt-1">Daftar Member Baru — Gratis</div>
            </RouterLink>

            <form @submit.prevent="submit" class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 lg:p-8 shadow-2xl shadow-black/40 space-y-4">
                <div>
                    <h2 class="text-2xl font-bold">Buat Akun Member</h2>
                    <p class="text-sm text-slate-400 mt-1">Mulai latihan dengan akses ke semua kelas & sesi</p>
                </div>

                <div v-if="error" class="bg-rose-500/10 border border-rose-500/30 text-rose-300 px-3 py-2 rounded-lg text-sm">
                    ⚠️ {{ error }}
                </div>

                <div>
                    <label class="label">Nama Lengkap</label>
                    <input v-model="form.name" required class="input" placeholder="John Doe">
                    <p v-if="errors.name" class="text-xs text-rose-400 mt-1">{{ errors.name[0] }}</p>
                </div>
                <div>
                    <label class="label">Email</label>
                    <input v-model="form.email" type="email" required class="input" placeholder="email@contoh.com">
                    <p v-if="errors.email" class="text-xs text-rose-400 mt-1">{{ errors.email[0] }}</p>
                </div>
                <div>
                    <label class="label">No. Telepon</label>
                    <input v-model="form.phone" class="input" placeholder="0812xxxxxxxx">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Password</label>
                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required class="input pr-10" minlength="6" placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-200 transition"
                                    :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-xs text-rose-400 mt-1">{{ errors.password[0] }}</p>
                    </div>
                    <div>
                        <label class="label">Konfirmasi</label>
                        <div class="relative">
                            <input v-model="form.password_confirmation" :type="showPasswordConfirm ? 'text' : 'password'" required class="input pr-10" minlength="6" placeholder="••••••••">
                            <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-200 transition"
                                    :aria-label="showPasswordConfirm ? 'Sembunyikan' : 'Tampilkan'">
                                <svg v-if="!showPasswordConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="loading" class="btn-primary w-full disabled:opacity-50">
                    {{ loading ? 'Memproses...' : '🎯 Daftar Sekarang' }}
                </button>

                <div class="text-center text-sm text-slate-400">
                    Sudah punya akun?
                    <RouterLink to="/login" class="text-indigo-400 font-medium hover:text-indigo-300">Login</RouterLink>
                </div>
            </form>

            <div class="text-center mt-4">
                <RouterLink to="/" class="text-xs text-slate-500 hover:text-slate-300">← Kembali ke halaman utama</RouterLink>
            </div>
        </div>
    </div>
</template>
