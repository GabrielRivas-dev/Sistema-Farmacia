<template>
  <div class="admin-panel">
    <!-- Sidebar -->
    <div class="admin-sidebar">
      <div class="sidebar-header">
        <h3>🏥 Admin Panel</h3>
        <div class="user-info">
          <span class="user-name">{{ user?.name }}</span>
          <span class="user-role">Administrador</span>
        </div>
      </div>
      
      <nav class="sidebar-nav">
        <button 
          class="nav-item"
          :class="{ 'active': currentSection === 'dashboard' }"
          @click="currentSection = 'dashboard'"
        >
          <span class="nav-icon">📊</span>
          <span class="nav-text">Dashboard</span>
        </button>
        
        <button 
          class="nav-item"
          :class="{ 'active': currentSection === 'users' }"
          @click="currentSection = 'users'"
        >
          <span class="nav-icon">👥</span>
          <span class="nav-text">Usuarios</span>
        </button>
        
        <button 
          class="nav-item"
          :class="{ 'active': currentSection === 'products' }"
          @click="currentSection = 'products'"
        >
          <span class="nav-icon">💊</span>
          <span class="nav-text">Productos</span>
        </button>
        
        <button 
          class="nav-item"
          :class="{ 'active': currentSection === 'orders' }"
          @click="currentSection = 'orders'"
        >
          <span class="nav-icon">📦</span>
          <span class="nav-text">Pedidos</span>
        </button>
        
        <button 
          class="nav-item"
          :class="{ 'active': currentSection === 'support' }"
          @click="currentSection = 'support'"
        >
          <span class="nav-icon">💬</span>
          <span class="nav-text">Soporte</span>
          <span v-if="unreadConversations > 0" class="nav-badge">
            {{ unreadConversations }}
          </span>
        </button>
      </nav>
      
      <div class="sidebar-footer">
        <button class="btn-logout" @click="handleLogout">
          <span class="nav-icon">🚪</span>
          <span class="nav-text">Salir del Admin</span>
        </button>
        <button class="btn-back" @click="goToStore">
          <span class="nav-icon">🏪</span>
          <span class="nav-text">Ir a la Tienda</span>
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
      <div class="admin-header">
        <h1>{{ sectionTitles[currentSection] }}</h1>
        <div class="header-actions">
          <span class="last-updated">
            Actualizado: {{ lastUpdated }}
          </span>
        </div>
      </div>
      
      <div class="admin-content">
        <!-- Dashboard -->
        <AdminDashboard 
          v-if="currentSection === 'dashboard'"
          :key="'dashboard'"
        />
        
        <!-- Gestión de Usuarios -->
        <AdminUsers 
          v-else-if="currentSection === 'users'"
          :key="'users'"
        />
        
        <!-- Gestión de Productos -->
        <AdminProducts 
          v-else-if="currentSection === 'products'"
          :key="'products'"
        />
        
        <!-- Gestión de Pedidos -->
        <AdminOrders 
          v-else-if="currentSection === 'orders'"
          :key="'orders'"
        />
        
        <!-- Soporte -->
        <AdminSupport 
          v-else-if="currentSection === 'support'"
          :key="'support'"
        />
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'
import AdminDashboard from '@/components/admin/AdminDashboard.vue'
import AdminUsers from '@/components/admin/AdminUsers.vue'
import AdminProducts from '@/components/admin/AdminProducts.vue'
import AdminOrders from '@/components/admin/AdminOrders.vue'
import AdminSupport from '@/components/admin/AdminSupport.vue'

export default {
  name: 'AdminPanel',
  components: {
    AdminDashboard,
    AdminUsers,
    AdminProducts,
    AdminOrders,
    AdminSupport
  },
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      currentSection: 'dashboard',
      unreadConversations: 0,
      lastUpdated: new Date().toLocaleTimeString()
    }
  },
  computed: {
    sectionTitles() {
      return {
        dashboard: 'Dashboard General',
        users: 'Gestión de Usuarios',
        products: 'Gestión de Productos',
        orders: 'Gestión de Pedidos',
        support: 'Soporte al Cliente'
      }
    }
  },
  async mounted() {
    await this.verifyAdminAccess()
    this.loadUnreadConversations()
    // Actualizar hora cada minuto
    this.updateInterval = setInterval(() => {
      this.lastUpdated = new Date().toLocaleTimeString()
    }, 60000)
  },
  beforeUnmount() {
    if (this.updateInterval) {
      clearInterval(this.updateInterval)
    }
  },
  methods: {
    async verifyAdminAccess() {
      if (!this.user || this.user.role !== 'admin') {
        this.$emit('navigate', 'products')
        return false
      }
      return true
    },
    
    async loadUnreadConversations() {
      try {
        const response = await api.get('/admin/conversations?status=open')
        this.unreadConversations = response.data.data?.length || 0
      } catch (error) {
        console.error('Error loading conversations:', error)
      }
    },
    
    goToStore() {
      this.$emit('navigate', 'products')
    },
    
    async handleLogout() {
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Error al cerrar sesión:', error)
      } finally {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        this.$emit('navigate', 'products')
      }
    }
  }
}
</script>

<style scoped>
.admin-panel {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
}

/* Sidebar Styles */
.admin-sidebar {
  width: 280px;
  background: linear-gradient(135deg, #1e293b, #334155);
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #475569;
}

.sidebar-header h3 {
  margin: 0 0 1rem 0;
  color: white;
  font-size: 1.2rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name {
  font-weight: 600;
  font-size: 0.9rem;
}

.user-role {
  background: #3b82f6;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  align-self: flex-start;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
}

.nav-item {
  display: flex;
  align-items: center;
  width: 100%;
  background: none;
  border: none;
  color: #cbd5e1;
  text-decoration: none;
  padding: 1rem 1.5rem;
  transition: all 0.2s ease;
  position: relative;
  border-left: 3px solid transparent;
  cursor: pointer;
  text-align: left;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border-left-color: #3b82f6;
}

.nav-icon {
  font-size: 1.2rem;
  margin-right: 1rem;
  width: 20px;
  text-align: center;
}

.nav-text {
  font-weight: 500;
  flex: 1;
}

.nav-badge {
  background: #ef4444;
  color: white;
  border-radius: 10px;
  padding: 0.25rem 0.5rem;
  font-size: 0.7rem;
  font-weight: 600;
}

.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #475569;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.btn-logout, .btn-back {
  display: flex;
  align-items: center;
  width: 100%;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9rem;
}

.btn-logout:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.3);
}

.btn-back:hover {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.3);
}

/* Main Content Styles */
.admin-main {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.admin-header {
  background: white;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.admin-header h1 {
  margin: 0;
  color: #1e293b;
  font-size: 1.5rem;
  font-weight: 600;
}

.last-updated {
  color: #64748b;
  font-size: 0.9rem;
}

.admin-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
  background: #f8fafc;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-panel {
    flex-direction: column;
  }
  
  .admin-sidebar {
    width: 100%;
    height: auto;
  }
  
  .sidebar-nav {
    display: flex;
    overflow-x: auto;
    padding: 1rem;
  }
  
  .nav-item {
    flex-shrink: 0;
    border-left: none;
    border-bottom: 3px solid transparent;
    padding: 0.75rem 1rem;
  }
  
  .nav-item.active {
    border-left: none;
    border-bottom-color: #3b82f6;
  }
  
  .nav-text {
    font-size: 0.8rem;
  }
}
</style>