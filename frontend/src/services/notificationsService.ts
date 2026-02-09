import { api } from 'src/boot/axios'
import type { NotificationReadResponse, NotificationsResponse } from 'src/types/notifications'

type FetchNotificationsOptions = {
  onlyUnread?: boolean
  limit?: number
}

export async function fetchNotifications(options: FetchNotificationsOptions = {}) {
  const params: Record<string, string | number | boolean> = {}

  if (options.onlyUnread) {
    params.only_unread = true
  }

  if (typeof options.limit === 'number') {
    params.limit = options.limit
  }

  const { data } = await api.get<NotificationsResponse>('/notifications', { params })
  return data
}

export async function markNotificationAsRead(id: string) {
  const { data } = await api.patch<NotificationReadResponse>(`/notifications/${id}/read`)
  return data
}
