<template>
  <div class="product-catalog">
    <!-- Header del catálogo -->
    <div class="catalog-header">
      <h2 class="catalog-title">Catálogo de Medicamentos</h2>
      <p class="catalog-subtitle">Encuentra los mejores productos para tu salud</p>
      
      <!-- Controles -->
      <div class="catalog-controls">
        <!-- Búsqueda -->
        <div class="search-control">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Buscar medicamentos..." 
            class="search-input"
            @input="handleSearch"
          >
          <span class="search-icon">🔍</span>
        </div>

        <!-- Filtros -->
        <div class="filter-controls">
          <select v-model="selectedCategory" @change="loadProducts" class="filter-select">
            <option value="">Todas las categorías</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.nombre }}
            </option>
          </select>

          <select v-model="sortBy" @change="loadProducts" class="filter-select">
            <option value="nombre">Ordenar por nombre</option>
            <option value="precio">Ordenar por precio</option>
            <option value="laboratorio">Ordenar por laboratorio</option>
          </select>

          <button class="btn-filter" @click="toggleFilters">
            🎛️ Filtros
          </button>
        </div>
      </div>

      <!-- Filtros avanzados -->
      <div v-if="showAdvancedFilters" class="advanced-filters">
        <div class="filter-group">
          <label>Precio máximo: S/ {{ maxPrice }}</label>
          <input 
            v-model="maxPrice" 
            type="range" 
            min="0" 
            max="500" 
            step="10"
            class="price-slider"
            @change="loadProducts"
          >
        </div>
        
        <div class="filter-group">
          <label>
            <input v-model="onlyAvailable" type="checkbox" @change="loadProducts">
            Solo productos disponibles
          </label>
        </div>

        <div class="filter-group">
          <label>
            <input v-model="onlyPromotions" type="checkbox" @change="loadProducts">
            Solo ofertas
          </label>
        </div>
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando productos...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">⚠️</div>
      <h3>Error al cargar productos</h3>
      <p>{{ error }}</p>
      <button class="btn-retry" @click="loadProducts">Reintentar</button>
    </div>

    <!-- Productos -->
    <div v-else class="products-grid">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="formatProduct(product)"
        @add-to-cart="handleAddToCart"
        @toggle-favorite="handleToggleFavorite"
      />
    </div>

    <!-- Sin resultados -->
    <div v-if="!loading && !error && products.length === 0" class="empty-state">
      <div class="empty-icon">😔</div>
      <h3>No se encontraron productos</h3>
      <p>Intenta ajustar tus filtros de búsqueda</p>
      <button class="btn-reset" @click="resetFilters">Restablecer filtros</button>
    </div>

    <!-- Paginación -->
    <div v-if="!loading && !error && products.length > 0" class="pagination">
      <button 
        class="pagination-btn" 
        :disabled="currentPage === 1"
        @click="prevPage"
      >
        ← Anterior
      </button>
      
      <span class="pagination-info">
        Página {{ currentPage }} de {{ pagination.last_page || 1 }}
      </span>
      
      <button 
        class="pagination-btn" 
        :disabled="currentPage === pagination.last_page"
        @click="nextPage"
      >
        Siguiente →
      </button>
    </div>
  </div>
</template>

<script>
import ProductCard from './ProductCard.vue'
import api from '@/services/api'

export default {
  name: 'ProductCatalog',
  components: {
    ProductCard
  },
  data() {
    return {
      products: [],
      categories: [],
      loading: true,
      error: null,
      searchQuery: '',
      selectedCategory: '',
      sortBy: 'nombre',
      showAdvancedFilters: false,
      maxPrice: 500,
      onlyAvailable: false,
      onlyPromotions: false,
      currentPage: 1,
      pagination: {},
      searchTimeout: null
    }
  },
  async mounted() {
    await this.loadCategories();
    await this.loadProducts();
  },
  methods: {
    async loadProducts() {
      try {
        this.loading = true;
        this.error = null;

        const params = {
          page: this.currentPage,
          per_page: 12
        };

        // Agregar filtros
        if (this.searchQuery) {
          params.buscar = this.searchQuery;
        }

        if (this.selectedCategory) {
          params.categoria = this.selectedCategory;
        }

        if (this.onlyAvailable) {
          params.disponible = true;
        }

        if (this.onlyPromotions) {
          params.promocion = true;
        }

        if (this.sortBy) {
          params.sort_by = this.sortBy;
          params.sort_order = 'asc';
        }

        const response = await api.get('/productos', { params });
        
        if (response.data.success) {
          this.products = response.data.data;
          this.pagination = response.data.pagination;
        } else {
          throw new Error('Error en la respuesta del servidor');
        }

      } catch (error) {
        console.error('Error cargando productos:', error);
        this.error = error.response?.data?.message || 'Error al cargar los productos';
        this.products = [];
      } finally {
        this.loading = false;
      }
    },

    async loadCategories() {
      try {
        const response = await api.get('/productos-categorias');
        if (response.data.success) {
          this.categories = response.data.data;
        }
      } catch (error) {
        console.error('Error cargando categorías:', error);
        // Si falla, usar categorías por defecto
        this.categories = [
          { id: 'analgesicos', nombre: 'Analgésicos' },
          { id: 'antibioticos', nombre: 'Antibióticos' },
          { id: 'vitaminas', nombre: 'Vitaminas' },
          { id: 'dermatologicos', nombre: 'Dermatológicos' },
          { id: 'respiratorios', nombre: 'Respiratorios' }
        ];
      }
    },

    formatProduct(product) {
      return {
        id: product.id,
        nombre: product.nombre,
        descripcion: product.descripcion,
        precio: parseFloat(product.precio),
        precio_original: product.precio_original ? parseFloat(product.precio_original) : null,
        descuento: product.descuento,
        imagen: product.imagen || 'https://picsum.photos/300/200?random=' + product.id,
        laboratorio: product.laboratorio,
        stock: product.stock,
        disponible: product.disponible,
        promocion: product.en_promocion,
        receta: product.requiere_receta,
        favorito: product.es_favorito || false,
        categoria: product.categoria,
        principio_activo: product.principio_activo,
        presentacion: product.presentacion,
        concentracion: product.concentracion,
        fecha_vencimiento: product.fecha_vencimiento,
        estado_vencimiento: product.estado_vencimiento
      };
    },

    handleSearch() {
      // Debounce para evitar muchas llamadas a la API
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1;
        this.loadProducts();
      }, 500);
    },

    handleAddToCart(product) {
      this.$emit('add-to-cart', product);
      // La notificación ahora se maneja en ProductCard.vue
      console.log('Producto agregado desde catalog:', product.nombre);
    },

    async handleToggleFavorite(productId) {
      try {
        // En una implementación real, aquí harías una llamada a la API
        const product = this.products.find(p => p.id === productId);
        if (product) {
          product.es_favorito = !product.es_favorito;
          
          // Simular llamada a API para actualizar favorito
          // await api.patch(`/productos/${productId}/favorito`, {
          //   favorito: product.es_favorito
          // });
        }
      } catch (error) {
        console.error('Error actualizando favorito:', error);
      }
    },

    toggleFilters() {
      this.showAdvancedFilters = !this.showAdvancedFilters;
    },

    resetFilters() {
      this.searchQuery = '';
      this.selectedCategory = '';
      this.maxPrice = 500;
      this.onlyAvailable = false;
      this.onlyPromotions = false;
      this.currentPage = 1;
      this.loadProducts();
    },

    nextPage() {
      if (this.currentPage < this.pagination.last_page) {
        this.currentPage++;
        this.loadProducts();
        this.scrollToTop();
      }
    },

    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.loadProducts();
        this.scrollToTop();
      }
    },

    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }
}
</script>

<style scoped>
/* Los estilos se mantienen igual que en la versión anterior */
.product-catalog {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.catalog-header {
  margin-bottom: 2rem;
}

.catalog-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.catalog-subtitle {
  color: #64748b;
  margin: 0 0 2rem 0;
}

.catalog-controls {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 1rem;
}

.search-control {
  position: relative;
  flex: 1;
  min-width: 300px;
}

.search-input {
  width: 100%;
  padding: 0.8rem 1rem 0.8rem 2.5rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #2563eb;
}

.search-icon {
  position: absolute;
  left: 0.8rem;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
}

.filter-controls {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-select {
  padding: 0.8rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  min-width: 180px;
}

.btn-filter {
  background: #f1f5f9;
  border: 2px solid #e2e8f0;
  padding: 0.8rem 1.2rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-filter:hover {
  background: #e2e8f0;
}

.advanced-filters {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 8px;
  margin-top: 1rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.price-slider {
  width: 100%;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.loading-state {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #2563eb;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  text-align: center;
  padding: 3rem;
  color: #dc2626;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.btn-retry {
  background: #dc2626;
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 1rem;
}

.btn-retry:hover {
  background: #b91c1c;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.btn-reset {
  background: #2563eb;
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 1rem;
}

.btn-reset:hover {
  background: #1d4ed8;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination-btn {
  background: #2563eb;
  color: white;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #1d4ed8;
}

.pagination-btn:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

.pagination-info {
  color: #64748b;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .product-catalog {
    padding: 1rem;
  }

  .catalog-controls {
    flex-direction: column;
  }

  .search-control {
    min-width: 100%;
  }

  .filter-controls {
    width: 100%;
    justify-content: space-between;
  }

  .filter-select {
    min-width: 150px;
  }

  .products-grid {
    grid-template-columns: 1fr;
  }

  .advanced-filters {
    grid-template-columns: 1fr;
  }
}
</style>