<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useTheme } from 'vuetify';

const router = useRouter();
const authStore = useAuthStore();
const theme = useTheme();

const toggleTheme = () => {
    theme.global.name.value = theme.global.current.value.dark
        ? 'sneatLight'
        : 'sneatDark';
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>

<template>
    <v-app>
        <!-- Public Top Navigation Bar -->
        <v-app-bar flat elevation="1" class="px-md-8 px-4 sneat-public-navbar">
            <div
                class="d-flex align-center cursor-pointer"
                @click="router.push('/')"
            >
                <v-avatar color="primary" size="36" class="me-3 elevation-1">
                    <v-icon
                        icon="mdi-newspaper"
                        color="white"
                        size="20"
                    ></v-icon>
                </v-avatar>
                <div>
                    <h3
                        class="font-weight-bold text-h6 text-primary mb-0"
                        style="letter-spacing: -0.5px"
                    >
                        Sneat Berita
                    </h3>
                    <span class="text-caption text-secondary d-none d-sm-inline"
                        >User-Generated News Portal</span
                    >
                </div>
            </div>

            <v-spacer></v-spacer>

            <!-- Navigation Links -->
            <v-btn
                to="/"
                variant="text"
                prepend-icon="mdi-home-outline"
                class="me-2 d-none d-sm-inline-flex"
            >
                Beranda
            </v-btn>

            <!-- Dark / Light Theme Toggle -->
            <v-btn
                icon
                variant="text"
                size="small"
                @click="toggleTheme"
                class="me-2 text-secondary"
            >
                <v-icon
                    :icon="
                        theme.global.current.value.dark
                            ? 'mdi-weather-sunny'
                            : 'mdi-weather-night'
                    "
                ></v-icon>
            </v-btn>

            <!-- Guest buttons vs Logged-in buttons -->
            <template v-if="authStore.isAuthenticated">
                <v-btn
                    to="/my-posts"
                    color="primary"
                    variant="tonal"
                    prepend-icon="mdi-view-dashboard-outline"
                    class="me-2 text-capitalize"
                >
                    Dasbor Saya
                </v-btn>
                <v-btn
                    to="/posts/create"
                    color="primary"
                    variant="flat"
                    prepend-icon="mdi-pencil-plus"
                    class="me-3 text-capitalize d-none d-sm-inline-flex"
                >
                    Tulis Berita
                </v-btn>

                <!-- User Menu -->
                <v-menu min-width="180px" rounded="lg">
                    <template #activator="{ props }">
                        <v-btn icon v-bind="props" size="small">
                            <v-avatar color="primary" size="32">
                                <span
                                    class="text-white text-caption font-weight-bold"
                                    >{{
                                        authStore.user?.name?.charAt(0) || 'U'
                                    }}</span
                                >
                            </v-avatar>
                        </v-btn>
                    </template>
                    <v-card class="pa-2">
                        <v-card-text class="pa-2">
                            <div class="font-weight-bold text-body-2">
                                {{ authStore.user?.name }}
                            </div>
                            <div class="text-caption text-secondary">
                                {{ authStore.user?.email }}
                            </div>
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-list density="compact" nav>
                            <v-list-item
                                to="/my-posts"
                                prepend-icon="mdi-newspaper"
                                title="Berita Saya"
                            ></v-list-item>
                            <v-list-item
                                @click="handleLogout"
                                prepend-icon="mdi-logout"
                                title="Keluar"
                                color="error"
                            ></v-list-item>
                        </v-list>
                    </v-card>
                </v-menu>
            </template>
            <template v-else>
                <v-btn
                    to="/login"
                    variant="text"
                    prepend-icon="mdi-login"
                    class="me-2 text-capitalize"
                >
                    Masuk
                </v-btn>
                <v-btn
                    to="/register"
                    color="primary"
                    variant="flat"
                    prepend-icon="mdi-account-plus"
                    class="text-capitalize"
                >
                    Daftar
                </v-btn>
            </template>
        </v-app-bar>

        <!-- Main Content Slot -->
        <v-main class="sneat-blank-container">
            <slot />
        </v-main>

        <!-- Public Footer -->
        <v-footer
            class="d-flex flex-column py-6 mt-12 bg-surface border-t text-center"
        >
            <div class="text-caption text-secondary">
                © {{ new Date().getFullYear() }}
                <strong>Sneat Berita UGC</strong>. UTS Pemrograman Web -
                Arsitektur Terpisah (Decoupled Laravel 13 + Vuetify 3).
            </div>
        </v-footer>
    </v-app>
</template>

<style scoped>
.sneat-public-navbar {
    background-color: var(--v-theme-surface) !important;
}
.sneat-blank-container {
    background-color: var(--v-theme-background);
    min-height: calc(100vh - 120px);
}
</style>
