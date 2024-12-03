# woocomerce-stock-cronometer
 
### README - WooCommerce Stock Cron Plugin

---

#### **Información del Autor**
**Nombre:** José Miguel González y González  

---

### **Funciones y Propósitos**

1. **wc_stock_cron_activation**  
   - **Propósito:** Activa el evento cron `wc_update_stock_event` para ejecutar la tarea periódica.  
   - **Ubicación:** Se ejecuta al activar el plugin.

2. **wc_stock_cron_deactivation**  
   - **Propósito:** Elimina el evento cron al desactivar el plugin para evitar procesos innecesarios.  
   - **Ubicación:** Se ejecuta al desactivar el plugin.

3. **wc_update_stock_cron_task**  
   - **Propósito:** Actualiza el inventario de productos según el listado global `$product_stock_updates`.  
   - **Proceso:** Realiza solicitudes a la API REST de WooCommerce mediante autenticación básica o mediante claves API.  

4. **Manejadores de Cron**  
   - **Propósito:** Añade un intervalo de cron personalizado (5 minutos) para controlar la frecuencia de ejecución del evento.

---

### **Guía de Instalación**

1. **Requisitos Previos**
   - WordPress instalado.
   - WooCommerce configurado.
   - Plugin WP Control instalado (opcional para administrar tareas cron).
   - Plugin JSON Basic Authentication configurado para permitir autenticación básica.

2. **Instalación del Plugin**
   1. Copia el archivo `woocommerce-stock-cron.php` al directorio de plugins de WordPress (`wp-content/plugins/`).
   2. Activa el plugin desde el panel de administración de WordPress en la sección de "Plugins".

3. **Configuración Inicial**
   - Edita el archivo del plugin y ajusta las siguientes variables globales según tus necesidades:
     ```php
     global $basic_auth;
     $basic_auth = true; // Cambiar a 'false' para usar claves de API
     global $product_stock_updates;
     $product_stock_updates = [
         ['SKU' => 'pc01', 'stock' => 10],
         ['SKU' => 'pc02', 'stock' => 20],
     ];
     ```

---

### **Herramientas Utilizadas**

1. **WooCommerce**  
   - API REST para manipular datos del inventario.
2. **WP Control**  
   - Monitoreo y gestión de eventos cron en WordPress.
3. **JSON Basic Authentication**  
   - Habilita la autenticación básica para API REST en WooCommerce.

---

### **Cómo Ejecutarlo y Probarlo**

1. **Modificar Valores de Inventario**
   - Cambia los valores en `$product_stock_updates` para ajustar las existencias deseadas de cada producto.

2. **Forzar Ejecución**
   - Desde el plugin WP Control, ubica el evento `wc_update_stock_event` y selecciona "Ejecutar ahora" para forzar la tarea cron.  
  ![Descripción de la imagen](https://github.com/JoseGon20335/woocomerce-stock-cronometer/blob/main/imagen1.png)

3. **Verificar Resultados**
   - Revisa los niveles de inventario actualizados en el panel de administración de WooCommerce.  
   ![Imagen del resultado del cron](https://github.com/JoseGon20335/woocomerce-stock-cronometer/blob/main/iamgen2.png)

4. **Pruebas Adicionales**
   - Cambia entre `$basic_auth = true` o `false` para probar la autenticación básica o mediante claves API.
