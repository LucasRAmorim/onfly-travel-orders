<template>
  <div class="page-hero reveal-1">
    <div>
      <div class="text-h5">Pedidos de Viagem</div>
      <div class="subtle">
        {{ isAdmin ? 'Administrador: visualiza todos os pedidos' : 'Voce visualiza apenas seus pedidos' }}
      </div>
    </div>

    <div class="row items-center q-gutter-sm">
      <q-btn color="primary" icon="add" label="Novo pedido" @click="$emit('create')" />
      <q-btn flat icon="refresh" label="Atualizar" @click="$emit('refresh')" :loading="loading" />
      <q-btn
        v-if="isAdmin"
        flat
        icon="menu_book"
        label="Documentacao"
        :href="docsUrl"
        target="_blank"
        rel="noopener"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  isAdmin: boolean
  loading: boolean
}>()

const docsUrl = `${(import.meta.env.VITE_API_URL || '').replace(/\/$/, '')}/api/documentation`

defineEmits<{
  (e: 'create'): void
  (e: 'refresh'): void
}>()
</script>
