# Instrucciones para Ejecutar el Proyecto

Este proyecto está dividido en dos partes: **backend** (Laravel) y **frontend** (Vue 3). Sigue los pasos a continuación para configurar y ejecutar el proyecto localmente.

---

## 🚀 Configuración del Backend

1. **Accede a la carpeta del backend:**
   ```bash
   cd backend
   ```

2. **Instala las dependencias de PHP con Composer:**
   ```bash
   composer install
   ```

3. **Configura el archivo `.env`:**
   - Copia el archivo `.env.example` y renómbralo a `.env`.
   - Configura las variables de entorno (base de datos, claves, etc.).

4. **Genera la clave de la aplicación:**
   ```bash
   php artisan key:generate
   ```

5. **Ejecuta el servidor de desarrollo:**
   ```bash
   php artisan serve
   ```
   El backend estará disponible en: [http://localhost:8000](http://localhost:8000)

---

## 🎨 Configuración del Frontend

1. **Accede a la carpeta del frontend (en una nueva terminal):**
   ```bash
   cd frontend
   ```

2. **Instala las dependencias de Node.js:**
   ```bash
   npm install --force
   ```

3. **Ejecuta el servidor de desarrollo del frontend:**
   ```bash
   npm run dev
   ```
   El frontend estará disponible en: [http://localhost:5173](http://localhost:5173)

---

## 🌐 Accede al Proyecto
Abre tu navegador y visita:
👉 [http://localhost:5173](http://localhost:5173)