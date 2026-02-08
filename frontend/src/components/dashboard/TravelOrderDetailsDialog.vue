<template>
  <q-dialog v-model="open">
    <q-card class="glass-card" style="width: 560px; max-width: 95vw;">
      <q-card-section class="row items-center justify-between">
        <div class="text-h6">Detalhes do pedido</div>
        <q-btn flat dense icon="close" @click="open = false" />
      </q-card-section>

      <q-separator />

      <q-card-section v-if="order">
        <div class="q-mb-sm"><strong>ID:</strong> {{ order.id }}</div>
        <div class="q-mb-sm"><strong>Solicitante:</strong> {{ order.requester_name }}</div>
        <div class="q-mb-sm"><strong>Destino:</strong> {{ order.destination }}</div>
        <div class="q-mb-sm"><strong>Ida:</strong> {{ order.departure_date?.slice(0, 10) }}</div>
        <div class="q-mb-sm"><strong>Volta:</strong> {{ order.return_date?.slice(0, 10) }}</div>
        <div class="q-mb-sm">
          <strong>Status:</strong>
          <q-badge :class="['status-badge', statusClass(order.status)]">
            {{ statusLabel(order.status) }}
          </q-badge>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useStatusHelpers } from 'src/composables/useStatusHelpers'
import type { TravelOrder } from 'src/types/travel-orders'

const props = defineProps<{
  modelValue: boolean
  order: TravelOrder | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const open = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const { statusLabel, statusClass } = useStatusHelpers()
</script>
