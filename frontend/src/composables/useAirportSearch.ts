import { ref } from 'vue'
import { searchAirports } from 'src/services/airportsService'
import type { Airport, AirportOption } from 'src/types/airports'

type UseAirportSearchOptions = {
  minLength?: number
  limit?: number
}

export function useAirportSearch(options: UseAirportSearchOptions = {}) {
  const minLength = options.minLength ?? 2
  const limit = options.limit ?? 8

  const airportOptions = ref<AirportOption[]>([])
  const loading = ref(false)
  let searchToken = 0

  function mapAirportOption(airport: Airport): AirportOption {
    const code = airport.iata_code || airport.icao_code || ''
    const base = `${airport.city} - ${airport.name}`
    const value = code ? `${base} (${code})` : base

    const option: AirportOption = {
      value,
      label: base,
      city: airport.city,
      country: airport.country,
    }

    if (code) {
      option.code = code
    }

    return option
  }

  function filterAirports(val: string, update: (callback: () => void) => void) {
    const term = val.trim()

    if (term.length < minLength) {
      update(() => {
        airportOptions.value = []
      })
      return
    }

    const requestId = ++searchToken
    loading.value = true

    searchAirports(term, limit)
      .then((airports: Airport[]) => {
        if (requestId !== searchToken) return
        const options = airports.map(mapAirportOption)
        update(() => {
          airportOptions.value = options
        })
      })
      .catch(() => {
        if (requestId !== searchToken) return
        update(() => {
          airportOptions.value = []
        })
      })
      .finally(() => {
        if (requestId === searchToken) {
          loading.value = false
        }
      })
  }

  function addCustomDestination(val: string, done: (value: string) => void) {
    const clean = val.trim()
    if (!clean) return
    done(clean)
  }

  return {
    airportOptions,
    loading,
    filterAirports,
    addCustomDestination,
  }
}
