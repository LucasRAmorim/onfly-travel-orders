<template>
  <div class="fullscreen bg-blue text-white text-center q-pa-md flex flex-center">
    <div>
      <div style="font-size: 30vh">
        404
      </div>

      <div class="text-h2" style="opacity:.4">
        Ops. Nada por aqui...
      </div>

      <q-btn
        class="q-mt-xl"
        color="white"
        text-color="blue"
        unelevated
        to="/"
        label="Voltar ao inicio"
        no-caps
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useMeta } from 'quasar'

useMeta({
  title: 'OnFly  - Pagina nao encontrada',
  meta: {
    description: {
      name: 'description',
      content: 'Controle seus pedidos de viagem com rapidez e clareza na OnFly.',
    },
    ogTitle: { property: 'og:title', content: 'OnFly  - Pagina nao encontrada' },
    ogDescription: {
      property: 'og:description',
      content: 'Controle seus pedidos de viagem com rapidez e clareza na OnFly.',
    },
    ogType: { property: 'og:type', content: 'website' },
  },
})
</script>
