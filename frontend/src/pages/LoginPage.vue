<template>
  <q-page class="auth-page">
    <div class="auth-grid">
      <div class="auth-hero reveal-1">
        <div class="brand-pill">
          <q-icon name="flight_takeoff" />
          OnFly Central de Viagens
        </div>
        <h1>Controle seus pedidos de viagem com rapidez e clareza.</h1>
        <p>
          Centralize aprovacoes, acompanhe status e pesquise aeroportos com a mesma fluidez dos grandes sites de viagens.
        </p>
        <div class="hero-stats">
          <div class="stat-card">
            <div class="stat-value">30+ aeroportos</div>
            <div class="stat-label">Indexados</div>
          </div>
          <div class="stat-card">
            <div class="stat-value">Aprovacao rapida</div>
            <div class="stat-label">Fluxo simples</div>
          </div>
        </div>
      </div>

      <q-card class="auth-card glass-card reveal-2" style="width: 420px; max-width: 92vw;">
        <q-card-section>
          <div class="text-h6">Acesso ao painel</div>
          <div class="text-subtitle2 text-grey-7">Entre para gerenciar seus pedidos</div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <q-form @submit.prevent="onSubmit">
            <q-input v-model="email" label="E-mail" type="email" dense outlined class="q-mb-md" />
            <q-input v-model="password" label="Senha" type="password" dense outlined class="q-mb-md" />

            <q-btn label="Entrar" type="submit" color="primary" class="full-width" :loading="loading" />
          </q-form>

        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Notify, useMeta } from 'quasar'
import { api } from 'src/boot/axios'

const router = useRouter()
const email = ref('admin@onfly.local')
const password = ref('password')
const loading = ref(false)

useMeta({
  title: 'OnFly  - Login',
  meta: {
    description: {
      name: 'description',
      content: 'Controle seus pedidos de viagem com rapidez e clareza na OnFly.',
    },
    ogTitle: { property: 'og:title', content: 'OnFly  - Login' },
    ogDescription: {
      property: 'og:description',
      content: 'Controle seus pedidos de viagem com rapidez e clareza na OnFly.',
    },
    ogType: { property: 'og:type', content: 'website' },
  },
})

async function onSubmit() {
  loading.value = true
  try {
    const { data } = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    Notify.create({ type: 'positive', message: 'Acesso realizado com sucesso.' })
    await router.push('/')
  } catch (e: unknown) {
    const message =
        typeof e === 'object' && e !== null && 'response' in e
        ? (e as { response?: { data?: { message?: string } } }).response?.data?.message
        : undefined

    Notify.create({ type: 'negative', message: message || 'Falha no login.' })

  } finally {
    loading.value = false
  }
}
</script>
