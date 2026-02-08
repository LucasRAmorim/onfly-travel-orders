<template>
  <q-dialog v-model="open" persistent>
    <q-card class="glass-card" style="width: 560px; max-width: 95vw;">
      <q-card-section class="row items-center justify-between">
        <div class="text-h6">Novo pedido</div>
        <q-btn flat dense icon="close" @click="open = false" />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-form @submit.prevent="createOrder">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-input v-model="form.requester_name" label="Solicitante" dense outlined />
            </div>

            <div class="col-12">
              <q-select
                v-model="form.destination"
                :options="airportOptions"
                label="Destino (aeroporto)"
                dense
                outlined
                use-input
                :input-debounce="300"
                clearable
                fill-input
                hide-selected
                map-options
                emit-value
                option-label="label"
                option-value="value"
                new-value-mode="add-unique"
                :loading="airportLoading"
                hint="Digite cidade, aeroporto ou IATA/ICAO"
                @filter="filterAirports"
                @new-value="addCustomDestination"
              >
                <template #option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.label }}</q-item-label>
                      <q-item-label caption>{{ scope.opt.city }} - {{ scope.opt.country }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-chip v-if="scope.opt.code" class="chip-code" dense>{{ scope.opt.code }}</q-chip>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>

            <div class="col-12 col-md-6">
              <q-input
                v-model="form.departure_date"
                label="Data de ida"
                dense
                outlined
                readonly
                :rules="[
                  (val) => !!val || 'Informe a data de ida',
                  (val) => val >= today || 'A data de ida deve ser hoje ou futura',
                ]"
              >
                <template #append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-date v-model="form.departure_date" mask="DD/MM/YYYY" :min="today" :options="departureDateOptions">
                        <div class="row items-center justify-end q-gutter-sm">
                          <q-btn v-close-popup label="OK" color="primary" flat />
                        </div>
                      </q-date>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>

            <div class="col-12 col-md-6">
              <q-input
                v-model="form.return_date"
                label="Data de volta"
                dense
                outlined
                readonly
                :rules="[
                  (val) => !!val || 'Informe a data de volta',
                  (val) => val >= returnMinDate || 'A data de volta deve ser hoje ou posterior',
                ]"
              >
                <template #append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-date v-model="form.return_date" mask="DD/MM/YYYY" :min="returnMinDate" :options="returnDateOptions">
                        <div class="row items-center justify-end q-gutter-sm">
                          <q-btn v-close-popup label="OK" color="primary" flat />
                        </div>
                      </q-date>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
          </div>

          <div class="row justify-end q-gutter-sm q-mt-md">
            <q-btn flat label="Cancelar" @click="open = false" :disable="saving" />
            <q-btn color="primary" label="Criar" type="submit" :loading="saving" />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Notify, date } from 'quasar'
import { useAirportSearch } from 'src/composables/useAirportSearch'
import { createTravelOrder } from 'src/services/travelOrdersService'
import type { ApiError } from 'src/types/api'
import type { TravelOrderForm } from 'src/types/travel-orders'
import { toApiDate } from 'src/utils/date'

const props = defineProps<{
  modelValue: boolean
  requesterName: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'created'): void
}>()

const open = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const form = ref<TravelOrderForm>({
  requester_name: props.requesterName || '',
  destination: '',
  departure_date: '',
  return_date: '',
})

const saving = ref(false)

const { airportOptions, loading: airportLoading, filterAirports, addCustomDestination } = useAirportSearch()

const today = date.formatDate(Date.now(), 'DD/MM/YYYY')
const todayOption = date.formatDate(Date.now(), 'YYYY/MM/DD')
const returnMinDate = computed(() => form.value.departure_date || today)

function isOnOrAfter(dateStr: string, minStr: string) {
  return dateStr >= minStr
}

function departureDateOptions(dateStr: string) {
  return isOnOrAfter(dateStr, todayOption)
}

function toOptionDate(value: string) {
  if (!value) return todayOption
  const parsed = date.extractDate(value, 'DD/MM/YYYY')
  if (Number.isNaN(parsed.getTime())) return todayOption
  return date.formatDate(parsed, 'YYYY/MM/DD')
}

function returnDateOptions(dateStr: string) {
  const min = toOptionDate(form.value.departure_date)
  return isOnOrAfter(dateStr, min)
}

function resetForm() {
  form.value = {
    requester_name: props.requesterName || '',
    destination: '',
    departure_date: '',
    return_date: '',
  }
}

watch(
  () => props.modelValue,
  (value) => {
    if (value) {
      resetForm()
    }
  }
)

watch(
  () => props.requesterName,
  (value) => {
    if (props.modelValue) {
      form.value.requester_name = value || ''
    }
  }
)

async function createOrder() {
  saving.value = true
  try {
    const payload: TravelOrderForm = {
      ...form.value,
      departure_date: toApiDate(form.value.departure_date),
      return_date: toApiDate(form.value.return_date),
    }
    await createTravelOrder(payload)
    Notify.create({ type: 'positive', message: 'Pedido criado com sucesso.' })
    emit('created')
    open.value = false
    resetForm()
  } catch (e: unknown) {
    const err = e as ApiError
    const msg =
      err?.response?.data?.message ||
      (err?.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : 'Erro ao criar pedido.')
    Notify.create({ type: 'negative', message: msg })
  } finally {
    saving.value = false
  }
}
</script>
