<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
// Componentes de Jetstream/Tailwind
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Definición del formulario con useForm
const form = useForm({
    dni: '',
    nombre: '',
    correo: '',
    telefono: '',
});

// Función para enviar el formulario
const createCliente = () => {
    // Petición POST a la ruta 'clientes.store'
    form.post(route('clientes.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(), // Limpiar formulario al éxito
    });
};
</script>

<template>
    <AppLayout title="Crear Cliente">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Cliente
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <FormSection @submitted="createCliente">
                    <template #title>Datos del Cliente</template>

                    <template #description>Ingrese la información del nuevo cliente.</template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="dni" value="DNI" />
                            <TextInput id="dni" v-model="form.dni" type="text" class="mt-1 block w-full" required autocomplete="dni" />
                            <InputError :message="form.errors.dni" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="nombre" value="Nombre" />
                            <TextInput id="nombre" v-model="form.nombre" type="text" class="mt-1 block w-full" required autocomplete="nombre" />
                            <InputError :message="form.errors.nombre" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="correo" value="Correo Electrónico" />
                            <TextInput id="correo" v-model="form.correo" type="email" class="mt-1 block w-full" required autocomplete="correo" />
                            <InputError :message="form.errors.correo" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="telefono" value="Teléfono" />
                            <TextInput id="telefono" v-model="form.telefono" type="text" class="mt-1 block w-full" autocomplete="telefono" />
                            <InputError :message="form.errors.telefono" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar Cliente
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>