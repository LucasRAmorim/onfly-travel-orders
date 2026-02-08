import type { StatusValue } from './travel-orders'

export type NotificationData = {
  travel_order_id: number
  status: StatusValue
  destination: string
  departure_date: string
  return_date: string
}

export type NotificationItem = {
  id: string
  created_at: string | null
  read_at: string | null
  data: NotificationData
}

export type NotificationsResponse = {
  data: NotificationItem[]
  meta?: {
    unread_count?: number
  }
}

export type NotificationReadResponse = {
  data: {
    id: string
    read_at: string | null
  }
}
