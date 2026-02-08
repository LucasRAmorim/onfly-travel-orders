<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated class="glass-card" height-hint="64">
      <q-toolbar class="q-px-md app-toolbar">
        <div class="app-toolbar-left"></div>

        <div class="app-title row items-center q-gutter-sm">
          <q-icon name="flight" />
          <span class="text-weight-bold">OnFly</span>
          <span class="app-subtitle text-grey-7">Central de Viagens</span>
        </div>

        <div class="app-toolbar-right row items-center no-wrap">
          <NotificationsMenu
            :is-admin="isAdmin"
            :notifications="notifications"
            :unread-count="unreadCount"
            @open="fetchNotifications"
            @read="markNotificationAsRead"
          />
          <div class="column items-center">
            <div class="text-caption text-grey-7 text-center">{{ displayName }}</div>
            <div class="text-caption text-grey-7 text-center">{{ roleLabel }}</div>
          </div>
          <q-btn class="logout-btn" dense label="Sair" color="negative" flat @click="logout" />
        </div>
      </q-toolbar>
    </q-header>

    <q-page-container class="q-pa-sm">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Notify } from 'quasar'
import { api } from 'src/boot/axios'
import NotificationsMenu from 'src/components/dashboard/NotificationsMenu.vue'

const router = useRouter()
let currentUser: { name?: string; role?: string } | null = null

try {
  const rawUser = localStorage.getItem('user')
  currentUser = rawUser ? JSON.parse(rawUser) : null
} catch {
  currentUser = null
}

const displayName = computed(() => currentUser?.name || 'Usuario')
const roleLabel = computed(() => (currentUser?.role === 'admin' ? 'Administrador' : 'Colaborador'))
const isAdmin = computed(() => currentUser?.role === 'admin')

const notifications = ref<Array<{
  id: string
  created_at: string | null
  read_at: string | null
  data: {
    travel_order_id: number
    status: 'requested' | 'approved' | 'canceled'
    destination: string
    departure_date: string
    return_date: string
  }
}>>([])
const unreadCount = ref(0)

async function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  Notify.create({ message: 'Sessao encerrada', type: 'info' })
  await router.push('/login')
}

async function fetchNotifications(): Promise<void> {
  if (isAdmin.value) return
  try {
    const { data } = await api.get('/notifications')
    notifications.value = data.data || []
    unreadCount.value = data.meta?.unread_count ?? 0
  } catch {
    notifications.value = []
    unreadCount.value = 0
  }
}

async function markNotificationAsRead(id: string): Promise<void> {
  try {
    await api.patch(`/notifications/${id}/read`)
    notifications.value = notifications.value.map((note) =>
      note.id === id ? { ...note, read_at: new Date().toISOString() } : note
    )
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  } catch {
    // no-op
  }
}

onMounted(async () => {
  await fetchNotifications()
})
</script>
