<template>
    <div class="form-page">

        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="page-top">
            <div class="page-top__left">
                <RouterLink to="/kategori" class="btn-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                        width="15" height="15">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Kembali
                </RouterLink>
                <div>
                    <h1 class="pg-title">{{ isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}</h1>
                    <p class="pg-sub">
                        {{ isEdit
                            ? 'Perbarui informasi kategori barang.'
                            : 'Buat kategori baru untuk mengelompokkan barang.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loadingData" class="loading-state">
            <div class="loading-spinner"></div>
            <p>Memuat data kategori...</p>
        </div>

        <!-- ── Content grid ────────────────────────────────────────────── -->
        <div v-else class="content-grid">

            <!-- LEFT: Form ───────────────────────────────────────────────── -->
            <div class="form-col">

                <!-- Nama & Deskripsi -->
                <div class="form-card">
                    <div class="form-card__header">
                        <div class="form-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <rect x="3" y="3" width="7" height="7" />
                                <rect x="14" y="3" width="7" height="7" />
                                <rect x="3" y="14" width="7" height="7" />
                                <rect x="14" y="14" width="7" height="7" />
                            </svg>
                        </div>
                        <h2 class="form-card__title">Informasi Kategori</h2>
                    </div>

                    <div class="form-body">
                        <!-- Nama -->
                        <div class="field-group">
                            <label class="field-label">
                                Nama Kategori <span class="required">*</span>
                            </label>
                            <input v-model="form.nama" type="text" class="field-input"
                                :class="{ 'field-input--error': errors.nama }"
                                placeholder="Contoh: Peralatan Sholat, Perlengkapan Dapur..."
                                @input="errors.nama = ''" />
                            <p v-if="errors.nama" class="field-error">{{ errors.nama }}</p>
                        </div>

                        <!-- Deskripsi -->
                        <div class="field-group">
                            <label class="field-label">Deskripsi</label>
                            <textarea v-model="form.deskripsi" class="field-input field-textarea"
                                placeholder="Keterangan singkat tentang jenis barang dalam kategori ini..."
                                rows="3"></textarea>
                            <p class="field-hint">
                                Opsional — membantu admin mengenali jenis barang dalam kategori ini.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ikon Picker -->
                <div class="form-card">
                    <div class="form-card__header">
                        <div class="form-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                                <line x1="9" y1="9" x2="9.01" y2="9" />
                                <line x1="15" y1="9" x2="15.01" y2="9" />
                            </svg>
                        </div>
                        <h2 class="form-card__title">Pilih Ikon</h2>
                        <span class="form-card__badge" v-if="form.ikon">{{ form.ikon }}</span>
                    </div>

                    <div class="form-body">
                        <!-- Preview ikon terpilih -->
                        <div class="ikon-preview-bar">
                            <div class="ikon-preview-box" :style="{
                                background: itemColor(form.nama || 'x') + '20',
                                color: itemColor(form.nama || 'x')
                            }">
                                <component v-if="form.ikon" :is="getIcon(form.ikon)" width="22" height="22" />
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" width="20" height="20">
                                    <rect x="3" y="3" width="7" height="7" />
                                    <rect x="14" y="3" width="7" height="7" />
                                    <rect x="3" y="14" width="7" height="7" />
                                    <rect x="14" y="14" width="7" height="7" />
                                </svg>
                            </div>
                            <div class="ikon-preview-info">
                                <p class="ikon-preview-name">{{ form.ikon || 'Belum dipilih' }}</p>
                                <p class="field-hint" style="margin:0">
                                    {{ form.ikon ? 'Klik ikon lain untuk ganti' : 'Klik ikon di bawah untuk memilih' }}
                                </p>
                            </div>
                            <button v-if="form.ikon" type="button" class="ikon-clear-btn" @click="form.ikon = ''">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" width="12" height="12">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                Hapus
                            </button>
                        </div>

                        <!-- Filter kategori ikon -->
                        <div class="ikon-cats">
                            <button v-for="cat in ikonCategories" :key="cat" type="button" class="ikon-cat-btn"
                                :class="{ 'ikon-cat-btn--active': activeIkonCat === cat }"
                                @click="activeIkonCat = cat; ikonSearch = ''">
                                {{ cat }}
                            </button>
                        </div>

                        <!-- Search ikon -->
                        <div class="search-wrap">
                            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" width="14" height="14">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input v-model="ikonSearch" type="text" placeholder="Cari nama ikon Lucide..."
                                class="search-input" />
                            <button v-if="ikonSearch" class="search-clear" @click="ikonSearch = ''">✕</button>
                        </div>

                        <!-- Grid ikon -->
                        <div class="ikon-picker-grid" v-if="filteredIkons.length">
                            <button v-for="icon in filteredIkons" :key="icon.name" type="button" class="ikon-pick-btn"
                                :class="{ 'ikon-pick-btn--active': form.ikon === icon.name }" :title="icon.name"
                                @click="form.ikon = (form.ikon === icon.name ? '' : icon.name)">
                                <component :is="getIcon(icon.name)" width="20" height="20" />
                                <span>{{ icon.label }}</span>
                            </button>
                        </div>
                        <div v-else class="ikon-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28"
                                height="28" style="color:#cbd5e1">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <p>Tidak ada ikon yang cocok</p>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="form-actions">
                    <RouterLink to="/kategori" class="btn-cancel">Batal</RouterLink>
                    <button class="btn-submit" :disabled="submitting" @click="handleSubmit">
                        <svg v-if="submitting" class="spin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" width="15" height="15">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" width="15" height="15">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        {{ submitting ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat Kategori') }}
                    </button>
                </div>
            </div>

            <!-- RIGHT: Sidebar ───────────────────────────────────────────── -->
            <div class="right-col">

                <!-- Preview card -->
                <div class="preview-section">
                    <p class="section-label">Pratinjau Kartu</p>
                    <div class="preview-kategori-card">
                        <div class="preview-card-top">
                            <div class="preview-ikon" :style="{
                                background: itemColor(form.nama || 'x') + '20',
                                color: itemColor(form.nama || 'x')
                            }">
                                <component v-if="form.ikon" :is="getIcon(form.ikon)" width="22" height="22" />
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" width="20" height="20">
                                    <rect x="3" y="3" width="7" height="7" />
                                    <rect x="14" y="3" width="7" height="7" />
                                    <rect x="3" y="14" width="7" height="7" />
                                    <rect x="14" y="14" width="7" height="7" />
                                </svg>
                            </div>
                            <div class="preview-color-dot" :style="{ background: itemColor(form.nama || 'x') }"></div>
                        </div>
                        <p class="preview-nama">{{ form.nama || 'Nama Kategori' }}</p>
                        <p class="preview-desc">
                            {{ form.deskripsi || 'Deskripsi kategori akan muncul di sini...' }}
                        </p>
                        <div class="preview-footer">
                            <span class="preview-count">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="11" height="11">
                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                </svg>
                                {{ isEdit ? (currentKategori?.barangs_count ?? 0) : 0 }} barang
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Edit mode: info barang yang ada -->
                <div class="info-card" v-if="isEdit && currentKategori">
                    <p class="section-label">Statistik Kategori</p>
                    <div class="stat-mini-grid">
                        <div class="stat-mini">
                            <p class="stat-mini-val" :style="{ color: itemColor(form.nama || 'x') }">
                                {{ currentKategori.barangs_count ?? 0 }}
                            </p>
                            <p class="stat-mini-label">Total Barang</p>
                        </div>
                        <div class="stat-mini">
                            <p class="stat-mini-val" style="color:#f59e0b">
                                {{ barangRusakCount }}
                            </p>
                            <p class="stat-mini-label">Perlu Perhatian</p>
                        </div>
                    </div>

                    <div v-if="currentKategori.barangs?.length" class="barang-mini-list">
                        <p class="list-label">Barang Terdaftar</p>
                        <div v-for="b in currentKategori.barangs.slice(0, 5)" :key="b.id" class="barang-mini-item">
                            <div class="barang-mini-dot" :class="kondisiDotClass(b.kondisi)"></div>
                            <span class="barang-mini-nama">{{ b.nama }}</span>
                            <span class="barang-mini-stok">{{ b.stok_tersedia }}</span>
                        </div>
                        <p v-if="currentKategori.barangs.length > 5" class="more-text">
                            +{{ currentKategori.barangs.length - 5 }} barang lainnya
                        </p>
                    </div>

                    <div class="warn-box" v-if="(currentKategori.barangs_count ?? 0) > 0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="13" height="13">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        Kategori ini tidak dapat dihapus selama masih memiliki barang.
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-card">
                    <p class="tips-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="14" height="14" style="color:#16a34a">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="16" x2="12" y2="12" />
                            <line x1="12" y1="8" x2="12.01" y2="8" />
                        </svg>
                        Tips Pengisian
                    </p>
                    <ul class="tips-list">
                        <li>Gunakan nama yang singkat dan mudah dipahami</li>
                        <li>Pilih ikon yang relevan untuk memudahkan identifikasi visual</li>
                        <li>Deskripsi membantu admin memilih kategori yang tepat saat tambah barang</li>
                        <li>Kategori yang sudah memiliki barang tidak dapat dihapus</li>
                    </ul>
                </div>

                <!-- Daftar kategori lain (jika tambah baru) -->
                <div class="existing-card" v-if="!isEdit && otherKategoris.length">
                    <p class="section-label">Kategori yang Ada</p>
                    <div class="existing-list">
                        <div v-for="k in otherKategoris" :key="k.id" class="existing-item">
                            <div class="existing-ikon"
                                :style="{ background: itemColor(k.nama) + '18', color: itemColor(k.nama) }">
                                <component v-if="k.ikon" :is="getIcon(k.ikon)" width="13" height="13" />
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="13" height="13">
                                    <rect x="3" y="3" width="7" height="7" />
                                    <rect x="14" y="3" width="7" height="7" />
                                    <rect x="3" y="14" width="7" height="7" />
                                    <rect x="14" y="14" width="7" height="7" />
                                </svg>
                            </div>
                            <span class="existing-nama">{{ k.nama }}</span>
                            <span class="existing-count">{{ k.barangs_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useKategoriStore } from '@/stores/kategori'
import kategoriService from '@/services/kategoriService'
import { useToast } from 'vue-toastification'
import * as LucideIcons from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const store = useKategoriStore()
const toast = useToast()

// ── Icon system ─────────────────────────────────────────────────────
function getIcon(name) {
    return LucideIcons[name] || LucideIcons.Box
}

const IKON_SETS = {
    'Furnitur': [
        { name: 'Armchair', label: 'Armchair' },
        { name: 'Bed', label: 'Bed' },
        { name: 'Lamp', label: 'Lamp' },
        { name: 'Table2', label: 'Table' },
        { name: 'BookMarked', label: 'Shelf' },
        { name: 'LayoutDashboard', label: 'Room' },
    ],
    'Peralatan': [
        { name: 'Wrench', label: 'Wrench' },
        { name: 'Hammer', label: 'Hammer' },
        { name: 'Scissors', label: 'Scissors' },
        { name: 'Plug', label: 'Plug' },
        { name: 'Lightbulb', label: 'Lampu' },
        { name: 'Settings', label: 'Settings' },
        { name: 'Tool', label: 'Tool' },
        { name: 'Drill', label: 'Drill' },
    ],
    'Ibadah': [
        { name: 'BookOpen', label: 'Al-Quran' },
        { name: 'Book', label: 'Kitab' },
        { name: 'Star', label: 'Bintang' },
        { name: 'Heart', label: 'Hati' },
        { name: 'Moon', label: 'Bulan' },
        { name: 'Sun', label: 'Matahari' },
        { name: 'Compass', label: 'Kiblat' },
        { name: 'Bell', label: 'Adzan' },
    ],
    'Dapur': [
        { name: 'Utensils', label: 'Sendok' },
        { name: 'UtensilsCrossed', label: 'Peralatan' },
        { name: 'Coffee', label: 'Minuman' },
        { name: 'Droplet', label: 'Air' },
        { name: 'ShoppingBasket', label: 'Belanja' },
        { name: 'Refrigerator', label: 'Kulkas' },
        { name: 'Microwave', label: 'Microwave' },
        { name: 'ChefHat', label: 'Masak' },
    ],
    'Kebersihan': [
        { name: 'Trash2', label: 'Sampah' },
        { name: 'Wind', label: 'Angin' },
        { name: 'Sparkles', label: 'Bersih' },
        { name: 'Package', label: 'Paket' },
        { name: 'Archive', label: 'Arsip' },
        { name: 'Brush', label: 'Kuas' },
        { name: 'WashingMachine', label: 'Cuci' },
    ],
    'Elektronik': [
        { name: 'Monitor', label: 'Monitor' },
        { name: 'Smartphone', label: 'HP' },
        { name: 'Wifi', label: 'Wifi' },
        { name: 'Speaker', label: 'Speaker' },
        { name: 'Camera', label: 'Kamera' },
        { name: 'Printer', label: 'Printer' },
        { name: 'Projector', label: 'Proyektor' },
        { name: 'Mic', label: 'Mikrofon' },
    ],
    'Penyimpanan': [
        { name: 'Box', label: 'Kotak' },
        { name: 'Archive', label: 'Lemari' },
        { name: 'Package', label: 'Kardus' },
        { name: 'FolderOpen', label: 'Folder' },
        { name: 'Database', label: 'Storage' },
        { name: 'Layers', label: 'Susun' },
    ],
    'Lainnya': [
        { name: 'Tag', label: 'Label' },
        { name: 'Briefcase', label: 'Tas' },
        { name: 'Key', label: 'Kunci' },
        { name: 'Shield', label: 'Aman' },
        { name: 'Flag', label: 'Bendera' },
        { name: 'Grid', label: 'Grid' },
        { name: 'Circle', label: 'Bulat' },
        { name: 'Zap', label: 'Zap' },
    ],
}

const ikonCategories = ['Semua', ...Object.keys(IKON_SETS)]
const activeIkonCat = ref('Semua')
const ikonSearch = ref('')

const allIkons = computed(() =>
    Object.entries(IKON_SETS).flatMap(([, icons]) => icons)
)

const filteredIkons = computed(() => {
    const base = activeIkonCat.value === 'Semua'
        ? allIkons.value
        : (IKON_SETS[activeIkonCat.value] || [])

    if (!ikonSearch.value.trim()) return base
    const q = ikonSearch.value.toLowerCase()
    return allIkons.value.filter(
        i => i.name.toLowerCase().includes(q) || i.label.toLowerCase().includes(q)
    )
})

// ── Mode ────────────────────────────────────────────────────────────
const isEdit = computed(() => !!route.params.id)
const currentKategori = ref(null)
const loadingData = ref(false)
const submitting = ref(false)

const form = ref({ nama: '', ikon: '', deskripsi: '' })
const errors = ref({})

// ── Computed ────────────────────────────────────────────────────────
const otherKategoris = computed(() => store.kategoris.slice(0, 8))

const barangRusakCount = computed(() =>
    (currentKategori.value?.barangs ?? []).filter(
        b => b.kondisi !== 'Baik'
    ).length
)

// ── Methods ─────────────────────────────────────────────────────────
async function loadData() {
    if (!store.kategoris.length) {
        await store.fetchKategoris()
    }
    if (!isEdit.value) return

    loadingData.value = true
    try {
        const data = await kategoriService.getOne(route.params.id)
        currentKategori.value = data
        form.value = {
            nama: data.nama ?? '',
            ikon: data.ikon ?? '',
            deskripsi: data.deskripsi ?? '',
        }
    } catch {
        toast.error('Gagal memuat data kategori.')
        router.push('/kategori')
    } finally {
        loadingData.value = false
    }
}

function validate() {
    const e = {}
    if (!form.value.nama.trim()) e.nama = 'Nama kategori wajib diisi.'
    if (form.value.nama.length > 100) e.nama = 'Nama maksimal 100 karakter.'
    errors.value = e
    return Object.keys(e).length === 0
}

async function handleSubmit() {
    if (!validate()) return
    submitting.value = true

    const payload = {
        nama: form.value.nama.trim(),
        ikon: form.value.ikon || null,
        deskripsi: form.value.deskripsi || null,
    }

    try {
        if (isEdit.value) {
            await store.updateKategori(Number(route.params.id), payload)
            toast.success('Kategori berhasil diperbarui!')
        } else {
            await store.createKategori(payload)
            toast.success('Kategori berhasil ditambahkan!')
        }
        router.push('/kategori')
    } catch (err) {
        const errData = err?.response?.data?.errors
        if (errData) {
            const mapped = {}
            Object.entries(errData).forEach(([k, v]) => {
                mapped[k] = Array.isArray(v) ? v[0] : v
            })
            errors.value = mapped
        }
        toast.error(err?.response?.data?.message || 'Gagal menyimpan kategori.')
    } finally {
        submitting.value = false
    }
}

// ── Helpers ──────────────────────────────────────────────────────────
const COLORS = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']
function itemColor(nama = '') {
    let h = 0
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return COLORS[h % COLORS.length]
}

function kondisiDotClass(kondisi) {
    if (kondisi === 'Baik') return 'dot--baik'
    if (kondisi === 'Rusak Ringan') return 'dot--ringan'
    return 'dot--berat'
}

onMounted(() => loadData())
</script>

<style scoped>
.form-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
    max-width: 900px;
}

/* ── Header ──────────────────────────────────────────────────────── */
.page-top {
    margin-bottom: 20px;
}

.page-top__left {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    text-decoration: none;
    border: 1.5px solid #e5e7eb;
    padding: 7px 12px;
    border-radius: 9px;
    background: white;
    transition: all 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 2px;
}

.btn-back:hover {
    border-color: #d1d5db;
    color: #374151;
    background: #f9fafb;
}

.pg-title {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.pg-sub {
    font-size: 13px;
    color: #6b7280;
    margin: 2px 0 0;
}

/* Loading */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 64px;
    gap: 12px;
    color: #9ca3af;
    font-size: 14px;
}

.loading-spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #f0f0f0;
    border-top-color: #16a34a;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ── Grid ────────────────────────────────────────────────────────── */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 268px;
    gap: 16px;
    align-items: start;
}

.form-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.right-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ── Form card ───────────────────────────────────────────────────── */
.form-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.form-card__header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 18px;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}

.form-card__icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #f0fdf4;
    color: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.form-card__title {
    font-size: 13.5px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    flex: 1;
}

.form-card__badge {
    font-size: 11px;
    font-weight: 600;
    color: #16a34a;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    padding: 2px 8px;
}

.form-body {
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* ── Fields ──────────────────────────────────────────────────────── */
.field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-label {
    font-size: 12.5px;
    font-weight: 600;
    color: #374151;
}

.required {
    color: #ef4444;
    margin-left: 2px;
}

.field-input {
    padding: 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    color: #111827;
    background: white;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
    width: 100%;
    box-sizing: border-box;
}

.field-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.field-input::placeholder {
    color: #c4c9d1;
}

.field-input--error {
    border-color: #fca5a5;
    background: #fff5f5;
}

.field-textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.6;
}

.field-hint {
    font-size: 11.5px;
    color: #9ca3af;
    margin: 0;
}

.field-error {
    font-size: 11.5px;
    color: #ef4444;
    margin: 0;
}

.field-error::before {
    content: '⚠ ';
}

/* ── Ikon preview bar ────────────────────────────────────────────── */
.ikon-preview-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
}

.ikon-preview-box {
    width: 44px;
    height: 44px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s;
}

.ikon-preview-info {
    flex: 1;
    min-width: 0;
}

.ikon-preview-name {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ikon-clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 600;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
    background: white;
    border-radius: 7px;
    padding: 5px 10px;
    cursor: pointer;
    transition: all 0.15s;
    flex-shrink: 0;
    font-family: inherit;
}

.ikon-clear-btn:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #dc2626;
}

/* ── Kategori filter ─────────────────────────────────────────────── */
.ikon-cats {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.ikon-cat-btn {
    font-size: 11.5px;
    font-weight: 600;
    padding: 5px 11px;
    border: 1.5px solid #e5e7eb;
    border-radius: 99px;
    background: white;
    color: #6b7280;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.13s;
    line-height: 1;
}

.ikon-cat-btn:hover {
    border-color: #d1fae5;
    color: #374151;
    background: #f9fafb;
}

.ikon-cat-btn--active {
    background: #f0fdf4;
    border-color: #86efac;
    color: #16a34a;
}

/* ── Search ──────────────────────────────────────────────────────── */
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 11px;
    color: #9ca3af;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 8px 30px 8px 34px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    color: #111827;
    background: white;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
    box-sizing: border-box;
}

.search-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.search-input::placeholder {
    color: #c4c9d1;
}

.search-clear {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 12px;
    padding: 2px 4px;
}

/* ── Ikon grid ───────────────────────────────────────────────────── */
.ikon-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
    gap: 6px;
    max-height: 260px;
    overflow-y: auto;
    padding-right: 2px;
}

.ikon-picker-grid::-webkit-scrollbar {
    width: 4px;
}

.ikon-picker-grid::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

.ikon-picker-grid::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.ikon-pick-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    padding: 9px 4px 7px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.13s;
    font-family: inherit;
}

.ikon-pick-btn:hover {
    border-color: #86efac;
    background: #f0fdf4;
    color: #16a34a;
    transform: translateY(-1px);
}

.ikon-pick-btn--active {
    border-color: #16a34a;
    background: #f0fdf4;
    color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
}

.ikon-pick-btn span {
    font-size: 9.5px;
    font-weight: 600;
    color: inherit;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 100%;
    padding: 0 2px;
}

.ikon-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 24px;
    color: #9ca3af;
    font-size: 13px;
}

/* ── Form actions ───────────────────────────────────────────────── */
.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-cancel {
    padding: 10px 20px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    font-family: inherit;
}

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 22px;
    border-radius: 9px;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
    font-family: inherit;
}

.btn-submit:hover:not(:disabled) {
    background: #15803d;
}

.btn-submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.spin-icon {
    animation: spin 0.8s linear infinite;
}

/* ── RIGHT COL ───────────────────────────────────────────────────── */
.section-label {
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 10px;
}

/* Preview */
.preview-section {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px;
}

.preview-kategori-card {
    background: #f9fafb;
    border-radius: 12px;
    border: 1.5px solid #f0f0f0;
    padding: 16px;
    width: 100%;
    box-sizing: border-box;
    transition: all 0.2s;
}

.preview-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
}

.preview-ikon {
    width: 44px;
    height: 44px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}

.preview-color-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 4px;
    transition: background 0.2s;
}

.preview-nama {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
}

.preview-desc {
    font-size: 12px;
    color: #9ca3af;
    margin: 0 0 12px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-style: italic;
}

.preview-footer {
    border-top: 1px solid #e5e7eb;
    padding-top: 10px;
}

.preview-count {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 600;
    color: #6b7280;
}

/* Info card (edit mode) */
.info-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px 16px;
}

.stat-mini-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 14px;
}

.stat-mini {
    background: #f9fafb;
    border-radius: 9px;
    padding: 10px 12px;
    text-align: center;
}

.stat-mini-val {
    font-size: 22px;
    font-weight: 800;
    margin: 0;
    line-height: 1;
}

.stat-mini-label {
    font-size: 10.5px;
    color: #9ca3af;
    margin: 4px 0 0;
    font-weight: 500;
}

.list-label {
    font-size: 10.5px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin: 0 0 8px;
}

.barang-mini-list {
    display: flex;
    flex-direction: column;
}

.barang-mini-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 0;
    border-bottom: 1px solid #f9fafb;
    font-size: 12.5px;
}

.barang-mini-item:last-of-type {
    border-bottom: none;
}

.barang-mini-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dot--baik {
    background: #16a34a;
}

.dot--ringan {
    background: #f59e0b;
}

.dot--berat {
    background: #ef4444;
}

.barang-mini-nama {
    flex: 1;
    color: #374151;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.barang-mini-stok {
    font-size: 11px;
    font-weight: 700;
    color: #16a34a;
    flex-shrink: 0;
}

.more-text {
    font-size: 11.5px;
    color: #9ca3af;
    margin: 6px 0 0;
}

.warn-box {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    padding: 9px 10px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    font-size: 12px;
    color: #92400e;
    line-height: 1.5;
    margin-top: 12px;
}

/* Tips */
.tips-card {
    background: #f0fdf4;
    border-radius: 11px;
    border: 1px solid #bbf7d0;
    padding: 13px 15px;
}

.tips-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #166534;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.tips-list {
    margin: 0;
    padding-left: 16px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.tips-list li {
    font-size: 12px;
    color: #166534;
    opacity: 0.85;
    line-height: 1.5;
}

/* Existing kategoris */
.existing-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px 16px;
}

.existing-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.existing-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid #f9fafb;
    font-size: 12.5px;
}

.existing-item:last-child {
    border-bottom: none;
}

.existing-ikon {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.existing-nama {
    flex: 1;
    color: #374151;
    font-weight: 500;
}

.existing-count {
    font-size: 11px;
    font-weight: 700;
    color: #16a34a;
}

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 760px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .right-col {
        order: -1;
    }

    .ikon-picker-grid {
        grid-template-columns: repeat(auto-fill, minmax(56px, 1fr));
    }
}
</style>