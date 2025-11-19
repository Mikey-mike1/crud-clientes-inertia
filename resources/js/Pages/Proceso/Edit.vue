<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3'; // Importar router para delete
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue'; // Para borrar
import TextInput from '@/Components/TextInput.vue';

// Recibimos los datos
const props = defineProps({
    proceso: Object, // El objeto a editar
    clientes: Array,
    usuarios: Array,
    estados: Array,
});

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
    archivo: null, // Iniciamos null, solo se llena si el usuario sube uno nuevo
});

// Función de Actualizar
const updateProceso = () => {
    // Usamos post con _method: PUT para que soporte archivos
    form.post(route('procesos.update', props.proceso.id), {
        preserveScroll: true,
        onSuccess: () => form.reset('archivo'),
    });
};

// Función de Eliminar (Botón extra de utilidad)
const deleteProceso = () => {
    if (confirm('¿Estás seguro de querer eliminar este proceso? Esta acción no se puede deshacer.')) {
        router.delete(route('procesos.destroy', props.proceso.id));
    }
};

const handleFileChange = (event) => {
    form.archivo = event.target.files[0];
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
                        Editar Información
                    </template>
                    <template #description>
                        Modifica los detalles del proceso. Si subes un nuevo archivo, el anterior será reemplazado.
                    </template>

                    <template #form>
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
                            <InputLabel for="archivo" value="Actualizar Archivo (Opcional)" />
                            
                            <div v-if="props.proceso.archivos" class="mb-2 text-sm text-gray-600">
                                Archivo actual: 
                                <a :href="`/storage/${props.proceso.archivos}`" target="_blank" class="text-indigo-600 hover:underline font-semibold">
                                    Ver Documento
                                </a>
                            </div>

                            <input type="file" @change="handleFileChange" accept=".pdf,.doc,.docx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <InputError :message="form.errors.archivo" class="mt-2" />
                        </div>

                        <div class="col-span-6">
                            <InputLabel for="descripcion" value="Descripción" />
                            <textarea id="descripcion" v-model="form.descripcion" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                            <InputError :message="form.errors.descripcion" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <div class="flex justify-between w-full items-center">
                            <DangerButton type="button" @click="deleteProceso" class="mr-3">
                                Eliminar Proceso
                            </DangerButton>

                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Actualizar
                            </PrimaryButton>
                        </div>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>