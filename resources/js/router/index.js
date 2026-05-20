import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

//meta digunakan sebagai penanda untuk kebutuhan auth dan role pada route tertentu
const routes = [
    { path: '/', name: 'landing', component: () => import('../views/Landing.vue') },
    { path: '/login', name: 'login', component: () => import('../views/Login.vue'), meta: { guest: true } },
    { path: '/register', name: 'register', component: () => import('../views/Register.vue'), meta: { guest: true } },

    {
        path: '/dashboard',
        component: () => import('../layouts/AppLayout.vue'),
        meta: { auth: true },
        children: [
            { path: '', name: 'dashboard', component: () => import('../views/Dashboard.vue') },

            { path: '/classes', name: 'classes.index', component: () => import('../views/classes/Index.vue') },
            { path: '/classes/create', name: 'classes.create', component: () => import('../views/classes/Form.vue'), meta: { roles: ['admin'] } },
            { path: '/classes/:id', name: 'classes.show', component: () => import('../views/classes/Show.vue') },
            { path: '/classes/:id/edit', name: 'classes.edit', component: () => import('../views/classes/Form.vue'), meta: { roles: ['admin'] } },

            { path: '/trainers', name: 'trainers.index', component: () => import('../views/trainers/Index.vue') },
            { path: '/trainers/create', name: 'trainers.create', component: () => import('../views/trainers/Form.vue'), meta: { roles: ['admin'] } },
            { path: '/trainers/:id', name: 'trainers.show', component: () => import('../views/trainers/Show.vue') },
            { path: '/trainers/:id/edit', name: 'trainers.edit', component: () => import('../views/trainers/Form.vue'), meta: { roles: ['admin', 'trainer'] } },

            { path: '/sessions', name: 'sessions.index', component: () => import('../views/sessions/Index.vue') },
            { path: '/sessions/create', name: 'sessions.create', component: () => import('../views/sessions/Form.vue'), meta: { roles: ['admin'] } },
            { path: '/sessions/:id', name: 'sessions.show', component: () => import('../views/sessions/Show.vue') },
            { path: '/sessions/:id/edit', name: 'sessions.edit', component: () => import('../views/sessions/Form.vue'), meta: { roles: ['admin'] } },

            { path: '/bookings', name: 'bookings.index', component: () => import('../views/bookings/Index.vue') },
            { path: '/bookings/create', name: 'bookings.create', component: () => import('../views/bookings/Form.vue'), meta: { roles: ['member'] } },
            { path: '/bookings/:id', name: 'bookings.show', component: () => import('../views/bookings/Show.vue') },
        ],
    },

    { path: '/:pathMatch(.*)*', component: () => import('../views/NotFound.vue') },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});


//cek kebutuhan auth dan role sebelum masuk ke route
router.beforeEach((to) => {
    const auth = useAuthStore();

    if (to.meta.auth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }
    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }
    if (to.meta.roles && Array.isArray(to.meta.roles) && !to.meta.roles.includes(auth.role)) {
        return { name: 'dashboard' };
    }
    return true;
});

export default router;
