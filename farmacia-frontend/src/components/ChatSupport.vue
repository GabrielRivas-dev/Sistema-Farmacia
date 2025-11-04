<template>
  <div class="chat-support">
    <!-- Botón flotante -->
    <button 
      v-if="!isOpen" 
      class="chat-floating-btn"
      @click="openChat"
      :class="{ 'has-notification': unreadCount > 0 }"
    >
      💬
      <span v-if="unreadCount > 0" class="notification-badge">
        {{ unreadCount }}
      </span>
    </button>

    <!-- Modal del chat -->
    <div v-if="isOpen" class="chat-modal">
      <!-- Header -->
      <div class="chat-header">
        <div class="header-content">
          <h3>Soporte al Cliente</h3>
          <div class="header-actions">
            <button class="btn-new-chat" @click="showNewConversation = true" v-if="!activeConversation">
              Nueva Conversación
            </button>
            <button class="btn-close" @click="closeChat">
              ×
            </button>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="chat-content">
        <!-- Lista de conversaciones -->
        <div v-if="!activeConversation && !showNewConversation" class="conversations-list">
          <div class="list-header">
            <h4>Tus Conversaciones</h4>
            <button class="btn-new" @click="showNewConversation = true">
              + Nueva
            </button>
          </div>
          
          <div v-if="loading" class="loading-conversations">
            <div class="spinner"></div>
            <p>Cargando conversaciones...</p>
          </div>

          <div v-else-if="conversations.length === 0" class="empty-conversations">
            <div class="empty-icon">💬</div>
            <p>No tienes conversaciones activas</p>
            <button class="btn-start-chat" @click="showNewConversation = true">
              Iniciar conversación
            </button>
          </div>

          <div v-else class="conversations">
            <div 
              v-for="conversation in conversations" 
              :key="conversation.id"
              class="conversation-item"
              :class="{ 
                'active': activeConversation?.id === conversation.id,
                'unread': conversation.unread_messages_count > 0
              }"
              @click="selectConversation(conversation)"
            >
              <div class="conversation-info">
                <h5>{{ conversation.subject || 'Sin asunto' }}</h5>
                <p class="last-message">
                  {{ getLastMessagePreview(conversation) }}
                </p>
                <span class="conversation-date">
                  {{ formatDate(conversation.last_message_at) }}
                </span>
              </div>
              <div class="conversation-status">
                <span class="status-badge" :class="conversation.status">
                  {{ conversation.status }}
                </span>
                <span v-if="conversation.unread_messages_count > 0" class="unread-badge">
                  {{ conversation.unread_messages_count }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulario nueva conversación -->
        <div v-if="showNewConversation" class="new-conversation">
          <div class="new-conversation-header">
            <button class="btn-back" @click="showNewConversation = false">
              ← Volver
            </button>
            <h4>Nueva Conversación</h4>
          </div>
          
          <form @submit.prevent="createConversation" class="new-conversation-form">
            <div class="form-group">
              <label>Asunto</label>
              <input 
                v-model="newConversation.subject" 
                type="text" 
                placeholder="Ej: Problema con mi pedido #123"
                required
              >
            </div>
            
            <div class="form-group">
              <label>Mensaje</label>
              <textarea 
                v-model="newConversation.message" 
                placeholder="Describe tu consulta o problema..."
                rows="5"
                required
              ></textarea>
            </div>

            <div class="form-actions">
              <button 
                type="button" 
                class="btn-cancel"
                @click="showNewConversation = false"
              >
                Cancelar
              </button>
              <button 
                type="submit" 
                class="btn-send"
                :disabled="sendingMessage"
              >
                {{ sendingMessage ? 'Enviando...' : 'Iniciar Conversación' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Chat activo -->
        <div v-if="activeConversation" class="active-chat">
          <div class="chat-header-detail">
            <button class="btn-back" @click="activeConversation = null">
              ← Conversaciones
            </button>
            <div class="chat-info">
              <h4>{{ activeConversation.subject }}</h4>
              <span class="chat-status" :class="activeConversation.status">
                {{ activeConversation.status }}
              </span>
            </div>
            <button 
              v-if="activeConversation.status === 'open'"
              class="btn-close-chat"
              @click="closeConversation"
            >
              Cerrar Chat
            </button>
          </div>

          <!-- Mensajes -->
          <div class="messages-container" ref="messagesContainer">
            <div v-if="loadingMessages" class="loading-messages">
              <div class="spinner"></div>
              <p>Cargando mensajes...</p>
            </div>

            <div v-else class="messages">
              <div 
                v-for="message in messages" 
                :key="message.id"
                class="message"
                :class="{
                  'user-message': message.user_id === user.id,
                  'admin-message': message.admin_id
                }"
              >
                <div class="message-content">
                  <p>{{ message.message }}</p>
                  <span class="message-time">
                    {{ formatTime(message.created_at) }}
                  </span>
                </div>
                <div class="message-sender">
                  {{ message.user_id ? 'Tú' : 'Soporte' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Input de mensaje -->
          <div class="message-input-container">
            <form @submit.prevent="sendMessage" class="message-form">
              <div class="input-group">
                <input 
                  v-model="newMessage" 
                  type="text" 
                  placeholder="Escribe tu mensaje..."
                  :disabled="activeConversation.status === 'closed'"
                >
                <button 
                  type="submit" 
                  :disabled="!newMessage.trim() || sendingMessage || activeConversation.status === 'closed'"
                  class="btn-send-message"
                >
                  {{ sendingMessage ? '⏳' : '📤' }}
                </button>
              </div>
            </form>
            
            <div v-if="activeConversation.status === 'closed'" class="chat-closed-notice">
              Esta conversación está cerrada. Inicia una nueva para continuar.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'

export default {
  name: 'ChatSupport',
  data() {
    return {
      isOpen: false,
      showNewConversation: false,
      activeConversation: null,
      conversations: [],
      messages: [],
      loading: false,
      loadingMessages: false,
      sendingMessage: false,
      unreadCount: 0,
      newMessage: '',
      newConversation: {
        subject: '',
        message: ''
      },
      user: JSON.parse(localStorage.getItem('user') || '{}'),
      pollInterval: null
    }
  },
  methods: {
    openChat() {
      this.isOpen = true
      this.loadConversations()
    },

    closeChat() {
      this.isOpen = false
      this.showNewConversation = false
      this.activeConversation = null
      this.stopPolling()
    },

    async loadConversations() {
      this.loading = true
      try {
        const response = await api.get('/chat/conversations')
        this.conversations = response.data.conversations
        this.unreadCount = response.data.unread_count
      } catch (error) {
        console.error('Error loading conversations:', error)
        this.$toast.error('Error al cargar las conversaciones')
      } finally {
        this.loading = false
      }
    },

    async selectConversation(conversation) {
      this.activeConversation = conversation
      this.showNewConversation = false
      await this.loadMessages(conversation.id)
      this.startPolling(conversation.id)
    },

    async loadMessages(conversationId) {
      this.loadingMessages = true
      try {
        const response = await api.get(`/chat/conversations/${conversationId}/messages`)
        this.messages = response.data.messages
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      } catch (error) {
        console.error('Error loading messages:', error)
        this.$toast.error('Error al cargar los mensajes')
      } finally {
        this.loadingMessages = false
      }
    },

    async createConversation() {
      this.sendingMessage = true
      try {
        const response = await api.post('/chat/conversations', this.newConversation)
        
        this.conversations.unshift(response.data.conversation)
        this.activeConversation = response.data.conversation
        this.messages = response.data.conversation.messages
        this.showNewConversation = false
        this.newConversation = { subject: '', message: '' }
        
        this.$toast.success('Conversación creada exitosamente')
        this.startPolling(this.activeConversation.id)
      } catch (error) {
        console.error('Error creating conversation:', error)
        this.$toast.error('Error al crear la conversación')
      } finally {
        this.sendingMessage = false
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || !this.activeConversation) return

      this.sendingMessage = true
      try {
        const response = await api.post(
          `/chat/conversations/${this.activeConversation.id}/messages`,
          { message: this.newMessage }
        )

        this.messages.push(response.data.message)
        this.newMessage = ''
        
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      } catch (error) {
        console.error('Error sending message:', error)
        this.$toast.error('Error al enviar el mensaje')
      } finally {
        this.sendingMessage = false
      }
    },

    async closeConversation() {
      try {
        await api.put(`/chat/conversations/${this.activeConversation.id}/close`)
        this.activeConversation.status = 'closed'
        this.$toast.success('Conversación cerrada')
      } catch (error) {
        console.error('Error closing conversation:', error)
        this.$toast.error('Error al cerrar la conversación')
      }
    },

    scrollToBottom() {
      const container = this.$refs.messagesContainer
      if (container) {
        container.scrollTop = container.scrollHeight
      }
    },

    getLastMessagePreview(conversation) {
      if (!conversation.last_message) return 'Sin mensajes'
      const message = conversation.last_message.message
      return message.length > 50 ? message.substring(0, 50) + '...' : message
    },

    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      const now = new Date()
      const diffTime = Math.abs(now - date)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

      if (diffDays === 1) return 'Ayer'
      if (diffDays < 7) return `${diffDays} días`
      
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short'
      })
    },

    formatTime(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    startPolling(conversationId) {
      this.stopPolling()
      this.pollInterval = setInterval(async () => {
        if (this.activeConversation && this.activeConversation.id === conversationId) {
          await this.loadMessages(conversationId)
          await this.loadConversations() // Actualizar contador de no leídos
        }
      }, 5000) // Poll cada 5 segundos
    },

    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval)
        this.pollInterval = null
      }
    }
  },
  beforeUnmount() {
    this.stopPolling()
  }
}
</script>

<style scoped>
.chat-support {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
}

/* Botón flotante */
.chat-floating-btn {
  position: relative;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.chat-floating-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
}

.chat-floating-btn.has-notification {
  animation: pulse 2s infinite;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
  100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Modal del chat */
.chat-modal {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 400px;
  height: 600px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Header */
.chat-header {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 1rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-content h3 {
  margin: 0;
  font-size: 1.2rem;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.btn-new-chat, .btn-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: background 0.2s ease;
}

.btn-new-chat:hover, .btn-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.btn-close {
  font-size: 1.2rem;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Contenido del chat */
.chat-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Lista de conversaciones */
.conversations-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.list-header h4 {
  margin: 0;
  color: #1e293b;
}

.btn-new {
  background: #10b981;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
}

.loading-conversations, .empty-conversations {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  text-align: center;
  color: #64748b;
}

.spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #e2e8f0;
  border-top: 3px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.btn-start-chat {
  background: #667eea;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 1rem;
}

.conversations {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: background 0.2s ease;
}

.conversation-item:hover {
  background: #f8fafc;
}

.conversation-item.active {
  background: #f0f9ff;
}

.conversation-item.unread {
  background: #fef7cd;
}

.conversation-info {
  flex: 1;
}

.conversation-info h5 {
  margin: 0 0 0.25rem 0;
  color: #1e293b;
  font-size: 0.9rem;
}

.last-message {
  margin: 0 0 0.25rem 0;
  color: #64748b;
  font-size: 0.8rem;
}

.conversation-date {
  font-size: 0.7rem;
  color: #94a3b8;
}

.conversation-status {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
}

.status-badge.open {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.closed {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.pending {
  background: #dbeafe;
  color: #1e40af;
}

.unread-badge {
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 0.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Nueva conversación */
.new-conversation {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.new-conversation-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.btn-back {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  font-size: 1rem;
}

.new-conversation-form {
  flex: 1;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.form-group input, .form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.9rem;
}

.form-group input:focus, .form-group textarea:focus {
  outline: none;
  border-color: #667eea;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: auto;
  padding-top: 1rem;
}

.btn-cancel, .btn-send {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.btn-cancel {
  background: #f1f5f9;
  color: #64748b;
}

.btn-send {
  background: #667eea;
  color: white;
}

.btn-send:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Chat activo */
.active-chat {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.chat-header-detail {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.chat-info {
  flex: 1;
  margin: 0 1rem;
}

.chat-info h4 {
  margin: 0 0 0.25rem 0;
  color: #1e293b;
  font-size: 1rem;
}

.chat-status {
  font-size: 0.8rem;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-weight: 600;
}

.chat-status.open {
  background: #d1fae5;
  color: #065f46;
}

.chat-status.closed {
  background: #fef3c7;
  color: #92400e;
}

.btn-close-chat {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Mensajes */
.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #f8fafc;
}

.loading-messages {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #64748b;
}

.messages {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message {
  display: flex;
  flex-direction: column;
  max-width: 80%;
}

.message.user-message {
  align-self: flex-end;
}

.message.admin-message {
  align-self: flex-start;
}

.message-content {
  padding: 0.75rem 1rem;
  border-radius: 18px;
  position: relative;
}

.user-message .message-content {
  background: #667eea;
  color: white;
  border-bottom-right-radius: 4px;
}

.admin-message .message-content {
  background: white;
  color: #1e293b;
  border: 1px solid #e2e8f0;
  border-bottom-left-radius: 4px;
}

.message-time {
  font-size: 0.7rem;
  opacity: 0.7;
  margin-top: 0.25rem;
  display: block;
}

.message-sender {
  font-size: 0.7rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.user-message .message-sender {
  text-align: right;
}

/* Input de mensaje */
.message-input-container {
  padding: 1rem;
  border-top: 1px solid #e2e8f0;
}

.message-form {
  display: flex;
  gap: 0.5rem;
}

.input-group {
  flex: 1;
  display: flex;
  gap: 0.5rem;
}

.input-group input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 24px;
  outline: none;
}

.input-group input:focus {
  border-color: #667eea;
}

.btn-send-message {
  background: #667eea;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-send-message:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.chat-closed-notice {
  text-align: center;
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: #fef2f2;
  border-radius: 6px;
}

/* Responsive */
@media (max-width: 768px) {
  .chat-support {
    bottom: 1rem;
    right: 1rem;
  }

  .chat-modal {
    width: 100vw;
    height: 100vh;
    bottom: 0;
    right: 0;
    border-radius: 0;
  }

  .chat-floating-btn {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }
}
</style>