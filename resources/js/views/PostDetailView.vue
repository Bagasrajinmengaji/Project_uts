<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/plugins/axios';

const route = useRoute();
const router = useRouter();

interface Post {
    id: number;
    title: string;
    slug: string;
    content: string;
    image_url: string | null;
    status: string;
    created_at: string;
    category?: { id: number; name: string; slug: string };
    user?: { id: number; name: string; email: string };
}

const post = ref<Post | null>(null);
const loading = ref<boolean>(true);
const error = ref<string | null>(null);

const fetchPostDetail = async () => {
    loading.value = true;
    error.value = null;
    try {
        const slug = route.params.slug as string;
        const response = await apiClient.get(`/posts/${slug}`);
        post.value = response.data.post;
    } catch (err: any) {
        error.value =
            err.response?.data?.message ||
            'Berita tidak ditemukan atau telah dihapus.';
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const copyLink = () => {
    if (typeof window !== 'undefined' && navigator?.clipboard) {
        navigator.clipboard.writeText(window.location.href);
    }
};

onMounted(() => {
    fetchPostDetail();
});
</script>

<template>
    <div class="post-detail-page py-8">
        <v-container>
            <!-- Back Button -->
            <v-btn
                variant="text"
                color="primary"
                prepend-icon="mdi-arrow-left"
                class="mb-6 font-weight-bold"
                @click="router.push('/')"
            >
                Kembali ke Beranda
            </v-btn>

            <!-- Loading State -->
            <div v-if="loading" class="py-16 text-center">
                <v-progress-circular
                    indeterminate
                    color="primary"
                    size="64"
                ></v-progress-circular>
                <div class="mt-4 text-body-1 text-secondary">
                    Memuat artikel berita...
                </div>
            </div>

            <!-- Error State -->
            <v-card
                v-else-if="error"
                class="pa-12 rounded-xl bg-surface elevation-2 text-center"
            >
                <v-icon
                    icon="mdi-alert-circle-outline"
                    size="64"
                    color="error"
                    class="mb-3"
                ></v-icon>
                <h3 class="text-h5 font-weight-bold mb-2">Terjadi Kendala</h3>
                <p class="text-body-1 text-secondary mb-6">{{ error }}</p>
                <v-btn color="primary" @click="router.push('/')"
                    >Kembali ke Beranda</v-btn
                >
            </v-card>

            <!-- Post Content Card -->
            <v-card
                v-else-if="post"
                class="rounded-xl elevation-3 bg-surface overflow-hidden"
            >
                <!-- Cover Image -->
                <v-img
                    :src="
                        post.image_url ||
                        'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=1200&q=80'
                    "
                    max-height="450"
                    cover
                    class="bg-grey-lighten-2"
                ></v-img>

                <div class="pa-6 pa-md-10">
                    <!-- Meta Row (Category & Date) -->
                    <div class="d-flex align-center gap-3 mb-4 flex-wrap">
                        <v-chip color="primary" class="font-weight-bold me-3">
                            <v-icon
                                icon="mdi-tag-outline"
                                start
                                size="16"
                            ></v-icon>
                            {{ post.category?.name || 'Kategori' }}
                        </v-chip>

                        <v-chip
                            v-if="post.status === 'draft'"
                            color="warning"
                            variant="tonal"
                            class="me-3"
                        >
                            Draft (Hanya Anda yang dapat melihat)
                        </v-chip>

                        <div
                            class="text-caption text-secondary d-flex align-center"
                        >
                            <v-icon
                                icon="mdi-calendar-clock"
                                size="16"
                                class="me-1"
                            ></v-icon>
                            {{ formatDate(post.created_at) }}
                        </div>
                    </div>

                    <!-- Article Title -->
                    <h1
                        class="text-h4 text-md-h3 font-weight-bold mb-6 text-high-emphasis article-title"
                    >
                        {{ post.title }}
                    </h1>

                    <!-- Author Info Card -->
                    <v-card
                        variant="tonal"
                        color="primary"
                        class="pa-4 rounded-lg mb-8 d-flex align-center"
                    >
                        <v-avatar color="primary" size="44" class="me-3">
                            <span class="text-white font-weight-bold text-h6">{{
                                post.user?.name?.charAt(0) || 'U'
                            }}</span>
                        </v-avatar>
                        <div>
                            <div class="text-caption text-secondary">
                                Ditulis oleh:
                            </div>
                            <div
                                class="text-subtitle-1 font-weight-bold text-primary"
                            >
                                {{ post.user?.name }}
                            </div>
                        </div>
                    </v-card>

                    <v-divider class="my-6"></v-divider>

                    <!-- Article Body Content -->
                    <div
                        class="article-content text-body-1"
                        style="
                            line-height: 1.8;
                            font-size: 1.15rem;
                            white-space: pre-wrap;
                        "
                    >
                        {{ post.content }}
                    </div>

                    <v-divider class="my-8"></v-divider>

                    <!-- Bottom Actions -->
                    <div
                        class="d-flex align-center justify-space-between gap-4 flex-wrap"
                    >
                        <v-btn
                            variant="outlined"
                            color="secondary"
                            prepend-icon="mdi-share-variant-outline"
                            @click="copyLink"
                        >
                            Salin Tautan
                        </v-btn>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-arrow-left"
                            @click="router.push('/')"
                        >
                            Jelajah Berita Lainnya
                        </v-btn>
                    </div>
                </div>
            </v-card>
        </v-container>
    </div>
</template>

<style scoped>
.article-title {
    line-height: 1.3;
    letter-spacing: -0.5px;
}
.article-content {
    color: var(--v-theme-on-surface);
}
</style>
