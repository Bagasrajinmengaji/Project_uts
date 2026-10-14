<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { navItems } from '@/navigation/vertical';
import { useTheme } from 'vuetify';

const router = useRouter();
const authStore = useAuthStore();
const theme = useTheme();

const drawer = ref(true);

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
        <!-- Sneat Vertical Navigation Drawer -->
        <v-navigation-drawer
            v-model="drawer"
            elevation="1"
            class="sneat-drawer"
            width="260"
        >
            <!-- Brand / Logo -->
            <div class="d-flex align-center px-6 py-5">
                <v-avatar color="primary" size="36" class="me-3 elevation-2">
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
                    <span class="text-caption text-secondary"
                        >Portal UGC Penulis</span
                    >
                </div>
            </div>

            <v-divider class="mb-3"></v-divider>

            <!-- Navigation Links -->
            <v-list density="compact" nav class="px-3">
                <v-list-subheader
                    class="text-uppercase text-caption font-weight-bold text-medium-emphasis"
                >
                    Menu Utama
                </v-list-subheader>

                <v-list-item
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    :prepend-icon="item.icon"
                    :title="item.title"
                    active-color="primary"
                    rounded="lg"
                    class="mb-1 sneat-nav-item"
                >
                </v-list-item>
            </v-list>

            <template #append>
                <div class="pa-4">
                    <v-card
                        color="primary"
                        variant="tonal"
                        class="pa-3 rounded-lg text-center"
                    >
                        <v-icon
                            icon="mdi-feather"
                            color="primary"
                            size="28"
                            class="mb-1"
                        ></v-icon>
                        <div class="text-caption font-weight-bold text-primary">
                            Status Penulis Aktif
                        </div>
                        <div class="text-caption text-truncate">
                            {{ authStore.user?.email }}
                        </div>
                    </v-card>
                </div>
            </template>
        </v-navigation-drawer>

        <!-- Top App Bar -->
        <v-app-bar flat elevation="0" class="sneat-topbar px-4 border-b">
            <v-app-bar-nav-icon
                @click="drawer = !drawer"
                color="secondary"
            ></v-app-bar-nav-icon>

            <v-toolbar-title
                class="text-subtitle-1 font-weight-bold text-secondary"
            >
                Dasbor Kelola Berita
            </v-toolbar-title>

            <v-spacer></v-spacer>

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

            <!-- User Menu -->
            <v-menu min-width="200px" rounded="lg">
                <template #activator="{ props }">
                    <v-btn icon v-bind="props" class="ms-1">
                        <v-avatar color="primary" size="36">
                            <span class="text-white font-weight-bold">{{
                                authStore.user?.name?.charAt(0) || 'U'
                            }}</span>
                        </v-avatar>
                    </v-btn>
                </template>
                <v-card class="pa-2">
                    <v-card-text class="pa-3">
                        <div class="font-weight-bold">
                            {{ authStore.user?.name }}
                        </div>
                        <div class="text-caption text-secondary">
                            {{ authStore.user?.email }}
                        </div>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-list density="compact" nav>
                        <v-list-item
                            to="/"
                            prepend-icon="mdi-web"
                            title="Lihat Portal Berita"
                            rounded="md"
                        ></v-list-item>
                        <v-list-item
                            @click="handleLogout"
                            prepend-icon="mdi-logout"
                            title="Keluar (Logout)"
                            color="error"
                            rounded="md"
                        ></v-list-item>
                    </v-list>
                </v-card>
            </v-menu>
        </v-app-bar>

        <!-- Main Content Area -->
        <v-main class="sneat-main-container">
            <v-container fluid class="pa-6">
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.sneat-drawer {
    background-color: var(--v-theme-surface) !important;
}
.sneat-topbar {
    background-color: var(--v-theme-surface) !important;
}
.sneat-nav-item {
    font-weight: 500;
    transition: all 0.2s ease;
}
.sneat-main-container {
    background-color: var(--v-theme-background);
    min-height: 100vh;
}
</style>
