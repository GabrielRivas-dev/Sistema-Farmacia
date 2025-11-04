<template>
  <div class="products-page">
    <ProductCatalog @add-to-cart="handleAddToCart" />
  </div>
</template>

<script>
import ProductCatalog from '@/components/ProductCatalog.vue'

export default {
  name: 'ProductsView',
  components: {
    ProductCatalog
  },
  methods: {
    handleAddToCart(product) {
      // Lógica para agregar al carrito
      console.log('Agregar al carrito:', product);
      // Aquí puedes conectar con tu store de Pinia/Vuex
    }
  }
}
</script>