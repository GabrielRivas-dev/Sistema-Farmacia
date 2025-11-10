<template>
  <div class="admin-orders">
    <div class="section-header">
      <h2>Gestión de Pedidos</h2>
      <div class="header-actions">
        <div class="filter-group">
          <label>Filtrar por estado:</label>
          <select v-model="statusFilter" @change="loadOrders">
            <option value="">Todos los estados</option>
            <option value="pending">Pendiente</option>
            <option value="processing">Procesando</option>
            <option value="completed">Completado</option>
            <option value="cancelled">Cancelado</option>
          </select>
        </div>
        <div class="search-box">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar pedidos..." 
            @input="searchOrders"
          >
          <span class="search-icon">🔍</span>
        </div>
      </div>
    </div>

    <!-- Statistics -->
    <div class="orders-stats">
      <div class="stat-item">
        <span class="stat-number">{{ stats.total || 0 }}</span>
        <span class="stat-label">Total</span>
      </div>
      <div class="stat-item pending">
        <span class="stat-number">{{ stats.pending || 0 }}</span>
        <span class="stat-label">Pendientes</span>
      </div>
      <div class="stat-item processing">
        <span class="stat-number">{{ stats.processing || 0 }}</span>
        <span class="stat-label">Procesando</span>
      </div>
      <div class="stat-item completed">
        <span class="stat-number">{{ stats.completed || 0 }}</span>
        <span class="stat-label">Completados</span>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="orders-table-container">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Cargando pedidos...</p>
      </div>
      
      <div v-else-if="orders.length === 0" class="empty-state">
        <div class="empty-icon">📦</div>
        <p>No se encontraron pedidos</p>
        <button class="btn-refresh" @click="loadOrders">🔄 Recargar</button>
      </div>

      <div v-else class="orders-table">
        <div class="table-header">
          <div class="table-cell">Pedido</div>
          <div class="table-cell">Cliente</div>
          <div class="table-cell">Productos</div>
          <div class="table-cell">Total</div>
          <div class="table-cell">Estado</div>
          <div class="table-cell">Fecha</div>
          <div class="table-cell actions">Acciones</div>
        </div>

        <div v-for="order in orders" :key="order.id" class="table-row">
          <div class="table-cell">
            <div class="order-number">
              <strong>#{{ order.id }}</strong>
            </div>
          </div>
          
          <div class="table-cell">
            <div class="customer-info">
              <strong>{{ order.user?.name || 'Cliente' }}</strong>
              <span>{{ order.user?.email || '' }}</span>
            </div>
          </div>
          
          <div class="table-cell">
            <div class="products-count">
              {{ getProductsCount(order) }} productos
            </div>
          </div>
          
          <div class="table-cell">
            <div class="order-total">
              S/ {{ (order.total || 0).toFixed(2) }}
            </div>
          </div>
          
          <div class="table-cell">
            <select 
              :value="order.status" 
              @change="updateOrderStatus(order.id, $event.target.value)"
              :class="`status-select status-${order.status}`"
            >
              <option value="pending">Pendiente</option>
              <option value="processing">Procesando</option>
              <option value="completed">Completado</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>
          
          <div class="table-cell">
            <div class="order-date">
              {{ formatDate(order.created_at) }}
            </div>
            <div class="order-time">
              {{ formatTime(order.created_at) }}
            </div>
          </div>
          
          <div class="table-cell actions">
            <button 
              class="btn-action view" 
              @click="viewOrderDetails(order)"
              title="Ver detalles"
            >
              👁️
            </button>
            <button 
              class="btn-action edit" 
              @click="editOrder(order)"
              title="Editar pedido"
            >
              ✏️
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="orders.length > 0" class="pagination">
        <button 
          :disabled="currentPage === 1" 
          @click="changePage(currentPage - 1)"
          class="pagination-btn"
        >
          ← Anterior
        </button>
        
        <span class="page-info">
          Página {{ currentPage }} de {{ totalPages }}
        </span>
        
        <button 
          :disabled="currentPage === totalPages" 
          @click="changePage(currentPage + 1)"
          class="pagination-btn"
        >
          Siguiente →
        </button>
      </div>
    </div>

    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="modal-overlay" @click="selectedOrder = null">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Detalles del Pedido #{{ selectedOrder.id }}</h3>
          <button class="modal-close" @click="selectedOrder = null">×</button>
        </div>
        <div class="modal-body">
          <OrderDetails :order="selectedOrder" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'

// Componente para detalles del pedido (puedes moverlo a un archivo separado)
const OrderDetails = {
  props: ['order'],
  template: `
    <div class="order-details">
      <div class="detail-section">
        <h4>Información del Cliente</h4>
        <div class="detail-grid">
          <div class="detail-item">
            <label>Nombre:</label>
            <span>{{ order.user?.name || 'N/A' }}</span>
          </div>
          <div class="detail-item">
            <label>Email:</label>
            <span>{{ order.user?.email || 'N/A' }}</span>
          </div>
          <div class="detail-item">
            <label>Teléfono:</label>
            <span>{{ order.user?.phone || 'N/A' }}</span>
          </div>
        </div>
      </div>
      
      <div class="detail-section">
        <h4>Productos</h4>
        <div class="products-list">
          <div v-for="item in order.items || []" :key="item.id" class="product-item">
            <div class="product-info">
              <strong>{{ item.product?.nombre || 'Producto' }}</strong>
              <span>Cantidad: {{ item.quantity }}</span>
            </div>
            <div class="product-price">
              S/ {{ (item.price * item.quantity).toFixed(2) }}
            </div>
          </div>
        </div>
      </div>
      
      <div class="detail-section">
        <h4>Resumen</h4>
        <div class="summary-grid">
          <div class="summary-item">
            <label>Subtotal:</label>
            <span>S/ {{ (order.total - 5).toFixed(2) }}</span>
          </div>
          <div class="summary-item">
            <label>Envío:</label>
            <span>S/ 5.00</span>
          </div>
          <div class="summary-item total">
            <label>Total:</label>
            <span>S/ {{ (order.total || 0).toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>
  `
}

export default {
  name: 'AdminOrders',
  components: {
    OrderDetails
  },
  data() {
    return {
      orders: [],
      stats: {},
      loading: false,
      searchQuery: '',
      statusFilter: '',
      currentPage: 1,
      totalPages: 1,
      selectedOrder: null,
      searchTimeout: null
    }
  },
  async mounted() {
    await this.loadOrders()
    await this.loadOrderStats()
  },
  methods: {
    async loadOrders() {
      this.loading = true
      try {
        const params = {
          page: this.currentPage,
          status: this.statusFilter || undefined,
          search: this.searchQuery || undefined
        }
        
        const response = await api.get('/admin/orders', { params })
        this.orders = response.data.data || response.data
        this.totalPages = response.data.last_page || 1
      } catch (error) {
        console.error('Error loading orders:', error)
        this.$toast.error('Error al cargar pedidos')
      } finally {
        this.loading = false
      }
    },

    async loadOrderStats() {
      try {
        const response = await api.get('/admin/dashboard-stats')
        this.stats = {
          total: response.data.total_orders,
          pending: response.data.pending_orders,
          processing: 0, // Necesitarías agregar este campo en el backend
          completed: response.data.total_orders - response.data.pending_orders
        }
      } catch (error) {
        console.error('Error loading order stats:', error)
      }
    },

    searchOrders() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1
        this.loadOrders()
      }, 500)
    },

    async updateOrderStatus(orderId, newStatus) {
      try {
        await api.put(`/admin/orders/${orderId}/status`, { status: newStatus })
        this.$toast.success('Estado del pedido actualizado')
        this.loadOrders()
        this.loadOrderStats()
      } catch (error) {
        console.error('Error updating order status:', error)
        this.$toast.error('Error al actualizar el estado')
        this.loadOrders() // Recargar para revertir cambios visuales
      }
    },

    viewOrderDetails(order) {
      this.selectedOrder = order
    },

    editOrder(order) {
      // Implementar edición de pedido
      console.log('Edit order:', order)
      this.$toast.info(`Editando pedido #${order.id}`)
    },

    changePage(page) {
      this.currentPage = page
      this.loadOrders()
    },

    getProductsCount(order) {
      if (order.items && Array.isArray(order.items)) {
        return order.items.reduce((total, item) => total + item.quantity, 0)
      }
      return 0
    },

    formatDate(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('es-ES')
    },

    formatTime(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.admin-orders {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.section-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-header h2 {
  margin: 0;
  color: #1e293b;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group label {
  font-size: 0.9rem;
  color: #64748b;
}

.filter-group select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box input {
  padding: 0.5rem 2.5rem 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  width: 250px;
  font-size: 0.9rem;
}

.search-icon {
  position: absolute;
  right: 0.75rem;
  color: #64748b;
}

/* Statistics */
.orders-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  padding: 1.5rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.stat-item {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-item.pending { border-left: 4px solid #f59e0b; }
.stat-item.processing { border-left: 4px solid #3b82f6; }
.stat-item.completed { border-left: 4px solid #10b981; }

.stat-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.8rem;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 600;
}

/* Orders Table */
.orders-table-container {
  padding: 1.5rem;
}

.loading, .empty-state {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.btn-refresh {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 1rem;
}

.orders-table {
  display: flex;
  flex-direction: column;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.table-header {
  display: grid;
  grid-template-columns: 1fr 2fr 1fr 1fr 1.5fr 1.5fr 1fr;
  background: #f8fafc;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e2e8f0;
}

.table-row {
  display: grid;
  grid-template-columns: 1fr 2fr 1fr 1fr 1.5fr 1.5fr 1fr;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.2s ease;
}

.table-row:hover {
  background: #f8fafc;
}

.table-row:last-child {
  border-bottom: none;
}

.table-cell {
  padding: 1rem;
  display: flex;
  align-items: center;
  border-right: 1px solid #f1f5f9;
}

.table-cell:last-child {
  border-right: none;
}

.table-cell.actions {
  justify-content: center;
  gap: 0.5rem;
}

.order-number strong {
  color: #3b82f6;
}

.customer-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.customer-info span {
  font-size: 0.8rem;
  color: #64748b;
}

.products-count {
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

.order-total {
  font-weight: 600;
  color: #059669;
}

.status-select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-select.status-pending { background: #fef3c7; color: #92400e; }
.status-select.status-processing { background: #dbeafe; color: #1e40af; }
.status-select.status-completed { background: #dcfce7; color: #166534; }
.status-select.status-cancelled { background: #fee2e2; color: #dc2626; }

.order-date {
  font-weight: 500;
  color: #1e293b;
}

.order-time {
  font-size: 0.8rem;
  color: #64748b;
}

.btn-action {
  background: none;
  border: none;
  padding: 0.5rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-action.view:hover {
  background: #dbeafe;
}

.btn-action.edit:hover {
  background: #fef3c7;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.pagination-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

.pagination-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.page-info {
  color: #64748b;
  font-size: 0.9rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  margin: 0;
  color: #1e293b;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #64748b;
}

.modal-body {
  padding: 1.5rem;
}

/* Order Details Styles */
.order-details {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-section h4 {
  margin: 0 0 1rem 0;
  color: #1e293b;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 0.5rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item label {
  font-weight: 600;
  color: #64748b;
  font-size: 0.9rem;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 6px;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.product-info span {
  font-size: 0.8rem;
  color: #64748b;
}

.product-price {
  font-weight: 600;
  color: #059669;
}

.summary-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.summary-item.total {
  border-top: 2px solid #e2e8f0;
  border-bottom: none;
  font-weight: 700;
  font-size: 1.1rem;
  color: #1e293b;
  padding-top: 1rem;
}
</style>