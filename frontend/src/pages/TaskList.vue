<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const { toast } = useToast();

// Interfaces
interface Keyword {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

interface Task {
  id: number;
  title: string;
  is_done: number;
  keywords: Keyword[];
  created_at: string;
  updated_at: string;
}

interface ApiResponse<T> {
  code: number;
  data: T;
  message?: string;
}

interface NewTask {
  title: string;
  keyword_ids: number[];
  is_done: boolean;
}

interface NewKeyword {
  name: string;
}

// Configuración de axios
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL2 || 'http://localhost:8000/api';

// Estados reactivos con tipos
const tasks = ref<Task[]>([]);
const loading = ref<boolean>(false);
const keywordLoading = ref<boolean>(false);
const availableKeywords = ref<Keyword[]>([]);
const showKeywordModal = ref<boolean>(false);

const newTask = ref<NewTask>({
  title: '',
  keyword_ids: [],
  is_done: false
});

const newKeyword = ref<NewKeyword>({
  name: ''
});

// Computed para extraer keywords únicas de todas las tareas
const existingKeywords = computed<Keyword[]>(() => {
  const allKeywords = tasks.value.flatMap(task => task.keywords);
  const uniqueKeywords: Keyword[] = [];
  const seen = new Set<number>();

  allKeywords.forEach(keyword => {
    if (!seen.has(keyword.id)) {
      seen.add(keyword.id);
      uniqueKeywords.push(keyword);
    }
  });

  return uniqueKeywords;
});

// Cargar tareas
const fetchTasks = async (): Promise<void> => {
  loading.value = true;
  try {
    const response = await axios.get<ApiResponse<Task[]>>(`${API_BASE_URL}/tasks/index`);
    tasks.value = response.data.data;
  } catch (error) {
    console.error('Error al cargar tareas:', error);
    toast("Error", 'Error al cargar las tareas', "danger");
  } finally {
    loading.value = false;
  }
}

// Cargar palabras clave disponibles
const fetchKeywords = async (): Promise<void> => {
  loading.value = true;
  try {
    const response = await axios.get<ApiResponse<Keyword[]>>(`${API_BASE_URL}/keywords/index`);
    availableKeywords.value = response.data.data;
  } catch (error) {
    console.error('Error al cargar keywords:', error);
    toast("Error", 'Error al cargar las palabras claves', "danger");
  } finally {
    loading.value = false;
  }
}

// Crear nueva tarea
const createTask = async (): Promise<void> => {
  if (!newTask.value.title.trim()) {
    toast("Error", 'Por favor ingresa un título para la tarea', "danger");
    return;
  }

  loading.value = true;
  try {
    const taskToCreate: NewTask = {
      title: newTask.value.title,
      keyword_ids: newTask.value.keyword_ids,
      is_done: false
    };

    const response = await axios.post<ApiResponse<Task>>(`${API_BASE_URL}/tasks/store`, taskToCreate);

    // Resetear formulario
    newTask.value.title = '';
    newTask.value.keyword_ids = [];

    // Recargar tareas
    await fetchTasks();

    toast('Éxito', 'Tarea creada exitosamente', 'success');
  } catch (error) {
    console.error('Error al crear tarea:', error);
    toast('Error', 'Error al crear la tarea', 'danger');
  } finally {
    loading.value = false;
  }
}

// Crear nueva palabra clave
const createKeyword = async (): Promise<void> => {
  if (!newKeyword.value.name.trim()) {
    toast("Error", 'Por favor ingresa un nombre para la palabra clave', "danger");
    return;
  }

  keywordLoading.value = true;
  try {
    const response = await axios.post<ApiResponse<Keyword>>(`${API_BASE_URL}/keywords/store`, {
      name: newKeyword.value.name
    });

    // Cerrar modal y resetear formulario
    showKeywordModal.value = false;
    newKeyword.value.name = '';

    // Recargar la lista de palabras clave
    await fetchKeywords();

    toast('Éxito', 'Palabra clave creada exitosamente', 'success');
  } catch (error) {
    console.error('Error al crear palabra clave:', error);
    toast('Error', 'Error al crear la palabra clave', 'danger');
  } finally {
    keywordLoading.value = false;
  }
}

// Cambiar estado de la tarea
const toggleTaskStatus = async (taskId: number, isDone: boolean): Promise<void> => {
  loading.value = true;
  try {
    const response = await axios.put<ApiResponse<Task>>(`${API_BASE_URL}/tasks/toggle/${taskId}`, {
      is_done: isDone ? 1 : 0
    });

    // Actualizar la tarea en la lista local
    const taskIndex = tasks.value.findIndex(task => task.id === taskId);
    if (taskIndex !== -1) {
      if (response.data.data) {
        tasks.value[taskIndex] = response.data.data;
      } else {
        tasks.value[taskIndex].is_done = isDone ? 1 : 0;
        tasks.value[taskIndex].updated_at = new Date().toISOString().split('T')[0];
      }
    }
  } catch (error) {
    console.error('Error al actualizar tarea:', error);
    toast("Error", 'Error al actualizar el estado de la tarea', "danger");
  } finally {
    loading.value = false;
  }
}

// Cargar datos al montar el componente
onMounted(async (): Promise<void> => {
  await fetchTasks();
  await fetchKeywords();
});
</script>

<template>
  <div class="container mt-4">
    <h1 class="mb-4">Lista de Tareas</h1>

    <!-- Formulario para crear nueva tarea -->
    <div class="card mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Nueva Tarea</h5>
          <button type="button" class="btn btn-outline-primary btn-sm" @click="showKeywordModal = true">
            Crear Palabra Clave
          </button>
        </div>
      </div>
      <div class="card-body">
        <form @submit.prevent="createTask">
          <div class="mb-3">
            <label for="taskTitle" class="form-label">Título</label>
            <input type="text" class="form-control" id="taskTitle" v-model="newTask.title" required
              placeholder="Ingresa el título de la tarea" :disabled="loading">
          </div>

          <div class="mb-3">
            <label for="taskKeywords" class="form-label">Palabras Clave</label>
            <select multiple class="form-select" id="taskKeywords" v-model="newTask.keyword_ids" :disabled="loading">
              <option v-for="keyword in availableKeywords" :key="keyword.id" :value="keyword.id">
                {{ keyword.name }}
              </option>
            </select>
            <div class="form-text">
              Mantén presionada la tecla Ctrl (Cmd en Mac) para seleccionar múltiples palabras clave
            </div>
          </div>

          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Creando...' : 'Crear Tarea' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Modal para crear palabra clave -->
    <div v-if="showKeywordModal" class="modal fade show d-block" tabindex="-1"
      style="background-color: rgba(0, 0, 0, 50%);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Crear Nueva Palabra Clave</h5>
            <button type="button" class="btn-close" @click="showKeywordModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="createKeyword">
              <div class="mb-3">
                <label for="keywordName" class="form-label">Nombre de la Palabra Clave</label>
                <input type="text" class="form-control" id="keywordName" v-model="newKeyword.name" required
                  placeholder="Ingresa el nombre de la palabra clave" :disabled="keywordLoading">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showKeywordModal = false"
              :disabled="keywordLoading">
              Cancelar
            </button>
            <button type="button" class="btn btn-primary" @click="createKeyword" :disabled="keywordLoading">
              <span v-if="keywordLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ keywordLoading ? 'Creando...' : 'Crear Palabra Clave' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de tareas -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Tareas</h5>

        <div v-if="loading && tasks.length === 0" class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>

        <div v-else-if="tasks.length === 0" class="text-center text-muted">
          No hay tareas disponibles
        </div>

        <div v-else>
          <div v-for="task in tasks" :key="task.id" class="card mb-3" :class="{ 'border-success': task.is_done }">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <h6 class="card-title mb-2" :class="{ 'text-decoration-line-through text-muted': task.is_done }">
                    {{ task.title }}
                  </h6>

                  <!-- Estado -->
                  <span class="badge mb-2" :class="task.is_done ? 'bg-success' : 'bg-warning'">
                    {{ task.is_done ? 'Completada' : 'Pendiente' }}
                  </span>

                  <!-- Palabras clave -->
                  <div class="mt-2">
                    <span v-for="keyword in task.keywords" :key="keyword.id" class="badge bg-secondary me-1 mb-1">
                      {{ keyword.name }}
                    </span>
                    <span v-if="task.keywords.length === 0" class="text-muted small">
                      Sin palabras clave
                    </span>
                  </div>

                  <!-- Fechas -->
                  <div class="mt-2 small text-muted">
                    <div>Creado: {{ task.created_at }}</div>
                    <div>Actualizado: {{ task.updated_at }}</div>
                  </div>
                </div>

                <div class="btn-group ms-3">
                  <!-- Botón para alternar estado -->
                  <button @click="toggleTaskStatus(task.id, !task.is_done)" class="btn btn-sm"
                    :class="task.is_done ? 'btn-outline-warning' : 'btn-outline-success'" :disabled="loading">
                    {{ task.is_done ? 'Marcar como pendiente' : 'Marcar como completada' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-title {
  color: black !important;
}

.form-label {
  color: black !important;
}

.card {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 10%);
}

.badge {
  font-size: 0.75em;
}

.btn-group .btn {
  margin-inline-start: 0.25rem;
}

.spinner-border-sm {
  block-size: 1rem;
  inline-size: 1rem;
}

/* Estilos para la modal personalizada */
.modal {
  background-color: rgba(0, 0, 0, 50%);
}

.modal-content {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 10%);
}
</style>
