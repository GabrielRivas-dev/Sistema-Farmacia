<template>
  <div class="auth-container">
    <div class="auth-card">
      <!-- Logo -->
      <div class="auth-header">
        <div class="logo">🏥</div>
        <h2 class="auth-title">Únete a FarmaPlus</h2>
        <p class="auth-subtitle">Crea tu cuenta en minutos</p>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="handleRegister" class="auth-form">
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
          <label for="dni" class="form-label">DNI</label>
          <input
            id="dni"
            v-model="form.dni"
            type="text"
            class="form-input"
            :class="{ 'error': errors.dni }"
            placeholder="12345678"
            required
          >
          <span v-if="errors.dni" class="error-message">{{ errors.dni }}</span>
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

        <button type="submit" class="auth-btn" :disabled="loading">
          <span v-if="loading" class="btn-spinner"></span>
          {{ loading ? 'Creando cuenta...' : 'Crear Cuenta' }}
        </button>
      </form>

      <!-- Enlaces -->
      <div class="auth-links">
        <p>¿Ya tienes cuenta? 
          <router-link to="/login" class="auth-link">Inicia sesión aquí</router-link>
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
</template>

<script>
import api from '@/services/api'

export default {
  name: 'RegisterForm',
  data() {
    return {
      form: {
        nombre: '',
        apellido: '',
        email: '',
        password: '',
        password_confirmation: '',
        telefono: '',
        dni: '',
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
        const response = await api.post('/register', this.form)
        
        if (response.data.success) {
          this.successMessage = '¡Cuenta creada exitosamente! Redirigiendo...'
          
          // Guardar token y datos del usuario
          localStorage.setItem('auth_token', response.data.data.access_token)
          localStorage.setItem('user', JSON.stringify(response.data.data.user))
          
          // Redirigir después de 2 segundos
          setTimeout(() => {
            this.$emit('register-success', response.data.data.user)
            this.$router.push('/')
          }, 2000)
        }
      } catch (error) {
        console.error('Register error:', error)
        
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
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
/* Reutilizamos los mismos estilos del Login y agregamos algunos específicos */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
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
}

.success-icon {
  font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>