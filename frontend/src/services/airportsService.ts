import { api } from 'src/boot/axios'
import type { AirportsResponse } from 'src/types/airports'

export async function searchAirports(query: string, limit: number) {
  const { data } = await api.get<AirportsResponse>('/airports', { params: { q: query, limit } })
  return data.data
}
