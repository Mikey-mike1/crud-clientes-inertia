<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, watch, defineProps } from 'vue';

const props = defineProps({
  cambios: { type: Object, required: true },
  usuarios: { type: Array, required: true },
  estados: { type: Array, required: true },
  filters: { type: Object, default: () => ({}) },
});

// Formulario reactivo para filtros y orden
const form = reactive({
  search: props.filters.search ?? '',
  estado: props.filters.estado ?? '',
  editor_id: props.filters.editor_id ?? '',
  per_page: props.filters.per_page ?? 10,
  sort_by: props.filters.sort_by ?? 'fecha',
  sort_direction: props.filters.sort_direction ?? 'desc',
});

// Función debounce para enviar solicitudes
let timeout = null;
const debounce = (callback, delay) => (...args) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => callback(...args), delay);
};

const sendRequest = () => {
  router.get(route('cambios.mostrarTodos'), { ...form }, { preserveState: true, replace: true });
};

watch(form, debounce(sendRequest, 300));

// Función para ordenar columnas
const sort = (column) => {
  if (form.sort_by === column) {
    form.sort_direction = form.sort_direction === 'asc' ? 'desc' : 'asc';
  } else {
    form.sort_by = column;
    form.sort_direction = 'desc';
  }
};

// Función para borrar cambio
const confirmDelete = (cambio) => {
  if (confirm(`¿Desea eliminar el cambio "${cambio.titulo}"?`)) {
    router.delete(route('procesos.cambios.destroy', { proceso: cambio.proceso_id, cambio: cambio.id }), {
      preserveState: true,
    });
  }
};
</script>

<template>
<AppLayout title="Todos los Cambios">
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow-xl sm:rounded-lg p-6">

        <!-- FILTROS -->
        <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
          <input v-model="form.search" type="text" placeholder="Buscar título..." class="border p-2 rounded-md flex-grow">
          <select v-model="form.estado" class="border p-2 rounded-md">
            <option value="">— Estado —</option>
            <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
          </select>
          <select v-model="form.editor_id" class="border p-2 rounded-md">
            <option value="">— Editor —</option>
            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>

        <!-- TABLA -->
        <div class="overflow-x-auto rounded-lg border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th @click="sort('id')" class="px-6 py-3 cursor-pointer">ID</th>
                <th @click="sort('titulo')" class="px-6 py-3 cursor-pointer">Título</th>
                <th>Estado</th>
                <th>Editor</th>
                <th @click="sort('fecha')" class="px-6 py-3 cursor-pointer">Fecha</th>
                <th>Documentos</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="cambio in cambios.data" :key="cambio.id">
                <td class="px-6 py-4">{{ cambio.id }}</td>
                <td class="px-6 py-4">{{ cambio.titulo }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                          'bg-yellow-100 text-yellow-800': cambio.estado === 'Pendiente',
                          'bg-green-100 text-green-800': cambio.estado === 'Finalizado',
                          'bg-blue-100 text-blue-800': cambio.estado === 'Entregado',
                          'bg-red-100 text-red-800': cambio.estado === 'En Revision',
                        }">
                    {{ cambio.estado }}
                  </span>
                </td>
                <td class="px-6 py-4 flex items-center">
                  <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 mr-2">
                    {{ cambio.editor.name.charAt(0) }}
                  </div>
                  {{ cambio.editor.name }}
                </td>
                <td class="px-6 py-4">{{ cambio.fecha }}</td>
                <td class="px-6 py-4 space-y-1">
                  <template v-if="cambio.documentos && cambio.documentos.length">
                    <div v-for="doc in cambio.documentos" :key="doc.id">
                      <a :href="`/storage/${doc.ruta}`" target="_blank" class="text-blue-600 hover:underline">
                        {{ doc.nombre_original }}
                      </a>
                    </div>
                  </template>
                  <span v-else>—</span>
                </td>
                <td class="px-6 py-4 flex space-x-2">
                  <!-- Editar -->
                  <Link :href="route('procesos.cambios.edit', { proceso: cambio.proceso_id, cambio: cambio.id })" class="text-blue-600 hover:underline">
                    Editar
                  </Link>
                  <!-- Borrar -->
                  <button @click="confirmDelete(cambio)" class="text-red-600 hover:underline">
                    Borrar
                  </button>
                  <!-- Ver -->

                </td>
              </tr>

              <tr v-if="cambios.data.length === 0">
                <td colspan="7" class="text-center py-10 text-gray-500">No se encontraron cambios.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINACIÓN -->
        <div v-if="cambios.meta" class="mt-4 flex justify-between items-center">
          <div>
            Mostrando {{ cambios.meta.from ?? 0 }} a {{ cambios.meta.to ?? 0 }} de {{ cambios.meta.total ?? 0 }} resultados.
          </div>
          <div class="flex space-x-1">
            <Link v-for="link in cambios.links" :key="link.label" :href="link.url" v-html="link.label"
                  class="px-3 py-1 border rounded-md"
                  :class="{'bg-indigo-600 text-white border-indigo-600': link.active}"/>
          </div>
        </div>

      </div>
    </div>
  </div>
</AppLayout>
</template>
