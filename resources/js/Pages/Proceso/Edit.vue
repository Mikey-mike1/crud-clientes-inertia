<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Props
const props = defineProps({
    proceso: Object, // Debe incluir 'documentos'
    clientes: Array,
    usuarios: Array,
    estados: Array,
});

// === Confirmaciones ===
const confirmingProcessDeletion = ref(false);
const confirmingDocumentDeletionId = ref(null);

// Funciones de eliminación
const deleteProceso = () => confirmingProcessDeletion.value = true;
const deleteProcesoConfirmed = () => router.delete(route('procesos.destroy', props.proceso.id));

const confirmDocumentDeletion = (documentoId) => confirmingDocumentDeletionId.value = documentoId;
const deleteDocumentoConfirmed = () => {
    const documentoId = confirmingDocumentDeletionId.value;
    if (!documentoId) return;

    router.delete(route('procesos.documentos.destroy', [props.proceso.id, documentoId]), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            // Actualiza la lista localmente
            props.proceso.documentos = props.proceso.documentos.filter(d => d.id !== documentoId);
            confirmingDocumentDeletionId.value = null;
        }
    });
};

// === Formulario ===
const form = useForm({
    _method: 'PUT',
    cliente_id: props.proceso.cliente_id,
    tipo: props.proceso.tipo,
    descripcion: props.proceso.descripcion,
    estado: props.proceso.estado,
    fecha_inicio: props.proceso.fecha_inicio,
    fecha_final: props.proceso.fecha_final,
    editor_id: props.proceso.editor_id,
    archivos: null,
});

const updateProceso = () => {
    form.post(route('procesos.update', props.proceso.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.archivos = null,
    });
};

const handleFilesChange = (event) => form.archivos = event.target.files;
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
                <template #title>Editar Información y Documentos</template>
                <template #description>Modifica los detalles del proceso y gestiona los documentos asociados.</template>

                <template #form>
                    <!-- Cliente -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="cliente_id" value="Cliente" />
                        <select v-model="form.cliente_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">{{ cliente.nombre }}</option>
                        </select>
                        <InputError :message="form.errors.cliente_id" />
                    </div>

                    <!-- Editor -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="editor_id" value="Asignado a" />
                        <select v-model="form.editor_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option v-for="user in usuarios" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <InputError :message="form.errors.editor_id" />
                    </div>

                    <!-- Tipo -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="tipo" value="Tipo" />
                        <TextInput v-model="form.tipo" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.tipo" />
                    </div>

                    <!-- Estado -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="estado" value="Estado" />
                        <select v-model="form.estado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option v-for="estado in estados" :key="estado" :value="estado">{{ estado }}</option>
                        </select>
                        <InputError :message="form.errors.estado" />
                    </div>

                    <!-- Fechas -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="fecha_inicio" value="Fecha Inicio" />
                        <TextInput v-model="form.fecha_inicio" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.fecha_inicio" />
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="fecha_final" value="Fecha Final" />
                        <TextInput v-model="form.fecha_final" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.fecha_final" />
                    </div>

                    <!-- Descripción -->
                    <div class="col-span-6">
                        <InputLabel for="descripcion" value="Descripción" />
                        <textarea v-model="form.descripcion" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        <InputError :message="form.errors.descripcion" />
                    </div>

                    <!-- Documentos Existentes -->
                    <div class="col-span-6 border-t pt-4 border-gray-200">
                        <h3 class="text-lg font-medium mb-3">Documentos Existentes</h3>
                        <div v-if="props.proceso.documentos.length > 0" class="space-y-3">
                            <div v-for="documento in props.proceso.documentos" :key="documento.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center">
                                    <a :href="`/storage/${documento.ruta}`" target="_blank" class="text-sm text-gray-700 hover:text-indigo-600 font-medium truncate">
                                        {{ documento.nombre_original }}
                                    </a>
                                </div>
                                <DangerButton @click="confirmDocumentDeletion(documento.id)" class="p-1 h-8 w-8 flex items-center justify-center">
                                    Eliminar
                                </DangerButton>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400 italic">No hay documentos asociados.</p>
                    </div>

                    <!-- Subir Archivos Nuevos -->
                    <div class="col-span-6 mt-4">
                        <InputLabel for="archivos" value="Añadir Nuevos Archivos" />
                        <input type="file" multiple @change="handleFilesChange" accept=".pdf,.doc,.docx,image/*" class="mt-2 block w-full" />
                        <InputError :message="form.errors.archivos" />
                    </div>
                </template>

                <template #actions>
                    <div class="flex justify-between items-center w-full">
                        <DangerButton type="button" @click="deleteProceso">Eliminar Proceso</DangerButton>
                        <PrimaryButton :disabled="form.processing">Actualizar Proceso</PrimaryButton>
                    </div>
                </template>
            </FormSection>
        </div>
    </div>

    <!-- Modal Eliminar Documento -->
    <div v-if="confirmingDocumentDeletionId" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6">
            <h3 class="text-lg font-bold text-red-600 mb-3">Confirmar Eliminación de Documento</h3>
            <p class="text-sm text-gray-600 mb-6">¿Deseas eliminar este documento? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end space-x-3">
                <PrimaryButton type="button" @click="confirmingDocumentDeletionId = null" class="bg-gray-200 text-gray-700">Cancelar</PrimaryButton>
                <DangerButton @click="deleteDocumentoConfirmed">Eliminar Documento</DangerButton>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Proceso -->
    <div v-if="confirmingProcessDeletion" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6">
            <h3 class="text-lg font-bold text-red-600 mb-3">Confirmar Eliminación de Proceso</h3>
            <p class="text-sm text-gray-600 mb-6">¿Deseas eliminar este proceso y todos los documentos asociados? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end space-x-3">
                <PrimaryButton type="button" @click="confirmingProcessDeletion = false" class="bg-gray-200 text-gray-700">Cancelar</PrimaryButton>
                <DangerButton @click="deleteProcesoConfirmed">Eliminar Proceso</DangerButton>
            </div>
        </div>
    </div>
</AppLayout>
</template>
