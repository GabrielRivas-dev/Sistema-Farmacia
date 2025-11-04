<template>
  <div class="cart-overlay" v-if="isOpen" @click="closeCart">
    <div class="cart-sidebar" @click.stop>
      <!-- Header del carrito -->
      <div class="cart-header">
        <h3>🛒 Tu Carrito ({{ totalItems }})</h3>
        <button class="close-btn" @click="closeCart">×</button>
      </div>

      <!-- Lista de productos -->
      <div class="cart-content">
        <div v-if="isEmpty" class="empty-cart">
          <div class="empty-icon">😔</div>
          <p>Tu carrito está vacío</p>
          <button class="continue-shopping" @click="closeCart">
            Seguir comprando
          </button>
        </div>

        <div v-else class="cart-items">
          <div v-for="item in items" :key="item.id" class="cart-item">
            <div class="item-image">
              <img :src="item.imagen" :alt="item.nombre" @error="handleImageError">
            </div>
            
            <div class="item-details">
              <h4 class="item-name">{{ item.nombre }}</h4>
              <p class="item-laboratory">{{ item.laboratorio }}</p>
              <p class="item-price">S/ {{ item.precio.toFixed(2) }}</p>
              
              <div class="quantity-controls">
                <button 
                  class="qty-btn" 
                  @click="updateQuantity(item.id, item.quantity - 1)"
                  :disabled="item.quantity <= 1"
                >
                  −
                </button>
                <span class="quantity">{{ item.quantity }}</span>
                <button 
                  class="qty-btn" 
                  @click="updateQuantity(item.id, item.quantity + 1)"
                >
                  +
                </button>
              </div>
            </div>

            <button class="remove-btn" @click="removeItem(item.id)">
              🗑️
            </button>
          </div>
        </div>
      </div>

      <!-- Footer del carrito -->
      <div v-if="!isEmpty" class="cart-footer">
        <div class="cart-totals">
          <div class="total-line">
            <span>Subtotal ({{ totalItems }} items):</span>
            <span>S/ {{ totalPrice.toFixed(2) }}</span>
          </div>
          <div class="total-line">
            <span>Envío:</span>
            <span>S/ 5.00</span>
          </div>
          <div class="total-line grand-total">
            <span>Total:</span>
            <span>S/ {{ (totalPrice + 5).toFixed(2) }}</span>
          </div>
        </div>

        <div class="cart-actions">
          <button class="btn-clear" @click="clearCart">
            Vaciar carrito
          </button>
          <button class="btn-checkout" @click="handleCheckout">
            Proceder al pago
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useCartStore } from '@/stores/cart'
import { storeToRefs } from 'pinia'

export default {
  name: 'CartSidebar',
  setup() {
    const cartStore = useCartStore()
    // Hacer reactivos todos los estados y getters
    const { items, isOpen, totalItems, totalPrice, isEmpty } = storeToRefs(cartStore)

    return {
      items,
      isOpen,
      totalItems,
      totalPrice,
      isEmpty,
      removeItem: cartStore.removeItem,
      updateQuantity: cartStore.updateQuantity,
      clearCart: cartStore.clearCart,
      closeCart: cartStore.closeCart
    }
  },
  methods: {
    handleImageError(event) {
      event.target.src = 'https://picsum.photos/100/100?random=1'
    },

    handleCheckout() {
      if (!localStorage.getItem('auth_token')) {
        alert('⚠️ Necesitas iniciar sesión para proceder al pago')
        this.closeCart()
        this.$emit('navigate-to-login')
        return
      }

      // Aquí iría la lógica de checkout
      this.closeCart()
  this.$emit('navigate-to-checkout')
    }
  }
}
</script>

<style scoped>
/* Tus estilos se mantienen igual */
.cart-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  justify-content: flex-end;
}

.cart-sidebar {
  width: 400px;
  height: 100vh;
  background: white;
  display: flex;
  flex-direction: column;
  animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.cart-header h3 {
  margin: 0;
  color: #1e293b;
  font-size: 1.3rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #64748b;
  padding: 0.5rem;
  border-radius: 4px;
}

.close-btn:hover {
  background: #e2e8f0;
  color: #374151;
}

.cart-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.empty-cart {
  text-align: center;
  padding: 3rem 1rem;
  color: #64748b;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.continue-shopping {
  background: #2563eb;
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 1rem;
}

.continue-shopping:hover {
  background: #1d4ed8;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.item-image {
  width: 80px;
  height: 80px;
  border-radius: 6px;
  overflow: hidden;
  flex-shrink: 0;
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details {
  flex: 1;
}

.item-name {
  margin: 0 0 0.25rem 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

.item-laboratory {
  margin: 0 0 0.5rem 0;
  font-size: 0.8rem;
  color: #64748b;
}

.item-price {
  margin: 0 0 0.75rem 0;
  font-weight: 700;
  color: #2563eb;
  font-size: 1rem;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.qty-btn:hover:not(:disabled) {
  background: #f3f4f6;
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  min-width: 30px;
  text-align: center;
  font-weight: 600;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  align-self: flex-start;
}

.remove-btn:hover {
  background: #fef2f2;
}

.cart-footer {
  border-top: 1px solid #e2e8f0;
  padding: 1.5rem;
  background: #f8fafc;
}

.cart-totals {
  margin-bottom: 1.5rem;
}

.total-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: #374151;
}

.grand-total {
  font-weight: 700;
  font-size: 1.1rem;
  color: #1e293b;
  border-top: 1px solid #e2e8f0;
  padding-top: 0.5rem;
  margin-top: 0.5rem;
}

.cart-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-clear {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
  padding: 0.8rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.btn-clear:hover {
  background: #fee2e2;
}

.btn-checkout {
  background: #059669;
  color: white;
  border: none;
  padding: 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
}

.btn-checkout:hover {
  background: #047857;
}

/* Responsive */
@media (max-width: 768px) {
  .cart-sidebar {
    width: 100%;
  }
}
</style>  