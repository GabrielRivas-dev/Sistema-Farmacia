<template>
  <div class="checkout-page">
    <!-- Header -->
    <div class="checkout-header">
      <div class="header-content">
        <button class="back-btn" @click="$router.back()">
          ← Volver
        </button>
        <h1>Confirmar Pago Móvil</h1>
        <div class="step-indicator">Paso 1 de 1</div>
      </div>
    </div>

    <div class="checkout-layout">
      <!-- Columna principal -->
      <div class="checkout-main">
        <!-- Información bancaria -->
        <div class="bank-info-card">
          <div class="card-header">
            <div class="bank-icon">🏦</div>
            <h2>Datos para Transferencia</h2>
          </div>
          
          <div class="bank-details">
            <div class="detail-row">
              <div class="detail-label">
                <span class="label-icon">🏢</span>
                Banco
              </div>
              <div class="detail-value">Banco de Venezuela</div>
            </div>
            
            <div class="detail-row">
              <div class="detail-label">
                <span class="label-icon">📱</span>
                Número
              </div>
              <div class="detail-value">0414-1234567</div>
            </div>
            
            <div class="detail-row">
              <div class="detail-label">
                <span class="label-icon">👤</span>
                Nombre
              </div>
              <div class="detail-value">FarmaPlus C.A.</div>
            </div>
            
            <div class="detail-row">
              <div class="detail-label">
                <span class="label-icon">📄</span>
                RIF
              </div>
              <div class="detail-value">J-12345678-9</div>
            </div>

            <div class="detail-row highlight">
              <div class="detail-label">
                <span class="label-icon">💰</span>
                Monto a Transferir
              </div>
              <div class="detail-value total-amount">S/ {{ (totalPrice + 5).toFixed(2) }}</div>
            </div>
          </div>

          <button class="copy-btn" @click="copyBankInfo">
            <span class="copy-icon">📋</span>
            Copiar todos los datos
          </button>
        </div>

        <!-- Formulario -->
        <div class="form-card">
          <h3>Completar Información de Pago</h3>
          
          <form @submit.prevent="enviarPago" class="payment-form">
            <!-- Banco usado -->
            <div class="form-group">
              <label for="banco" class="form-label">
                <span class="label-icon">🏦</span>
                Banco usado para el pago
              </label>
              <select 
                v-model="banco" 
                id="banco" 
                class="form-input"
                required
              >
                <option value="">Selecciona tu banco</option>
                <option value="Banco de Venezuela">Banco de Venezuela</option>
                <option value="Banesco">Banesco</option>
                <option value="Mercantil">Mercantil</option>
                <option value="Provincial">Provincial</option>
                <option value="Bancaribe">Bancaribe</option>
                <option value="Otro">Otro</option>
              </select>
            </div>

            <!-- Referencia de pago -->
            <div class="form-group">
              <label for="referencia" class="form-label">
                <span class="label-icon">🔢</span>
                Número de referencia
              </label>
              <input 
                v-model="referencia_pago" 
                type="text" 
                id="referencia" 
                class="form-input"
                placeholder="Ej: 123456789"
                required
              />
              <div class="input-help">Ingresa el número de referencia de tu transferencia</div>
            </div>

            <!-- Subida de imagen -->
            <div class="form-group">
              <label class="form-label">
                <span class="label-icon">📷</span>
                Comprobante de pago
              </label>
              
              <div 
                class="upload-area"
                :class="{ 
                  'has-file': imagen_pago
                }"
                @click="triggerFileInput"
              >
                <input 
                  type="file" 
                  ref="fileInput"
                  @change="onFileChange"
                  accept="image/*"
                  style="display: none"
                />
                
                <div class="upload-content">
                  <div v-if="!imagen_pago" class="upload-placeholder">
                    <div class="upload-icon">📤</div>
                    <div class="upload-text">
                      <p class="upload-title">Subir comprobante</p>
                      <p class="upload-subtitle">Haz clic para seleccionar una imagen</p>
                    </div>
                  </div>
                  
                  <div v-else class="file-preview">
                    <div class="file-icon">✅</div>
                    <div class="file-info">
                      <p class="file-name">{{ imagen_pago.name }}</p>
                      <p class="file-size">{{ formatFileSize(imagen_pago.size) }}</p>
                    </div>
                    <button 
                      type="button" 
                      class="change-file-btn"
                      @click.stop="triggerFileInput"
                    >
                      Cambiar
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="upload-requirements">
                <p>✅ Formatos: JPG, PNG, WEBP</p>
                <p>✅ Tamaño máximo: 5MB</p>
              </div>
            </div>

            <!-- Botón de envío -->
            <button 
              type="submit" 
              class="submit-btn"
              :disabled="loading || !formValid"
              :class="{ 'loading': loading, 'disabled': !formValid }"
            >
              <span v-if="loading" class="loading-spinner"></span>
              <span v-else class="submit-icon">✅</span>
              {{ loading ? 'Enviando comprobante...' : 'Confirmar Pago' }}
            </button>
          </form>
        </div>

        <!-- Mensajes de estado -->
        <div v-if="successMessage" class="message success-message">
          <div class="message-icon">🎉</div>
          <div class="message-content">
            <h4>¡Pago Confirmado!</h4>
            <p>{{ successMessage }}</p>
          </div>
        </div>

        <div v-if="errorMessage" class="message error-message">
          <div class="message-icon">⚠️</div>
          <div class="message-content">
            <h4>Error al procesar pago</h4>
            <p>{{ errorMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Sidebar con resumen -->
      <div class="checkout-sidebar">
        <div class="summary-card">
          <h3>Resumen del Pedido</h3>
          
          <div class="order-items">
            <div 
              v-for="item in items" 
              :key="item.id" 
              class="order-item"
            >
              <img 
                :src="item.imagen" 
                :alt="item.nombre"
                class="item-image"
                @error="handleImageError"
              />
              <div class="item-details">
                <span class="item-name">{{ item.nombre }}</span>
                <span class="item-quantity">x{{ item.quantity }}</span>
              </div>
              <span class="item-price">S/ {{ (item.precio * item.quantity).toFixed(2) }}</span>
            </div>
          </div>

          <div class="order-totals">
            <div class="total-row">
              <span>Subtotal:</span>
              <span>S/ {{ totalPrice.toFixed(2) }}</span>
            </div>
            <div class="total-row">
              <span>Envío:</span>
              <span>S/ 5.00</span>
            </div>
            <div class="total-row grand-total">
              <span>Total:</span>
              <span>S/ {{ (totalPrice + 5).toFixed(2) }}</span>
            </div>
          </div>

          <div class="support-info">
            <h4>¿Necesitas ayuda?</h4>
            <div class="contact-methods">
              <div class="contact-item">
                <span class="contact-icon">📞</span>
                <span>(0212) 123-4567</span>
              </div>
              <div class="contact-item">
                <span class="contact-icon">💬</span>
                <span>+58 414-1234567</span>
              </div>
              <div class="contact-item">
                <span class="contact-icon">✉️</span>
                <span>soporte@farmaplus.com</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'
import { storeToRefs } from 'pinia'

export default {
  name: 'CheckoutPage',
  setup() {
    const cartStore = useCartStore()
    const { items, totalPrice } = storeToRefs(cartStore)
    return { items, totalPrice, clearCart: cartStore.clearCart }
  },
  data() {
    return {
      banco: '',
      referencia_pago: '',
      imagen_pago: null,
      loading: false,
      successMessage: '',
      errorMessage: ''
    }
  },
  computed: {
    formValid() {
      return this.banco && this.referencia_pago && this.imagen_pago
    }
  },
  methods: {
    onFileChange(e) {
      const file = e.target.files[0]
      if (file) {
        this.validateAndSetFile(file)
      }
    },

    triggerFileInput() {
      this.$refs.fileInput.click()
    },

    validateAndSetFile(file) {
      const validTypes = ['image/jpeg', 'image/png', 'image/webp']
      const maxSize = 5 * 1024 * 1024 // 5MB

      if (!validTypes.includes(file.type)) {
        alert('Por favor, sube una imagen (JPG, PNG, WEBP)')
        return
      }

      if (file.size > maxSize) {
        alert('El archivo es demasiado grande. Máximo 5MB')
        return
      }

      this.imagen_pago = file
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },

    copyBankInfo() {
      const bankInfo = `
Datos para transferencia - FarmaPlus
Banco: Banco de Venezuela
Número: 0414-1234567
Nombre: FarmaPlus C.A.
RIF: J-12345678-9
Monto: S/ ${(this.totalPrice + 5).toFixed(2)}
      `.trim()
      
      navigator.clipboard.writeText(bankInfo)
      alert('✅ Datos copiados al portapapeles')
    },

    handleImageError(event) {
      event.target.src = 'https://picsum.photos/100/100?random=1'
    },

    async enviarPago() {
      this.loading = true
      this.successMessage = ''
      this.errorMessage = ''

      try {
        const formData = new FormData()
        formData.append('banco', this.banco)
        formData.append('referencia_pago', this.referencia_pago)
        formData.append('total', this.totalPrice + 5)

        const cartStore = useCartStore()
        const items = cartStore.items

        const formattedItems = items.map(i => ({
          id: i.id,
          quantity: i.quantity,
          price: i.precio
        }))

        formData.append('items', JSON.stringify(formattedItems))

        if (this.imagen_pago) {
          formData.append('imagen_pago', this.imagen_pago)
        }

        const token = localStorage.getItem('auth_token')

        await api.post('/checkout', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        })

        this.successMessage = '✅ Pago enviado correctamente. ¡Gracias por tu compra!'
        cartStore.clearCart()
        
        // Redirigir después de 3 segundos
        setTimeout(() => {
          this.$router.push('/')
        }, 3000)
        
      } catch (error) {
        console.error('Error al enviar pago:', error.response?.data)
        this.errorMessage =
          error.response?.data?.message ||
          JSON.stringify(error.response?.data?.errors || {}) ||
          '❌ Error al enviar el pago. Intenta nuevamente.'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.checkout-page {
  min-height: 100vh;
  background: #f8fafc; /* Fondo sólido en lugar de gradiente */
  padding: 2rem 1rem;
}

.checkout-header {
  max-width: 1200px;
  margin: 0 auto 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1.5rem 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
}

.back-btn {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  color: #475569;
  transition: background-color 0.2s ease;
}

.back-btn:hover {
  background: #e2e8f0;
}

.checkout-header h1 {
  margin: 0;
  color: #1e293b;
  font-size: 1.8rem;
  font-weight: 700;
}

.step-indicator {
  background: #10b981;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
}

.checkout-layout {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
  align-items: start;
}

/* Tarjeta de información bancaria */
.bank-info-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.bank-icon {
  font-size: 2rem;
}

.card-header h2 {
  margin: 0;
  color: #1e293b;
  font-size: 1.4rem;
}

.bank-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.detail-row.highlight {
  background: #f0f9ff;
  border-color: #bae6fd;
}

.detail-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.label-icon {
  font-size: 1rem;
}

.detail-value {
  font-weight: 600;
  color: #1e293b;
}

.total-amount {
  font-size: 1.2rem;
  color: #059669;
}

.copy-btn {
  width: 100%;
  background: #3b82f6;
  color: white;
  border: none;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: background-color 0.2s ease;
}

.copy-btn:hover {
  background: #2563eb;
}

/* Formulario */
.form-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
}

.form-card h3 {
  margin: 0 0 1.5rem 0;
  color: #1e293b;
  font-size: 1.3rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
  font-size: 1rem;
}

.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s ease;
  background: white;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
}

select.form-input {
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1rem;
}

.input-help {
  margin-top: 0.5rem;
  color: #64748b;
  font-size: 0.875rem;
}

/* Área de subida */
.upload-area {
  border: 2px dashed #cbd5e1;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s ease;
  background: #fafafa;
}

.upload-area:hover {
  border-color: #3b82f6;
}

.upload-area.has-file {
  border-color: #10b981;
  background: #f0fdf4;
}

.upload-placeholder,
.file-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.upload-icon {
  font-size: 2.5rem;
  opacity: 0.7;
}

.upload-title {
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.upload-subtitle {
  color: #64748b;
  margin: 0;
  font-size: 0.9rem;
}

.file-preview {
  flex-direction: row;
  align-items: center;
  width: 100%;
}

.file-icon {
  font-size: 1.5rem;
  color: #10b981;
}

.file-info {
  flex: 1;
  text-align: left;
}

.file-name {
  font-weight: 600;
  color: #059669;
  margin: 0 0 0.25rem 0;
}

.file-size {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

.change-file-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  color: #475569;
  transition: background-color 0.2s ease;
}

.change-file-btn:hover {
  background: #e2e8f0;
}

.upload-requirements {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  font-size: 0.875rem;
  color: #64748b;
}

/* Botón de envío */
.submit-btn {
  width: 100%;
  background: #10b981;
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1.1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.submit-btn:hover:not(.disabled) {
  background: #059669;
}

.submit-btn.loading {
  background: #9ca3af;
  cursor: not-allowed;
}

.submit-btn.disabled {
  background: #d1d5db;
  cursor: not-allowed;
}

.loading-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid transparent;
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Mensajes */
.message {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.success-message {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}

.error-message {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
}

.message-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.message-content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
}

.message-content p {
  margin: 0;
  line-height: 1.5;
  font-size: 0.9rem;
}

/* Sidebar */
.checkout-sidebar {
  position: sticky;
  top: 2rem;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
}

.summary-card h3 {
  margin: 0 0 1rem 0;
  color: #1e293b;
  text-align: center;
  font-size: 1.2rem;
}

.order-items {
  margin-bottom: 1.5rem;
  max-height: 250px;
  overflow-y: auto;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.order-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}

.item-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.item-name {
  font-weight: 600;
  color: #374151;
  font-size: 0.85rem;
}

.item-quantity {
  font-size: 0.75rem;
  color: #64748b;
}

.item-price {
  font-weight: 600;
  color: #059669;
  font-size: 0.9rem;
}

.order-totals {
  border-top: 1px solid #e2e8f0;
  padding-top: 1rem;
  margin-bottom: 1.5rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: #374151;
  font-size: 0.9rem;
}

.total-row.grand-total {
  font-weight: 700;
  font-size: 1rem;
  color: #1e293b;
  border-top: 1px solid #e2e8f0;
  padding-top: 0.5rem;
  margin-top: 0.5rem;
}

.support-info {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1rem;
}

.support-info h4 {
  margin: 0 0 0.75rem 0;
  color: #374151;
  text-align: center;
  font-size: 1rem;
}

.contact-methods {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #475569;
  font-size: 0.8rem;
}

.contact-icon {
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .checkout-layout {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .checkout-sidebar {
    order: -1;
    position: static;
  }
}

@media (max-width: 768px) {
  .checkout-page {
    padding: 1rem 0.5rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
    padding: 1rem;
  }
  
  .back-btn {
    align-self: flex-start;
  }
  
  .checkout-header h1 {
    font-size: 1.4rem;
  }
  
  .bank-info-card,
  .form-card,
  .summary-card {
    padding: 1.5rem;
  }
  
  .upload-requirements {
    flex-direction: column;
    gap: 0.25rem;
  }
}
</style>