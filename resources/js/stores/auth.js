import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: null,
    }),
    getters: {
        isAuthenticated: (s) => !!s.token,
        role: (s) => s.user?.role || 'guest',
        isAdmin: (s) => s.user?.role === 'admin',
        isTrainer: (s) => s.user?.role === 'trainer',
        isMember: (s) => s.user?.role === 'member',
    },
    actions: {
        hydrate() {
            const token = localStorage.getItem('fitflow_token');
            const user = localStorage.getItem('fitflow_user');
            if (token) this.token = token;
            if (user) {
                try { this.user = JSON.parse(user); } catch (e) { this.user = null; }
            }
        },
        async login(email, password) {
            const { data } = await axios.post('/auth/login', { email, password });
            this.setSession(data.data.token, data.data.user);
            return data;
        },
        async register(payload) {
            const { data } = await axios.post('/auth/register', payload);
            this.setSession(data.data.token, data.data.user);
            return data;
        },
        async fetchMe() {
            const { data } = await axios.get('/auth/me');
            this.user = data.data;
            localStorage.setItem('fitflow_user', JSON.stringify(this.user));
            return data.data;
        },
        async logout() {
            try { await axios.post('/auth/logout'); } catch (e) { /* ignore */ }
            this.clearSession();
        },
        setSession(token, user) {
            this.token = token;
            this.user = user;
            localStorage.setItem('fitflow_token', token);
            localStorage.setItem('fitflow_user', JSON.stringify(user));
        },
        clearSession() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('fitflow_token');
            localStorage.removeItem('fitflow_user');
        },
    },
});
