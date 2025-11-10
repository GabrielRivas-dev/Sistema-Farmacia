<template>
  <div class="admin-dashboard">
    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon users">👥</div>
        <div class="stat-info">
          <h3>{{ stats.total_users || 0 }}</h3>
          <p>Usuarios Totales</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon products">💊</div>
        <div class="stat-info">
          <h3>{{ stats.total_products || 0 }}</h3>
          <p>Productos</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon orders">📦</div>
        <div class="stat-info">
          <h3>{{ stats.total_orders || 0 }}</h3>
          <p>Pedidos Totales</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon revenue">💰</div>
        <div class="stat-info">
          <h3>S/ {{ (stats.total_revenue || 0).toFixed(2) }}</h3>
          <p>Ingresos Totales</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon pending">⏳</div>
        <div class="stat-info">
          <h3>{{ stats.pending_orders || 0 }}</h3>
          <p>Pedidos Pendientes</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon support">💬</div>
        <div class="stat-info">
          <h3>{{ stats.active_conversations || 0 }}</h3>
          <p>Chats Activos</p>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="recent-section">
      <div class="section-header">
        <h3>Pedidos Recientes</h3>
        <button class="btn-refresh" @click="loadDashboardStats">🔄 Actualizar</button>
      </div>
      
      <div class="recent-orders">
        <div v-if="loading" class="loading">Cargando...</div>
        <div v-else-if="!stats.recent_orders || stats.recent_orders.length === 0" class="empty-state">
          <div class="empty-icon">📦</div>
          <p>No hay pedidos recientes</p>
        </div>
        <div v-else class="orders-list">
          <div v-for="order in stats.recent_orders" :key="order.id" class="order-item">
            <div class="order-info">
              <div class="order-header">
                <strong>Pedido #{{ order.id }}</strong>
                <span class="order-date">{{ formatDate(order.created_at) }}</span>
              </div>
              <span class="order-user">{{ order.user?.name || 'Cliente' }}</span>
            </div>
            <div class="order-details">
              <span class="order-amount">S/ {{ (order.total || 0).toFixed(2) }}</span>
              <span :class="`status-${order.status}`" class="order-status">
                {{ getStatusText(order.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3>Acciones Rápidas</h3>
      <div class="actions-grid">
        <button class="action-btn" @click="goToSection('products')">
          <span class="action-icon">➕</span>
          <span>Agregar Producto</span>
        </button>
        <button class="action-btn" @click="goToSection('orders')">
          <span class="action-icon">📋</span>
          <span>Ver Pedidos</span>
        </button>
        <button class="action-btn" @click="goToSection('support')">
          <span class="action-icon">💬</span>
          <span>Soporte</span>
        </button>
        <button class="action-btn" @click="goToSection('users')">
          <span class="action-icon">👥</span>
          <span>Gestión de Usuarios</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'

export default {
  name: 'AdminDashboard',
  data() {
    return {
      stats: {},
      loading: false
    }
  },
  async mounted() {
    await this.loadDashboardStats()
  },
  methods: {
    async loadDashboardStats() {
      this.loading = true
      try {
        const response = await api.get('/admin/dashboard-stats')
        this.stats = response.data
      } catch (error) {
        console.error('Error loading dashboard stats:', error)
        this.$toast.error('Error al cargar estadísticas')
      } finally {
        this.loading = false
      }
    },

    formatDate(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('es-ES')
    },

    getStatusText(status) {
      const statusMap = {
        'pending': 'Pendiente',
        'processing': 'Procesando',
        'completed': 'Completado',
        'cancelled': 'Cancelado'
      }
      return statusMap[status] || status
    },

    goToSection(section) {
      this.$parent.currentSection = section
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-icon.users { background: #dbeafe; }
.stat-icon.products { background: #dcfce7; }
.stat-icon.orders { background: #fef3c7; }
.stat-icon.revenue { background: #f3e8ff; }
.stat-icon.pending { background: #ffe4e6; }
.stat-icon.support { background: #e0f2fe; }

.stat-info h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.5rem;
  color: #1e293b;
}

.stat-info p {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
}

.recent-section, .quick-actions {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.section-header h3 {
  margin: 0;
  color: #1e293b;
}

.btn-refresh {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 4px solid #3b82f6;
  transition: background 0.2s ease;
}

.order-item:hover {
  background: #f1f5f9;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.order-date {
  font-size: 0.8rem;
  color: #64748b;
}

.order-user {
  color: #475569;
  font-size: 0.9rem;
}

.order-details {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.order-amount {
  font-weight: 600;
  color: #059669;
}

.order-status {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed { background: #dcfce7; color: #166534; }
.status-cancelled { background: #fee2e2; color: #dc2626; }

.loading, .empty-state {
  text-align: center;
  padding: 2rem;
  color: #64748b;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.quick-actions h3 {
  margin: 0 0 1rem 0;
  color: #1e293b;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-btn {
  background: white;
  border: 2px solid #e2e8f0;
  padding: 1.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.action-btn:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.action-icon {
  font-size: 2rem;
}

.action-btn span:last-child {
  font-weight: 500;
  color: #374151;
}
</style>