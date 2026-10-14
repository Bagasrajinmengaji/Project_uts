<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const errorMessage = ref('');
const loading = ref(false);
const googleLoading = ref(false);

const handleLogin = async () => {
    if (!email.value || !password.value) {
        errorMessage.value = 'Silakan isi email dan kata sandi Anda.';
        return;
    }

    loading.value = true;
    errorMessage.value = '';

    try {
        await authStore.login({
            email: email.value,
            password: password.value,
        });
        router.push('/my-posts');
    } catch (err: any) {
        if (err.response?.data?.errors?.email) {
            errorMessage.value = err.response.data.errors.email[0];
        } else if (err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value =
                'Terjadi kesalahan saat masuk. Periksa koneksi atau kredensial Anda.';
        }
    } finally {
        loading.value = false;
    }
};

const handleGoogleLogin = () => {
    googleLoading.value = true;
    // Redirect to Google OAuth endpoint
    window.location.href = '/api/auth/google/redirect';
};

onMounted(async () => {
    const token = route.query.token as string | undefined;
    const errorParam = route.query.error as string | undefined;

    if (token) {
        loading.value = true;
        try {
            localStorage.setItem('access_token', token);
            authStore.token = token;
            await authStore.fetchUser();
            router.push('/my-posts');
        } catch {
            errorMessage.value = 'Gagal memverifikasi akun Google.';
        } finally {
            loading.value = false;
        }
    } else if (errorParam) {
        errorMessage.value = decodeURIComponent(errorParam);
    }
});
</script>

<template>
    <div
        class="auth-wrapper d-flex align-center pa-4 justify-center"
        style="min-height: calc(100vh - 120px)"
    >
        <v-card
            max-width="450"
            width="100%"
            class="pa-6 pa-sm-8 rounded-xl elevation-3 bg-surface"
        >
            <!-- Brand Header -->
            <div class="mb-6 text-center">
                <v-avatar color="primary" size="48" class="mb-3 elevation-2">
                    <v-icon
                        icon="mdi-newspaper"
                        color="white"
                        size="28"
                    ></v-icon>
                </v-avatar>
                <h2 class="text-h5 font-weight-bold mb-1">
                    Selamat Datang di Sneat! 👋
                </h2>
                <p class="text-body-2 text-secondary">
                    Silakan masuk ke akun penulis Anda untuk mengelola berita
                    UGC
                </p>
            </div>

            <!-- Error Alert -->
            <v-alert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                class="mb-4 text-caption rounded-lg"
                closable
                @click:close="errorMessage = ''"
            >
                {{ errorMessage }}
            </v-alert>

            <!-- Google Login Button -->
            <v-btn
                block
                size="large"
                variant="outlined"
                color="secondary"
                :loading="googleLoading"
                class="mb-4 text-capitalize font-weight-bold rounded-lg border-opacity-50"
                @click="handleGoogleLogin"
            >
                <v-icon icon="mdi-google" color="error" class="me-2"></v-icon>
                Masuk dengan Google
            </v-btn>

            <!-- Divider -->
            <div class="d-flex align-center my-4">
                <v-divider></v-divider>
                <span class="text-caption text-secondary px-3 text-uppercase"
                    >atau dengan email</span
                >
                <v-divider></v-divider>
            </div>

            <!-- Form -->
            <v-form @submit.prevent="handleLogin">
                <div class="mb-4">
                    <label
                        class="text-caption font-weight-bold text-secondary mb-1 d-block"
                        >EMAIL</label
                    >
                    <v-text-field
                        v-model="email"
                        placeholder="nama@email.com"
                        type="email"
                        prepend-inner-icon="mdi-email-outline"
                        required
                        hide-details="auto"
                    ></v-text-field>
                </div>

                <div class="mb-6">
                    <div class="d-flex justify-space-between align-center mb-1">
                        <label
                            class="text-caption font-weight-bold text-secondary"
                            >KATA SANDI</label
                        >
                    </div>
                    <v-text-field
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="
                            showPassword
                                ? 'mdi-eye-off-outline'
                                : 'mdi-eye-outline'
                        "
                        @click:append-inner="showPassword = !showPassword"
                        required
                        hide-details="auto"
                    ></v-text-field>
                </div>

                <v-btn
                    type="submit"
                    color="primary"
                    block
                    size="large"
                    :loading="loading"
                    class="font-weight-bold mb-4 text-capitalize elevation-1 rounded-lg"
                >
                    Masuk Akun
                </v-btn>

                <div class="text-body-2 text-center">
                    <span class="text-secondary">Belum memiliki akun?</span>
                    <router-link
                        to="/register"
                        class="ms-1 font-weight-bold text-primary text-decoration-none"
                    >
                        Daftar Penulis Baru
                    </router-link>
                </div>

                <div class="mt-4 text-center">
                    <v-btn
                        variant="text"
                        size="small"
                        to="/"
                        color="secondary"
                        prepend-icon="mdi-arrow-left"
                    >
                        Kembali ke Halaman Berita
                    </v-btn>
                </div>
            </v-form>
        </v-card>
    </div>
</template>
