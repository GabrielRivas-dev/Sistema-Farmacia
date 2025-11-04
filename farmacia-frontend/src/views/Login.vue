<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-card">
        <!-- Logo -->
        <div class="login-header">
          <div class="logo">🏥</div>
          <h2 class="login-title">Bienvenido a FarmaPlus</h2>
          <p class="login-subtitle">Ingresa a tu cuenta</p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="handleLogin" class="login-form">
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

          <button type="submit" class="login-btn" :disabled="loading">
            <span v-if="loading" class="btn-spinner"></span>
            {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
          </button>
        </form>

        <!-- Enlaces -->
        <div class="login-links">
          <p>¿No tienes cuenta? 
            <a href="#" @click.prevent="$emit('navigate', 'register')" class="auth-link">
              Regístrate aquí
            </a>
          </p>
        </div>

        <!-- Mensajes de error -->
        <div v-if="authError" class="auth-error">
          <span class="error-icon">⚠️</span>
          {{ authError }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../services/api'

export default {
  name: 'LoginView',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      errors: {},
      loading: false,
      authError: ''
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true
      this.errors = {}
      this.authError = ''

      try {
        const response = await api.post('/login', this.form)
        
        if (response.data.success) {
          // Guardar token y datos del usuario
          localStorage.setItem('auth_token', response.data.data.access_token)
          localStorage.setItem('user', JSON.stringify(response.data.data.user))
          
          // Mostrar mensaje de éxito
          this.showSuccess('¡Login exitoso! Redirigiendo...')
          this.$emit('login-success', response.data.data.user)
          
          // Redirigir al home después de 1 segundo
          setTimeout(() => {
            this.$emit('navigate', 'products')
          }, 1000)
        }
      } catch (error) {
        console.error('Login error:', error)
        
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else if (error.response?.status === 401) {
          this.authError = 'Credenciales incorrectas. Por favor, verifica tu email y contraseña.'
        } else {
          this.authError = 'Error al iniciar sesión. Por favor, intenta nuevamente.'
        }
      } finally {
        this.loading = false
      }
    },
    
    showSuccess(message) {
      // Crear notificación temporal
      const successDiv = document.createElement('div')
      successDiv.className = 'auth-success show'
      successDiv.innerHTML = `
        <span class="success-icon">✅</span>
        ${message}
      `
      document.querySelector('.login-card').appendChild(successDiv)
      
      setTimeout(() => {
        successDiv.remove()
      }, 3000)
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.login-container {
  width: 100%;
  max-width: 450px;
}

.login-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  padding: 3rem;
  border: 1px solid #e2e8f0;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.login-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.login-subtitle {
  color: #64748b;
  margin: 0;
}

.login-form {
  margin-bottom: 2rem;
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

.error-message {
  display: block;
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.login-btn {
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

.login-btn:hover:not(:disabled) {
  background: #1d4ed8;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.login-btn:disabled {
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

.login-links {
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

.error-icon {
  font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 768px) {
  .login-page {
    padding: 1rem;
  }
  
  .login-card {
    padding: 2rem;
  }
}
</style>