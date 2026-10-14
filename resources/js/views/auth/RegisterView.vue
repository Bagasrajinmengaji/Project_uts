<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const showPassword = ref(false);
const errorMessage = ref('');
const fieldErrors = ref<Record<string, string[]>>({});
const loading = ref(false);
const googleLoading = ref(false);

const handleGoogleRegister = () => {
    googleLoading.value = true;
    window.location.href = '/api/auth/google/redirect';
};

const handleRegister = async () => {
    if (
        !name.value ||
        !email.value ||
        !password.value ||
        !password_confirmation.value
    ) {
        errorMessage.value = 'Semua kolom wajib diisi.';
        return;
    }

    if (password.value !== password_confirmation.value) {
        errorMessage.value = 'Konfirmasi kata sandi tidak cocok.';
        return;
    }

    loading.value = true;
    errorMessage.value = '';
    fieldErrors.value = {};

    try {
        await authStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value,
        });
        router.push('/my-posts');
    } catch (err: any) {
        if (err.response?.data?.errors) {
            fieldErrors.value = err.response.data.errors;
            const firstKey = Object.keys(err.response.data.errors)[0];
            errorMessage.value = err.response.data.errors[firstKey][0];
        } else if (err.response?.data?.message) {
            errorMessage.value = err.response.data.message;
        } else {
            errorMessage.value =
                'Gagal melakukan pendaftaran. Silakan coba beberapa saat lagi.';
        }
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div
        class="auth-wrapper d-flex align-center pa-4 justify-center"
        style="min-height: calc(100vh - 120px)"
    >
        <v-card
            max-width="480"
            width="100%"
            class="pa-6 pa-sm-8 rounded-xl elevation-3 bg-surface"
        >
            <!-- Brand Header -->
            <div class="mb-6 text-center">
                <v-avatar color="primary" size="48" class="mb-3 elevation-2">
                    <v-icon
                        icon="mdi-account-plus"
                        color="white"
                        size="28"
                    ></v-icon>
                </v-avatar>
                <h2 class="text-h5 font-weight-bold mb-1">
                    Mulai Petualangan Menulis 🚀
                </h2>
                <p class="text-body-2 text-secondary">
                    Daftarkan akun kontributor berita Anda sekarang juga!
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

            <!-- Google Register Button -->
            <v-btn
                block
                size="large"
                variant="outlined"
                color="secondary"
                :loading="googleLoading"
                class="mb-4 text-capitalize font-weight-bold rounded-lg border-opacity-50"
                @click="handleGoogleRegister"
            >
                <v-icon icon="mdi-google" color="error" class="me-2"></v-icon>
                Daftar dengan Google
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
            <v-form @submit.prevent="handleRegister">
                <div class="mb-4">
                    <label
                        class="text-caption font-weight-bold text-secondary mb-1 d-block"
                        >NAMA LENGKAP</label
                    >
                    <v-text-field
                        v-model="name"
                        placeholder="John Doe"
                        prepend-inner-icon="mdi-account-outline"
                        :error-messages="fieldErrors.name"
                        required
                        hide-details="auto"
                    ></v-text-field>
                </div>

                <div class="mb-4">
                    <label
                        class="text-caption font-weight-bold text-secondary mb-1 d-block"
                        >ALAMAT EMAIL</label
                    >
                    <v-text-field
                        v-model="email"
                        placeholder="nama@email.com"
                        type="email"
                        prepend-inner-icon="mdi-email-outline"
                        :error-messages="fieldErrors.email"
                        required
                        hide-details="auto"
                    ></v-text-field>
                </div>

                <div class="mb-4">
                    <label
                        class="text-caption font-weight-bold text-secondary mb-1 d-block"
                        >KATA SANDI</label
                    >
                    <v-text-field
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Minimal 8 karakter"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="
                            showPassword
                                ? 'mdi-eye-off-outline'
                                : 'mdi-eye-outline'
                        "
                        @click:append-inner="showPassword = !showPassword"
                        :error-messages="fieldErrors.password"
                        required
                        hide-details="auto"
                    ></v-text-field>
                </div>

                <div class="mb-6">
                    <label
                        class="text-caption font-weight-bold text-secondary mb-1 d-block"
                        >KONFIRMASI KATA SANDI</label
                    >
                    <v-text-field
                        v-model="password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Ulangi kata sandi"
                        prepend-inner-icon="mdi-lock-check-outline"
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
                    Daftar Sebagai Penulis
                </v-btn>

                <div class="text-body-2 text-center">
                    <span class="text-secondary">Sudah memiliki akun?</span>
                    <router-link
                        to="/login"
                        class="ms-1 font-weight-bold text-primary text-decoration-none"
                    >
                        Masuk di Sini
                    </router-link>
                </div>
            </v-form>
        </v-card>
    </div>
</template>
