<template>
  <div v-if="!isAdmin" class="notifications-menu">
    <q-btn round dense flat icon="notifications" color="primary">
      <q-badge v-if="unreadCount" color="negative" floating rounded>{{ unreadCount }}</q-badge>
      <q-menu anchor="bottom right" self="top right" :offset="[0, 10]" @show="emit('open')">
      <q-card class="notification-menu-card">
        <q-card-section class="row items-center justify-between">
          <div class="section-title">Notificacoes</div>
          <q-badge v-if="unreadCount" color="negative" rounded>{{ unreadCount }} novas</q-badge>
        </q-card-section>
        <q-separator />
        <q-card-section v-if="notifications.length" class="column q-gutter-sm">
          <div
            v-for="note in notifications"
            :key="note.id"
            class="notification-item"
            :class="{ unread: !note.read_at }"
          >
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
                  @click.stop="$emit('read', note.id)"
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
        </q-card-section>
        <q-card-section v-else class="text-caption text-grey-6">
          Nenhuma notificacao por enquanto.
        </q-card-section>
      </q-card>
      </q-menu>
    </q-btn>
  </div>
</template>

<script setup lang="ts">
import { date } from 'quasar'
import { useStatusHelpers } from 'src/composables/useStatusHelpers'
import type { StatusValue } from './types'

defineProps<{
  isAdmin: boolean
  notifications: Array<{
    id: string
    created_at: string | null
    read_at: string | null
    data: {
      travel_order_id: number
      status: StatusValue
      destination: string
      departure_date: string
      return_date: string
    }
  }>
  unreadCount: number
}>()

const emit = defineEmits<{
  (e: 'read', id: string): void
  (e: 'open'): void
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
