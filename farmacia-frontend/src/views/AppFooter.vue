<template>
  <footer class="app-footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>🏥 FarmaPlus</h3>
          <p>Tu farmacia de confianza, siempre cerca de ti.</p>
        </div>
        
        <div class="footer-section">
          <h4>Enlaces Rápidos</h4>
          <router-link to="/" class="footer-link">Inicio</router-link>
          <router-link to="/productos" class="footer-link">Productos</router-link>
          <router-link to="/sucursales" class="footer-link">Sucursales</router-link>
        </div>
        
        <div class="footer-section">
          <h4>Contacto</h4>
          <p>📞 (01) 234-5678</p>
          <p>✉️ info@farmaplus.com</p>
          <p>📍 Unellez, Barinas</p>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; 2024 FarmaPlus. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'AppFooter'
}
</script>

<style scoped>
.app-footer {
  background: #1e293b;
  color: white;
  padding: 2rem 0 1rem;
  margin-top: auto;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-section h3,
.footer-section h4 {
  margin-bottom: 1rem;
  color: #f8fafc;
}

.footer-section p {
  color: #cbd5e1;
  margin-bottom: 0.5rem;
}

.footer-link {
  display: block;
  color: #cbd5e1;
  text-decoration: none;
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: #60a5fa;
}

.footer-bottom {
  border-top: 1px solid #374151;
  padding-top: 1rem;
  text-align: center;
  color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
}
</style>