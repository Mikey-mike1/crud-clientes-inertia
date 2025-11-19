<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
// Componentes de Jetstream/Tailwind
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { defineProps } from 'vue';

const props = defineProps({
    cliente: Object, // El objeto Cliente pasado desde el controlador
});

// Inicialización del formulario con los datos existentes del cliente
const form = useForm({
    dni: props.cliente.dni,
    nombre: props.cliente.nombre,
    correo: props.cliente.correo,
    telefono: props.cliente.telefono,
});

// Función para enviar la actualización (usa put() o patch())
const updateCliente = () => {
    // Método PUT a la ruta 'clientes.update' pasando el ID del cliente
    form.put(route('clientes.update', props.cliente.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :title="`Editar Cliente: ${props.cliente.nombre}`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Cliente: **{{ props.cliente.nombre }}**
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <FormSection @submitted="updateCliente">
                    <template #title>Información del Cliente</template>

                    <template #description>Actualice los campos del cliente seleccionado.</template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="dni" value="DNI" />
                            <TextInput id="dni" v-model="form.dni" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.dni" class="mt-2" />
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="nombre" value="Nombre" />
                            <TextInput id="nombre" v-model="form.nombre" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.nombre" class="mt-2" />
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="correo" value="Correo Electrónico" />
                            <TextInput id="correo" v-model="form.correo" type="email" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.correo" class="mt-2" />
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="telefono" value="Teléfono" />
                            <TextInput id="telefono" v-model="form.telefono" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.telefono" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar Cambios
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>