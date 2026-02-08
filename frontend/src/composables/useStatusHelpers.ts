import type { StatusValue } from 'src/components/dashboard/types'

export function useStatusHelpers() {
  function statusLabel(status: StatusValue) {
    if (status === 'requested') return 'Solicitado'
    if (status === 'approved') return 'Aprovado'
    return 'Cancelado'
  }

  function statusClass(status: StatusValue) {
    if (status === 'requested') return 'status-badge--requested'
    if (status === 'approved') return 'status-badge--approved'
    return 'status-badge--canceled'
  }

  return { statusLabel, statusClass }
}
