import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    isOpen: false
  }),

  getters: {
    totalItems: (state) => state.items.reduce((total, item) => total + item.quantity, 0),
    totalPrice: (state) => state.items.reduce((total, item) => total + (item.precio * item.quantity), 0),
    itemCount: (state) => (productId) => {
      const item = state.items.find(item => item.id === productId)
      return item ? item.quantity : 0
    },
    isEmpty: (state) => state.items.length === 0
  },

  actions: {
    addItem(product) {
      const existingItem = this.items.find(item => item.id === product.id)
      
      if (existingItem) {
        existingItem.quantity += 1
      } else {
        this.items.push({
          ...product,
          quantity: 1
        })
      }
      
      this.saveToLocalStorage()
    },

    removeItem(productId) {
      this.items = this.items.filter(item => item.id !== productId)
      this.saveToLocalStorage()
    },

    updateQuantity(productId, quantity) {
      const item = this.items.find(item => item.id === productId)
      
      if (item) {
        if (quantity <= 0) {
          this.removeItem(productId)
        } else {
          item.quantity = quantity
        }
      }
      
      this.saveToLocalStorage()
    },

    clearCart() {
      this.items = []
      this.saveToLocalStorage()
    },

    toggleCart() {
      this.isOpen = !this.isOpen
    },

    openCart() {
      this.isOpen = true
    },

    closeCart() {
      this.isOpen = false
    },

    // Persistencia en localStorage
    saveToLocalStorage() {
      localStorage.setItem('cart_items', JSON.stringify(this.items))
    },

    loadFromLocalStorage() {
      const savedItems = localStorage.getItem('cart_items')
      if (savedItems) {
        this.items = JSON.parse(savedItems)
      }
    }
  }
})