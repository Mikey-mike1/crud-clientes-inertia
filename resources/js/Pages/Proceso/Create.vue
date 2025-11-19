<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Recibimos las listas desde el controlador
const props = defineProps({
    clientes: Array,
    usuarios: Array, // Lista de editores
    estados: Array,  // Lista de estados válidos
});

// Inicializamos el formulario
const form = useForm({
    cliente_id: '',
    tipo: '',
    descripcion: '',
    estado: 'Pendiente', // Valor por defecto
    fecha_inicio: new Date().toISOString().split('T')[0], // Fecha de hoy
    fecha_final: '',
    editor_id: '',
    archivo: null,
});

// Manejo del envío del formulario
const createProceso = () => {
    // Inertia maneja automáticamente la subida de archivos si detecta uno
    form.post(route('procesos.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

// Manejo del input de archivo
const handleFileChange = (event) => {
    form.archivo = event.target.files[0];
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
                <FormSection @submitted="createProceso">
                    <template #title>
                        Información del Proceso
                    </template>
                    <template #description>
                        Complete los detalles del nuevo proceso, asigne un cliente, un responsable y adjunte la documentación necesaria.
                    </template>

                    <template #form>
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
                            <InputLabel for="editor_id" value="Asignar a (Editor)" />
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

                        <div class="col-span-6">
                            <InputLabel for="archivo" value="Documento Adjunto (PDF, DOCX)" />
                            <div class="mt-2 flex items-center">
                                <input 
                                    type="file" 
                                    @change="handleFileChange" 
                                    accept=".pdf,.doc,.docx"
                                    class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                    hover:file:bg-indigo-100"
                                />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Máximo 10MB.</p>
                            <InputError :message="form.errors.archivo" class="mt-2" />
                        </div>

                        <div class="col-span-6">
                            <InputLabel for="descripcion" value="Descripción Detallada" />
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="Describe los detalles del proceso..."
                                required
                            ></textarea>
                            <InputError :message="form.errors.descripcion" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar Proceso
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>