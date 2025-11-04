import { createRouter, createWebHistory } from 'vue-router'

// Definir rutas directamente sin imports
const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/Products.vue') // Lazy loading
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue')
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/Register.vue')
  },
  {
    path: '/medicamentos',
    name: 'Medicamentos',
    component: () => import('../views/Products.vue')
  },
  {
  path: '/checkout',
  name: 'Checkout',
  component: () => import('../views/CheckoutPage.vue'),
  meta: { requiresAuth: true }
}

]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router