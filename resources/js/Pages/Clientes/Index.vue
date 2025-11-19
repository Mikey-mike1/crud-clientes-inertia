<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { defineProps } from 'vue';

// Props pasadas desde el controlador de Laravel
const props = defineProps({
    clientes: Object, // Laravel Paginate es un objeto en Inertia
    // Flash message para notificaciones
    flash: {
        type: Object,
        default: () => ({})
    },
});

// Función de eliminación
const deleteCliente = (id) => {
    if (confirm('¿Estás seguro de que quieres eliminar este cliente? Esta acción es irreversible.')) {
        // Usa router.delete de Inertia para enviar la petición DELETE
        router.delete(route('clientes.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AppLayout title="Clientes">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Clientes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="flash.success" class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                    {{ flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-end mb-4">
                        <Link :href="route('clientes.create')"
                              class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition duration-150">
                            Crear Nuevo Cliente
                        </Link>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    DNI
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Correo
                                </th>
                                <th class="px-6 py-3 bg-gray-50">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="cliente in clientes.data" :key="cliente.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ cliente.dni }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ cliente.nombre }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ cliente.correo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link :href="route('clientes.edit', cliente.id)" 
                                          class="text-indigo-600 hover:text-indigo-900 mr-4">
                                        Editar
                                    </Link>
                                    
                                    <button @click="deleteCliente(cliente.id)" 
                                            class="text-red-600 hover:text-red-900 focus:outline-none">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <template v-for="(link, key) in clientes.links" :key="key">
                            <Link v-if="link.url"
                                  :href="link.url"
                                  v-html="link.label"
                                  class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                                  :class="{ 'bg-indigo-500 text-white': link.active }"
                            />
                            <span v-else v-html="link.label" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded text-gray-400"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>