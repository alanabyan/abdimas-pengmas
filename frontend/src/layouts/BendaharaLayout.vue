<script setup>
import { computed, ref } from 'vue'
import { RouterView, RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  LayoutDashboard,
  Wallet,
  CircleDollarSign,
  Landmark,
  LogOut,
  Bell,
  Menu,
  Settings,
  BarChart3,
} from 'lucide-vue-next'

const authStore = useAuthStore()
const router = useRouter()

const mobileOpen = ref(false)
const showNotif = ref(false)
const showProfileMenu = ref(false)

const initials = computed(() => {
  const nama = authStore.user?.nama || authStore.user?.nama_bendahara || 'Bendahara'
  return nama.split(' ').map((item) => item[0]).join('').toUpperCase().slice(0, 2)
})

const menuItems = [
  { label: 'Dashboard', to: '/bendahara/dashboard', icon: LayoutDashboard },
  { label: 'Keuangan', to: '/bendahara/keuangan', icon: Wallet },
  { label: 'Qurban', to: '/bendahara/qurban', icon: CircleDollarSign },
  { label: 'Laporan', to: '/bendahara/laporan', icon: BarChart3 },
  { label: 'Kas', to: '/bendahara/kas', icon: Landmark },
]

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'Login' })
}
</script>

<template>
  <div class="bendahara-shell">
    <aside
      class="sidebar"
      :class="mobileOpen ? 'sidebar-open' : ''"
    >
      <div class="brand-block">
        <div class="brand-mark">AP</div>
        <div>
          <p class="brand-name">Abdimas Pengmas</p>
          <p class="brand-subtitle">Bendahara</p>
        </div>
      </div>

      <nav class="nav-menu">
        <RouterLink
          v-for="item in menuItems"
          :key="item.to"
          :to="item.to"
          class="nav-item"
          active-class="router-link-active"
        >
          <component :is="item.icon" :size="18" />
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>

      <div class="profile-box">
        <div class="avatar">{{ initials }}</div>
        <div class="profile-text">
          <strong>{{ authStore.user?.nama || authStore.user?.nama_bendahara || 'Bendahara' }}</strong>
          <span>{{ authStore.user?.role || 'bendahara' }}</span>
        </div>
        <button type="button" class="logout-button" @click="handleLogout" aria-label="Keluar">
          <LogOut :size="16" />
        </button>
      </div>
    </aside>

    <div v-if="mobileOpen" class="backdrop" @click="mobileOpen = false" />

    <div class="content-column">
      <header class="topbar">
        <button class="menu-button md-hidden" type="button" @click="mobileOpen = !mobileOpen">
          <Menu :size="18" />
        </button>

        <div class="topbar-spacer" />

        <div class="topbar-tools">
          <div class="notification-wrap">
            <button
              type="button"
              class="icon-button"
              @click="showNotif = !showNotif; showProfileMenu = false"
              aria-label="Notifikasi"
            >
              <Bell :size="18" />
              <span class="badge-dot" />
            </button>

            <div v-if="showNotif" class="dropdown-panel notification-panel">
              <div class="dropdown-header">
                <h3>Notifikasi</h3>
                <span>1 Baru</span>
              </div>
              <div class="dropdown-content">
                <p>Belum ada pemberitahuan baru untuk bendahara.</p>
              </div>
            </div>
          </div>

          <div class="profile-wrap">
            <button
              type="button"
              class="profile-button"
              @click="showProfileMenu = !showProfileMenu; showNotif = false"
            >
              <span class="profile-avatar">{{ initials }}</span>
            </button>

            <div v-if="showProfileMenu" class="dropdown-panel profile-panel">
              <div class="dropdown-header profile-header">
                <div>
                  <p class="panel-label">Akun Bendahara</p>
                  <strong>{{ authStore.user?.nama || authStore.user?.nama_bendahara || 'Bendahara' }}</strong>
                </div>
              </div>
              <div class="dropdown-content actions">
                <RouterLink to="/bendahara/dashboard" class="dropdown-link" @click="showProfileMenu = false">
                  <LayoutDashboard :size="15" />
                  <span>Dashboard</span>
                </RouterLink>
                <RouterLink to="/bendahara/keuangan" class="dropdown-link" @click="showProfileMenu = false">
                  <Wallet :size="15" />
                  <span>Keuangan</span>
                </RouterLink>
                <button type="button" class="dropdown-link danger" @click="handleLogout">
                  <LogOut :size="15" />
                  <span>Keluar</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main class="main-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<style scoped>
.bendahara-shell {
  min-height: 100vh;
  display: flex;
  background: #f2f6f3;
  color: #1d2e26;
  font-family: 'DM Sans', sans-serif;
}

.sidebar {
  width: 260px;
  background: #ffffff;
  border-right: 1px solid #dfe9e0;
  display: flex;
  flex-direction: column;
  padding: 22px 18px 16px;
  transition: transform 0.25s ease;
  z-index: 50;
}

.brand-block {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 8px 18px;
  border-bottom: 1px solid #ebf1eb;
}

.brand-mark {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #176b4b, #2b8a66);
  color: white;
  font-weight: 800;
}

.brand-name {
  margin: 0;
  font-size: 12px;
  font-weight: 800;
  color: #183a2d;
}

.brand-subtitle {
  margin: 4px 0 0;
  font-size: 10px;
  letter-spacing: 0.12em;
  color: #6e7d73;
  text-transform: uppercase;
}

.nav-menu {
  margin-top: 18px;
  display: grid;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 12px;
  border-radius: 12px;
  color: #41564d;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s ease;
}

.nav-item:hover,
.router-link-active {
  background: #ebfaf1;
  color: #0e6a3d;
}

.profile-box {
  margin-top: auto;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 10px 4px;
  border-top: 1px solid #ebf1eb;
}

.avatar,
.profile-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: #176b4b;
  color: white;
  font-size: 11px;
  font-weight: 800;
}

.profile-text {
  flex: 1;
  display: grid;
  gap: 2px;
}

.profile-text strong {
  font-size: 12px;
  color: #153f31;
}

.profile-text span {
  font-size: 10px;
  color: #6d7f72;
  text-transform: capitalize;
}

.logout-button {
  border: 1px solid #d7e3da;
  background: white;
  color: #345445;
  border-radius: 10px;
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.content-column {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.topbar {
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  background: #ffffff;
  border-bottom: 1px solid #e6efe7;
  padding: 0 20px;
}

.topbar-spacer {
  flex: 0 0 12px;
}

.topbar-tools {
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
}

.icon-button,
.profile-button,
.menu-button {
  border: none;
  background: #f4f8f5;
  color: #2b4a3d;
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  cursor: pointer;
  position: relative;
}

.badge-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #d93025;
  border: 2px solid white;
}

.notification-wrap,
.profile-wrap {
  position: relative;
}

.dropdown-panel {
  position: absolute;
  right: 0;
  top: calc(100% + 12px);
  width: 260px;
  background: white;
  border: 1px solid #e7efe9;
  border-radius: 16px;
  box-shadow: 0 18px 40px rgba(16, 30, 21, 0.08);
  overflow: hidden;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px;
  border-bottom: 1px solid #eef3ef;
  background: #f7faf8;
}

.dropdown-header h3 {
  margin: 0;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #176b4b;
}

.dropdown-header span {
  font-size: 10px;
  color: #0f6a3c;
  font-weight: 700;
  background: #ebfaf1;
  border-radius: 999px;
  padding: 5px 8px;
}

.dropdown-content {
  padding: 14px 16px;
}

.notification-panel p {
  margin: 0;
  color: #5d7067;
  font-size: 12px;
  line-height: 1.6;
}

.panel-label {
  margin: 0 0 6px;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #6b7a73;
}

.profile-header strong {
  display: block;
  font-size: 13px;
  color: #173d2d;
}

.actions {
  display: grid;
  gap: 8px;
}

.dropdown-link {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  border: none;
  background: transparent;
  padding: 10px 12px;
  border-radius: 10px;
  color: #3f584d;
  text-decoration: none;
  font-weight: 700;
  text-align: left;
  cursor: pointer;
}

.dropdown-link:hover {
  background: #edfaf2;
  color: #0f6a3c;
}

.dropdown-link.danger {
  color: #b8433a;
}

.main-content {
  flex: 1;
  overflow-y: auto;
}

.backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 18, 0.38);
  z-index: 20;
  display: none;
}

@media (max-width: 900px) {
  .bendahara-shell {
    display: block;
    position: relative;
  }

  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 260px;
    transform: translateX(-110%);
    box-shadow: 18px 0 35px rgba(17, 31, 23, 0.1);
  }

  .sidebar-open {
    transform: translateX(0);
  }

  .backdrop {
    display: block;
  }
}

@media (min-width: 901px) {
  .menu-button {
    display: none;
  }
}

@media (max-width: 640px) {
  .topbar {
    padding: 0 12px;
  }

  .dropdown-panel {
    width: min(240px, calc(100vw - 24px));
  }
}
</style>
