<script setup lang="ts">
import { ref, onMounted } from 'vue';
import apiClient from '@/plugins/axios';

interface Category {
    id: number;
    name: string;
    slug: string;
    posts_count?: number;
}

interface Post {
    id: number;
    title: string;
    slug: string;
    content: string;
    image_url: string | null;
    created_at: string;
    category?: { id: number; name: string; slug: string };
    user?: { id: number; name: string };
}

const categories = ref<Category[]>([]);
const posts = ref<Post[]>([]);
const selectedCategorySlug = ref<string>('all');
const searchQuery = ref<string>('');
const loading = ref<boolean>(false);
const currentPage = ref<number>(1);
const totalPages = ref<number>(1);

// Fetch categories
const fetchCategories = async () => {
    try {
        const response = await apiClient.get('/categories');
        categories.value = response.data.categories || [];
    } catch (err) {
        console.error('Failed to load categories', err);
    }
};

// Fetch posts with filter & search
const fetchPosts = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page,
            per_page: 9,
        };

        if (
            selectedCategorySlug.value &&
            selectedCategorySlug.value !== 'all'
        ) {
            params.category = selectedCategorySlug.value;
        }

        if (searchQuery.value.trim()) {
            params.search = searchQuery.value.trim();
        }

        const response = await apiClient.get('/posts', { params });
        posts.value = response.data.data || [];
        currentPage.value = response.data.current_page || 1;
        totalPages.value = response.data.last_page || 1;
    } catch (err) {
        console.error('Failed to load posts', err);
    } finally {
        loading.value = false;
    }
};

let searchTimeout: any = null;
const handleSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        fetchPosts(1);
    }, 400);
};

const handleCategoryChange = (slug: string) => {
    selectedCategorySlug.value = slug;
    currentPage.value = 1;
    fetchPosts(1);
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

onMounted(async () => {
    await fetchCategories();
    await fetchPosts(1);
});
</script>

<template>
    <div class="home-page py-6">
        <v-container>
            <!-- Hero Banner -->
            <v-card
                class="mb-8 pa-8 rounded-xl elevation-2 hero-banner text-center"
                color="primary"
                theme="dark"
            >
                <v-icon
                    icon="mdi-newspaper-variant"
                    size="48"
                    class="mb-2"
                ></v-icon>
                <h1 class="text-h4 font-weight-bold mb-2">
                    Portal Berita UGC Terpercaya
                </h1>
                <p
                    class="text-subtitle-1 text-white-darken-1 mb-6"
                    style="max-width: 600px; margin: 0 auto"
                >
                    Wadah berbagi informasi, gagasan, dan berita aktual dari
                    kontributor dan penulis pilihan.
                </p>

                <!-- Search Input with Vuetify VTextField -->
                <v-row justify="center">
                    <v-col cols="12" sm="10" md="8">
                        <v-text-field
                            v-model="searchQuery"
                            @update:model-value="handleSearchInput"
                            placeholder="Cari judul berita atau topik menarik..."
                            prepend-inner-icon="mdi-magnify"
                            clearable
                            variant="solo"
                            rounded="lg"
                            density="comfortable"
                            bg-color="surface"
                            class="elevation-3"
                            hide-details
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card>

            <!-- Category Filter Chips with VChipGroup -->
            <div class="mb-8">
                <div class="d-flex align-center justify-space-between mb-3">
                    <h2 class="text-h6 font-weight-bold d-flex align-center">
                        <v-icon
                            icon="mdi-shape-outline"
                            color="primary"
                            class="me-2"
                        ></v-icon>
                        Kategori Berita
                    </h2>
                </div>

                <v-chip-group
                    v-model="selectedCategorySlug"
                    selected-class="text-primary bg-primary-lighten-4 font-weight-bold"
                    mandatory
                >
                    <v-chip
                        value="all"
                        variant="outlined"
                        filter
                        @click="handleCategoryChange('all')"
                    >
                        Semua Kategori
                    </v-chip>

                    <v-chip
                        v-for="cat in categories"
                        :key="cat.slug"
                        :value="cat.slug"
                        variant="outlined"
                        filter
                        @click="handleCategoryChange(cat.slug)"
                    >
                        {{ cat.name }}
                        <span
                            v-if="cat.posts_count !== undefined"
                            class="ms-1 text-caption opacity-70"
                        >
                            ({{ cat.posts_count }})
                        </span>
                    </v-chip>
                </v-chip-group>
            </div>

            <!-- News Grid Section -->
            <div class="posts-section">
                <div class="d-flex align-center justify-space-between mb-4">
                    <h2 class="text-h6 font-weight-bold d-flex align-center">
                        <v-icon
                            icon="mdi-fire"
                            color="primary"
                            class="me-2"
                        ></v-icon>
                        Berita Terkini
                    </h2>
                    <span class="text-caption text-secondary" v-if="!loading">
                        Ditemukan {{ posts.length }} berita
                    </span>
                </div>

                <!-- Loading State with Skeleton Loader / Circular Progress -->
                <div v-if="loading" class="py-12 text-center">
                    <v-progress-circular
                        indeterminate
                        color="primary"
                        size="50"
                    ></v-progress-circular>
                    <div class="mt-4 text-body-2 text-secondary">
                        Memuat berita terbaru...
                    </div>
                </div>

                <!-- Empty State -->
                <v-card
                    v-else-if="posts.length === 0"
                    class="pa-12 rounded-xl bg-surface text-center"
                >
                    <v-icon
                        icon="mdi-file-search-outline"
                        size="64"
                        color="secondary"
                        class="mb-3"
                    ></v-icon>
                    <h3 class="text-h6 font-weight-bold mb-1">
                        Belum Ada Berita
                    </h3>
                    <p class="text-body-2 text-secondary mb-4">
                        Tidak ada berita yang cocok dengan kriteria pencarian
                        atau kategori ini.
                    </p>
                    <v-btn
                        color="primary"
                        variant="tonal"
                        @click="
                            selectedCategorySlug = 'all';
                            searchQuery = '';
                            fetchPosts(1);
                        "
                    >
                        Reset Pencarian
                    </v-btn>
                </v-card>

                <!-- Posts List with VRow & VCol & VCard -->
                <v-row v-else>
                    <v-col
                        v-for="post in posts"
                        :key="post.id"
                        cols="12"
                        sm="6"
                        md="4"
                    >
                        <v-card
                            class="h-100 d-flex flex-column rounded-xl sneat-post-card overflow-hidden"
                            elevation="2"
                        >
                            <!-- Image Cover -->
                            <v-img
                                :src="
                                    post.image_url ||
                                    'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=800&q=80'
                                "
                                height="200"
                                cover
                                class="align-end"
                            >
                                <div class="pa-2 bg-gradient">
                                    <v-chip
                                        size="small"
                                        color="primary"
                                        label
                                        class="font-weight-bold"
                                    >
                                        {{ post.category?.name || 'Umum' }}
                                    </v-chip>
                                </div>
                            </v-img>

                            <v-card-item class="pb-1 pt-4">
                                <v-card-title
                                    class="text-subtitle-1 font-weight-bold line-clamp-2 text-wrap"
                                    style="line-height: 1.4"
                                >
                                    {{ post.title }}
                                </v-card-title>
                            </v-card-item>

                            <v-card-text
                                class="text-body-2 text-secondary pb-2 line-clamp-3 flex-grow-1"
                            >
                                {{ post.content }}
                            </v-card-text>

                            <v-divider></v-divider>

                            <v-card-actions
                                class="px-4 py-3 justify-space-between align-center"
                            >
                                <div
                                    class="d-flex align-center text-caption text-secondary"
                                >
                                    <v-icon
                                        icon="mdi-account-outline"
                                        size="16"
                                        class="me-1"
                                    ></v-icon>
                                    <span
                                        class="text-truncate"
                                        style="max-width: 110px"
                                        >{{ post.user?.name }}</span
                                    >
                                    <span class="mx-1">•</span>
                                    <span>{{
                                        formatDate(post.created_at)
                                    }}</span>
                                </div>

                                <v-btn
                                    :to="`/posts/${post.slug}`"
                                    color="primary"
                                    variant="text"
                                    density="comfortable"
                                    class="font-weight-bold text-capitalize"
                                    append-icon="mdi-arrow-right"
                                >
                                    Baca
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="d-flex mt-8 justify-center">
                    <v-pagination
                        v-model="currentPage"
                        :length="totalPages"
                        :total-visible="5"
                        color="primary"
                        rounded="circle"
                        @update:model-value="fetchPosts"
                    ></v-pagination>
                </div>
            </div>
        </v-container>
    </div>
</template>

<style scoped>
.hero-banner {
    background: linear-gradient(135deg, #696cff 0%, #4044f4 100%) !important;
}
.sneat-post-card {
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}
.sneat-post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(105, 108, 255, 0.18) !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bg-gradient {
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, 0.6) 0%,
        transparent 100%
    );
}
</style>
