<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/plugins/axios';

const route = useRoute();
const router = useRouter();

const isEditMode = computed(() => !!route.params.id);

// Form Fields
const title = ref('');
const categoryId = ref<number | null>(null);
const content = ref('');
const status = ref<'published' | 'draft'>('published');
const imageFile = ref<File | null>(null);
const existingImageUrl = ref<string | null>(null);
const previewImageUrl = ref<string | null>(null);

// UI State
const loading = ref(false);
const initialLoading = ref(false);
const categories = ref<{ id: number; name: string }[]>([]);
const fieldErrors = ref<Record<string, string[]>>({});
const generalError = ref('');

// Toast
const snackbar = ref({
    show: false,
    text: '',
    color: 'success',
});

const triggerToast = (text: string, color = 'success') => {
    snackbar.value = { show: true, text, color };
};

// Fetch categories for VSelect
const fetchCategories = async () => {
    try {
        const response = await apiClient.get('/categories');
        categories.value = response.data.categories || [];
    } catch (err) {
        console.error('Failed to load categories', err);
    }
};

// Fetch existing post for edit mode
const fetchExistingPost = async () => {
    if (!isEditMode.value) return;
    initialLoading.value = true;
    try {
        const postId = route.params.id;
        const response = await apiClient.get(`/posts/id/${postId}`);
        const post = response.data.post;

        title.value = post.title;
        categoryId.value = post.category_id;
        content.value = post.content;
        status.value = post.status;
        existingImageUrl.value = post.image_url;
    } catch (err: any) {
        generalError.value =
            err.response?.data?.message ||
            'Gagal memuat data berita yang akan diubah.';
    } finally {
        initialLoading.value = false;
    }
};

// Handle file input change for live preview
const handleImageChange = (fileOrFiles: any) => {
    let file = fileOrFiles;
    if (Array.isArray(fileOrFiles) && fileOrFiles.length > 0) {
        file = fileOrFiles[0];
    }

    if (file && file instanceof File) {
        imageFile.value = file;
        previewImageUrl.value = URL.createObjectURL(file);
    } else {
        imageFile.value = null;
        previewImageUrl.value = null;
    }
};

// Submit handler
const handleSubmit = async () => {
    // Reset errors
    fieldErrors.value = {};
    generalError.value = '';

    if (!title.value || !categoryId.value || !content.value) {
        generalError.value = 'Silakan lengkapi semua kolom yang wajib diisi.';
        return;
    }

    loading.value = true;

    try {
        const formData = new FormData();
        formData.append('title', title.value);
        formData.append('category_id', String(categoryId.value));
        formData.append('content', content.value);
        formData.append('status', status.value);

        if (imageFile.value) {
            formData.append('image', imageFile.value);
        }

        if (isEditMode.value) {
            // As specified in KONTEKS.MD section 4.5:
            // "Proses ubah berita dengan gambar baru menggunakan method POST dengan menyertakan atribut _method = PUT dalam FormData"
            formData.append('_method', 'PUT');
            const postId = route.params.id;
            await apiClient.post(`/posts/${postId}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            triggerToast('Berita berhasil diperbarui!', 'success');
        } else {
            await apiClient.post('/posts', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            triggerToast('Berita berhasil dibuat dan diterbitkan!', 'success');
        }

        setTimeout(() => {
            router.push('/my-posts');
        }, 800);
    } catch (err: any) {
        if (err.response?.data?.errors) {
            fieldErrors.value = err.response.data.errors;
            const first = Object.keys(err.response.data.errors)[0];
            generalError.value = err.response.data.errors[first][0];
        } else if (err.response?.data?.message) {
            generalError.value = err.response.data.message;
        } else {
            generalError.value = 'Terjadi kesalahan saat menyimpan berita.';
        }
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await fetchCategories();
    if (isEditMode.value) {
        await fetchExistingPost();
    }
});
</script>

<template>
    <div class="post-form-view">
        <!-- Header -->
        <div class="d-flex align-center justify-space-between mb-6">
            <div>
                <h2 class="text-h5 font-weight-bold mb-1">
                    {{ isEditMode ? 'Ubah Berita ✏️' : 'Tulis Berita Baru ✍️' }}
                </h2>
                <p class="text-body-2 text-secondary mb-0">
                    {{
                        isEditMode
                            ? 'Perbarui informasi, status, atau sampul berita Anda.'
                            : 'Bagikan berita informatif dan aktual kepada seluruh pembaca.'
                    }}
                </p>
            </div>
            <v-btn
                to="/my-posts"
                variant="outlined"
                color="secondary"
                prepend-icon="mdi-arrow-left"
                class="font-weight-bold text-capitalize"
            >
                Kembali
            </v-btn>
        </div>

        <!-- Initial Loading -->
        <div v-if="initialLoading" class="pa-12 text-center">
            <v-progress-circular
                indeterminate
                color="primary"
                size="48"
            ></v-progress-circular>
            <div class="mt-3 text-body-2 text-secondary">
                Memuat data artikel...
            </div>
        </div>

        <!-- Form Card -->
        <v-card v-else class="rounded-xl pa-6 pa-md-8 elevation-2 bg-surface">
            <!-- Alert Error -->
            <v-alert
                v-if="generalError"
                type="error"
                variant="tonal"
                class="mb-6 rounded-lg text-body-2"
                closable
                @click:close="generalError = ''"
            >
                {{ generalError }}
            </v-alert>

            <v-form @submit.prevent="handleSubmit">
                <v-row>
                    <!-- Left Column: Main Content -->
                    <v-col cols="12" md="8">
                        <!-- Judul Berita (VTextField) -->
                        <div class="mb-5">
                            <label
                                class="text-caption font-weight-bold text-secondary mb-1 d-block"
                            >
                                JUDUL BERITA <span class="text-error">*</span>
                            </label>
                            <v-text-field
                                v-model="title"
                                placeholder="Masukkan judul berita yang menarik dan informatif"
                                prepend-inner-icon="mdi-format-title"
                                :error-messages="fieldErrors.title"
                                required
                                hide-details="auto"
                            ></v-text-field>
                        </div>

                        <!-- Isi Berita (VTextarea) -->
                        <div class="mb-5">
                            <label
                                class="text-caption font-weight-bold text-secondary mb-1 d-block"
                            >
                                ISI BERITA LENGKAP
                                <span class="text-error">*</span>
                            </label>
                            <v-textarea
                                v-model="content"
                                placeholder="Tuliskan isi artikel berita secara lengkap di sini..."
                                rows="12"
                                prepend-inner-icon="mdi-text-box-outline"
                                :error-messages="fieldErrors.content"
                                required
                                hide-details="auto"
                            ></v-textarea>
                        </div>
                    </v-col>

                    <!-- Right Column: Metadata & Image -->
                    <v-col cols="12" md="4">
                        <v-card
                            variant="outlined"
                            class="pa-5 rounded-lg mb-6 border"
                        >
                            <h3
                                class="text-subtitle-2 font-weight-bold mb-4 d-flex align-center"
                            >
                                <v-icon
                                    icon="mdi-cog-outline"
                                    color="primary"
                                    class="me-2"
                                ></v-icon>
                                Pengaturan Berita
                            </h3>

                            <!-- Kategori Berita (VSelect) -->
                            <div class="mb-4">
                                <label
                                    class="text-caption font-weight-bold text-secondary mb-1 d-block"
                                >
                                    KATEGORI BERITA
                                    <span class="text-error">*</span>
                                </label>
                                <v-select
                                    v-model="categoryId"
                                    :items="categories"
                                    item-title="name"
                                    item-value="id"
                                    placeholder="Pilih Kategori"
                                    prepend-inner-icon="mdi-shape-outline"
                                    :error-messages="fieldErrors.category_id"
                                    required
                                    hide-details="auto"
                                ></v-select>
                            </div>

                            <!-- Pilihan Status (VRadioGroup & VRadio) -->
                            <div class="mb-4">
                                <label
                                    class="text-caption font-weight-bold text-secondary mb-1 d-block"
                                >
                                    STATUS PUBLIKASI
                                    <span class="text-error">*</span>
                                </label>
                                <v-radio-group
                                    v-model="status"
                                    color="primary"
                                    hide-details
                                    class="mt-1"
                                >
                                    <v-radio
                                        value="published"
                                        label="Published (Publik)"
                                    ></v-radio>
                                    <v-radio
                                        value="draft"
                                        label="Draft (Disimpan Pribadi)"
                                    ></v-radio>
                                </v-radio-group>
                            </div>
                        </v-card>

                        <!-- Upload Gambar Sampul (VFileInput + Live Preview VImg) -->
                        <v-card
                            variant="outlined"
                            class="pa-5 rounded-lg border"
                        >
                            <h3
                                class="text-subtitle-2 font-weight-bold mb-3 d-flex align-center"
                            >
                                <v-icon
                                    icon="mdi-image-outline"
                                    color="primary"
                                    class="me-2"
                                ></v-icon>
                                Gambar Sampul
                            </h3>

                            <!-- Live Preview -->
                            <div class="mb-4 text-center">
                                <v-img
                                    v-if="previewImageUrl || existingImageUrl"
                                    :src="
                                        previewImageUrl ||
                                        existingImageUrl ||
                                        ''
                                    "
                                    height="180"
                                    cover
                                    rounded="lg"
                                    class="elevation-1 mb-2 border"
                                ></v-img>
                                <div
                                    v-else
                                    class="pa-6 rounded-lg bg-surface-light border-dashed text-center"
                                >
                                    <v-icon
                                        icon="mdi-image-plus"
                                        size="36"
                                        color="secondary"
                                        class="mb-1"
                                    ></v-icon>
                                    <div class="text-caption text-secondary">
                                        Belum ada gambar terpilih
                                    </div>
                                </div>
                            </div>

                            <!-- VFileInput -->
                            <v-file-input
                                label="Pilih Gambar Sampul"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                prepend-icon=""
                                prepend-inner-icon="mdi-camera"
                                show-size
                                :error-messages="fieldErrors.image"
                                @update:model-value="handleImageChange"
                                hide-details="auto"
                            ></v-file-input>
                            <span
                                class="text-caption text-secondary mt-1 d-block"
                                >Format: JPG, PNG, WEBP (Maks: 2MB)</span
                            >
                        </v-card>
                    </v-col>
                </v-row>

                <v-divider class="my-6"></v-divider>

                <!-- Action Buttons -->
                <div class="d-flex gap-3 justify-end">
                    <v-btn
                        to="/my-posts"
                        variant="outlined"
                        color="secondary"
                        class="px-6 font-weight-bold text-capitalize"
                    >
                        Batal
                    </v-btn>
                    <v-btn
                        type="submit"
                        color="primary"
                        class="px-8 font-weight-bold text-capitalize elevation-2"
                        :loading="loading"
                    >
                        {{
                            isEditMode ? 'Simpan Perubahan' : 'Terbitkan Berita'
                        }}
                    </v-btn>
                </div>
            </v-form>
        </v-card>

        <!-- Toast Notification (VSnackbar) -->
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
.border-dashed {
    border: 2px dashed rgba(105, 108, 255, 0.3) !important;
}
</style>
