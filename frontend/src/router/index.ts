import { defineRouter } from '#q-app/wrappers'
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router'
import routes from './routes'

export default defineRouter(function () {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history'
        ? createWebHistory
        : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,
    history: createHistory(process.env.VUE_ROUTER_BASE),
  })

  // 🔐 Auth Guard
  Router.beforeEach((to, _from, next) => {
    const token = localStorage.getItem('token')

    if (to.meta.requiresAuth && !token) {
      next('/login')
      return
    }

    if (to.path === '/login' && token) {
      next('/')
      return
    }

    next()
  })

  return Router
})
