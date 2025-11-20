<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
// Importamos los componentes de Jetstream/Tailwind/Inertia
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Recibimos las listas desde el controlador
// CORRECCIÓN: Se utiliza la sintaxis simple de constructor (Array) para defineProps
// para evitar el error de compilación del entorno.
const props = defineProps({
    clientes: Array,
    usuarios: Array, // Lista de editores
    estados: Array,  // Lista de estados válidos
});

// Definición de opciones para Prioridad
const prioridades = ['Baja', 'Media', 'Alta', 'Urgente'];

// Inicializamos el formulario
const form = useForm({
    cliente_id: '',
    tipo: '',
    descripcion: '',
    estado: 'Pendiente', // Valor por defecto
    prioridad: 'Media', // Prioridad por defecto
    fecha_inicio: new Date().toISOString().split('T')[0], // Fecha de hoy
    fecha_final: '',
    editor_id: '',
    archivos: null, // Asignará la lista de archivos
});

// Manejo del envío del formulario
const createProceso = () => {
    // Inertia maneja automáticamente la subida de archivos si detecta uno (multipart/form-data)
    form.post(route('procesos.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            console.error('Error al crear proceso:', errors);
        }
    });
};

// Manejo del input de archivo
const handleFileChange = (event) => {
    // Asignamos la lista COMPLETA de archivos seleccionados
    form.archivos = event.target.files;
};

const cancel = () => {
    // Asumiendo que quieres volver al índice de procesos
    window.history.back();
};
</script>

<template>
    <AppLayout title="Crear Proceso">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Proceso
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Se utiliza FormSection para una mejor estructura y diseño -->
                <FormSection @submitted="createProceso">
                    <template #title>
                        Información del Proceso
                    </template>
                    <template #description>
                        Complete los detalles del nuevo proceso, asigne un cliente, un responsable, defina su estado y prioridad, y adjunte la documentación necesaria.
                    </template>

                    <template #form>
                        <!-- ASIGNACIÓN Y RESPONSABLE -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="cliente_id" value="Cliente" />
                            <select
                                id="cliente_id"
                                v-model="form.cliente_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>Selecciona un cliente</option>
                                <option v-for="cliente in props.clientes" :key="cliente.id" :value="cliente.id">
                                    {{ cliente.nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.cliente_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="editor_id" value="Asignar a (Editor/Responsable)" />
                            <select
                                id="editor_id"
                                v-model="form.editor_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>Selecciona un usuario</option>
                                <option v-for="user in props.usuarios" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.editor_id" class="mt-2" />
                        </div>

                        <!-- TIPO DE PROCESO (Input de texto) -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="tipo" value="Tipo de Proceso" />
                            <TextInput
                                id="tipo"
                                v-model="form.tipo"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Ej. Legal, Administrativo..."
                                required
                            />
                            <InputError :message="form.errors.tipo" class="mt-2" />
                        </div>

                        <!-- ESTADO (Select) -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="estado" value="Estado Actual" />
                            <select
                                id="estado"
                                v-model="form.estado"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option disabled value="">Selecciona estado</option>
                                <option v-for="estado in props.estados" :key="estado" :value="estado">
                                    {{ estado }}
                                </option>
                            </select>
                            <InputError :message="form.errors.estado" class="mt-2" />
                        </div>
                        
                        <!-- PRIORIDAD (Select) -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="prioridad" value="Prioridad del Proceso" />
                            <select
                                id="prioridad"
                                v-model="form.prioridad"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option disabled value="">Selecciona prioridad</option>
                                <option v-for="prioridad in prioridades" :key="prioridad" :value="prioridad">
                                    {{ prioridad }}
                                </option>
                            </select>
                            <InputError :message="form.errors.prioridad" class="mt-2" />
                        </div>

                        <!-- FECHAS -->
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="fecha_inicio" value="Fecha de Inicio" />
                            <TextInput
                                id="fecha_inicio"
                                v-model="form.fecha_inicio"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.fecha_inicio" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="fecha_final" value="Fecha Final (Estimada)" />
                            <TextInput
                                id="fecha_final"
                                v-model="form.fecha_final"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.fecha_final" class="mt-2" />
                        </div>

                        <!-- ARCHIVO ADJUNTO - AHORA MÚLTIPLE -->
                        <div class="col-span-6">
                            <InputLabel for="archivos" value="Documentos Adjuntos (Múltiples)" />
                            <div class="mt-2 flex items-center">
                                <input 
                                    type="file" 
                                    @change="handleFileChange" 
                                    accept=".pdf,.doc,.docx"
                                    multiple 
                                    class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                    hover:file:bg-indigo-100"
                                />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Puedes seleccionar múltiples archivos. Máximo 10MB por archivo. Formatos aceptados: PDF, DOC, DOCX.</p>
                            <InputError :message="form.errors.archivos" class="mt-2" />
                        </div>

                        <!-- DESCRIPCIÓN DETALLADA -->
                        <div class="col-span-6">
                            <InputLabel for="descripcion" value="Descripción Detallada" />
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="Describe los detalles del proceso, el alcance, los hitos clave y la documentación inicial requerida..."
                                required
                            ></textarea>
                            <InputError :message="form.errors.descripcion" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <SecondaryButton @click="cancel" class="mr-3">
                            Cancelar
                        </SecondaryButton>
                        
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar Proceso
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>