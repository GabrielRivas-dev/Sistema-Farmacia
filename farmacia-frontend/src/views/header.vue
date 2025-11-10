<script>
import api from '../services/api'
import { useCartStore } from '@/stores/cart'
import { storeToRefs } from 'pinia'

export default {
  name: 'HeaderView',
  setup() {
    const cartStore = useCartStore()
    // Hacer reactivos los getters y estado
    const { totalItems, isOpen } = storeToRefs(cartStore)
    
    return {
      totalItems,
      isOpen,
      openCart: cartStore.openCart,
      closeCart: cartStore.closeCart
    }
  },
  data() {
    return {
      user: null
    }
  },
  mounted() {
    this.checkAuthStatus()
  },
  methods: {
    checkAuthStatus() {
      const userData = localStorage.getItem('user')
      if (userData) {
        this.user = JSON.parse(userData)
      }
    },
    
 async handleLogout() {
  try {
    const token = localStorage.getItem('auth_token')

    await api.post('/logout', {}, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })
  } catch (error) {
    console.error('Error al cerrar sesión:', error)
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    this.user = null
    this.$emit('navigate', 'products')
    this.showLogoutMessage()
  }
},

    
    showLogoutMessage() {
      const messageDiv = document.createElement('div')
      messageDiv.className = 'logout-message'
      messageDiv.innerHTML = '✅ Sesión cerrada correctamente'
      messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
      `
      
      document.body.appendChild(messageDiv)
      
      setTimeout(() => {
        messageDiv.remove()
      }, 3000)
    },
    
   goToProfile() {
  this.$emit('navigate', 'profile')
}
  }
}
</script>

<template>
  <header class="elegant-header">
    <div class="container">
      <div class="header-content">
        <!-- Logo y Eslogan -->
        <div class="brand">
          <a href="#" @click.prevent="$emit('navigate', 'products')" class="brand-link">
            <div class="logo-icon">🏥</div>
            <div class="brand-text">
              <h2 class="brand-name">FarmaPlus</h2>
              <p class="brand-tagline">Tu salud es nuestra prioridad</p>
            </div>
          </a>
        </div>

        <!-- Búsqueda -->
        <div class="search-bar">
          <input 
            type="text" 
            placeholder="Buscar medicamentos..." 
            class="search-input"
          >
          <button class="search-btn">🔍</button>
        </div>

        <!-- Información de usuario o auth -->
        <div class="user-section">
          <div v-if="user" class="user-info">
            <div class="user-welcome">
              <span class="welcome-text">Hola, </span>
              <span class="user-name">{{ user.nombre || user.name }}</span>
            </div>
            <div class="user-actions">
              <button class="user-btn" @click="goToProfile">
                <span class="btn-icon">👤</span>
                Perfil
              </button>
              <button class="user-btn btn-cart" @click="openCart">
                <span class="btn-icon">🛒</span>
                Carrito
                <span v-if="totalItems > 0" class="cart-badge">{{ totalItems }}</span>
              </button>
              <button class="user-btn btn-logout" @click="handleLogout">
                <span class="btn-icon">🚪</span>
                Salir
              </button>
              <!-- En la sección de user-actions, agregar este botón -->
<button 
  v-if="user?.role === 'admin'" 
  class="user-btn btn-admin" 
  @click="$emit('navigate', 'admin')"
>
  <span class="btn-icon">⚙️</span>
  Admin
</button>
            </div>
          </div>
          
          <div v-else class="auth-buttons">
            <a href="#" class="auth-btn login" @click.prevent="$emit('navigate', 'login')">
              Ingresar
            </a>
            <a href="#" class="auth-btn register" @click.prevent="$emit('navigate', 'register')">
              Registrarse
            </a>
          </div>
        </div>
      </div>

      <!-- Navegación secundaria -->
      <nav class="secondary-nav">
        <div class="nav-links">
          <a href="#" class="nav-item" @click.prevent="$emit('navigate', 'products')">Inicio</a>
          <a href="#" class="nav-item" @click.prevent="$emit('navigate', 'products')">Medicamentos</a>
          <a href="#" class="nav-item">Recetas</a>
          <a href="#" class="nav-item">Laboratorios</a>
          <a href="#" class="nav-item">Promociones</a>
          <a href="#" class="nav-item">Sucursales</a>
        </div>
        
        <div class="contact-info">
          <div class="contact-item">
            <span class="icon">📞</span>
            <div class="contact-text">
              <span class="label">Llámanos</span>
              <span class="value">(01) 234-5678</span>
            </div>
          </div>
        </div>
      </nav>
    </div>
  </header>
</template>

<style scoped>
/* Tus estilos se mantienen igual */
.elegant-header {
  background: white;
  border-bottom: 3px solid #2563eb;
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
}

.brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-icon {
  font-size: 2.5rem;
}

.brand-text {
  line-height: 1.2;
}
.btn-admin {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border-color: #8b5cf6;
}

.btn-admin:hover {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  border-color: #7c3aed;
}

.brand-name {
  margin: 0;
  color: #2563eb;
  font-size: 1.5rem;
  font-weight: 700;
}

.brand-tagline {
  margin: 0;
  color: #64748b;
  font-size: 0.8rem;
}

.search-bar {
  display: flex;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 25px;
  overflow: hidden;
  width: 400px;
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 0.8rem 1.5rem;
  outline: none;
  font-size: 0.9rem;
}

.search-btn {
  background: #2563eb;
  border: none;
  color: white;
  padding: 0.8rem 1.2rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

.search-btn:hover {
  background: #1d4ed8;
}

.contact-info {
  display: flex;
  gap: 1.5rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon {
  font-size: 1.2rem;
}

.contact-text {
  display: flex;
  flex-direction: column;
}

.label {
  font-size: 0.8rem;
  color: #64748b;
}

.value {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

/* Navegación secundaria */
.secondary-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.8rem 0;
  border-top: 1px solid #e2e8f0;
}

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-item {
  color: #475569;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 0;
  position: relative;
  transition: color 0.3s ease;
}

.nav-item:hover {
  color: #2563eb;
}

.user-section {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.user-welcome {
  color: #374151;
  font-weight: 500;
}

.welcome-text {
  color: #64748b;
}

.user-name {
  color: #2563eb;
  font-weight: 600;
}

.user-actions {
  display: flex;
  gap: 0.5rem;
}

.user-btn {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.user-btn:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

.btn-cart {
  position: relative;
}

.cart-badge {
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 600;
  position: absolute;
  top: -5px;
  right: -5px;
}

.btn-logout {
  background: #fef2f2;
  border-color: #fecaca;
  color: #dc2626;
}

.btn-logout:hover {
  background: #fee2e2;
}

.auth-buttons {
  display: flex;
  gap: 1rem;
}

.auth-btn {
  padding: 0.6rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  font-size: 0.9rem;
}

.auth-btn.login {
  background: transparent;
  color: #2563eb;
  border: 2px solid #2563eb;
}

.auth-btn.login:hover {
  background: #2563eb;
  color: white;
}

.auth-btn.register {
  background: #2563eb;
  color: white;
}

.auth-btn.register:hover {
  background: #1d4ed8;
  transform: translateY(-2px);
}

.btn-icon {
  font-size: 1rem;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .search-bar {
    width: 300px;
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
  }
  
  .search-bar {
    width: 100%;
    order: 3;
  }
  
  .user-section {
    order: 2;
  }
  
  .user-info {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .user-actions {
    justify-content: center;
  }
  
  .auth-buttons {
    flex-direction: column;
    width: 100%;
  }
  
  .auth-btn {
    text-align: center;
  }
}
</style>