<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

const products = ref([])

onMounted(async () => {
  const res = await axios.get('http://127.0.0.1:8000/api/products')
  products.value = res.data
})
</script>

<template>
  <div>
    <h1>Productos</h1>
    <ul>
      <li v-for="p in products" :key="p.id">{{ p.nombre }} - ${{ p.precio }}</li>
    </ul>
  </div>
</template>
