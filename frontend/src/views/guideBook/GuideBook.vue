<template>
    <div class="guidebook-page">

        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="page-top">
            <div class="page-top__left">
                <div class="page-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        width="20" height="20">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="pg-title">Panduan Penggunaan Sistem</h1>
                    <p class="pg-sub">Guidebook resmi SI MAS RAYYAN — Sistem Inventaris Masjid Ar-Rayyan</p>
                </div>
            </div>

            <a :href="pdfUrl" download="Guidebook_SI_MAS_RAYYAN.pdf" class="btn-download">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    width="14" height="14">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Unduh PDF
            </a>
        </div>

        <!-- ── Info strip ──────────────────────────────────────────────── -->
        <div class="info-strip">
            <div class="info-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    width="13" height="13" style="color:#16a34a">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                <span>Versi terbaru</span>
            </div>
            <div class="info-sep">·</div>
            <div class="info-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    width="13" height="13" style="color:#16a34a">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
                <span>16 halaman panduan</span>
            </div>
            <div class="info-sep">·</div>
            <div class="info-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    width="13" height="13" style="color:#16a34a">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <span>Khusus Marbot / Pengurus</span>
            </div>
        </div>

        <!-- ── TOC + Viewer layout ─────────────────────────────────────── -->
        <div class="main-layout">

            <!-- Sidebar TOC -->
            <aside class="toc-sidebar">
                <p class="toc-label">Daftar Isi</p>
                <nav class="toc-nav">
                    <button v-for="(item, i) in tocItems" :key="i" class="toc-item"
                        :class="{ 'toc-item--active': activeToc === i }" @click="goToPage(item.page, i)">
                        <span class="toc-num">{{ String(i + 1).padStart(2, '0') }}</span>
                        <span class="toc-text">{{ item.label }}</span>
                        <svg v-if="activeToc === i" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" width="12" height="12"
                            style="color:#16a34a; flex-shrink:0; margin-left:auto;">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </button>
                </nav>

                <!-- Quick tip -->
                <div class="toc-tip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="14" height="14" style="color:#16a34a; flex-shrink:0;">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <p>Klik item daftar isi untuk loncat ke halaman yang sesuai.</p>
                </div>
            </aside>

            <!-- PDF Viewer -->
            <div class="viewer-wrap">

                <!-- Toolbar -->
                <div class="viewer-toolbar">
                    <div class="toolbar-left">
                        <button class="tool-btn" @click="prevPage" :disabled="currentPage <= 1"
                            title="Halaman Sebelumnya">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" width="15" height="15">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </button>
                        <div class="page-indicator">
                            <input type="number" class="page-input" :value="currentPage" :min="1" :max="totalPages"
                                @change="onPageInput" />
                            <span class="page-total">/ {{ totalPages }}</span>
                        </div>
                        <button class="tool-btn" @click="nextPage" :disabled="currentPage >= totalPages"
                            title="Halaman Berikutnya">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" width="15" height="15">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                    </div>

                    <div class="toolbar-center">
                        <span class="toolbar-title">Guidebook SI MAS RAYYAN</span>
                    </div>

                    <div class="toolbar-right">
                        <button class="tool-btn" @click="zoomOut" :disabled="zoom <= 60" title="Perkecil">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                <line x1="8" y1="11" x2="14" y2="11" />
                            </svg>
                        </button>
                        <span class="zoom-label">{{ zoom }}%</span>
                        <button class="tool-btn" @click="zoomIn" :disabled="zoom >= 200" title="Perbesar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                <line x1="11" y1="8" x2="11" y2="14" />
                                <line x1="8" y1="11" x2="14" y2="11" />
                            </svg>
                        </button>
                        <div class="tool-sep"></div>
                        <button class="tool-btn" @click="toggleFullscreen" title="Layar Penuh">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <polyline points="15 3 21 3 21 9" />
                                <polyline points="9 21 3 21 3 15" />
                                <line x1="21" y1="3" x2="14" y2="10" />
                                <line x1="3" y1="21" x2="10" y2="14" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Canvas area -->
                <div class="canvas-area" ref="canvasAreaRef">
                    <!-- Loading state -->
                    <div v-if="pdfLoading" class="pdf-loading">
                        <span class="spinner-pdf"></span>
                        <p>Memuat dokumen...</p>
                    </div>

                    <!-- Error state -->
                    <div v-else-if="pdfError" class="pdf-error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40"
                            height="40" style="color:#d1d5db; margin-bottom:12px">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="12" y1="18" x2="12" y2="12" />
                            <line x1="9" y1="15" x2="15" y2="15" />
                        </svg>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px">Gagal memuat PDF</p>
                        <p style="font-size:12px; color:#9ca3af">Pastikan file guidebook tersedia di server.</p>
                    </div>

                    <!-- PDF Canvas -->
                    <div v-show="!pdfLoading && !pdfError" class="canvas-wrapper"
                        :style="{ transform: `scale(${zoom / 100})`, transformOrigin: 'top center' }">
                        <canvas ref="pdfCanvas" class="pdf-canvas"></canvas>
                    </div>
                </div>

                <!-- Bottom nav -->
                <div class="bottom-nav">
                    <button class="btn-nav" @click="prevPage" :disabled="currentPage <= 1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="14" height="14">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Sebelumnya
                    </button>
                    <div class="bottom-dots">
                        <span v-for="p in totalPages" :key="p" class="dot" :class="{ 'dot--active': p === currentPage }"
                            @click="goToPage(p, -1)"></span>
                    </div>
                    <button class="btn-nav btn-nav--next" @click="nextPage" :disabled="currentPage >= totalPages">
                        Berikutnya
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="14" height="14">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// ── Config ────────────────────────────────────────────────────────────────────
// Ganti path ini sesuai lokasi file PDF di server Anda
const pdfUrl = ref('/guidebook/Guidebook_SI_MAS_RAYYAN.pdf')

// ── State ─────────────────────────────────────────────────────────────────────
const pdfCanvas = ref(null)
const canvasAreaRef = ref(null)
const pdfLoading = ref(true)
const pdfError = ref(false)
const currentPage = ref(1)
const totalPages = ref(18)
const zoom = ref(100)
const activeToc = ref(0)

let pdfDoc = null
let rendering = false

// ── TOC ───────────────────────────────────────────────────────────────────────
const tocItems = [
    { label: 'Cara Masuk ke Sistem (Login)', page: 3 },
    { label: 'Dashboard (Pusat Informasi)', page: 3 },
    { label: 'Pengaturan dan Notifikasi', page: 4 },
    { label: 'Tombol Akses Cepat (Quick Access)', page: 5 },
    { label: 'Mengelola Daftar Barang (Inventaris)', page: 6 },
    { label: 'Mengelola Kategori Barang', page: 8 },
    { label: 'Mengelola Data Warga', page: 10 },
    { label: 'Mengelola Transaksi Peminjaman', page: 12 },
    { label: 'Mengelola Pengembalian Barang', page: 14 },
    { label: 'Mengelola Laporan dan Rekap Data', page: 16 },
    { label: 'Mengelola Akun Pengurus', page: 17 },
]

// ── PDF.js loader ─────────────────────────────────────────────────────────────
async function loadPdfJs() {
    if (window.pdfjsLib) return window.pdfjsLib
    return new Promise((resolve, reject) => {
        const script = document.createElement('script')
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js'
        script.onload = () => {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'
            resolve(window.pdfjsLib)
        }
        script.onerror = reject
        document.head.appendChild(script)
    })
}

async function initPdf() {
    pdfLoading.value = true
    pdfError.value = false
    try {
        const pdfjsLib = await loadPdfJs()
        pdfDoc = await pdfjsLib.getDocument(pdfUrl.value).promise
        totalPages.value = pdfDoc.numPages
        await renderPage(currentPage.value)
    } catch (e) {
        console.error('PDF load error:', e)
        pdfError.value = true
    } finally {
        pdfLoading.value = false
    }
}

async function renderPage(num) {
    if (!pdfDoc || rendering) return
    rendering = true
    try {
        const page = await pdfDoc.getPage(num)
        const viewport = page.getViewport({ scale: 1.5 })
        const canvas = pdfCanvas.value
        const ctx = canvas.getContext('2d')
        canvas.height = viewport.height
        canvas.width = viewport.width
        await page.render({ canvasContext: ctx, viewport }).promise
        updateActiveToc(num)
    } catch (e) {
        console.error('Render error:', e)
    } finally {
        rendering = false
    }
}

function updateActiveToc(page) {
    // Find which TOC section this page belongs to
    let idx = 0
    for (let i = 0; i < tocItems.length; i++) {
        if (page >= tocItems[i].page) idx = i
    }
    activeToc.value = idx
}

// ── Navigation ────────────────────────────────────────────────────────────────
async function goToPage(page, tocIdx) {
    if (page < 1 || page > totalPages.value) return
    currentPage.value = page
    if (tocIdx >= 0) activeToc.value = tocIdx
    await renderPage(page)
    canvasAreaRef.value?.scrollTo({ top: 0, behavior: 'smooth' })
}

async function prevPage() {
    if (currentPage.value <= 1) return
    currentPage.value--
    await renderPage(currentPage.value)
    canvasAreaRef.value?.scrollTo({ top: 0, behavior: 'smooth' })
}

async function nextPage() {
    if (currentPage.value >= totalPages.value) return
    currentPage.value++
    await renderPage(currentPage.value)
    canvasAreaRef.value?.scrollTo({ top: 0, behavior: 'smooth' })
}

async function onPageInput(e) {
    const val = parseInt(e.target.value)
    if (!isNaN(val) && val >= 1 && val <= totalPages.value) {
        await goToPage(val, -1)
    }
}

// ── Zoom ─────────────────────────────────────────────────────────────────────
function zoomIn() { if (zoom.value < 200) zoom.value += 20 }
function zoomOut() { if (zoom.value > 60) zoom.value -= 20 }

// ── Fullscreen ────────────────────────────────────────────────────────────────
function toggleFullscreen() {
    const el = canvasAreaRef.value
    if (!document.fullscreenElement) {
        el?.requestFullscreen()
    } else {
        document.exitFullscreen()
    }
}

// ── Keyboard navigation ───────────────────────────────────────────────────────
function onKeydown(e) {
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') nextPage()
    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') prevPage()
}

onMounted(() => {
    initPdf()
    window.addEventListener('keydown', onKeydown)
})
</script>

<style scoped>
.guidebook-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
    min-height: 100%;
}

/* ── Header ───────────────────────────────────────────────────────────────── */
.page-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

.page-top__left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.page-icon {
    width: 44px;
    height: 44px;
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16a34a;
    flex-shrink: 0;
}

.pg-title {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.pg-sub {
    font-size: 12.5px;
    color: #6b7280;
    margin: 2px 0 0;
}

.btn-download {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 16px;
    border-radius: 9px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
    font-family: inherit;
    flex-shrink: 0;
}

.btn-download:hover {
    background: #15803d;
}

/* ── Info strip ───────────────────────────────────────────────────────────── */
.info-strip {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 9px 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #166534;
}

.info-sep {
    color: #86efac;
    font-size: 14px;
}

/* ── Main layout ──────────────────────────────────────────────────────────── */
.main-layout {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

/* ── TOC Sidebar ──────────────────────────────────────────────────────────── */
.toc-sidebar {
    width: 220px;
    flex-shrink: 0;
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px;
    position: sticky;
    top: 16px;
}

.toc-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #9ca3af;
    margin: 0 0 10px;
}

.toc-nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-bottom: 14px;
}

.toc-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 8px;
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
    width: 100%;
    font-family: inherit;
    transition: background 0.12s;
}

.toc-item:hover {
    background: #f9fafb;
}

.toc-item--active {
    background: #f0fdf4 !important;
}

.toc-num {
    font-size: 10px;
    font-weight: 700;
    color: #d1d5db;
    font-variant-numeric: tabular-nums;
    flex-shrink: 0;
}

.toc-item--active .toc-num {
    color: #16a34a;
}

.toc-text {
    font-size: 11.5px;
    font-weight: 500;
    color: #6b7280;
    line-height: 1.35;
    flex: 1;
}

.toc-item--active .toc-text {
    color: #15803d;
    font-weight: 700;
}

.toc-tip {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    background: #f9fafb;
    border-radius: 8px;
    padding: 9px 10px;
    border: 1px solid #f0f0f0;
}

.toc-tip p {
    font-size: 11px;
    color: #9ca3af;
    line-height: 1.5;
    margin: 0;
}

/* ── Viewer ───────────────────────────────────────────────────────────────── */
.viewer-wrap {
    flex: 1;
    min-width: 0;
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Toolbar */
.viewer-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid #f0f0f0;
    background: #f9fafb;
    gap: 12px;
}

.toolbar-left,
.toolbar-right {
    display: flex;
    align-items: center;
    gap: 6px;
}

.toolbar-center {
    flex: 1;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.tool-btn {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    border: 1.5px solid #e5e7eb;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    transition: all 0.15s;
    flex-shrink: 0;
}

.tool-btn:hover:not(:disabled) {
    background: #f0fdf4;
    border-color: #86efac;
    color: #16a34a;
}

.tool-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.page-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 7px;
    padding: 4px 10px;
}

.page-input {
    width: 34px;
    border: none;
    outline: none;
    font-size: 12px;
    font-weight: 700;
    color: #111827;
    text-align: center;
    font-family: inherit;
    background: transparent;
    -moz-appearance: textfield;
}

.page-input::-webkit-outer-spin-button,
.page-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
}

.page-total {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

.zoom-label {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    min-width: 36px;
    text-align: center;
}

.tool-sep {
    width: 1px;
    height: 20px;
    background: #e5e7eb;
    margin: 0 2px;
}

/* Canvas area */
.canvas-area {
    flex: 1;
    overflow: auto;
    background: #e5e7eb;
    min-height: 500px;
    padding: 24px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}

.canvas-wrapper {
    display: flex;
    justify-content: center;
}

.pdf-canvas {
    box-shadow: 0 4px 32px rgba(0, 0, 0, 0.18);
    border-radius: 4px;
    display: block;
    max-width: 100%;
    height: auto;
}

/* States */
.pdf-loading,
.pdf-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 80px 24px;
    color: #9ca3af;
    font-size: 13px;
    width: 100%;
}

.spinner-pdf {
    width: 32px;
    height: 32px;
    border: 3px solid #e5e7eb;
    border-top-color: #16a34a;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Bottom nav */
.bottom-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    border-top: 1px solid #f0f0f0;
    background: #f9fafb;
}

.btn-nav {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: white;
    color: #374151;
    font-size: 12.5px;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s;
}

.btn-nav:hover:not(:disabled) {
    background: #f0fdf4;
    border-color: #86efac;
    color: #16a34a;
}

.btn-nav:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.btn-nav--next {
    background: #16a34a;
    color: white;
    border-color: #16a34a;
}

.btn-nav--next:hover:not(:disabled) {
    background: #15803d;
    border-color: #15803d;
    color: white;
}

.bottom-dots {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
    justify-content: center;
    max-width: 300px;
}

.dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #d1d5db;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.dot--active {
    background: #16a34a;
    width: 18px;
    border-radius: 4px;
}

/* ── Responsive ───────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .main-layout {
        flex-direction: column;
    }

    .toc-sidebar {
        width: 100%;
        position: static;
    }

    .toc-nav {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .toolbar-center {
        display: none;
    }

    .canvas-area {
        padding: 12px;
    }

    .info-sep {
        display: none;
    }
}
</style>