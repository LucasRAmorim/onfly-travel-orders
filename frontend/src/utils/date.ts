import { date } from 'quasar'

const API_MASK = 'YYYY-MM-DD'
const BR_MASK = 'DD/MM/YYYY'

export function toApiDate(value: string | null | undefined) {
  if (!value) return ''
  if (value.includes('-')) return value
  const parsed = date.extractDate(value, BR_MASK)
  if (Number.isNaN(parsed.getTime())) return value
  return date.formatDate(parsed, API_MASK)
}

export function toBrDate(value: string | null | undefined) {
  if (!value) return ''
  const raw = value.slice(0, 10)
  if (raw.includes('/')) return raw
  const parsed = date.extractDate(raw, API_MASK)
  if (Number.isNaN(parsed.getTime())) return raw
  return date.formatDate(parsed, BR_MASK)
}
