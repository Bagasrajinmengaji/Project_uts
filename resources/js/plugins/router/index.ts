import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';

import DefaultLayout from '@/layouts/DefaultLayoutWithVerticalNav.vue';
import BlankLayout from '@/layouts/blank.vue';

import HomeView from '@/views/HomeView.vue';
import PostDetailView from '@/views/PostDetailView.vue';
import LoginView from '@/views/auth/LoginView.vue';
import RegisterView from '@/views/auth/RegisterView.vue';
import MyPostsView from '@/views/dashboard/MyPostsView.vue';
import PostFormView from '@/views/dashboard/PostFormView.vue';

const routes: RouteRecordRaw[] = [
    // Public routes (using BlankLayout)
    {
        path: '/',
        name: 'home',
        component: HomeView,
        meta: { layout: BlankLayout, title: 'Jelajah Berita - Sneat UGC' },
    },
    {
        path: '/posts/:slug',
        name: 'post.detail',
        component: PostDetailView,
        meta: { layout: BlankLayout, title: 'Detail Berita - Sneat UGC' },
    },

    // Auth guest-only routes
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: {
            layout: BlankLayout,
            guestOnly: true,
            title: 'Masuk Akun - Sneat UGC',
        },
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: {
            layout: BlankLayout,
            guestOnly: true,
            title: 'Daftar Akun - Sneat UGC',
        },
    },

    // Protected Author routes (using DefaultLayoutWithVerticalNav)
    {
        path: '/my-posts',
        name: 'my.posts',
        component: MyPostsView,
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Berita Saya - Dasbor Sneat',
        },
    },
    {
        path: '/posts/create',
        name: 'posts.create',
        component: PostFormView,
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Tulis Berita Baru - Dasbor Sneat',
        },
    },
    {
        path: '/posts/edit/:id',
        name: 'posts.edit',
        component: PostFormView,
        meta: {
            layout: DefaultLayout,
            requiresAuth: true,
            title: 'Ubah Berita - Dasbor Sneat',
        },
    },

    // Fallback to home
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

// Router Guard per KONTEKS.MD section 5.D
router.beforeEach(async (to, from, next) => {
    const token = localStorage.getItem('access_token');

    // Set page title
    if (typeof to.meta.title === 'string') {
        document.title = to.meta.title;
    }

    // Protected routes check
    if (to.meta.requiresAuth && !token) {
        return next({ name: 'login', query: { redirect: to.fullPath } });
    }

    // Guest only routes check (redirect to my-posts if already logged in)
    if (to.meta.guestOnly && token) {
        return next({ name: 'my.posts' });
    }

    next();
});

export default router;
