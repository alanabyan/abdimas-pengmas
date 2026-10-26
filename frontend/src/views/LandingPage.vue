<template>
  <main class="public-page">
    <header class="site-header">
      <a class="brand" href="#top" aria-label="Abdimas Pengmas, beranda">
        <span class="brand-mark">AP</span>
        <span><strong>Abdimas Pengmas</strong><small>Kolaborasi untuk masyarakat</small></span>
      </a>
      <nav aria-label="Navigasi utama">
        <a href="#profil">Tentang</a>
        <a href="#program">Program</a>
        <a href="#infaq">Infaq & sedekah</a>
      </nav>
      <RouterLink class="login-link" to="/login"><LogIn :size="17" /> Login</RouterLink>
    </header>

    <section id="top" class="hero">
      <div class="hero-copy">
        <p class="eyebrow"><span></span> Gerakan kolaborasi warga</p>
        <h1>Menguatkan kepedulian, <em>menghadirkan manfaat.</em></h1>
        <p class="hero-description">Abdimas Pengmas menghubungkan semangat pengabdian dengan kebutuhan nyata masyarakat melalui kegiatan sosial, pendidikan, dan pemberdayaan.</p>
        <div class="hero-actions">
          <a class="button-primary" href="#infaq">Salurkan kebaikan <ArrowDownRight :size="18" /></a>
          <a class="text-link" href="#profil">Kenali organisasi <ArrowRight :size="16" /></a>
        </div>
        <div class="hero-note"><HeartHandshake :size="18" /><span>Berangkat dari kepedulian, tumbuh bersama masyarakat.</span></div>
      </div>
      <div class="hero-photo" role="img" aria-label="Kegiatan kebersamaan warga di ruang terbuka">
        <div class="photo-label"><span class="photo-dot"></span><span><strong>Bergerak bersama</strong><small>Dari kepedulian menjadi aksi</small></span></div>
        <span class="photo-caption">Abdimas Pengmas · Cikarang</span>
      </div>
    </section>

    <section id="profil" class="profile-section">
      <div class="section-kicker">Tentang kami <span>01</span></div>
      <div class="profile-grid">
        <h2>Pengabdian yang dekat dengan kehidupan sehari-hari.</h2>
        <div class="profile-copy">
          <p>Abdimas Pengmas adalah ruang gotong royong untuk menghadirkan program yang relevan, terbuka, dan berkelanjutan. Kami percaya perubahan yang berarti tumbuh ketika potensi warga bertemu dengan dukungan yang tepat.</p>
          <div class="values-list">
            <div><span class="value-number">01</span><strong>Peka pada kebutuhan</strong><small>Mendengar dan menyusun kegiatan bersama warga.</small></div>
            <div><span class="value-number">02</span><strong>Terbuka untuk kolaborasi</strong><small>Menghubungkan relawan, komunitas, dan mitra.</small></div>
            <div><span class="value-number">03</span><strong>Manfaat yang berkelanjutan</strong><small>Menguatkan kapasitas, bukan sekadar kegiatan.</small></div>
          </div>
        </div>
      </div>
    </section>

    <section id="program" class="program-band">
      <div class="section-kicker">Ruang gerak <span>02</span></div>
      <div class="program-grid">
        <article><BookOpenCheck :size="22" /><h3>Pendidikan & literasi</h3><p>Mendukung akses belajar, berbagi pengetahuan, dan ruang tumbuh bagi generasi muda.</p></article>
        <article><Sprout :size="22" /><h3>Pemberdayaan warga</h3><p>Mendorong inisiatif lokal yang memperkuat kemandirian dan potensi komunitas.</p></article>
        <article><HandHeart :size="22" /><h3>Aksi sosial</h3><p>Menggalang kepedulian dan menyalurkannya untuk kebutuhan yang paling berarti.</p></article>
      </div>
    </section>

    <section id="infaq" class="donation-section">
      <div class="donation-intro">
        <div class="section-kicker">Titipkan kebaikan <span>03</span></div>
        <h2>Satu niat baik, banyak kemungkinan manfaat.</h2>
        <p>Isi formulir infaq atau sedekah. Bendahara kami akan menghubungi Anda untuk konfirmasi penyaluran. Pengiriman formulir ini belum merupakan bukti pembayaran.</p>
        <div class="donation-promise"><ShieldCheck :size="20" /><span>Data digunakan untuk menindaklanjuti pengajuan Anda.</span></div>
      </div>

      <form class="donation-form" @submit.prevent="submitInfaq">
        <div class="form-heading"><div><span>FORMULIR DONASI</span><h3>Infaq & sedekah</h3></div><HandCoins :size="25" /></div>
        <div class="kind-switch" role="group" aria-label="Jenis donasi">
          <button type="button" :class="{ selected: form.jenis === 'infaq' }" @click="form.jenis = 'infaq'">Infaq</button>
          <button type="button" :class="{ selected: form.jenis === 'sedekah' }" @click="form.jenis = 'sedekah'">Sedekah</button>
        </div>
        <label>Nama lengkap<input v-model.trim="form.nama" required maxlength="150" autocomplete="name" placeholder="Nama Anda" /></label>
        <div class="field-pair">
          <label>Nomor WhatsApp<input v-model.trim="form.telepon" required maxlength="30" autocomplete="tel" inputmode="tel" placeholder="08xx xxxx xxxx" /></label>
          <label>Email <span>(opsional)</span><input v-model.trim="form.email" type="email" maxlength="255" autocomplete="email" placeholder="nama@email.com" /></label>
        </div>
        <label>Nominal (Rp)<div class="amount-input"><span>Rp</span><input v-model.number="form.jumlah" type="number" min="1000" max="1000000000" step="1000" required inputmode="numeric" placeholder="100000" /></div></label>
        <div class="amount-presets" aria-label="Pilihan nominal">
          <button v-for="amount in presets" :key="amount" type="button" @click="form.jumlah = amount">{{ formatRupiah(amount) }}</button>
        </div>
        <label>Pesan <span>(opsional)</span><textarea v-model.trim="form.catatan" maxlength="1000" rows="3" placeholder="Titip doa atau catatan untuk pengurus"></textarea></label>
        <p v-if="errorMessage" class="form-message error" role="alert">{{ errorMessage }}</p>
        <p v-if="successMessage" class="form-message success" role="status">{{ successMessage }}</p>
        <button class="submit-button" type="submit" :disabled="submitting">
          {{ submitting ? 'Mengirim pengajuan...' : 'Kirim pengajuan' }} <ArrowRight v-if="!submitting" :size="18" />
        </button>
        <p class="form-footnote">Tidak ada pembayaran yang diproses melalui formulir ini.</p>
      </form>
    </section>

    <footer class="site-footer">
      <a class="brand footer-brand" href="#top"><span class="brand-mark">AP</span><span><strong>Abdimas Pengmas</strong><small>Kolaborasi untuk masyarakat</small></span></a>
      <span>Bergerak bersama, bertumbuh bersama.</span>
      <a href="#top">Kembali ke atas <ArrowUpRight :size="15" /></a>
    </footer>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ArrowDownRight, ArrowRight, ArrowUpRight, BookOpenCheck, HandCoins, HandHeart, HeartHandshake, LogIn, ShieldCheck, Sprout } from 'lucide-vue-next'
import infaqService from '@/services/infaqService'

const presets = [25000, 50000, 100000, 250000]
const form = reactive({ nama: '', telepon: '', email: '', jumlah: 100000, jenis: 'infaq', catatan: '' })
const submitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 0 }).format(value)
}

async function submitInfaq() {
  submitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await infaqService.submit({ ...form })
    successMessage.value = response.message
    Object.assign(form, { nama: '', telepon: '', email: '', jumlah: 100000, jenis: 'infaq', catatan: '' })
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || 'Pengajuan belum dapat dikirim. Silakan coba kembali.'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap');

:global(html) { scroll-behavior: smooth; }
.public-page { --ink: #1e3028; --muted: #64736b; --green: #176b4b; --green-dark: #104d37; --lime: #d8e9ae; --line: #dce4dc; color: var(--ink); font-family: 'DM Sans', sans-serif; background: #f6f7f2; }
.site-header { height: 82px; max-width: 1240px; margin: auto; padding: 0 34px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
.brand { display: inline-flex; align-items: center; gap: 11px; color: var(--ink); text-decoration: none; min-width: max-content; }
.brand-mark { width: 39px; height: 39px; display: grid; place-items: center; background: var(--green); color: #fff; font: 800 13px 'Manrope', sans-serif; border-radius: 50% 50% 50% 8px; }
.brand strong, .brand small { display: block; }
.brand strong { font: 800 14px 'Manrope', sans-serif; }
.brand small { color: var(--muted); font-size: 10px; margin-top: 2px; }
.site-header nav { display: flex; align-items: center; gap: 30px; }
.site-header nav a, .site-footer > a:last-child { color: #48584e; text-decoration: none; font-size: 12px; font-weight: 600; }
.site-header nav a:hover, .site-footer > a:last-child:hover { color: var(--green); }
.login-link { display: inline-flex; align-items: center; gap: 8px; color: var(--green-dark); text-decoration: none; font-weight: 700; font-size: 12px; border: 1px solid #b9ccbd; padding: 10px 15px; border-radius: 4px; }
.hero { max-width: 1240px; min-height: 560px; margin: auto; padding: 40px 34px 66px; display: grid; grid-template-columns: .92fr 1.08fr; align-items: center; gap: 48px; }
.hero-copy { padding: 24px 0 10px 4px; }
.eyebrow, .section-kicker { color: var(--green); font-size: 10px; letter-spacing: 0; text-transform: uppercase; font-weight: 800; }
.eyebrow { display: flex; align-items: center; gap: 9px; margin: 0 0 22px; }
.eyebrow span, .photo-dot { width: 7px; height: 7px; background: #bd7c39; border-radius: 50%; }
h1, h2, h3, p { margin-top: 0; }
h1 { max-width: 550px; margin-bottom: 20px; font: 800 54px/1.08 'Manrope', sans-serif; letter-spacing: 0; }
h1 em { color: var(--green); font-style: normal; }
.hero-description { max-width: 470px; color: #637168; font-size: 15px; line-height: 1.8; }
.hero-actions { display: flex; align-items: center; gap: 25px; margin-top: 29px; }
.button-primary, .submit-button { display: inline-flex; align-items: center; justify-content: center; gap: 15px; background: var(--green); color: white; border: 0; border-radius: 3px; text-decoration: none; font: 700 12px 'DM Sans', sans-serif; padding: 15px 19px; transition: background .2s, transform .2s; }
.button-primary:hover, .submit-button:hover { background: var(--green-dark); transform: translateY(-1px); }
.text-link { color: var(--green-dark); text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; gap: 7px; align-items: center; }
.hero-note { display: flex; gap: 10px; align-items: center; margin-top: 43px; color: #758078; font-size: 11px; }
.hero-note svg { color: #b47c36; }
.hero-photo { min-height: 454px; position: relative; overflow: hidden; background: #899b7f url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=1400&q=85') center 43% / cover; }
.hero-photo::after { position: absolute; inset: 0; content: ''; background: linear-gradient(180deg, transparent 54%, rgba(15, 38, 27, .5)); }
.photo-label { position: absolute; z-index: 1; left: 20px; bottom: 54px; display: flex; align-items: center; gap: 10px; background: #f7f7ef; padding: 12px 15px; }
.photo-label strong, .photo-label small { display: block; }
.photo-label strong { font: 700 12px 'Manrope', sans-serif; }
.photo-label small { color: var(--muted); font-size: 10px; margin-top: 3px; }
.photo-caption { z-index: 1; position: absolute; right: 19px; bottom: 22px; color: white; font-size: 10px; }
.profile-section, .program-band { padding: 74px max(34px, calc((100% - 1172px) / 2)); }
.profile-section { background: #fff; }
.section-kicker { display: flex; justify-content: space-between; max-width: 1172px; padding-bottom: 13px; border-bottom: 1px solid var(--line); }
.section-kicker span { color: #a8b2a9; font: 500 11px 'DM Sans', sans-serif; }
.profile-grid { max-width: 1172px; margin: 43px auto 0; display: grid; grid-template-columns: .85fr 1.15fr; gap: 80px; }
h2 { font: 800 35px/1.2 'Manrope', sans-serif; letter-spacing: 0; }
.profile-grid h2 { max-width: 430px; }
.profile-copy > p { color: var(--muted); font-size: 14px; line-height: 1.8; max-width: 610px; }
.values-list { display: grid; grid-template-columns: repeat(3, 1fr); margin-top: 30px; border-top: 1px solid var(--line); }
.values-list > div { padding: 18px 15px 0 0; }
.value-number { display: block; color: #b47c36; font: 700 11px 'Manrope', sans-serif; margin-bottom: 15px; }
.values-list strong, .values-list small { display: block; }
.values-list strong { font: 700 12px 'Manrope', sans-serif; }
.values-list small { color: var(--muted); font-size: 10px; line-height: 1.6; margin-top: 7px; }
.program-band { background: #e9eee4; }
.program-grid { max-width: 1172px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 38px; margin: 39px auto 6px; }
.program-grid article { border-top: 2px solid #95ad94; padding: 20px 15px 0 0; }
.program-grid article svg { color: var(--green); }
.program-grid h3 { margin: 17px 0 8px; font: 700 16px 'Manrope', sans-serif; }
.program-grid p { max-width: 300px; color: #68756c; font-size: 12px; line-height: 1.75; margin-bottom: 0; }
.donation-section { max-width: 1240px; padding: 88px 34px 96px; margin: auto; display: grid; grid-template-columns: 1fr 1fr; align-items: start; gap: 90px; }
.donation-intro .section-kicker { border-bottom: 0; padding: 0; }
.donation-intro h2 { max-width: 460px; margin: 32px 0 15px; }
.donation-intro > p { max-width: 440px; color: var(--muted); font-size: 13px; line-height: 1.8; }
.donation-promise { display: flex; gap: 10px; align-items: center; margin-top: 32px; color: var(--green-dark); font-size: 11px; }
.donation-promise svg { flex: none; }
.donation-form { background: white; border: 1px solid #e4e8df; padding: 29px; box-shadow: 0 14px 35px rgba(29, 49, 36, .06); }
.form-heading { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); padding-bottom: 17px; margin-bottom: 20px; color: var(--green); }
.form-heading span { color: #849087; font-size: 9px; font-weight: 800; letter-spacing: 0; }
.form-heading h3 { color: var(--ink); margin: 5px 0 0; font: 800 20px 'Manrope', sans-serif; }
.kind-switch { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid var(--line); margin-bottom: 18px; }
.kind-switch button { border: 0; padding: 10px; background: white; color: #69766d; font: 600 11px 'DM Sans', sans-serif; cursor: pointer; }
.kind-switch button + button { border-left: 1px solid var(--line); }
.kind-switch button.selected { background: #eaf2e9; color: var(--green-dark); }
.donation-form label { display: block; color: #435349; font-size: 10px; font-weight: 700; margin-bottom: 14px; }
.donation-form label > span { color: #8b978e; font-weight: 400; }
.donation-form input, .donation-form textarea { display: block; box-sizing: border-box; width: 100%; margin-top: 7px; border: 1px solid #dce4dc; border-radius: 2px; padding: 11px 12px; background: #fcfdfb; color: var(--ink); font: 400 12px 'DM Sans', sans-serif; outline: none; }
.donation-form input:focus, .donation-form textarea:focus { border-color: #5c9674; box-shadow: 0 0 0 2px rgba(23, 107, 75, .1); }
.donation-form input::placeholder, .donation-form textarea::placeholder { color: #a3ada5; }
.field-pair { display: grid; grid-template-columns: 1fr 1fr; gap: 13px; }
.amount-input { display: flex; align-items: center; margin-top: 7px; border: 1px solid #dce4dc; background: #fcfdfb; }
.amount-input:focus-within { border-color: #5c9674; box-shadow: 0 0 0 2px rgba(23, 107, 75, .1); }
.amount-input span { padding-left: 12px; color: #829087; font-size: 11px; }
.donation-form .amount-input input { border: 0; margin: 0; box-shadow: none; }
.amount-presets { display: flex; flex-wrap: wrap; gap: 7px; margin: -6px 0 16px; }
.amount-presets button { border: 1px solid #dce4dc; background: white; color: #53645a; border-radius: 2px; padding: 6px 9px; font: 600 10px 'DM Sans', sans-serif; cursor: pointer; }
.amount-presets button:hover { border-color: var(--green); color: var(--green); }
.donation-form textarea { resize: vertical; min-height: 72px; }
.submit-button { width: 100%; cursor: pointer; }
.submit-button:disabled { opacity: .65; cursor: wait; transform: none; }
.form-message { padding: 10px 11px; font-size: 11px; line-height: 1.5; }
.form-message.error { color: #a5352b; background: #fff1ef; }
.form-message.success { color: #18583c; background: #eaf5eb; }
.form-footnote { color: #8a968d; text-align: center; font-size: 9px; margin: 11px 0 0; }
.site-footer { min-height: 90px; border-top: 1px solid var(--line); padding: 18px max(34px, calc((100% - 1172px) / 2)); display: flex; align-items: center; justify-content: space-between; gap: 20px; }
.site-footer > span { color: var(--muted); font-size: 10px; }
.site-footer > a:last-child { display: inline-flex; gap: 6px; align-items: center; }

@media (max-width: 850px) {
  .site-header { padding: 0 22px; }
  .site-header nav { gap: 16px; }
  .hero { padding: 30px 22px 52px; gap: 28px; grid-template-columns: 1fr 1fr; }
  h1 { font-size: 43px; }
  .hero-photo { min-height: 390px; }
  .profile-section, .program-band { padding-left: 22px; padding-right: 22px; }
  .profile-grid { gap: 38px; }
  .donation-section { padding: 72px 22px; gap: 38px; }
  .site-footer { padding-left: 22px; padding-right: 22px; }
}
@media (max-width: 620px) {
  .site-header { height: 70px; padding: 0 16px; }
  .brand small, .site-header nav { display: none; }
  .brand strong { font-size: 12px; }
  .brand-mark { width: 35px; height: 35px; }
  .login-link { padding: 9px 12px; }
  .hero { display: flex; flex-direction: column; align-items: stretch; padding: 37px 18px 46px; gap: 30px; }
  .hero-copy { padding: 0; }
  h1 { font-size: 40px; max-width: 500px; }
  .hero-description { font-size: 13px; }
  .hero-actions { gap: 17px; flex-wrap: wrap; }
  .hero-note { margin-top: 28px; }
  .hero-photo { min-height: 330px; }
  .profile-section, .program-band { padding: 54px 18px; }
  .profile-grid { grid-template-columns: 1fr; gap: 20px; margin-top: 30px; }
  h2 { font-size: 29px; }
  .values-list { grid-template-columns: 1fr; }
  .values-list > div { display: grid; grid-template-columns: 35px 1fr; padding: 14px 0 0; }
  .value-number { grid-row: span 2; margin: 0; }
  .program-grid { grid-template-columns: 1fr; gap: 24px; margin-top: 28px; }
  .program-grid article { padding-top: 15px; }
  .program-grid h3 { margin-top: 10px; }
  .donation-section { grid-template-columns: 1fr; gap: 30px; padding: 58px 18px 68px; }
  .donation-intro h2 { margin-top: 25px; }
  .donation-form { padding: 20px 17px; }
  .field-pair { grid-template-columns: 1fr; gap: 0; }
  .site-footer { padding: 18px; flex-wrap: wrap; }
  .site-footer > span { order: 3; width: 100%; }
}
</style>