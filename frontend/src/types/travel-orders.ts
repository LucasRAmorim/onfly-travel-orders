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

export type StatusCounts = {
  requested?: number
  approved?: number
  canceled?: number
}

export type TravelOrdersListResponse = {
  data: {
    data: TravelOrder[]
    total: number
    current_page: number
    per_page: number
  }
  meta?: {
    status_counts?: StatusCounts
  }
}

export type TravelOrderResponse = {
  data: TravelOrder
}
