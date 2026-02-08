export type StatusValue = 'requested' | 'approved' | 'canceled'

export type TravelOrder = {
  id: number
  requester_name: string
  destination: string
  departure_date: string
  return_date: string
  status: StatusValue
  user_id: number
  created_at: string
  updated_at: string
}

export type PaginationState = {
  page: number
  rowsPerPage: number
  rowsNumber?: number
  sortBy?: string
  descending?: boolean
}

export type Filters = {
  status: StatusValue | null
  destination: string
  travel_from: string
  travel_to: string
}

export type TravelOrderForm = {
  requester_name: string
  destination: string
  departure_date: string
  return_date: string
}

export type Airport = {
  id: string
  iata_code: string | null
  icao_code: string | null
  name: string
  city: string
  country: string
}

export type AirportOption = {
  value: string
  label: string
  city: string
  country: string
  code?: string
}
