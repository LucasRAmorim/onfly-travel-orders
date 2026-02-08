<template>
  <q-card class="glass-card q-mb-md reveal-2">
    <q-card-section>
      <div class="section-title q-mb-md">Filtros</div>
      <div class="row q-col-gutter-md items-end">
        <div class="col-12 col-md-3">
          <q-select
            v-model="status"
            :options="statusOptions"
            label="Status"
            dense
            outlined
            emit-value
            map-options
            clearable
          />
        </div>

        <div class="col-12 col-md-3">
          <q-select
            v-model="destination"
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

        <div class="col-12 col-md-3">
          <q-input v-model="travelFrom" label="Viagem de" dense outlined readonly clearable>
            <template #append>
              <q-icon name="event" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                  <q-date v-model="travelFrom" mask="YYYY-MM-DD" :min="today" :options="filterDateOptions">
                    <div class="row items-center justify-end q-gutter-sm">
                      <q-btn v-close-popup label="OK" color="primary" flat />
                    </div>
                  </q-date>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </div>

        <div class="col-12 col-md-3">
          <q-input v-model="travelTo" label="Viagem ate" dense outlined readonly clearable>
            <template #append>
              <q-icon name="event" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                  <q-date v-model="travelTo" mask="YYYY-MM-DD" :min="today" :options="filterDateOptions">
                    <div class="row items-center justify-end q-gutter-sm">
                      <q-btn v-close-popup label="OK" color="primary" flat />
                    </div>
                  </q-date>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </div>

        <div class="col-12">
          <div class="row q-gutter-sm">
            <q-btn color="primary" label="Aplicar filtros" @click="$emit('apply')" :loading="loading" />
            <q-btn flat label="Limpar" @click="$emit('clear')" :disable="loading" />
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { date } from 'quasar'
import { useAirportSearch } from 'src/composables/useAirportSearch'
import type { Filters, StatusValue } from 'src/types/travel-orders'

type StatusOption = { label: string; value: StatusValue }

const props = defineProps<{
  modelValue: Filters
  statusOptions: StatusOption[]
  loading: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: Filters): void
  (e: 'apply'): void
  (e: 'clear'): void
}>()

const status = computed({
  get: () => props.modelValue.status,
  set: (value) => emit('update:modelValue', { ...props.modelValue, status: value }),
})

const destination = computed({
  get: () => props.modelValue.destination,
  set: (value) => emit('update:modelValue', { ...props.modelValue, destination: value }),
})

const travelFrom = computed({
  get: () => props.modelValue.travel_from,
  set: (value) => emit('update:modelValue', { ...props.modelValue, travel_from: value }),
})

const travelTo = computed({
  get: () => props.modelValue.travel_to,
  set: (value) => emit('update:modelValue', { ...props.modelValue, travel_to: value }),
})

const { airportOptions, loading: airportLoading, filterAirports, addCustomDestination } = useAirportSearch()

const today = date.formatDate(Date.now(), 'YYYY-MM-DD')
const todayOption = date.formatDate(Date.now(), 'YYYY/MM/DD')

function filterDateOptions(dateStr: string) {
  return dateStr >= todayOption
}
</script>
