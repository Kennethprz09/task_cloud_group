<script setup lang="ts">
import { onMounted, ref } from "vue";

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

interface NewTask {
  title: string;
  keyword_ids: number[];
  is_done: boolean;
}

interface NewKeyword {
  name: string;
}

// Estados reactivos con tipos
const tasks = ref<Task[]>([]);
const loading = ref<boolean>(false);
const keywordLoading = ref<boolean>(false);
const availableKeywords = ref<Keyword[]>([]);
const showKeywordModal = ref<boolean>(false);

const newTask = ref<NewTask>({
  title: "",
  keyword_ids: [],
  is_done: false,
});

const newKeyword = ref<NewKeyword>({
  name: "",
});

// Cargar tareas
const fetchTasks = async (): Promise<void> => {
  loading.value = true;
  try {
    const { data, response } = await useAxios("/tasks/index").get();

    if (response.status == 200 && data) {
      tasks.value = data.data;
    }
  } catch (error) {
    console.error("Error al cargar tareas:", error);
    toast("Error", "Error al cargar las tareas", "danger");
  } finally {
    loading.value = false;
  }
};

// Cargar palabras clave disponibles
const fetchKeywords = async (): Promise<void> => {
  loading.value = true;
  try {
    const { data, response } = await useAxios("/keywords/index").get();

    if (response.status == 200 && data) {
      availableKeywords.value = data.data;
    }
  } catch (error) {
    console.error("Error al cargar keywords:", error);
    toast("Error", "Error al cargar las palabras claves", "danger");
  } finally {
    loading.value = false;
  }
};

// Crear nueva tarea
const createTask = async (): Promise<void> => {
  if (!newTask.value.title.trim()) {
    toast("Error", "Por favor ingresa un título para la tarea", "danger");
    return;
  }

  loading.value = true;
  try {
    const { data, response } = await useAxios("/tasks/store").post({
      title: newTask.value.title,
      keyword_ids: newTask.value.keyword_ids,
    });

    if (response.status == 200 && data) {
      // Resetear formulario
      newTask.value.title = "";
      newTask.value.keyword_ids = [];

      // Recargar tareas
      await fetchTasks();
    }
  } catch (error) {
    console.error("Error al crear tarea:", error);
    toast("Error", "Error al crear la tarea", "danger");
  } finally {
    loading.value = false;
  }
};

// Crear nueva palabra clave
const createKeyword = async (): Promise<void> => {
  if (!newKeyword.value.name.trim()) {
    toast(
      "Error",
      "Por favor ingresa un nombre para la palabra clave",
      "danger"
    );
    return;
  }

  keywordLoading.value = true;
  try {
    const { data, response } = await useAxios("/keywords/store").post({
      name: newKeyword.value.name,
    });

    if (response.status == 200 && data) {
      // Cerrar modal y resetear formulario
      showKeywordModal.value = false;
      newKeyword.value.name = "";

      // Recargar la lista de palabras clave
      await fetchKeywords();
    }
  } catch (error) {
    console.error("Error al crear palabra clave:", error);
    toast("Error", "Error al crear la palabra clave", "danger");
  } finally {
    keywordLoading.value = false;
  }
};

// Cambiar estado de la tarea
const toggleTaskStatus = async (
  taskId: number,
  isDone: boolean
): Promise<void> => {
  loading.value = true;
  try {
    const { data, response } = await useAxios(`/tasks/toggle/${taskId}`).put({
      is_done: isDone ? 1 : 0,
    });
    if (response.status == 200 && data) {
      // Actualizar la tarea en la lista local
      const taskIndex = tasks.value.findIndex((task) => task.id === taskId);
      if (taskIndex !== -1) {
        if (response.data.data) {
          tasks.value[taskIndex] = response.data.data;
        } else {
          tasks.value[taskIndex].is_done = isDone ? 1 : 0;
          tasks.value[taskIndex].updated_at = new Date()
            .toISOString()
            .split("T")[0];
        }
      }
    }
  } catch (error) {
    console.error("Error al actualizar tarea:", error);
    toast("Error", "Error al actualizar el estado de la tarea", "danger");
  } finally {
    loading.value = false;
  }
};

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
          <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            @click="showKeywordModal = true"
          >
            Crear Palabra Clave
          </button>
        </div>
      </div>
      <div class="card-body">
        <form @submit.prevent="createTask">
          <div class="mb-3">
            <label for="taskTitle" class="form-label">Título</label>
            <input
              type="text"
              class="form-control"
              id="taskTitle"
              v-model="newTask.title"
              required
              placeholder="Ingresa el título de la tarea"
              :disabled="loading"
            />
          </div>

          <div class="mb-3">
            <label for="taskKeywords" class="form-label">Palabras Clave</label>
            <select
              multiple
              class="form-select"
              id="taskKeywords"
              v-model="newTask.keyword_ids"
              :disabled="loading"
            >
              <option
                v-for="keyword in availableKeywords"
                :key="keyword.id"
                :value="keyword.id"
              >
                {{ keyword.name }}
              </option>
            </select>
            <div class="form-text">
              Mantén presionada la tecla Ctrl (Cmd en Mac) para seleccionar
              múltiples palabras clave
            </div>
          </div>

          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span
              v-if="loading"
              class="spinner-border spinner-border-sm me-2"
            ></span>
            {{ loading ? "Creando..." : "Crear Tarea" }}
          </button>
        </form>
      </div>
    </div>

    <!-- Modal para crear palabra clave -->
    <div
      v-if="showKeywordModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 50%)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Crear Nueva Palabra Clave</h5>
            <button
              type="button"
              class="btn-close"
              @click="showKeywordModal = false"
            ></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="createKeyword">
              <div class="mb-3">
                <label for="keywordName" class="form-label"
                  >Nombre de la Palabra Clave</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="keywordName"
                  v-model="newKeyword.name"
                  required
                  placeholder="Ingresa el nombre de la palabra clave"
                  :disabled="keywordLoading"
                />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="showKeywordModal = false"
              :disabled="keywordLoading"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="createKeyword"
              :disabled="keywordLoading"
            >
              <span
                v-if="keywordLoading"
                class="spinner-border spinner-border-sm me-2"
              ></span>
              {{ keywordLoading ? "Creando..." : "Crear Palabra Clave" }}
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
          <div
            v-for="task in tasks"
            :key="task.id"
            class="card mb-3"
            :class="{ 'border-success': task.is_done }"
          >
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <h6
                    class="card-title mb-2"
                    :class="{
                      'text-decoration-line-through text-muted': task.is_done,
                    }"
                  >
                    {{ task.title }}
                  </h6>

                  <!-- Estado -->
                  <span
                    class="badge mb-2"
                    :class="task.is_done ? 'bg-success' : 'bg-warning'"
                  >
                    {{ task.is_done ? "Completada" : "Pendiente" }}
                  </span>

                  <!-- Palabras clave -->
                  <div class="mt-2">
                    <span
                      v-for="keyword in task.keywords"
                      :key="keyword.id"
                      class="badge bg-secondary me-1 mb-1"
                    >
                      {{ keyword.name }}
                    </span>
                    <span
                      v-if="task.keywords.length === 0"
                      class="text-muted small"
                    >
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
                  <button
                    @click="toggleTaskStatus(task.id, !task.is_done)"
                    class="btn btn-sm"
                    :class="
                      task.is_done
                        ? 'btn-outline-warning'
                        : 'btn-outline-success'
                    "
                    :disabled="loading"
                  >
                    {{
                      task.is_done
                        ? "Marcar como pendiente"
                        : "Marcar como completada"
                    }}
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
