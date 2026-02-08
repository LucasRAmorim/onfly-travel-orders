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
import { api } from 'src/boot/axios'
import DashboardHeader from 'src/components/dashboard/DashboardHeader.vue'
import DashboardStats from 'src/components/dashboard/DashboardStats.vue'
import DashboardFilters from 'src/components/dashboard/DashboardFilters.vue'
import TravelOrdersTable from 'src/components/dashboard/TravelOrdersTable.vue'
import TravelOrderCreateDialog from 'src/components/dashboard/TravelOrderCreateDialog.vue'
import TravelOrderDetailsDialog from 'src/components/dashboard/TravelOrderDetailsDialog.vue'
import type { Filters, PaginationState, StatusValue, TravelOrder } from 'src/components/dashboard/types'

type ApiError = {
  response?: {
    data?: {
      message?: string
      errors?: Record<string, string[]>
    }
  }
}

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
  if (filters.value.travel_from) p.travel_from = filters.value.travel_from
  if (filters.value.travel_to) p.travel_to = filters.value.travel_to

  p.page = pagination.value.page

  return p
}

async function fetchOrders(): Promise<void> {
  loading.value = true
  try {
    const { data } = await api.get('/travel-orders', { params: buildParams() })

    const paginator = data.data
    rows.value = paginator.data
    pagination.value.rowsNumber = paginator.total
    pagination.value.page = paginator.current_page
    pagination.value.rowsPerPage = paginator.per_page

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
  }).onOk(async () => {
    try {
      await api.patch(`/travel-orders/${payload.id}/status`, { status: payload.status })
      Notify.create({ type: 'positive', message: 'Status atualizado com sucesso.' })
      await fetchOrders()
    } catch (e) {
      const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
      const msg =
        err?.response?.data?.message ||
        (err?.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : 'Erro ao atualizar status.')
      Notify.create({ type: 'negative', message: msg })
    }
  })
}

onMounted(async () => {
  await fetchOrders()
})
</script>
