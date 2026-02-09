<template>
  <q-page class="q-pb-xl">
    <div class="page-content">
      <DashboardHeader
        :is-admin="isAdmin"
        :loading="loading"
        @create="openCreate = true"
        @refresh="fetchOrders"
      />

      <DashboardStats :summary="summary" />

      <DashboardFilters
        v-model="filters"
        :status-options="statusOptions"
        :loading="loading"
        @apply="applyFilters"
        @clear="clearFilters"
      />

      <TravelOrdersTable
        :rows="rows"
        :loading="loading"
        v-model:pagination="pagination"
        :is-admin="isAdmin"
        @request="onRequest"
        @view="viewOrder"
        @update-status="updateStatus"
      />

      <TravelOrderCreateDialog
        v-model="openCreate"
        :requester-name="currentUser?.name || ''"
        @created="fetchOrders"
      />

      <TravelOrderDetailsDialog v-model="openDetails" :order="selected" />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Notify, useMeta, useQuasar } from 'quasar'
import DashboardHeader from 'src/components/dashboard/DashboardHeader.vue'
import DashboardStats from 'src/components/dashboard/DashboardStats.vue'
import DashboardFilters from 'src/components/dashboard/DashboardFilters.vue'
import TravelOrdersTable from 'src/components/dashboard/TravelOrdersTable.vue'
import TravelOrderCreateDialog from 'src/components/dashboard/TravelOrderCreateDialog.vue'
import TravelOrderDetailsDialog from 'src/components/dashboard/TravelOrderDetailsDialog.vue'
import { listTravelOrders, updateTravelOrderStatus } from 'src/services/travelOrdersService'
import type { ApiError } from 'src/types/api'
import type { Filters, PaginationState, StatusValue, TravelOrder } from 'src/types/travel-orders'
import { toApiDate } from 'src/utils/date'

const rawUser = localStorage.getItem('user')
const currentUser = rawUser ? JSON.parse(rawUser) : null
const isAdmin = computed(() => currentUser?.role === 'admin')

const loading = ref(false)

const openCreate = ref(false)
const openDetails = ref(false)
const selected = ref<TravelOrder | null>(null)

const $q = useQuasar()

const rows = ref<TravelOrder[]>([])
const pagination = ref<PaginationState>({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0,
  sortBy: 'id',
  descending: true,
})

const statusOptions: { label: string; value: StatusValue }[] = [
  { label: 'Solicitado', value: 'requested' },
  { label: 'Aprovado', value: 'approved' },
  { label: 'Cancelado', value: 'canceled' },
]

const filters = ref<Filters>({
  status: null,
  destination: '',
  travel_from: '',
  travel_to: '',
})

const statusSummary = ref({
  requested: 0,
  approved: 0,
  canceled: 0,
})

useMeta({
  title: 'OnFly  - Painel',
  meta: {
    description: {
      name: 'description',
      content: 'Controle seus pedidos de viagem com rapidez e clareza na OnFly.',
    },
    ogTitle: { property: 'og:title', content: 'OnFly  - Painel' },
    ogDescription: {
      property: 'og:description',
      content: 'Controle seus pedidos de viagem com rapidez e clareza na OnFly.',
    },
    ogType: { property: 'og:type', content: 'website' },
  },
})

const summary = computed(() => ({
  total: pagination.value.rowsNumber ?? 0,
  requested: statusSummary.value.requested,
  approved: statusSummary.value.approved,
  canceled: statusSummary.value.canceled,
}))

function buildParams(): Record<string, string | number> {
  const p: Record<string, string | number> = {}

  if (filters.value.status) p.status = filters.value.status
  if (filters.value.destination) p.destination = filters.value.destination
  if (filters.value.travel_from) p.travel_from = toApiDate(filters.value.travel_from)
  if (filters.value.travel_to) p.travel_to = toApiDate(filters.value.travel_to)

  p.page = pagination.value.page
  p.per_page = pagination.value.rowsPerPage
  if (pagination.value.sortBy) {
    p.sort_by = pagination.value.sortBy
    p.sort_dir = pagination.value.descending ? 'desc' : 'asc'
  }

  return p
}

async function fetchOrders(): Promise<void> {
  loading.value = true
  try {
    const data = await listTravelOrders(buildParams())
    const paginator = data as unknown
    const list = extractOrders(paginator)
    const meta = extractPaginatorMeta(paginator)

    rows.value = list
    pagination.value.rowsNumber = meta?.total ?? list.length
    pagination.value.page = meta?.current_page ?? pagination.value.page
    pagination.value.rowsPerPage = meta?.per_page ?? pagination.value.rowsPerPage

    statusSummary.value = {
      requested: data.meta?.status_counts?.requested ?? 0,
      approved: data.meta?.status_counts?.approved ?? 0,
      canceled: data.meta?.status_counts?.canceled ?? 0,
    }

  } catch (e: unknown) {
    const err = e as ApiError
    Notify.create({ type: 'negative', message: err?.response?.data?.message || 'Falha ao carregar pedidos.' })
  } finally {
    loading.value = false
  }
}


async function onRequest(props: { pagination: PaginationState }) {
  pagination.value = {
    ...pagination.value,
    ...props.pagination,
  }
  await fetchOrders()
}

async function applyFilters() {
  pagination.value.page = 1
  await fetchOrders()
}

async function clearFilters() {
  filters.value = {
    status: null,
    destination: '',
    travel_from: '',
    travel_to: '',
  }
  pagination.value.page = 1
  await fetchOrders()
}

function viewOrder(row: TravelOrder) {
  selected.value = row
  openDetails.value = true
}

function updateStatus(payload: { id: number; status: StatusValue }) {
  async function applyStatusUpdate() {
    try {
      await updateTravelOrderStatus(payload.id, payload.status)
      Notify.create({ type: 'positive', message: 'Status atualizado com sucesso.' })
      await fetchOrders()
    } catch (e) {
      const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
      const msg =
        err?.response?.data?.message ||
        (err?.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : 'Erro ao atualizar status.')
      Notify.create({ type: 'negative', message: msg })
    }
  }

  $q.dialog({
    title: 'Confirmar',
    message: `Deseja ${payload.status === 'approved' ? 'aprovar' : 'cancelar'} este pedido?`,
    ok: {
      label: payload.status === 'approved' ? 'Aprovar' : 'Cancelar pedido',
      color: payload.status === 'approved' ? 'positive' : 'negative',
      unelevated: true,
    },
    cancel: {
      label: 'Voltar',
      color: 'grey-7',
      flat: true,
    },
    persistent: true,
  }).onOk(() => {
    void applyStatusUpdate()
  })
}

onMounted(async () => {
  await fetchOrders()
})

function extractOrders(payload: unknown): TravelOrder[] {
  if (Array.isArray(payload)) return payload as TravelOrder[]

  if (payload && typeof payload === 'object') {
    const obj = payload as { data?: unknown }
    if (Array.isArray(obj.data)) return obj.data as TravelOrder[]

    const nested = obj.data as { data?: unknown } | undefined
    if (nested && Array.isArray(nested.data)) return nested.data as TravelOrder[]
  }

  return []
}

function extractPaginatorMeta(payload: unknown): { total?: number; current_page?: number; per_page?: number } | null {
  if (isPaginatorMeta(payload)) return payload
  if (payload && typeof payload === 'object') {
    const obj = payload as { data?: unknown }
    if (isPaginatorMeta(obj.data)) return obj.data
  }
  return null
}

function isPaginatorMeta(
  payload: unknown
): payload is { total?: number; current_page?: number; per_page?: number } {
  if (!payload || typeof payload !== 'object') return false
  return 'total' in payload || 'current_page' in payload || 'per_page' in payload
}
</script>
