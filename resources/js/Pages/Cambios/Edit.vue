<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    cambio: Object,
    proceso: Object,
    estados: Array
});

// Modal eliminar documentos
const confirmingDocumentDeletionId = ref(null);

const confirmDocumentDeletion = (id) => {
    confirmingDocumentDeletionId.value = id;
};

const deleteDocumentoConfirmed = () => {
    const docId = confirmingDocumentDeletionId.value;
    if (!docId) return;

    router.delete(
        route('procesos.cambios.documentos.destroy', [props.proceso.id, props.cambio.id, docId]),
        {
            preserveScroll: true,
            onSuccess: (page) => {
                // Actualizamos la lista de documentos sin recargar
                props.cambio.documentos = page.props.cambio.documentos;
                confirmingDocumentDeletionId.value = null;
            }
        }
    );
};

// Formulario
const form = useForm({
    _method: 'PUT',
    titulo: props.cambio.titulo,
    descripcion: props.cambio.descripcion,
    estado: props.cambio.estado,
    archivos: [] // Array para múltiples archivos
});

// Manejo de archivos
const handleFilesChange = (e) => form.archivos = Array.from(e.target.files);

// Actualizar cambio
const updateCambio = () => {
    form.post(route('procesos.cambios.update', [props.proceso.id, props.cambio.id]), {
        forceFormData: true,
        preserveScroll: true
    });
};


// Cancelar
const cancel = () => router.get(route('procesos.cambios.index', props.proceso.id));
</script>
<template>
<AppLayout :title="`Editar Cambio #${props.cambio.id}`">
  <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <FormSection @submitted="updateCambio">
        <template #title>Editar Cambio</template>
        <template #description>Modifica los detalles y archivos del cambio.</template>

        <template #form>
          <!-- Título -->
          <div class="col-span-6">
            <InputLabel for="titulo" value="Título" />
            <TextInput id="titulo" v-model="form.titulo" type="text" class="mt-1 block w-full" required />
            <InputError :message="form.errors.titulo" class="mt-2" />
          </div>

          <!-- Descripción -->
          <div class="col-span-6 mt-4">
            <InputLabel for="descripcion" value="Descripción" />
            <textarea id="descripcion" v-model="form.descripcion" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            <InputError :message="form.errors.descripcion" class="mt-2" />
          </div>

          <!-- Estado -->
          <div class="col-span-6 mt-4">
            <InputLabel for="estado" value="Estado" />
            <select id="estado" v-model="form.estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
            </select>
            <InputError :message="form.errors.estado" class="mt-2" />
          </div>

          <!-- Subir archivos -->
          <div class="col-span-6 mt-4">
            <InputLabel for="archivos" value="Reemplazar o añadir archivos" />
            <input type="file" id="archivos" multiple @change="handleFilesChange" class="mt-2 block w-full" />
            <InputError :message="form.errors.archivos" class="mt-2" />
          </div>

          <!-- Documentos existentes -->
          <div v-if="cambio.documentos && cambio.documentos.length" class="col-span-6 mt-4">
            <h3 class="text-sm font-medium text-gray-700">Documentos actuales</h3>
            <div v-for="doc in cambio.documentos" :key="doc.id" class="flex items-center justify-between mt-1 p-2 bg-gray-50 rounded border">
              <a :href="`/storage/${doc.ruta}`" target="_blank" class="text-blue-600 hover:underline">{{ doc.nombre_original }}</a>
              <DangerButton type="button" @click="confirmDocumentDeletion(doc.id)">X</DangerButton>
            </div>
          </div>
        </template>

        <template #actions>
          <div class="flex justify-between items-center">
            <DangerButton type="button" @click="cancel" class="mr-3">Cancelar</DangerButton>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Guardar Cambios</PrimaryButton>
          </div>
        </template>
      </FormSection>
    </div>
  </div>

  <!-- Modal eliminar documento -->
  <div v-if="confirmingDocumentDeletionId" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6">
      <h3 class="text-lg font-bold text-red-600 mb-3">Confirmar Eliminación</h3>
      <p class="text-sm text-gray-600 mb-6">Esta acción no se puede deshacer.</p>
      <div class="flex justify-end space-x-3">
        <PrimaryButton type="button" @click="confirmingDocumentDeletionId = null" class="bg-gray-200 text-gray-700 hover:bg-gray-300">Cancelar</PrimaryButton>
        <DangerButton @click="deleteDocumentoConfirmed" :disabled="form.processing">Eliminar</DangerButton>
      </div>
    </div>
  </div>
</AppLayout>
</template>
