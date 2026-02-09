<template>
  <q-card v-if="notifications.length" class="glass-card q-mb-md reveal-2">
    <q-card-section>
      <div class="row items-center justify-between q-mb-sm">
        <div class="section-title">Notificações</div>
        <q-badge v-if="unreadCount" color="negative" rounded>{{ unreadCount }} novas</q-badge>
      </div>

      <div class="column q-gutter-sm">
        <div v-for="note in notifications" :key="note.id" class="notification-item" :class="{ unread: !note.read_at }">
          <div class="row items-center justify-between">
            <div class="row items-center q-gutter-sm">
              <q-badge :class="['status-badge', statusClass(note.data.status)]">
                {{ statusLabel(note.data.status) }}
              </q-badge>
              <div class="text-body2">
                Pedido <strong>#{{ note.data.travel_order_id }}</strong> foi {{ statusLabelLower(note.data.status) }}.
              </div>
            </div>
            <div class="row items-center q-gutter-sm">
              <q-btn
                v-if="!note.read_at"
                dense
                flat
                color="primary"
                label="Marcar como lida"
                @click="$emit('read', note.id)"
              />
              <div class="text-caption text-grey-6">
                {{ formatDate(note.created_at) }}
              </div>
            </div>
          </div>
          <div class="text-caption text-grey-7 q-mt-xs">
            {{ note.data.destination }} · {{ note.data.departure_date }} - {{ note.data.return_date }}
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { date } from 'quasar'
import { useStatusHelpers } from 'src/composables/useStatusHelpers'
import type { NotificationItem } from 'src/types/notifications'
import type { StatusValue } from 'src/types/travel-orders'

defineProps<{
  notifications: NotificationItem[]
  unreadCount: number
}>()

defineEmits<{
  (e: 'read', id: string): void
}>()

const { statusLabel, statusClass } = useStatusHelpers()

function statusLabelLower(status: StatusValue) {
  const label = statusLabel(status)
  return label.charAt(0).toLowerCase() + label.slice(1)
}

function formatDate(value: string | null) {
  if (!value) return ''
  return date.formatDate(value, 'DD/MM/YYYY HH:mm')
}
</script>
