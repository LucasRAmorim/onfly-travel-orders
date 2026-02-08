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

export type AirportsResponse = {
  data: Airport[]
}
