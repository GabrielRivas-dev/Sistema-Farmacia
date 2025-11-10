<template>
  <div class="admin-users">
    <div class="section-header">
      <h2>Gestión de Usuarios</h2>
      <div class="header-actions">
        <div class="search-box">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar usuarios..." 
            @input="searchUsers"
          >
          <span class="search-icon">🔍</span>
        </div>
      </div>
    </div>

    <div class="users-table-container">
      <div v-if="loading" class="loading">Cargando usuarios...</div>
      
      <div v-else-if="users.length === 0" class="empty-state">
        <div class="empty-icon">👥</div>
        <p>No se encontraron usuarios</p>
      </div>

      <div v-else class="users-table">
        <div class="table-header">
          <div class="table-cell">Usuario</div>
          <div class="table-cell">Email</div>
          <div class="table-cell">Rol</div>
          <div class="table-cell">Estado</div>
          <div class="table-cell">Registro</div>
          <div class="table-cell actions">Acciones</div>
        </div>

        <div v-for="user in users" :key="user.id" class="table-row">
          <div class="table-cell">
            <div class="user-info">
              <div class="user-avatar">{{ getUserInitial(user.name) }}</div>
              <div class="user-details">
                <strong>{{ user.name }}</strong>
                <span>ID: {{ user.id }}</span>
              </div>
            </div>
          </div>
          
          <div class="table-cell">{{ user.email }}</div>
          
          <div class="table-cell">
            <select 
              :value="user.role" 
              @change="updateUserRole(user.id, $event.target.value)"
              class="role-select"
            >
              <option value="user">Usuario</option>
              <option value="admin">Administrador</option>
            </select>
          </div>
          
          <div class="table-cell">
            <label class="toggle-switch">
              <input 
                type="checkbox" 
                :checked="user.is_active" 
                @change="toggleUserActive(user.id, $event.target.checked)"
              >
              <span class="slider"></span>
            </label>
            <span class="status-text">{{ user.is_active ? 'Activo' : 'Inactivo' }}</span>
          </div>
          
          <div class="table-cell">{{ formatDate(user.created_at) }}</div>
          
          <div class="table-cell actions">
            <button 
              class="btn-action view" 
              @click="viewUserDetails(user)"
              title="Ver detalles"
            >
              👁️
            </button>
            <button 
              class="btn-action delete" 
              @click="confirmDeleteUser(user)"
              title="Eliminar usuario"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="users.length > 0" class="pagination">
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
  </div>
</template>

<script>
import api from '@/services/api'

export default {
  name: 'AdminUsers',
  data() {
    return {
      users: [],
      loading: false,
      searchQuery: '',
      currentPage: 1,
      totalPages: 1,
      searchTimeout: null
    }
  },
  async mounted() {
    await this.loadUsers()
  },
  methods: {
    async loadUsers() {
      this.loading = true
      try {
        const params = {
          page: this.currentPage,
          search: this.searchQuery || undefined
        }
        
        const response = await api.get('/admin/users', { params })
        this.users = response.data.data || response.data
        this.totalPages = response.data.last_page || 1
      } catch (error) {
        console.error('Error loading users:', error)
        this.$toast.error('Error al cargar usuarios')
      } finally {
        this.loading = false
      }
    },

    searchUsers() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1
        this.loadUsers()
      }, 500)
    },

    async updateUserRole(userId, newRole) {
      try {
        await api.put(`/admin/users/${userId}`, { role: newRole })
        this.$toast.success('Rol de usuario actualizado')
        this.loadUsers() // Recargar para ver cambios
      } catch (error) {
        console.error('Error updating user role:', error)
        this.$toast.error('Error al actualizar el rol')
        this.loadUsers() // Recargar para revertir cambios visuales
      }
    },

    async toggleUserActive(userId, isActive) {
      try {
        await api.put(`/admin/users/${userId}`, { is_active: isActive })
        this.$toast.success(`Usuario ${isActive ? 'activado' : 'desactivado'}`)
      } catch (error) {
        console.error('Error updating user status:', error)
        this.$toast.error('Error al actualizar estado')
        this.loadUsers() // Recargar para revertir cambios visuales
      }
    },

    viewUserDetails(user) {
      // Implementar vista de detalles del usuario
      console.log('View user details:', user)
      this.$toast.info(`Viendo detalles de ${user.name}`)
    },

    confirmDeleteUser(user) {
      if (confirm(`¿Estás seguro de que quieres eliminar al usuario ${user.name}?`)) {
        this.deleteUser(user.id)
      }
    },

    async deleteUser(userId) {
      try {
        await api.delete(`/admin/users/${userId}`)
        this.$toast.success('Usuario eliminado correctamente')
        this.loadUsers()
      } catch (error) {
        console.error('Error deleting user:', error)
        this.$toast.error('Error al eliminar usuario')
      }
    },

    changePage(page) {
      this.currentPage = page
      this.loadUsers()
    },

    getUserInitial(name) {
      return name ? name.charAt(0).toUpperCase() : 'U'
    },

    formatDate(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('es-ES')
    }
  }
}
</script>

<style scoped>
.admin-users {
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

.users-table-container {
  padding: 1.5rem;
}

.loading, .empty-state {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.users-table {
  display: flex;
  flex-direction: column;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1fr;
  background: #f8fafc;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e2e8f0;
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 2fr 1fr 1fr 1fr 1fr;
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

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-details span {
  font-size: 0.8rem;
  color: #64748b;
}

.role-select {
  padding: 0.25rem 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  background: white;
  font-size: 0.8rem;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 20px;
  margin-right: 0.5rem;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 20px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #10b981;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

.status-text {
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

.btn-action.delete:hover {
  background: #fee2e2;
}

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
</style>