<template>
  <div class="row q-col-gutter-md q-mb-lg">
    <div class="col-12 col-md-3">
      <div class="stat-card reveal-2">
        <div class="stat-value">{{ summary.total }}</div>
        <div class="stat-label">Total (filtro)</div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="stat-card reveal-2">
        <div class="stat-value">{{ summary.requested }}</div>
        <div class="stat-label">Solicitados</div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="stat-card reveal-2">
        <div class="stat-value">{{ summary.approved }}</div>
        <div class="stat-label">Aprovados</div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="stat-card reveal-2">
        <div class="stat-value">{{ summary.canceled }}</div>
        <div class="stat-label">Cancelados</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  summary: {
    total: number
    requested: number
    approved: number
    canceled: number
  }
}>()
</script>
