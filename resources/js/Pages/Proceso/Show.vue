<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    proceso: Object // incluye cliente, editor, documentos y cambios
});

const confirmingProcessDeletion = ref(false);

const deleteProceso = () => confirmingProcessDeletion.value = true;
const deleteProcesoConfirmed = () => router.delete(route('procesos.destroy', props.proceso.id));
</script>

<template>
<AppLayout :title="`Proceso #${props.proceso.id}`">
    <template #header>
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">Detalle del Proceso #{{ props.proceso.id }}</h2>
    </template>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Información del Proceso -->
            <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200 space-y-4">
                <h3 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-2">Información del Proceso</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <p><strong>Cliente:</strong> {{ proceso.cliente.nombre }}</p>
                    <p><strong>Tipo:</strong> {{ proceso.tipo }}</p>
                    <p><strong>Estado:</strong> <span class="text-indigo-600 font-medium">{{ proceso.estado }}</span></p>
                    <p><strong>Fecha Inicio:</strong> {{ proceso.fecha_inicio }}</p>
                    <p><strong>Fecha Final:</strong> {{ proceso.fecha_final || 'N/A' }}</p>
                    <p><strong>Asignado a:</strong> {{ proceso.editor.name }}</p>
                </div>
                <p><strong>Descripción:</strong></p>
                <p class="bg-gray-50 p-3 rounded-md border border-gray-200">{{ proceso.descripcion }}</p>

                <!-- Documentos -->
                <div>
                    <h4 class="font-semibold text-gray-600 mt-3 mb-2">Documentos:</h4>
                    <div v-if="proceso.documentos.length" class="space-y-2">
                        <div v-for="doc in proceso.documentos" :key="doc.id" class="flex items-center bg-gray-50 p-2 rounded-lg border border-gray-200">
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
            </div>

            <!-- Cambios del Proceso -->
            <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200 space-y-4">
                <h3 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-2">Cambios del Proceso</h3>
                <div v-if="proceso.cambios.length" class="space-y-3">
                    <div v-for="cambio in proceso.cambios" :key="cambio.id" class="border rounded-lg p-4 bg-gray-50 shadow-sm space-y-2">
                        <div class="flex justify-between items-center">
                            <h4 class="font-medium text-gray-800">#{{ cambio.id }} - {{ cambio.titulo }}</h4>
                            <span class="text-sm text-indigo-600 font-medium">{{ cambio.estado }}</span>
                        </div>
                        <p class="text-gray-600 text-sm"><strong>Editor:</strong> {{ cambio.editor.name }}</p>
                        <p class="text-gray-600 text-sm"><strong>Fecha:</strong> {{ cambio.fecha }}</p>

                        <!-- Documentos del Cambio -->
                        <div v-if="cambio.documentos.length" class="flex flex-wrap gap-2 mt-2">
                            <a 
                                v-for="doc in cambio.documentos" 
                                :key="doc.id"
                                :href="`/storage/${doc.ruta}`" 
                                target="_blank"
                                class="text-sm text-green-700 bg-green-100 px-2 py-1 rounded-full hover:bg-green-200 truncate max-w-xs"
                                :title="doc.nombre_original">
                                {{ doc.nombre_original }}
                            </a>
                        </div>
                        <p v-else class="text-gray-400 italic mt-1">No hay documentos.</p>
                    </div>
                </div>
                <p v-else class="text-gray-400 italic">No hay cambios asociados.</p>
            </div>

            <!-- Botón Eliminar -->
            <div class="flex justify-end">
                <DangerButton @click="deleteProceso" class="px-6 py-2 text-lg">Eliminar Proceso</DangerButton>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación Eliminar Proceso -->
    <div v-if="confirmingProcessDeletion" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6">
            <h3 class="text-lg font-bold text-red-600 mb-3">Confirmar Eliminación</h3>
            <p class="text-gray-600 mb-6">¿Deseas eliminar este proceso y todos sus documentos asociados? Esta acción no se puede deshacer.</p>
            <div class="flex justify-end space-x-3">
                <PrimaryButton type="button" @click="confirmingProcessDeletion = false" class="bg-gray-200 text-gray-700">Cancelar</PrimaryButton>
                <DangerButton @click="deleteProcesoConfirmed">Eliminar</DangerButton>
            </div>
        </div>
    </div>
</AppLayout>
</template>
