<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/plugins/axios';

const router = useRouter();

interface Post {
    id: number;
    title: string;
    slug: string;
    content: string;
    image_url: string | null;
    status: 'published' | 'draft';
    created_at: string;
    category?: { id: number; name: string; slug: string };
}

const posts = ref<Post[]>([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

// Delete dialog state
const deleteDialog = ref(false);
const postToDelete = ref<Post | null>(null);
const deleteLoading = ref(false);

// Toast notification state
const snackbar = ref({
    show: false,
    text: '',
    color: 'success',
});

const triggerToast = (text: string, color = 'success') => {
    snackbar.value = {
        show: true,
        text,
        color,
    };
};

const fetchMyPosts = async (page = 1) => {
    loading.value = true;
    try {
        const response = await apiClient.get('/my-posts', {
            params: { page, per_page: 10 },
        });
        posts.value = response.data.data || [];
        currentPage.value = response.data.current_page || 1;
        totalPages.value = response.data.last_page || 1;
    } catch (err: any) {
        triggerToast('Gagal memuat daftar berita Anda.', 'error');
    } finally {
        loading.value = false;
    }
};

const confirmDelete = (post: Post) => {
    postToDelete.value = post;
    deleteDialog.value = true;
};

const handleDelete = async () => {
    if (!postToDelete.value) return;
    deleteLoading.value = true;
    try {
        await apiClient.delete(`/posts/${postToDelete.value.id}`);
        triggerToast('Berita berhasil dihapus.', 'success');
        deleteDialog.value = false;
        postToDelete.value = null;
        fetchMyPosts(currentPage.value);
    } catch (err: any) {
        triggerToast(
            err.response?.data?.message || 'Gagal menghapus berita.',
            'error',
        );
    } finally {
        deleteLoading.value = false;
    }
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

onMounted(() => {
    fetchMyPosts(1);
});
</script>

<template>
    <div class="my-posts-view">
        <!-- Page Header -->
        <div
            class="d-flex align-center justify-space-between gap-4 mb-6 flex-wrap"
        >
            <div>
                <h2 class="text-h5 font-weight-bold mb-1">Berita Saya 📰</h2>
                <p class="text-body-2 text-secondary mb-0">
                    Kelola seluruh tulisan, draf, dan artikel berita yang telah
                    Anda publikasikan.
                </p>
            </div>
            <v-btn
                to="/posts/create"
                color="primary"
                prepend-icon="mdi-plus"
                class="font-weight-bold text-capitalize elevation-2"
                rounded="lg"
            >
                Tulis Berita Baru
            </v-btn>
        </div>

        <!-- Posts Table Card with Sneat style -->
        <v-card class="rounded-xl elevation-2 bg-surface overflow-hidden">
            <!-- Loading Indicator -->
            <div v-if="loading" class="pa-12 text-center">
                <v-progress-circular
                    indeterminate
                    color="primary"
                    size="48"
                ></v-progress-circular>
                <div class="mt-3 text-body-2 text-secondary">
                    Mengambil data berita...
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="posts.length === 0" class="pa-12 text-center">
                <v-icon
                    icon="mdi-file-document-edit-outline"
                    size="64"
                    color="secondary"
                    class="mb-3"
                ></v-icon>
                <h3 class="text-h6 font-weight-bold mb-1">
                    Anda Belum Menulis Berita
                </h3>
                <p class="text-body-2 text-secondary mb-4">
                    Mulai berbagi informasi menarik dengan membuat artikel
                    pertama Anda.
                </p>
                <v-btn
                    to="/posts/create"
                    color="primary"
                    prepend-icon="mdi-pencil-plus"
                >
                    Buat Berita Sekarang
                </v-btn>
            </div>

            <!-- Table with VTable -->
            <v-table v-else hover class="sneat-table">
                <thead>
                    <tr>
                        <th
                            class="font-weight-bold text-caption text-uppercase text-left"
                        >
                            GAMBAR & JUDUL
                        </th>
                        <th
                            class="font-weight-bold text-caption text-uppercase text-left"
                        >
                            KATEGORI
                        </th>
                        <th
                            class="font-weight-bold text-caption text-uppercase text-left"
                        >
                            STATUS
                        </th>
                        <th
                            class="font-weight-bold text-caption text-uppercase text-left"
                        >
                            TANGGAL
                        </th>
                        <th
                            class="font-weight-bold text-caption text-uppercase text-center"
                        >
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="post in posts" :key="post.id">
                        <!-- Cover & Title -->
                        <td class="py-3" style="max-width: 360px">
                            <div class="d-flex align-center">
                                <v-avatar
                                    rounded="lg"
                                    size="52"
                                    class="me-3 bg-grey-lighten-2 elevation-1 shrink-0"
                                >
                                    <v-img
                                        :src="
                                            post.image_url ||
                                            'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=200&q=80'
                                        "
                                        cover
                                    ></v-img>
                                </v-avatar>
                                <div>
                                    <div
                                        class="font-weight-bold text-body-2 mb-1 line-clamp-1"
                                    >
                                        {{ post.title }}
                                    </div>
                                    <div
                                        class="text-caption text-secondary line-clamp-1"
                                    >
                                        {{ post.content }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Category -->
                        <td>
                            <v-chip
                                size="small"
                                variant="tonal"
                                color="primary"
                                class="font-weight-bold"
                            >
                                {{ post.category?.name || 'Umum' }}
                            </v-chip>
                        </td>

                        <!-- Status with VChip per spec: green for published, grey for draft -->
                        <td>
                            <v-chip
                                size="small"
                                :color="
                                    post.status === 'published'
                                        ? 'success'
                                        : 'secondary'
                                "
                                variant="flat"
                                class="font-weight-bold text-capitalize"
                            >
                                <v-icon
                                    :icon="
                                        post.status === 'published'
                                            ? 'mdi-check-circle-outline'
                                            : 'mdi-file-outline'
                                    "
                                    start
                                    size="14"
                                ></v-icon>
                                {{
                                    post.status === 'published'
                                        ? 'Published'
                                        : 'Draft'
                                }}
                            </v-chip>
                        </td>

                        <!-- Date -->
                        <td class="text-caption text-secondary">
                            {{ formatDate(post.created_at) }}
                        </td>

                        <!-- Actions -->
                        <td class="text-center">
                            <div
                                class="d-flex align-center gap-1 justify-center"
                            >
                                <!-- View Detail -->
                                <v-tooltip
                                    text="Lihat Publikasi"
                                    location="top"
                                >
                                    <template #activator="{ props }">
                                        <v-btn
                                            v-bind="props"
                                            icon="mdi-eye-outline"
                                            size="small"
                                            variant="text"
                                            color="info"
                                            :to="`/posts/${post.slug}`"
                                        ></v-btn>
                                    </template>
                                </v-tooltip>

                                <!-- Edit -->
                                <v-tooltip text="Ubah Berita" location="top">
                                    <template #activator="{ props }">
                                        <v-btn
                                            v-bind="props"
                                            icon="mdi-pencil-outline"
                                            size="small"
                                            variant="text"
                                            color="primary"
                                            :to="`/posts/edit/${post.id}`"
                                        ></v-btn>
                                    </template>
                                </v-tooltip>

                                <!-- Delete -->
                                <v-tooltip text="Hapus Berita" location="top">
                                    <template #activator="{ props }">
                                        <v-btn
                                            v-bind="props"
                                            icon="mdi-trash-can-outline"
                                            size="small"
                                            variant="text"
                                            color="error"
                                            @click="confirmDelete(post)"
                                        ></v-btn>
                                    </template>
                                </v-tooltip>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </v-table>

            <!-- Table Pagination -->
            <div
                v-if="totalPages > 1"
                class="d-flex pa-4 justify-center border-t"
            >
                <v-pagination
                    v-model="currentPage"
                    :length="totalPages"
                    :total-visible="5"
                    color="primary"
                    rounded="circle"
                    @update:model-value="fetchMyPosts"
                ></v-pagination>
            </div>
        </v-card>

        <!-- Delete Confirmation Modal (VDialog) per KONTEKS.MD section 6.B.3 -->
        <v-dialog v-model="deleteDialog" max-width="450" persistent>
            <v-card class="pa-6 rounded-xl text-center">
                <v-avatar
                    color="error"
                    variant="tonal"
                    size="64"
                    class="mb-4 mx-auto"
                >
                    <v-icon
                        icon="mdi-alert-outline"
                        size="36"
                        color="error"
                    ></v-icon>
                </v-avatar>

                <h3 class="text-h6 font-weight-bold mb-2">
                    Konfirmasi Hapus Berita
                </h3>
                <p class="text-body-2 text-secondary mb-6">
                    Apakah Anda yakin ingin menghapus artikel:
                    <br />
                    <strong class="text-high-emphasis"
                        >"{{ postToDelete?.title }}"</strong
                    >?
                    <br />
                    Tindakan ini permanen dan tidak dapat dibatalkan.
                </p>

                <div class="d-flex gap-3 justify-center">
                    <v-btn
                        variant="outlined"
                        color="secondary"
                        class="px-6 font-weight-bold text-capitalize"
                        :disabled="deleteLoading"
                        @click="
                            deleteDialog = false;
                            postToDelete = null;
                        "
                    >
                        Batal
                    </v-btn>
                    <v-btn
                        color="error"
                        class="px-6 font-weight-bold text-capitalize elevation-1"
                        :loading="deleteLoading"
                        @click="handleDelete"
                    >
                        Hapus
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>

        <!-- Toast Notification (VSnackbar) per KONTEKS.MD section 6.C -->
        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            timeout="4000"
            location="bottom end"
            rounded="lg"
            class="elevation-3"
        >
            <div class="d-flex align-center">
                <v-icon
                    :icon="
                        snackbar.color === 'success'
                            ? 'mdi-check-circle'
                            : 'mdi-alert-circle'
                    "
                    class="me-2"
                ></v-icon>
                <span>{{ snackbar.text }}</span>
            </div>
        </v-snackbar>
    </div>
</template>

<style scoped>
.sneat-table th {
    color: var(--v-theme-secondary) !important;
    background-color: var(--v-theme-surface);
}
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
