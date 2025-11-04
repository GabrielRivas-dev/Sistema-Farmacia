<template>
  <div class="product-card" :class="{ 'out-of-stock': !product.disponible }">
    <!-- Badge de estado -->
    <div class="product-badges">
      <span v-if="!product.disponible" class="badge out-of-stock-badge">Agotado</span>
      <span v-if="product.promocion" class="badge promotion-badge">Oferta</span>
      <span v-if="product.receta" class="badge prescription-badge">Con receta</span>
    </div>

    <!-- Imagen del producto -->
    <div class="product-image">
      <img 
        :src="product.imagen" 
        :alt="product.nombre"
        @error="handleImageError"
      >
      <div v-if="!product.disponible" class="overlay">
        <span>No disponible</span>
      </div>
    </div>

    <!-- Información del producto -->
    <div class="product-info">
      <h3 class="product-name">{{ product.nombre }}</h3>
      <p class="product-description">{{ product.descripcion }}</p>
      
      <div class="product-laboratory">
        <span class="lab-label">Laboratorio:</span>
        <span class="lab-name">{{ product.laboratorio }}</span>
      </div>

      <!-- Precio -->
      <div class="product-pricing">
        <div class="price-main">
          <span class="current-price">S/ {{ product.precio }}</span>
          <span v-if="product.precio_original" class="original-price">
            S/ {{ product.precio_original }}
          </span>
        </div>
        <div v-if="product.descuento" class="discount">
          -{{ product.descuento }}%
        </div>
      </div>

      <!-- Stock -->
      <div class="product-stock">
        <span class="stock-label">Disponible:</span>
        <span class="stock-value" :class="{ 'low-stock': product.stock < 10 }">
          {{ product.disponible ? product.stock + ' unidades' : 'Agotado' }}
        </span>
      </div>

      <!-- Acciones -->
      <div class="product-actions">
        <button 
          class="btn-add-cart" 
          :disabled="!product.disponible"
          @click="addToCart"
        >
          <span class="cart-icon">🛒</span>
          {{ product.disponible ? 'Agregar al carrito' : 'No disponible' }}
        </button>
        
        <button class="btn-favorite" @click="toggleFavorite">
          <span :class="['heart-icon', { 'favorited': product.favorito }]">
            {{ product.favorito ? '❤️' : '🤍' }}
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { useCartStore } from '@/stores/cart'

export default {
  name: 'ProductCard',
  props: {
    product: {
      type: Object,
      required: true,
      default: () => ({
        id: null,
        nombre: '',
        descripcion: '',
        precio: 0,
        precio_original: null,
        descuento: null,
        imagen: '',
        laboratorio: '',
        stock: 0,
        disponible: true,
        promocion: false,
        receta: false,
        favorito: false
      })
    }
  },
  setup() {
    const cartStore = useCartStore()
    
    return {
      cartStore
    }
  },
  methods: {
    addToCart() {
      if (this.product.disponible) {
        this.cartStore.addItem(this.product)
        this.$emit('add-to-cart', this.product);
        // Mostrar notificación
        this.showAddToCartNotification()
      }
    },
    toggleFavorite() {
      this.$emit('toggle-favorite', this.product.id);
    },
    handleImageError(event) {
      event.target.src = 'https://picsum.photos/300/200?random=1';
    },
    showAddToCartNotification() {
      const notification = document.createElement('div')
      notification.className = 'cart-notification'
      notification.innerHTML = `
        <span>✅ ${this.product.nombre} agregado al carrito</span>
      `
      notification.style.cssText = `
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
      
      document.body.appendChild(notification)
      
      setTimeout(() => {
        notification.remove()
      }, 3000)
    }
  }
}
</script>

<style scoped>
.product-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
  border: 1px solid #e2e8f0;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.product-card.out-of-stock {
  opacity: 0.7;
}

/* Badges */
.product-badges {
  position: absolute;
  top: 12px;
  left: 12px;
  display: flex;
  flex-direction: column;
  gap: 5px;
  z-index: 2;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.out-of-stock-badge {
  background: #ef4444;
  color: white;
}

.promotion-badge {
  background: #f59e0b;
  color: white;
}

.prescription-badge {
  background: #8b5cf6;
  color: white;
}

/* Imagen */
.product-image {
  position: relative;
  height: 200px;
  overflow: hidden;
  background: #f8fafc;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
}

/* Información */
.product-info {
  padding: 1.5rem;
}

.product-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
  line-height: 1.3;
}

.product-description {
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.4;
  margin: 0 0 1rem 0;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-laboratory {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.85rem;
}

.lab-label {
  color: #64748b;
  font-weight: 500;
}

.lab-name {
  color: #374151;
  font-weight: 600;
}

/* Precios */
.product-pricing {
  display: flex;
  justify-content: between;
  align-items: center;
  margin-bottom: 1rem;
}

.price-main {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.current-price {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2563eb;
}

.original-price {
  font-size: 1rem;
  color: #94a3b8;
  text-decoration: line-through;
}

.discount {
  background: #dc2626;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  margin-left: auto;
}

/* Stock */
.product-stock {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.2rem;
  padding: 0.5rem;
  background: #f8fafc;
  border-radius: 6px;
}

.stock-label {
  color: #64748b;
  font-size: 0.85rem;
}

.stock-value {
  font-weight: 600;
  color: #059669;
}

.stock-value.low-stock {
  color: #d97706;
}

/* Acciones */
.product-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-add-cart {
  flex: 1;
  background: #2563eb;
  color: white;
  border: none;
  padding: 0.8rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-add-cart:hover:not(:disabled) {
  background: #1d4ed8;
  transform: translateY(-2px);
}

.btn-add-cart:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.btn-favorite {
  background: #f1f5f9;
  border: none;
  padding: 0.8rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-favorite:hover {
  background: #e2e8f0;
  transform: scale(1.1);
}

.heart-icon.favorited {
  animation: pulse 0.5s ease;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

/* Estilos para la notificación */
.cart-notification {
  font-weight: 600;
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
@media (max-width: 768px) {
  .product-info {
    padding: 1rem;
  }
  
  .product-actions {
    flex-direction: column;
  }
  
  .btn-favorite {
    align-self: center;
  }
}
</style>