import { api } from 'src/boot/axios'
import type { NotificationReadResponse, NotificationsResponse } from 'src/types/notifications'

export async function fetchNotifications() {
  const { data } = await api.get<NotificationsResponse>('/notifications')
  return data
}

export async function markNotificationAsRead(id: string) {
  const { data } = await api.patch<NotificationReadResponse>(`/notifications/${id}/read`)
  return data
}
