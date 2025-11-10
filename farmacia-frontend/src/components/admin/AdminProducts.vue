<template>
  <div class="admin-products">
    <div class="section-header">
      <h2>Gestión de Productos</h2>
      <div class="header-actions">
        <div class="search-box">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar productos..." 
            @input="searchProducts"
          >
          <span class="search-icon">🔍</span>
        </div>
        <button class="btn-primary" @click="showAddProductModal = true">
          <span>+</span>
          Agregar Producto
        </button>
      </div>
    </div>

    <!-- Products Table -->
    <div class="products-table-container">
      <!-- Loading and empty states -->
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminProducts',
  data() {
    return {
      searchQuery: '',
      showAddProductModal: false
    }
  },
  methods: {
    searchProducts() {
      // Implement search
    }
  }
}
</script>

<style scoped>
.admin-products {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-primary {
  background: #10b981;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}
</style>