<template>
  <div class="register-page">
    <div class="register-container">
      <div class="register-card">
        <!-- Logo -->
        <div class="register-header">
          <div class="logo">🏥</div>
          <h2 class="register-title">Únete a FarmaPlus</h2>
          <p class="register-subtitle">Crea tu cuenta en minutos</p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-row">
            <div class="form-group">
              <label for="nombre" class="form-label">Nombre</label>
              <input
                id="nombre"
                v-model="form.nombre"
                type="text"
                class="form-input"
                :class="{ 'error': errors.nombre }"
                placeholder="Tu nombre"
                required
              >
              <span v-if="errors.nombre" class="error-message">{{ errors.nombre }}</span>
            </div>

            <div class="form-group">
              <label for="apellido" class="form-label">Apellido</label>
              <input
                id="apellido"
                v-model="form.apellido"
                type="text"
                class="form-input"
                :class="{ 'error': errors.apellido }"
                placeholder="Tu apellido"
                required
              >
              <span v-if="errors.apellido" class="error-message">{{ errors.apellido }}</span>
            </div>
          </div>

          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="form-input"
              :class="{ 'error': errors.email }"
              placeholder="tu@email.com"
              required
            >
            <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="password" class="form-label">Contraseña</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-input"
                :class="{ 'error': errors.password }"
                placeholder="••••••••"
                required
              >
              <span v-if="errors.password" class="error-message">{{ errors.password }}</span>
            </div>

            <div class="form-group">
              <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="form-input"
                :class="{ 'error': errors.password_confirmation }"
                placeholder="••••••••"
                required
              >
              <span v-if="errors.password_confirmation" class="error-message">{{ errors.password_confirmation }}</span>
            </div>
          </div>

          <div class="form-group">
            <label for="telefono" class="form-label">Teléfono</label>
            <input
              id="telefono"
              v-model="form.telefono"
              type="tel"
              class="form-input"
              :class="{ 'error': errors.telefono }"
              placeholder="+51 123 456 789"
              required
            >
            <span v-if="errors.telefono" class="error-message">{{ errors.telefono }}</span>
          </div>

          <div class="form-group">
            <label for="cedula" class="form-label">Cédula</label>
            <input
              id="cedula"
              v-model="form.cedula"
              type="text"
              class="form-input"
              :class="{ 'error': errors.cedula }"
              placeholder="12345678"
              required
            >
            <span v-if="errors.cedula" class="error-message">{{ errors.cedula }}</span>
          </div>

          <div class="form-group">
            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
            <input
              id="fecha_nacimiento"
              v-model="form.fecha_nacimiento"
              type="date"
              class="form-input"
              :class="{ 'error': errors.fecha_nacimiento }"
              required
            >
            <span v-if="errors.fecha_nacimiento" class="error-message">{{ errors.fecha_nacimiento }}</span>
          </div>

          <div class="form-group">
            <label for="direccion" class="form-label">Dirección</label>
            <textarea
              id="direccion"
              v-model="form.direccion"
              class="form-input"
              :class="{ 'error': errors.direccion }"
              placeholder="Tu dirección completa"
              rows="3"
              required
            ></textarea>
            <span v-if="errors.direccion" class="error-message">{{ errors.direccion }}</span>
          </div>

          <button type="submit" class="register-btn" :disabled="loading">
            <span v-if="loading" class="btn-spinner"></span>
            {{ loading ? 'Creando cuenta...' : 'Crear Cuenta' }}
          </button>
        </form>

        <!-- Enlaces -->
        <div class="register-links">
          <p>¿Ya tienes cuenta? 
            <a href="#" @click.prevent="$emit('navigate', 'login')" class="auth-link">
              Inicia sesión aquí
            </a>
          </p>
        </div>

        <!-- Mensajes de error -->
        <div v-if="authError" class="auth-error">
          <span class="error-icon">⚠️</span>
          {{ authError }}
        </div>

        <!-- Mensaje de éxito -->
        <div v-if="successMessage" class="auth-success">
          <span class="success-icon">✅</span>
          {{ successMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../services/api'

export default {
  name: 'RegisterView',
  data() {
    return {
      form: {
        nombre: '',
        apellido: '',
        email: '',
        password: '',
        password_confirmation: '',
        telefono: '',
        cedula: '', // Cambiado de dni a cedula
        fecha_nacimiento: '',
        direccion: ''
      },
      errors: {},
      loading: false,
      authError: '',
      successMessage: ''
    }
  },
  methods: {
    async handleRegister() {
      this.loading = true
      this.errors = {}
      this.authError = ''
      this.successMessage = ''

      try {
        console.log('Enviando datos:', this.form) // Para debug
        
        const response = await api.post('/register', this.form)
        
        if (response.data.success) {
          this.successMessage = '¡Cuenta creada exitosamente! Redirigiendo...'
           this.$emit('register-success', response.data.data.user)
          
          // Guardar token y datos del usuario
          localStorage.setItem('auth_token', response.data.data.access_token)
          localStorage.setItem('user', JSON.stringify(response.data.data.user))
          
          // Redirigir después de 2 segundos
          setTimeout(() => {
            this.$emit('navigate', 'products')
          }, 2000)
        }
      } catch (error) {
        console.error('Register error:', error)
        
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
          this.authError = 'Por favor, corrige los errores en el formulario.'
        } else if (error.response?.data?.message) {
          this.authError = error.response.data.message
        } else {
          this.authError = 'Error al crear la cuenta. Por favor, intenta nuevamente.'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
/* Los estilos se mantienen igual */
.register-page {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.register-container {
  width: 100%;
  max-width: 500px;
}

.register-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  padding: 3rem;
  border: 1px solid #e2e8f0;
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.register-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.register-subtitle {
  color: #64748b;
  margin: 0;
}

.register-form {
  margin-bottom: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8fafc;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: #2563eb;
  background: white;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-input.error {
  border-color: #dc2626;
  background: #fef2f2;
}

.form-input textarea {
  resize: vertical;
  min-height: 80px;
}

.error-message {
  display: block;
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.register-btn {
  width: 100%;
  background: #2563eb;
  color: white;
  border: none;
  padding: 1rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.register-btn:hover:not(:disabled) {
  background: #1d4ed8;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.register-btn:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.register-links {
  text-align: center;
  color: #64748b;
}

.auth-link {
  color: #2563eb;
  text-decoration: none;
  font-weight: 600;
}

.auth-link:hover {
  text-decoration: underline;
}

.auth-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
}

.auth-success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #15803d;
  padding: 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.error-icon, .success-icon {
  font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 768px) {
  .register-page {
    padding: 1rem;
  }
  
  .register-card {
    padding: 2rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>