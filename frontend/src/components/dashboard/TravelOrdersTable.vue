<template>
  <q-card class="table-card reveal-3">
    <q-card-section>
      <q-table
        title="Pedidos"
        :rows="rows"
        :columns="columns"
        row-key="id"
        :loading="loading"
        v-model:pagination="paginationModel"
        @request="onRequest"
        binary-state-sort
      >
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-badge :class="['status-badge', statusClass(props.row.status)]">
              {{ statusLabel(props.row.status) }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row q-gutter-xs justify-end">
              <q-btn flat dense icon="visibility" @click="viewOrder(props.row)" title="Detalhes" />

              <q-btn
                v-if="isAdmin"
                flat
                dense
                icon="check"
                color="positive"
                :disable="props.row.status !== 'requested'"
                @click="emitUpdate(props.row.id, 'approved')"
                title="Aprovar"
              />

              <q-btn
                v-if="isAdmin"
                flat
                dense
                icon="close"
                color="negative"
                :disable="props.row.status !== 'requested'"
                @click="emitUpdate(props.row.id, 'canceled')"
                title="Cancelar"
              />
            </div>
          </q-td>
        </template>
      </q-table>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { QTableColumn } from 'quasar'
import type { PaginationState, StatusValue, TravelOrder } from 'src/types/travel-orders'
import { useStatusHelpers } from 'src/composables/useStatusHelpers'
import { toBrDate } from 'src/utils/date'

const props = defineProps<{
  rows: TravelOrder[]
  loading: boolean
  pagination: PaginationState
  isAdmin: boolean
}>()

const emit = defineEmits<{
  (e: 'update:pagination', value: PaginationState): void
  (e: 'request', value: { pagination: PaginationState }): void
  (e: 'view', value: TravelOrder): void
  (e: 'update-status', value: { id: number; status: StatusValue }): void
}>()

const { statusLabel, statusClass } = useStatusHelpers()
const formatDate = (value: string | null | undefined) => (value ? toBrDate(value) : '')

const paginationModel = computed({
  get: () => props.pagination,
  set: (value) => emit('update:pagination', value),
})

const columns: QTableColumn<TravelOrder>[] = [
  { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
  { name: 'requester_name', label: 'Solicitante', field: 'requester_name', sortable: true, align: 'left' },
  { name: 'destination', label: 'Destino', field: 'destination', sortable: true, align: 'left' },
  {
    name: 'departure_date',
    label: 'Ida',
    field: (r) => r.departure_date?.slice(0, 10),
    format: (val) => formatDate(val as string | null | undefined),
    sortable: true,
    align: 'left',
  },
  {
    name: 'return_date',
    label: 'Volta',
    field: (r) => r.return_date?.slice(0, 10),
    format: (val) => formatDate(val as string | null | undefined),
    sortable: true,
    align: 'left',
  },
  { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'left' },
  { name: 'actions', label: 'Acoes', field: () => '', sortable: false, align: 'right' },
]

function onRequest(value: { pagination: PaginationState }) {
  emit('request', value)
}

function viewOrder(row: TravelOrder) {
  emit('view', row)
}

function emitUpdate(id: number, status: StatusValue) {
  emit('update-status', { id, status })
}
</script>
