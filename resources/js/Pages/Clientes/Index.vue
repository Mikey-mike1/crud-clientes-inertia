<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
// Importamos 'watch' para observar cambios en 'searchTerm'
import { Link, router, Head } from '@inertiajs/vue3';
import { defineProps, ref, watch, computed } from 'vue'; 

const props = defineProps({
    // La data de clientes ya viene paginada, filtrada y ordenada por el backend
    clientes: Object, 
    flash: {
        type: Object,
        default: () => ({})
    },
    // Añadimos props para recibir el estado actual del backend (opcional, pero buena práctica)
    filters: Object,
});

// 1. Estado para la búsqueda y el ordenamiento.
// Usamos los valores que vienen del backend o los predeterminados.
// Se usa ref() para los valores reactivos que controlan la URL/petición.
const searchTerm = ref(props.filters.search ?? '');
const sortBy = ref(props.filters.sort_by ?? 'dni'); 
const sortDirection = ref(props.filters.sort_direction ?? 'asc');

// Función principal para hacer la petición al backend
const performSearchAndSort = () => {
    // router.get enviará una petición GET al controlador actual (clientes.index)
    // incluyendo los parámetros en la URL (Query String).
    router.get(route('clientes.index'), {
        search: searchTerm.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
    }, {
        // Opciones de Inertia
        preserveState: true, // Mantiene los valores de los inputs (searchTerm, etc.)
        replace: true,       // Reemplaza la entrada en el historial del navegador (back/forward)
    });
};

// 2. Observador para el campo de búsqueda (`searchTerm`)
// Cada vez que 'searchTerm' cambia, se hace un nuevo GET al backend.
watch(searchTerm, (value) => {
    // Opcional: Podrías añadir un debounce aquí para no hacer una petición en cada pulsación
    performSearchAndSort();
});

// Función para cambiar el ordenamiento
const sort = (column) => {
    if (sortBy.value === column) {
        // Invierte la dirección si es la misma columna
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // Establece nueva columna y dirección predeterminada
        sortBy.value = column;
        sortDirection.value = 'asc';
    }
    // Llama a la función principal para que el backend reordene la data
    performSearchAndSort();
};

// Función de eliminación (SIN CAMBIOS)
const deleteCliente = (id) => {
    if (confirm('¿Estás seguro de que quieres eliminar este cliente? Esta acción es irreversible.')) {
        router.delete(route('clientes.destroy', id), {
            preserveScroll: true
        });
    }
};

// Función para obtener el icono de dirección (AHORA usa las variables de estado)
const getSortIcon = (column) => {
    if (sortBy.value === column) {
        return sortDirection.value === 'asc' ? '▲' : '▼'; 
    }
    return '';
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
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 space-y-4 sm:space-y-0">
                        
                        <div class="w-full sm:w-1/3">
                            <input type="text" v-model="searchTerm" placeholder="Buscar DNI, Nombre o Correo..."
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>
                        
                        <Link :href="route('clientes.create')"
                                class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition duration-150 text-center">
                            Crear Nuevo Cliente
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th @click="sort('dni')"
                                        class="cursor-pointer px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-100 transition duration-150">
                                        DNI {{ getSortIcon('dni') }}
                                    </th>
                                    <th @click="sort('nombre')"
                                        class="cursor-pointer px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-100 transition duration-150">
                                        Nombre {{ getSortIcon('nombre') }}
                                    </th>
                                    <th @click="sort('correo')"
                                        class="cursor-pointer px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-100 transition duration-150">
                                        Correo {{ getSortIcon('correo') }}
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
                                <tr v-if="clientes.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        No se encontraron clientes que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

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