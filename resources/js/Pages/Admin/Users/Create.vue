<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'editor', // Valor por defecto
});

const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => console.error('Errores al crear usuario:', errors),
    });
};

const cancel = () => window.history.back();
</script>

<template>
    <AppLayout title="Crear Usuario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>Información del Usuario</template>
                    <template #description>
                        Complete los datos del usuario, incluyendo rol y credenciales de acceso.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="name" value="Nombre" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="password" value="Contraseña" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.password_confirmation" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="role" value="Rol" />
                            <select id="role" v-model="form.role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="editor">Editor</option>
                                <option value="administrador">Administrador</option>
                            </select>
                            <InputError :message="form.errors.role" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <SecondaryButton @click="cancel" class="mr-3">Cancelar</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar Usuario
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
