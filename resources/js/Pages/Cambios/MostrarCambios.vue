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
          <input
            v-model="form.search"
            type="text"
            placeholder="Buscar título..."
            class="w-full md:w-auto border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm p-2 flex-grow"
          />
          <select v-model="form.estado" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm p-2">
            <option value="">— Estado —</option>
            <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
          </select>
          <select v-model="form.editor_id" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm p-2">
            <option value="">— Editor —</option>
            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>

        <!-- TABLA -->
        <div class="overflow-x-auto rounded-lg border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <button @click="sort('id')" class="flex items-center group">
                    ID
                    <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'id', 'opacity-100': form.sort_by === 'id'}" class="w-4 h-4 ml-1 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path v-if="form.sort_direction === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                </th>

                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Título
                  <button @click="sort('titulo')" class="ml-1 text-gray-400 hover:text-gray-700">↕</button>
                </th>

                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>

                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Editor
                </th>

                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fecha
                  <button @click="sort('fecha')" class="ml-1 text-gray-400 hover:text-gray-700">↕</button>
                </th>

                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Documentos
                </th>

                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="cambio in cambios.data" :key="cambio.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ cambio.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ cambio.titulo }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
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
                <td class="px-6 py-4 whitespace-nowrap flex items-center">
                  <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 mr-2">
                    {{ cambio.editor.name.charAt(0) }}
                  </div>
                  {{ cambio.editor.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ cambio.fecha }}</td>
                <td class="px-6 py-4 text-sm text-gray-500 space-y-1">
                  <template v-if="cambio.documentos && cambio.documentos.length">
                    <div v-for="doc in cambio.documentos" :key="doc.id">
                      <a :href="`/storage/${doc.ruta}`" target="_blank" class="text-blue-600 hover:underline">
                        {{ doc.nombre_original }}
                      </a>
                    </div>
                  </template>
                  <span v-else class="text-gray-400 italic">Sin documentos</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex space-x-2">
                  <Link :href="route('procesos.cambios.show', { proceso: cambio.proceso_id, cambio: cambio.id })" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold">
                  Ver
                  </Link>
                  <Link :href="route('procesos.cambios.edit', { proceso: cambio.proceso_id, cambio: cambio.id })" class="text-indigo-600 hover:text-indigo-900 font-bold">
                    Editar
                  </Link>
                  
                  <button @click="confirmDelete(cambio)" class="text-red-600 hover:text-red-900">Borrar</button>
                </td>
              </tr>

              <tr v-if="cambios.data.length === 0">
                <td colspan="7" class="text-center py-10 text-gray-500">No se encontraron cambios.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINACIÓN -->
        <div v-if="cambios.links && cambios.links.length > 3" class="mt-4 flex flex-wrap justify-between items-center space-y-3 sm:space-y-0">
          <div class="flex items-center space-x-2 order-2 sm:order-1">
            <span class="text-sm text-gray-700">Mostrar:</span>
            <select v-model="form.per_page" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-sm p-1">
              <option value="5">5/pág</option>
              <option value="10">10/pág</option>
              <option value="25">25/pág</option>
              <option value="50">50/pág</option>
            </select>
          </div>

          <div class="flex space-x-1 order-3 sm:order-2">
            <template v-for="(link, key) in cambios.links" :key="key">
              <div 
                v-if="!link.url" 
                v-html="link.label" 
                class="py-2 px-4 rounded-md border text-gray-400 cursor-not-allowed text-sm"
              />
              <Link
                v-else
                :href="link.url"
                v-html="link.label"
                class="py-2 px-4 rounded-md border transition-colors duration-150 ease-in-out text-sm"
                :class="{
                  'bg-indigo-600 text-white border-indigo-600': link.active,
                  'bg-white text-gray-700 hover:bg-gray-100 border-gray-300': !link.active
                }"
                preserve-scroll
                preserve-state
              />
            </template>
          </div>

          <div class="text-sm text-gray-700 order-1 sm:order-3 w-full sm:w-auto text-center sm:text-right">
            Mostrando {{ cambios.meta?.from ?? 0 }} a {{ cambios.meta?.to ?? 0 }} de {{ cambios.meta?.total ?? 0 }} resultados.
          </div>
        </div>

      </div>
    </div>
  </div>
</AppLayout>
</template>
