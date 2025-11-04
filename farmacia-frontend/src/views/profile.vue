<template>
  <div class="profile-page">
    <div class="container">
      <div class="profile-card">
        <!-- Header -->
        <div class="profile-header">
          <h1>Mi Perfil</h1>
          <p>Gestiona tu información personal</p>
        </div>

        <!-- Mensajes -->
        <div v-if="successMessage" class="alert alert-success">
          {{ successMessage }}
        </div>
        
        <div v-if="errorMessage" class="alert alert-error">
          {{ errorMessage }}
        </div>

        <!-- Formulario de perfil -->
        <form @submit.prevent="updateProfile" class="profile-form">
          <div class="form-grid">
            <div class="form-group">
              <label for="name">Nombre Completo *</label>
              <input
                id="name"
                v-model="profileForm.name"
                type="text"
                class="form-control"
                :class="{ 'error': errors.name }"
                required
              >
              <span v-if="errors.name" class="error-text">{{ errors.name[0] }}</span>
            </div>

            <div class="form-group">
              <label for="email">Email *</label>
              <input
                id="email"
                v-model="profileForm.email"
                type="email"
                class="form-control"
                :class="{ 'error': errors.email }"
                required
              >
              <span v-if="errors.email" class="error-text">{{ errors.email[0] }}</span>
            </div>

            <div class="form-group">
              <label for="telefono">Teléfono *</label>
              <input
                id="telefono"
                v-model="profileForm.telefono"
                type="tel"
                class="form-control"
                :class="{ 'error': errors.telefono }"
                required
              >
              <span v-if="errors.telefono" class="error-text">{{ errors.telefono[0] }}</span>
            </div>

            <div class="form-group">
              <label for="cedula">Cédula *</label>
              <input
                id="cedula"
                v-model="profileForm.cedula"
                type="text"
                class="form-control"
                :class="{ 'error': errors.cedula }"
                required
              >
              <span v-if="errors.cedula" class="error-text">{{ errors.cedula[0] }}</span>
            </div>

            <div class="form-group">
              <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
              <input
                id="fecha_nacimiento"
                v-model="profileForm.fecha_nacimiento"
                type="date"
                class="form-control"
                :class="{ 'error': errors.fecha_nacimiento }"
                required
              >
              <span v-if="errors.fecha_nacimiento" class="error-text">{{ errors.fecha_nacimiento[0] }}</span>
            </div>

            <div class="form-group full-width">
              <label for="direccion">Dirección *</label>
              <textarea
                id="direccion"
                v-model="profileForm.direccion"
                class="form-control"
                :class="{ 'error': errors.direccion }"
                rows="3"
                required
              ></textarea>
              <span v-if="errors.direccion" class="error-text">{{ errors.direccion[0] }}</span>
            </div>
          </div>

          <div class="form-actions">
            <button 
              type="submit" 
              class="btn btn-primary"
              :disabled="loading"
            >
              <span v-if="loading">Guardando...</span>
              <span v-else>Guardar Cambios</span>
            </button>
          </div>
        </form>

        <!-- Cambiar contraseña -->
        <div class="password-section">
          <h3>Cambiar Contraseña</h3>
          <form @submit.prevent="changePassword" class="password-form">
            <div class="form-grid">
              <div class="form-group">
                <label for="current_password">Contraseña Actual *</label>
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  class="form-control"
                  :class="{ 'error': passwordErrors.current_password }"
                  required
                >
                <span v-if="passwordErrors.current_password" class="error-text">{{ passwordErrors.current_password[0] }}</span>
              </div>

              <div class="form-group">
                <label for="new_password">Nueva Contraseña *</label>
                <input
                  id="new_password"
                  v-model="passwordForm.new_password"
                  type="password"
                  class="form-control"
                  :class="{ 'error': passwordErrors.new_password }"
                  required
                >
                <span v-if="passwordErrors.new_password" class="error-text">{{ passwordErrors.new_password[0] }}</span>
              </div>

              <div class="form-group">
                <label for="new_password_confirmation">Confirmar Contraseña *</label>
                <input
                  id="new_password_confirmation"
                  v-model="passwordForm.new_password_confirmation"
                  type="password"
                  class="form-control"
                  :class="{ 'error': passwordErrors.new_password_confirmation }"
                  required
                >
                <span v-if="passwordErrors.new_password_confirmation" class="error-text">{{ passwordErrors.new_password_confirmation[0] }}</span>
              </div>
            </div>

            <div class="form-actions">
              <button 
                type="submit" 
                class="btn btn-secondary"
                :disabled="passwordLoading"
              >
                <span v-if="passwordLoading">Cambiando...</span>
                <span v-else>Cambiar Contraseña</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'

export default {
  name: 'ProfileView',
  data() {
    return {
      loading: false,
      passwordLoading: false,
      errors: {},
      passwordErrors: {},
      successMessage: '',
      errorMessage: '',
      profileForm: {
        name: '',
        email: '',
        telefono: '',
        cedula: '',
        fecha_nacimiento: '',
        direccion: ''
      },
      passwordForm: {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      }
    }
  },
  mounted() {
    this.loadUserProfile()
  },
  methods: {
    async loadUserProfile() {
      try {
        console.log('Cargando perfil del usuario...')
        
        const response = await api.get('/profile')
        console.log('Respuesta del perfil:', response.data)
        
        if (response.data.success) {
          const userData = response.data.data.user
          console.log('Datos del usuario:', userData)
          
          // Asignar datos al formulario
          this.profileForm = {
            name: userData.name || '',
            email: userData.email || '',
            telefono: userData.telefono || '',
            cedula: userData.cedula || '',
            fecha_nacimiento: this.formatDateForInput(userData.fecha_nacimiento),
            direccion: userData.direccion || ''
          }
          
          console.log('Formulario cargado:', this.profileForm)
        }
      } catch (error) {
        console.error('Error cargando perfil:', error)
        if (error.response?.status === 401) {
          this.showError('Sesión expirada. Por favor inicia sesión nuevamente.')
          this.$router.push('/login')
        } else {
          this.showError('Error al cargar el perfil del usuario')
        }
      }
    },

    async updateProfile() {
      this.loading = true
      this.errors = {}
      this.successMessage = ''
      this.errorMessage = ''

      try {
        console.log('Actualizando perfil:', this.profileForm)
        
        const response = await api.put('/profile', this.profileForm)
        console.log('Respuesta de actualización:', response.data)
        
        if (response.data.success) {
          this.successMessage = 'Perfil actualizado exitosamente'
          
          // Actualizar localStorage si es necesario
          const userData = localStorage.getItem('user')
          if (userData) {
            const user = JSON.parse(userData)
            user.name = this.profileForm.name
            user.email = this.profileForm.email
            localStorage.setItem('user', JSON.stringify(user))
          }
        }
      } catch (error) {
        console.error('Error actualizando perfil:', error)
        
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        } else if (error.response?.status === 401) {
          this.showError('Sesión expirada. Por favor inicia sesión nuevamente.')
          this.$router.push('/login')
        } else {
          this.showError('Error al actualizar el perfil')
        }
      } finally {
        this.loading = false
      }
    },

    async changePassword() {
      this.passwordLoading = true
      this.passwordErrors = {}
      this.successMessage = ''
      this.errorMessage = ''

      try {
        const response = await api.post('/change-password', this.passwordForm)
        
        if (response.data.success) {
          this.successMessage = 'Contraseña cambiada exitosamente'
          this.passwordForm = {
            current_password: '',
            new_password: '',
            new_password_confirmation: ''
          }
        }
      } catch (error) {
        console.error('Error cambiando contraseña:', error)
        
        if (error.response?.status === 422) {
          this.passwordErrors = error.response.data.errors
        } else if (error.response?.status === 401) {
          this.showError('Sesión expirada. Por favor inicia sesión nuevamente.')
          this.$router.push('/login')
        } else if (error.response?.data?.message) {
          this.errorMessage = error.response.data.message
        } else {
          this.errorMessage = 'Error al cambiar la contraseña'
        }
      } finally {
        this.passwordLoading = false
      }
    },

    formatDateForInput(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toISOString().split('T')[0]
    },

    showError(message) {
      this.errorMessage = message
      setTimeout(() => {
        this.errorMessage = ''
      }, 5000)
    }
  }
}
</script>

<style scoped>
.profile-page {
  min-height: 80vh;
  padding: 2rem 0;
  background: #f8fafc;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem;
}

.profile-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 2rem;
}

.profile-header {
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.profile-header h1 {
  color: #1e293b;
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
}

.profile-header p {
  color: #64748b;
  margin: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #374151;
}

.form-control {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-control.error {
  border-color: #dc2626;
}

.error-text {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1rem;
}

.btn {
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #4b5563;
}

.password-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #e2e8f0;
}

.password-section h3 {
  color: #1e293b;
  margin-bottom: 1.5rem;
}

.alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.alert-success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.alert-error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .profile-card {
    padding: 1.5rem;
  }
}
</style>