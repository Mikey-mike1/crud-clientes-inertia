<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  cambio: Object, // incluye proceso, editor, documentos
});

const confirmingDelete = ref(false);

const deleteCambio = () => confirmingDelete.value = true;
const deleteCambioConfirmed = () => {
  router.delete(route('procesos.cambios.destroy', { proceso: props.cambio.proceso.id, cambio: props.cambio.id }));
};
</script>

<template>
<AppLayout :title="`Cambio #${props.cambio.id}`">
  <template #header>
    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Detalle del Cambio #{{ props.cambio.id }}</h2>
  </template>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

      <!-- Información del Cambio -->
      <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200 space-y-4">
        <h3 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-2">Información del Cambio</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <p><strong>Proceso:</strong> #{{ cambio.proceso.id }} - {{ cambio.proceso.tipo }}</p>
          <p><strong>Cliente:</strong> {{ cambio.proceso.cliente.nombre }}</p>
          <p><strong>Título:</strong> {{ cambio.titulo }}</p>
          <p><strong>Estado:</strong> <span class="text-indigo-600 font-medium">{{ cambio.estado }}</span></p>
          <p><strong>Editor:</strong> {{ cambio.editor.name }}</p>
          <p><strong>Fecha:</strong> {{ cambio.fecha }}</p>
        </div>
        <p><strong>Descripción:</strong></p>
        <p class="bg-gray-50 p-3 rounded-md border border-gray-200">{{ cambio.descripcion }}</p>
      </div>

      <!-- Documentos del Cambio -->
      <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200 space-y-4">
        <h3 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-2">Documentos del Cambio</h3>
        <div v-if="cambio.documentos.length" class="space-y-2">
          <div v-for="doc in cambio.documentos" :key="doc.id" class="flex items-center bg-gray-50 p-2 rounded-lg border border-gray-200">
            <a 
              :href="`/storage/${doc.ruta}`"
              target="_blank"
              class="text-sm text-blue-600 hover:underline truncate max-w-xs"
              :title="doc.nombre_original">
              {{ doc.nombre_original }}
            </a>
          </div>
        </div>
        <p v-else class="text-gray-400 italic">No hay documentos.</p>
      </div>

      <!-- Botones de acción -->
      <div class="flex justify-end space-x-3">
        <DangerButton @click="deleteCambio" class="px-6 py-2 text-lg">Eliminar Cambio</DangerButton>
      </div>
    </div>
  </div>

  <!-- Modal Confirmación Eliminar Cambio -->
  <div v-if="confirmingDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6">
      <h3 class="text-lg font-bold text-red-600 mb-3">Confirmar Eliminación</h3>
      <p class="text-gray-600 mb-6">¿Deseas eliminar este cambio y todos sus documentos asociados? Esta acción no se puede deshacer.</p>
      <div class="flex justify-end space-x-3">
        <PrimaryButton type="button" @click="confirmingDelete = false" class="bg-gray-200 text-gray-700">Cancelar</PrimaryButton>
        <DangerButton @click="deleteCambioConfirmed">Eliminar Cambio</DangerButton>
      </div>
    </div>
  </div>
</AppLayout>
</template>
