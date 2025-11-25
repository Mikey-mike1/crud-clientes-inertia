<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onSuccess: () => console.log('Usuario actualizado'),
        onError: (errors) => console.error('Errores al actualizar usuario:', errors),
    });
};

const cancel = () => window.history.back();
</script>

<template>
    <AppLayout title="Editar Usuario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <FormSection @submitted="submit">
                    <template #title>Actualizar Información del Usuario</template>
                    <template #description>
                        Modifique los datos del usuario, incluyendo rol y contraseña si desea cambiarla.
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
                            <InputLabel for="password" value="Contraseña (dejar en blanco si no desea cambiar)" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" />
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
                            Actualizar Usuario
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
