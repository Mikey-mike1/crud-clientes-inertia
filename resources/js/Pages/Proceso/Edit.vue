<script setup>
import { ref } from 'vue'; // Necesario para el manejo del modal de confirmación
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

// Recibimos los datos
const props = defineProps({
    proceso: Object, // El objeto a editar (ahora debe incluir 'documentos')
    clientes: Array,
    usuarios: Array,
    estados: Array,
});

// === LÓGICA DE CONFIRMACIÓN CUSTOM (Reemplazo de confirm()) ===
// Estados de confirmación
const confirmingProcessDeletion = ref(false);
const confirmingDocumentDeletionId = ref(null);

// Función de Eliminar Proceso (Abre modal)
const deleteProceso = () => {
    confirmingProcessDeletion.value = true;
};

// Función de Eliminar Proceso (Confirmado)
const deleteProcesoConfirmed = () => {
    router.delete(route('procesos.destroy', props.proceso.id));
};

// Función de Eliminar Documento (Abre modal)
const confirmDocumentDeletion = (documentoId) => {
    confirmingDocumentDeletionId.value = documentoId;
};

// Función de Eliminar Documento (Confirmado)
const deleteDocumentoConfirmed = () => {
    const documentoId = confirmingDocumentDeletionId.value;
    if (documentoId) {
        // Enviar petición DELETE al endpoint para eliminar el documento
        router.delete(route('documentos.destroy', documentoId), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => confirmingDocumentDeletionId.value = null, // Cierra modal
        });
    }
};
// === FIN LÓGICA DE CONFIRMACIÓN CUSTOM ===


// Inicializamos el form con los datos existentes
const form = useForm({
    _method: 'PUT', // Truco para enviar archivos en edición
    cliente_id: props.proceso.cliente_id,
    tipo: props.proceso.tipo,
    descripcion: props.proceso.descripcion,
    estado: props.proceso.estado,
    fecha_inicio: props.proceso.fecha_inicio,
    fecha_final: props.proceso.fecha_final,
    editor_id: props.proceso.editor_id,
    
    // CAMBIO: Ahora acepta una lista de archivos para subir
    archivos_a_subir: null, 
});

// Función de Actualizar
const updateProceso = () => {
    // Usamos post con _method: PUT para que soporte archivos
    form.post(route('procesos.update', props.proceso.id), {
        forceFormData: true, // Asegura que se envíen los archivos
        preserveScroll: true,
        // Limpiamos el input de archivos tras el éxito, pero no reseteamos todo el formulario
        onSuccess: () => form.archivos_a_subir = null,
    });
};

// CAMBIO: Manejo de múltiples archivos (FileList)
const handleFilesChange = (event) => {
    // Almacena la FileList, que es lo que Inertia necesita para múltiples archivos.
    form.archivos_a_subir = event.target.files;
};
</script>

<template>
    <AppLayout title="Editar Proceso">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Proceso #{{ props.proceso.id }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <FormSection @submitted="updateProceso">
                    <template #title>
                        Editar Información y Documentos
                    </template>
                    <template #description>
                        Modifica los detalles del proceso y gestiona los documentos asociados. Los nuevos archivos se añadirán a la colección existente.
                    </template>

                    <template #form>
                        <!-- Campos de Datos Generales (Sin cambios) -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="cliente_id" value="Cliente" />
                            <select id="cliente_id" v-model="form.cliente_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                    {{ cliente.nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.cliente_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="editor_id" value="Asignado a" />
                            <select id="editor_id" v-model="form.editor_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option v-for="user in usuarios" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.editor_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="tipo" value="Tipo" />
                            <TextInput id="tipo" v-model="form.tipo" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.tipo" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="estado" value="Estado" />
                            <select id="estado" v-model="form.estado" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option v-for="estado in estados" :key="estado" :value="estado">{{ estado }}</option>
                            </select>
                            <InputError :message="form.errors.estado" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="fecha_inicio" value="Fecha Inicio" />
                            <TextInput id="fecha_inicio" v-model="form.fecha_inicio" type="date" class="mt-1 block w-full" />
                            <InputError :message="form.errors.fecha_inicio" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="fecha_final" value="Fecha Final" />
                            <TextInput id="fecha_final" v-model="form.fecha_final" type="date" class="mt-1 block w-full" />
                            <InputError :message="form.errors.fecha_final" class="mt-2" />
                        </div>
                        
                        <div class="col-span-6">
                            <InputLabel for="descripcion" value="Descripción" />
                            <textarea id="descripcion" v-model="form.descripcion" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                            <InputError :message="form.errors.descripcion" class="mt-2" />
                        </div>


                        <!-- CAMBIO: Gestión de Documentos -->
                        <div class="col-span-6 border-t pt-4 border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Documentos Existentes</h3>

                            <div v-if="props.proceso.documentos && props.proceso.documentos.length > 0" class="space-y-3">
                                <div v-for="documento in props.proceso.documentos" :key="documento.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 10a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1-5a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                        <a :href="`/storage/${documento.ruta}`" target="_blank" class="text-sm text-gray-700 hover:text-indigo-600 hover:underline font-medium truncate" :title="documento.nombre_original">
                                            {{ documento.nombre_original }}
                                        </a>
                                    </div>
                                    <DangerButton type="button" @click="confirmDocumentDeletion(documento.id)" class="ml-4 p-1 h-8 w-8 flex items-center justify-center text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </DangerButton>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-400 italic">No hay documentos asociados a este proceso.</p>
                        </div>
                        
                        <!-- CAMBIO: Input para Múltiples Archivos Nuevos -->
                        <div class="col-span-6">
                            <InputLabel for="archivos_a_subir" value="Añadir Nuevos Archivos (Selecciona uno o más)" />
                            
                            <input 
                                id="archivos_a_subir"
                                type="file" 
                                multiple 
                                @change="handleFilesChange" 
                                accept=".pdf,.doc,.docx,image/*" 
                                class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
                            />
                            
                            <InputError :message="form.errors.archivos_a_subir" class="mt-2" />
                            <InputError v-if="form.errors['archivos_a_subir.0']" :message="form.errors['archivos_a_subir.0']" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Puedes seleccionar varios archivos a la vez para subir.</p>
                        </div>

                    </template>

                    <template #actions>
                        <div class="flex justify-between w-full items-center">
                            <!-- Botón que abre el modal de confirmación -->
                            <DangerButton type="button" @click="deleteProceso" class="mr-3">
                                Eliminar Proceso
                            </DangerButton>

                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Actualizar Proceso
                            </PrimaryButton>
                        </div>
                    </template>
                </FormSection>
            </div>
        </div>

        <!-- Confirmación de Eliminación de Documento (Simulación de Modal) -->
        <div v-if="confirmingDocumentDeletionId" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
            <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6">
                <h3 class="text-lg font-bold text-red-600 mb-3">Confirmar Eliminación de Documento</h3>
                <p class="text-sm text-gray-600 mb-6">
                    ¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.
                </p>
                <div class="flex justify-end space-x-3">
                    <PrimaryButton type="button" @click="confirmingDocumentDeletionId = null" class="bg-gray-200 text-gray-700 hover:bg-gray-300">
                        Cancelar
                    </PrimaryButton>
                    <DangerButton @click="deleteDocumentoConfirmed" :disabled="form.processing">
                        Eliminar Documento
                    </DangerButton>
                </div>
            </div>
        </div>

        <!-- Confirmación de Eliminación de Proceso (Simulación de Modal) -->
        <div v-if="confirmingProcessDeletion" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
            <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6">
                <h3 class="text-lg font-bold text-red-600 mb-3">Confirmar Eliminación de Proceso</h3>
                <p class="text-sm text-gray-600 mb-6">
                    ¿Estás seguro de que deseas eliminar todo este proceso? Esto eliminará todos los datos asociados, incluidos los documentos. Esta acción no se puede deshacer.
                </p>
                <div class="flex justify-end space-x-3">
                    <PrimaryButton type="button" @click="confirmingProcessDeletion = false" class="bg-gray-200 text-gray-700 hover:bg-gray-300">
                        Cancelar
                    </PrimaryButton>
                    <DangerButton @click="deleteProcesoConfirmed" :disabled="form.processing">
                        Eliminar Proceso
                    </DangerButton>
                </div>
            </div>
        </div>
    </AppLayout>
</template>