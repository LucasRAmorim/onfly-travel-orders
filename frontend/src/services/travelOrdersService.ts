import { api } from 'src/boot/axios'
import type { StatusValue, TravelOrderForm, TravelOrderResponse, TravelOrdersListResponse } from 'src/types/travel-orders'

export async function listTravelOrders(params: Record<string, string | number>) {
  const { data } = await api.get<TravelOrdersListResponse>('/travel-orders', { params })
  return data
}

export async function createTravelOrder(payload: TravelOrderForm) {
  const { data } = await api.post<TravelOrderResponse>('/travel-orders', payload)
  return data.data
}

export async function updateTravelOrderStatus(id: number, status: StatusValue) {
  const { data } = await api.patch<TravelOrderResponse>(`/travel-orders/${id}/status`, { status })
  return data.data
}
