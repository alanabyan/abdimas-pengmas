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
                    <h1 class="pg-title">
                        <span v-if="isEdit" class="pg-title__badge pg-title__badge--edit">Edit</span>
                        <span v-else class="pg-title__badge pg-title__badge--add">Baru</span>
                        {{ isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}
                    </h1>
                    <p class="pg-sub">
                        {{ isEdit
                            ? 'Perbarui informasi kategori barang inventaris masjid.'
                            : 'Buat kategori baru untuk mengelompokkan barang inventaris.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Loading ────────────────────────────────────────────────── -->
        <div v-if="loadingData" class="loading-state">
            <div class="loading-ring">
                <svg viewBox="0 0 50 50" width="40" height="40">
                    <circle cx="25" cy="25" r="20" fill="none" stroke="#e5e7eb" stroke-width="4" />
                    <circle cx="25" cy="25" r="20" fill="none" stroke="#16a34a" stroke-width="4"
                        stroke-dasharray="31.4 94.2" stroke-linecap="round" class="ring-spin" />
                </svg>
            </div>
            <p>Memuat data kategori...</p>
        </div>

        <!-- ── Content grid ───────────────────────────────────────────── -->
        <div v-else class="content-grid">

            <!-- LEFT: Form ───────────────────────────────────────────── -->
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
                            <div class="field-input-wrap">
                                <input v-model="form.nama" type="text" class="field-input"
                                    :class="{ 'field-input--error': errors.nama }"
                                    placeholder="Contoh: Peralatan Sholat, Perlengkapan Dapur..."
                                    @input="errors.nama = ''; isDirty = true" maxlength="100" />
                                <span class="char-counter" :class="{ 'char-counter--warn': form.nama.length > 80 }">
                                    {{ form.nama.length }}/100
                                </span>
                            </div>
                            <p v-if="errors.nama" class="field-error">{{ errors.nama }}</p>
                        </div>

                        <!-- Deskripsi -->
                        <div class="field-group">
                            <label class="field-label">
                                Deskripsi
                                <span class="field-label__opt">Opsional</span>
                            </label>
                            <textarea v-model="form.deskripsi" class="field-input field-textarea"
                                placeholder="Keterangan singkat tentang jenis barang dalam kategori ini..." rows="3"
                                @input="isDirty = true"></textarea>
                            <p class="field-hint">
                                Membantu admin mengenali jenis barang dalam kategori ini.
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
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </div>
                        <h2 class="form-card__title">Pilih Ikon</h2>
                        <span class="form-card__badge" v-if="form.ikon">
                            <component :is="getIcon(form.ikon)" width="11" height="11" />
                            {{ form.ikon }}
                        </span>
                    </div>

                    <div class="form-body">
                        <!-- Preview ikon terpilih -->
                        <div class="ikon-preview-bar" :class="{ 'ikon-preview-bar--active': form.ikon }">
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
                            <button v-if="form.ikon" type="button" class="ikon-clear-btn"
                                @click="form.ikon = ''; isDirty = true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" width="11" height="11">
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
                            <input v-model="ikonSearch" type="text" placeholder="Cari nama ikon..."
                                class="search-input" />
                            <button v-if="ikonSearch" class="search-clear" @click="ikonSearch = ''">✕</button>
                        </div>

                        <!-- Grid ikon -->
                        <div class="ikon-picker-grid" v-if="filteredIkons.length">
                            <button v-for="icon in filteredIkons" :key="icon.name" type="button" class="ikon-pick-btn"
                                :class="{ 'ikon-pick-btn--active': form.ikon === icon.name }" :title="icon.name" :style="form.ikon === icon.name
                                    ? { borderColor: itemColor(form.nama || 'x'), color: itemColor(form.nama || 'x'), background: itemColor(form.nama || 'x') + '12' }
                                    : {}"
                                @click="form.ikon = (form.ikon === icon.name ? '' : icon.name); isDirty = true">
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
                    <button v-if="isEdit && isDirty" type="button" class="btn-reset" @click="resetForm"
                        title="Reset ke data awal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="13" height="13">
                            <polyline points="1 4 1 10 7 10" />
                            <path d="M3.51 15a9 9 0 1 0 .49-3.27" />
                        </svg>
                        Reset
                    </button>
                    <RouterLink to="/kategori" class="btn-cancel">Batal</RouterLink>
                    <button class="btn-submit" :disabled="submitting || (isEdit && !isDirty)" @click="handleSubmit">
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

            <!-- RIGHT: Sidebar ───────────────────────────────────────── -->
            <div class="right-col">

                <!-- Preview card -->
                <div class="preview-section">
                    <p class="section-label">Pratinjau Kartu</p>
                    <div class="preview-kategori-card"
                        :style="{ borderColor: isDirty ? itemColor(form.nama || 'x') + '40' : '' }">
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
                        <p class="preview-desc" :class="{ 'preview-desc--placeholder': !form.deskripsi }">
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
                    <!-- Diff badge jika ada perubahan nama -->
                    <div v-if="isEdit && isDirty && originalForm.nama !== form.nama" class="diff-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="11" height="11">
                            <path d="M9 18V5l12-2v13" />
                            <circle cx="6" cy="18" r="3" />
                            <circle cx="18" cy="16" r="3" />
                        </svg>
                        Nama diubah dari
                        <strong>{{ originalForm.nama }}</strong>
                    </div>
                </div>

                <!-- Edit mode: statistik & info barang -->
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

                    <!-- ID info -->
                    <div class="id-row">
                        <span class="id-label">ID Kategori</span>
                        <span class="id-val">#{{ currentKategori.id }}</span>
                    </div>

                    <div v-if="currentKategori.barangs?.length" class="barang-mini-list">
                        <p class="list-label">Barang Terdaftar</p>
                        <div v-for="b in currentKategori.barangs.slice(0, 5)" :key="b.id" class="barang-mini-item">
                            <div class="barang-mini-dot" :class="kondisiDotClass(b.kondisi)"></div>
                            <span class="barang-mini-nama">{{ b.nama_barang ?? b.nama }}</span>
                            <span class="barang-mini-stok">{{ b.stok_tersedia ?? 0 }}</span>
                        </div>
                        <p v-if="currentKategori.barangs.length > 5" class="more-text">
                            +{{ currentKategori.barangs.length - 5 }} barang lainnya
                        </p>
                    </div>

                    <div class="warn-box" v-if="(currentKategori.barangs_count ?? 0) > 0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="13" height="13" style="flex-shrink:0;margin-top:1px">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        Kategori ini tidak dapat dihapus selama masih memiliki
                        <strong>{{ currentKategori.barangs_count }} barang</strong>.
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

        <!-- ── Modal Konfirmasi Discard ────────────────────────────────── -->
        <Transition name="modal-fade">
            <div v-if="showDiscardModal" class="modal-overlay" @click.self="showDiscardModal = false">
                <div class="modal">
                    <div class="modal-icon modal-icon--warn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="22" height="22">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>
                    <h3 class="modal-title">Buang Perubahan?</h3>
                    <p class="modal-body">
                        Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?
                    </p>
                    <div class="modal-actions">
                        <button class="btn-modal-cancel" @click="showDiscardModal = false">
                            Tetap di Sini
                        </button>
                        <button class="btn-modal-discard" @click="confirmDiscard">
                            Ya, Buang
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute, onBeforeRouteLeave } from 'vue-router'
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
        { name: 'Lamp', label: 'Lampu' },
        { name: 'Table2', label: 'Meja' },
        { name: 'BookMarked', label: 'Rak' },
        { name: 'LayoutDashboard', label: 'Ruangan' },
    ],
    'Peralatan': [
        { name: 'Wrench', label: 'Kunci' },
        { name: 'Hammer', label: 'Palu' },
        { name: 'Scissors', label: 'Gunting' },
        { name: 'Plug', label: 'Stop' },
        { name: 'Lightbulb', label: 'Lampu' },
        { name: 'Settings', label: 'Alat' },
        { name: 'Drill', label: 'Bor' },
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
        { name: 'ChefHat', label: 'Masak' },
    ],
    'Kebersihan': [
        { name: 'Trash2', label: 'Sampah' },
        { name: 'Wind', label: 'Kipas' },
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
        { name: 'Zap', label: 'Kilat' },
    ],
}

const ikonCategories = ['Semua', ...Object.keys(IKON_SETS)]
const activeIkonCat = ref('Semua')
const ikonSearch = ref('')

const allIkons = computed(() =>
    Object.entries(IKON_SETS).flatMap(([, icons]) => icons)
)

const filteredIkons = computed(() => {
    const q = ikonSearch.value.trim().toLowerCase()
    if (q) {
        return allIkons.value.filter(
            i => i.name.toLowerCase().includes(q) || i.label.toLowerCase().includes(q)
        )
    }
    if (activeIkonCat.value === 'Semua') return allIkons.value
    return IKON_SETS[activeIkonCat.value] || []
})

// ── Mode ─────────────────────────────────────────────────────────────
const isEdit = computed(() => !!route.params.id)
const currentKategori = ref(null)
const loadingData = ref(false)
const submitting = ref(false)
const isDirty = ref(false)

const form = ref({ nama: '', ikon: '', deskripsi: '' })
const originalForm = ref({ nama: '', ikon: '', deskripsi: '' })
const errors = ref({})

// Discard modal
const showDiscardModal = ref(false)
let pendingNavigation = null

// ── Guard: cegah keluar jika ada perubahan ──────────────────────────
onBeforeRouteLeave((to, from, next) => {
    if (isDirty.value && !submitting.value) {
        showDiscardModal.value = true
        pendingNavigation = next
        next(false)
    } else {
        next()
    }
})

function confirmDiscard() {
    isDirty.value = false
    showDiscardModal.value = false
    if (pendingNavigation) {
        pendingNavigation()
        pendingNavigation = null
    }
}

// ── Computed ──────────────────────────────────────────────────────────
const otherKategoris = computed(() => store.kategoris.slice(0, 8))

const barangRusakCount = computed(() =>
    (currentKategori.value?.barangs ?? []).filter(
        b => b.kondisi !== 'Baik'
    ).length
)

// ── Methods ───────────────────────────────────────────────────────────
async function loadData() {
    if (!store.kategoris.length) {
        await store.fetchKategoris()
    }
    if (!isEdit.value) return

    loadingData.value = true
    try {
        const data = await kategoriService.getOne(route.params.id)
        currentKategori.value = data

        const loaded = {
            nama: data.nama ?? '',
            ikon: data.ikon ?? '',
            deskripsi: data.deskripsi ?? '',
        }
        form.value = { ...loaded }
        originalForm.value = { ...loaded }
        isDirty.value = false
    } catch {
        toast.error('Gagal memuat data kategori.')
        router.push('/kategori')
    } finally {
        loadingData.value = false
    }
}

function resetForm() {
    form.value = { ...originalForm.value }
    isDirty.value = false
    errors.value = {}
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
    if (isEdit.value && !isDirty.value) return

    submitting.value = true

    const payload = {
        nama: form.value.nama.trim(),
        ikon: form.value.ikon || null,
        deskripsi: form.value.deskripsi.trim() || null,
    }

    try {
        if (isEdit.value) {
            await store.updateKategori(Number(route.params.id), payload)
            toast.success('Kategori berhasil diperbarui!')
        } else {
            await store.createKategori(payload)
            toast.success('Kategori berhasil ditambahkan!')
        }
        isDirty.value = false
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

// ── Helpers ───────────────────────────────────────────────────────────
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
    display: flex;
    align-items: center;
    gap: 8px;
}

.pg-title__badge {
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
}

.pg-title__badge--edit {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.pg-title__badge--add {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

.pg-sub {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0 0;
}

/* ── Loading ─────────────────────────────────────────────────────── */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 64px;
    gap: 14px;
    color: #9ca3af;
    font-size: 14px;
}

.loading-ring {
    animation: spin 1s linear infinite;
}

.ring-spin {
    transform-origin: 25px 25px;
    animation: spin 1s linear infinite;
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
    transition: border-color 0.2s;
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
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    color: #16a34a;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    padding: 2px 8px;
}

.form-card__dirty-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    color: #d97706;
    background: #fffbeb;
    border: 1px solid #fde68a;
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
    display: flex;
    align-items: center;
    gap: 6px;
}

.field-label__opt {
    font-size: 10.5px;
    font-weight: 500;
    color: #9ca3af;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    padding: 1px 6px;
    border-radius: 4px;
}

.required {
    color: #ef4444;
}

.field-input-wrap {
    position: relative;
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

.field-input-wrap .field-input {
    padding-right: 56px;
}

.field-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.field-input::placeholder {
    color: #c4c9d1;
}

.field-input--error {
    border-color: #fca5a5 !important;
    background: #fff5f5;
}

.field-textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.6;
}

.char-counter {
    position: absolute;
    right: 11px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    font-weight: 500;
    color: #9ca3af;
    pointer-events: none;
    transition: color 0.2s;
}

.char-counter--warn {
    color: #f59e0b;
}

.field-hint {
    font-size: 11.5px;
    color: #9ca3af;
    margin: 0;
    line-height: 1.5;
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
    transition: border-color 0.2s, background 0.2s;
}

.ikon-preview-bar--active {
    background: white;
    border-color: #d1fae5;
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

/* ── Form actions ─────────────────────────────────────────────────── */
.form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    align-items: center;
}

.btn-reset {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 9px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    background: white;
    font-size: 12.5px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    margin-right: auto;
}

.btn-reset:hover {
    background: #f9fafb;
    color: #374151;
}

.btn-cancel {
    padding: 10px 18px;
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
    opacity: 0.5;
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
    transition: all 0.3s;
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
    transition: background 0.3s;
}

.preview-nama {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
}

.preview-desc {
    font-size: 12px;
    color: #374151;
    margin: 0 0 12px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.preview-desc--placeholder {
    color: #9ca3af;
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

.diff-note {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
    padding: 7px 10px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    font-size: 11.5px;
    color: #1d4ed8;
    line-height: 1.4;
    flex-wrap: wrap;
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
    margin-bottom: 12px;
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

.id-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 10px;
    background: #f9fafb;
    border-radius: 7px;
    margin-bottom: 12px;
}

.id-label {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.id-val {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    font-family: 'Courier New', monospace;
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
    margin-bottom: 12px;
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

/* ── Modal ───────────────────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.modal {
    background: white;
    border-radius: 16px;
    padding: 24px;
    max-width: 360px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}

.modal-icon--warn {
    background: #fffbeb;
    color: #d97706;
}

.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px;
}

.modal-body {
    font-size: 13.5px;
    color: #6b7280;
    margin: 0 0 20px;
    line-height: 1.6;
}

.modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.btn-modal-cancel {
    padding: 9px 18px;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-modal-cancel:hover {
    background: #f9fafb;
}

.btn-modal-discard {
    padding: 9px 18px;
    border: none;
    border-radius: 9px;
    background: #d97706;
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-modal-discard:hover {
    background: #b45309;
}

/* Transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
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

    .form-actions {
        flex-wrap: wrap;
    }

    .btn-reset {
        margin-right: 0;
        width: 100%;
        justify-content: center;
        order: 3;
    }
}
</style>