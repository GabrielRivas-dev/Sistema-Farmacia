<script setup>
import { ref, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useCartStore } from './stores/cart'
import CartSidebar from './components/CartSidebar.vue'
import Header from './views/header.vue'
import Products from './views/Products.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'
import AppFooter from './views/AppFooter.vue'
import Profile from './views/profile.vue'
import CheckoutPage from './views/CheckoutPage.vue'
import AdminPanel from './components/AdminPanel.vue'

// Estado para la página actual
const currentPage = ref('products')
const currentUser = ref(null)

// Inicializar store del carrito
const cartStore = useCartStore()
const { isOpen } = storeToRefs(cartStore)

// Función para cambiar página
const navigateTo = (page) => {
  currentPage.value = page
  window.history.pushState({}, '', `/${page === 'products' ? '' : page}`)
}
const isAdmin = () => {
  return currentUser.value?.role === 'admin'
}

// Función para manejar login exitoso
const handleLoginSuccess = (user) => {
  currentUser.value = user
  navigateTo('products')
}

// Función para manejar registro exitoso
const handleRegisterSuccess = (user) => {
  currentUser.value = user
  navigateTo('products')
}

// Cargar carrito desde localStorage al iniciar y verificar autenticación
onMounted(() => {
  cartStore.loadFromLocalStorage()
  
  const userData = localStorage.getItem('user')
  if (userData) {
    currentUser.value = JSON.parse(userData)
  }
})

  // Manejar navegación del navegador
  window.addEventListener('popstate', () => {
    const path = window.location.pathname.substring(1)
    const validPages = ['products', 'login', 'register', 'profile', 'admin']
    if (validPages.includes(path)) {
      currentPage.value = path
    } else {
      currentPage.value = 'products'
    }
  })
    const initialPath = window.location.pathname.substring(1)
  if (initialPath && ['login', 'register', 'profile', 'admin'].includes(initialPath)) {
    currentPage.value = initialPath
  }

</script>

<template>
  
  <Header 
    @navigate="navigateTo" 
    :user="currentUser"
  />
  <main>
    <AdminPanel 
      v-if="currentPage === 'admin' && currentUser?.role === 'admin'"
      :user="currentUser"
      @navigate="navigateTo"
    />
    <Products 
      v-if="currentPage === 'products'" 
      :user="currentUser"
    />
    <Login 
      v-else-if="currentPage === 'login'" 
      @navigate="navigateTo"
      @login-success="handleLoginSuccess"
    />
    <Register 
      v-else-if="currentPage === 'register'" 
      @navigate="navigateTo"
      @register-success="handleRegisterSuccess"
    />
      <Profile 
      v-else-if="currentPage === 'profile'" 
      :user="currentUser"
      @navigate="navigateTo"
    />
     <CheckoutPage 
      v-else-if="currentPage === 'checkout'" 
      :user="currentUser"
    />
  </main>
  <AppFooter />
  
  <!-- Carrito - ESTE ES EL COMPONENTE QUE FALTABA -->
  <CartSidebar 
    v-if="isOpen"
    @navigate-to-login="navigateTo('login')" 
    @navigate-to-checkout="navigateTo('checkout')" 
  />
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8fafc;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}
</style>