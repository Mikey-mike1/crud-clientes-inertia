<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    proceso: Object,
    usuarios: Array,
    estados: Array,
});

// Formulario reactivo
const form = useForm({
    titulo: '',
    descripcion: '',
    estado: 'Pendiente',
    editor_id: '',
    fecha: new Date().toISOString().split('T')[0],
    archivos: null,
});

// Manejo de archivos múltiples
const handleFiles = (e) => {
    form.archivos = e.target.files;
};

// Envío de formulario
const submit = () => {
    form.post(route('procesos.cambios.store', props.proceso.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const cancel = () => window.history.back();
</script>

<template>
<AppLayout :title="`Registrar Cambio - Proceso #${proceso.id}`">
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <FormSection @submitted="submit">
                <template #title>
                    Registrar Nuevo Cambio
                </template>
                <template #description>
                    Complete los detalles del cambio, asignelo a un editor, defina su estado y adjunte documentos si es necesario.
                </template>

                <template #form>
                    <!-- Título -->
                    <div class="col-span-6">
                        <InputLabel for="titulo" value="Título" />
                        <TextInput id="titulo" v-model="form.titulo" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.titulo" class="mt-2" />
                    </div>

                    <!-- Descripción -->
                    <div class="col-span-6">
                        <InputLabel for="descripcion" value="Descripción" />
                        <textarea id="descripcion" v-model="form.descripcion" rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required></textarea>
                        <InputError :message="form.errors.descripcion" class="mt-2" />
                    </div>

                    <!-- Estado -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="estado" value="Estado" />
                        <select id="estado" v-model="form.estado"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option disabled value="">Seleccione estado</option>
                            <option v-for="estado in estados" :key="estado" :value="estado">{{ estado }}</option>
                        </select>
                        <InputError :message="form.errors.estado" class="mt-2" />
                    </div>

                    <!-- Editor -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="editor_id" value="Asignar a" />
                        <select id="editor_id" v-model="form.editor_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option disabled value="">Seleccione editor</option>
                            <option v-for="user in usuarios" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <InputError :message="form.errors.editor_id" class="mt-2" />
                    </div>

                    <!-- Fecha -->
                    <div class="col-span-6 sm:col-span-3">
                        <InputLabel for="fecha" value="Fecha" />
                        <TextInput id="fecha" v-model="form.fecha" type="date" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.fecha" class="mt-2" />
                    </div>

                    <!-- Archivos -->
                    <div class="col-span-6">
                        <InputLabel for="archivos" value="Archivos (Opcional)" />
                        <input type="file" multiple @change="handleFiles"
                            class="mt-2 block w-full text-sm text-gray-500
                                   file:mr-4 file:py-2 file:px-4
                                   file:rounded-full file:border-0
                                   file:text-sm file:font-semibold
                                   file:bg-indigo-50 file:text-indigo-700
                                   hover:file:bg-indigo-100" />
                        <InputError :message="form.errors.archivos" class="mt-2" />
                    </div>
                </template>

                <template #actions>
                    <SecondaryButton @click="cancel" class="mr-3">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">Guardar Cambio</PrimaryButton>
                </template>
            </FormSection>
        </div>
    </div>
</AppLayout>
</template>
