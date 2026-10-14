import { defineStore } from 'pinia';
import apiClient from '@/plugins/axios';

export interface User {
    id: number;
    name: string;
    email: string;
    created_at?: string;
}

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(
            localStorage.getItem('user_data') || 'null',
        ) as User | null,
        token: localStorage.getItem('access_token') || '',
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async register(payload: {
            name: string;
            email: string;
            password: string;
            password_confirmation: string;
        }) {
            this.loading = true;
            try {
                const response = await apiClient.post('/register', payload);
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('access_token', this.token);
                localStorage.setItem('user_data', JSON.stringify(this.user));
                return response.data;
            } finally {
                this.loading = false;
            }
        },

        async login(credentials: { email: string; password: string }) {
            this.loading = true;
            try {
                const response = await apiClient.post('/login', credentials);
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('access_token', this.token);
                localStorage.setItem('user_data', JSON.stringify(this.user));
                return response.data;
            } finally {
                this.loading = false;
            }
        },

        async fetchUser() {
            if (!this.token) return null;
            try {
                const response = await apiClient.get('/me');
                this.user = response.data.user;
                localStorage.setItem('user_data', JSON.stringify(this.user));
                return this.user;
            } catch {
                this.logoutLocal();
                return null;
            }
        },

        async logout() {
            this.loading = true;
            try {
                if (this.token) {
                    await apiClient.post('/logout');
                }
            } catch {
                // Ignore API logout error and clear local state anyway
            } finally {
                this.logoutLocal();
                this.loading = false;
            }
        },

        logoutLocal() {
            this.token = '';
            this.user = null;
            localStorage.removeItem('access_token');
            localStorage.removeItem('user_data');
        },
    },
});
