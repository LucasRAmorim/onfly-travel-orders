<template>
  <q-page class="q-pb-xl">
    <div class="page-content">
      <div class="page-hero">
        <div>
          <div class="eyebrow">Historico</div>
          <h1 class="page-hero-title">Notificações</h1>
          <div class="subtle">Veja todas as notificações, lidas e não lidas.</div>
        </div>
        <q-btn flat color="primary" label="Voltar ao painel" @click="goBack" />
      </div>

      <NotificationsPanel
        v-if="notifications.length"
        :notifications="notifications"
        :unread-count="unreadCount"
        @read="markNotificationAsRead"
      />

      <q-card v-else class="glass-card q-pa-lg text-center">
        <div class="text-body2 text-grey-7">Nenhuma notificação encontrada.</div>
      </q-card>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Notify, useMeta } from 'quasar'
import { useRouter } from 'vue-router'
import NotificationsPanel from 'src/components/dashboard/NotificationsPanel.vue'
import {
  fetchNotifications as fetchNotificationsService,
  markNotificationAsRead as markNotificationAsReadService,
} from 'src/services/notificationsService'
import type { NotificationItem } from 'src/types/notifications'

const router = useRouter()

const notifications = ref<NotificationItem[]>([])
const unreadCount = ref(0)

useMeta({
  title: 'OnFly  - Notificações',
  meta: {
    description: {
      name: 'description',
      content: 'Acompanhe todas as notificacoes dos pedidos de viagem na OnFly.',
    },
    ogTitle: { property: 'og:title', content: 'OnFly  - Notificações' },
    ogDescription: {
      property: 'og:description',
      content: 'Acompanhe todas as notificacoes dos pedidos de viagem na OnFly.',
    },
    ogType: { property: 'og:type', content: 'website' },
  },
})

async function fetchNotifications(): Promise<void> {
  try {
    const data = await fetchNotificationsService({ limit: 0 })
    notifications.value = data.data || []
    unreadCount.value = data.meta?.unread_count ?? 0
  } catch {
    notifications.value = []
    unreadCount.value = 0
    Notify.create({ type: 'negative', message: 'Falha ao carregar notificacoes.' })
  }
}

async function markNotificationAsRead(id: string): Promise<void> {
  try {
    await markNotificationAsReadService(id)
    notifications.value = notifications.value.map((note) =>
      note.id === id ? { ...note, read_at: new Date().toISOString() } : note
    )
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  } catch {
    Notify.create({ type: 'negative', message: 'Falha ao atualizar notificacao.' })
  }
}

async function goBack(): Promise<void> {
  await router.push('/')
}

onMounted(async () => {
  await fetchNotifications()
})
</script>
