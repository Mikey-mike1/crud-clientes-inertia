<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3'; 
import { reactive, defineProps, watch, computed } from 'vue';

const props = defineProps({
    users: {
        type: Object, // paginación tipo Laravel: { data: [...], links: [...], meta: {...} }
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const form = reactive({
    search: props.filters.search ?? '',
    per_page: props.filters.per_page ?? 10,
    sort_by: props.filters.sort_by ?? 'name',
    sort_direction: props.filters.sort_direction ?? 'asc',
});

// CLAVE: Función para generar la URL de paginación manteniendo los filtros y la ordenación
const getPaginatedUrl = (url) => {
    if (!url) return null;

    const urlObj = new URL(url);
    
    // Inyectamos los valores actuales del formulario en el enlace de la página
    if (form.search) urlObj.searchParams.set('search', form.search);
    if (form.per_page) urlObj.searchParams.set('per_page', form.per_page);
    if (form.sort_by) urlObj.searchParams.set('sort_by', form.sort_by);
    if (form.sort_direction) urlObj.searchParams.set('sort_direction', form.sort_direction);

    return urlObj.toString();
};

// Función para traducir las etiquetas de paginación de Laravel (Previous/Next)
const translateLabel = (label) => {
    return label.replace('Previous', 'Anterior').replace('Next', 'Siguiente').replace(/&laquo;|&raquo;/g, '');
};


let timeout = null;
const debounce = (callback, delay) => (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => callback(...args), delay);
};

const sendRequest = () => {
    router.get(
        route('admin.users.index'),
        { 
            search: form.search, 
            per_page: form.per_page,
            sort_by: form.sort_by,
            sort_direction: form.sort_direction,
        },
        { preserveState: true, replace: true, preserveScroll: true }
    );
};

watch(form, debounce(sendRequest, 300));

const sort = (column) => {
    if (form.sort_by === column) {
        form.sort_direction = form.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        form.sort_by = column;
        form.sort_direction = 'asc';
    }
};

const deleteUser = (id) => {
    if (confirm('¿Desea eliminar este usuario?')) {
        router.delete(route('admin.users.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
<AppLayout title="Usuarios">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex-grow w-full md:w-auto">
                        <input 
                            v-model="form.search" 
                            type="text" 
                            placeholder="Buscar por nombre o email..."
                            class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                    </div>
                    <Link :href="route('admin.users.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out shadow-md flex items-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Nuevo Usuario
                    </Link>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button @click="sort('name')" class="flex items-center group">
                                        Nombre
                                        <!-- Icono de ordenación -->
                                        <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'name', 'opacity-100': form.sort_by === 'name', 'rotate-180': form.sort_direction === 'desc' && form.sort_by === 'name'}" class="w-4 h-4 ml-1 transition-opacity transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button @click="sort('email')" class="flex items-center group">
                                        Email
                                        <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'email', 'opacity-100': form.sort_by === 'email', 'rotate-180': form.sort_direction === 'desc' && form.sort_by === 'email'}" class="w-4 h-4 ml-1 transition-opacity transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button @click="sort('role')" class="flex items-center group">
                                        Rol
                                        <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'role', 'opacity-100': form.sort_by === 'role', 'rotate-180': form.sort_direction === 'desc' && form.sort_by === 'role'}" class="w-4 h-4 ml-1 transition-opacity transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="relative px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.role }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                    <Link :href="route('admin.users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 transition-colors">Editar</Link>
                                    
                                    <!-- LÓGICA CLAVE: Verificamos si tiene procesos activos -->
                                    <template v-if="user.active_tasks_count > 0">
                                        <span class="text-gray-400 cursor-not-allowed italic text-xs ml-2" 
                                              title="No se puede eliminar: Tiene procesos asignados.">
                                            Asignado ({{ user.active_tasks_count }})
                                        </span>
                                    </template>
                                    <template v-else>
                                        <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900 transition-colors">Eliminar</button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación con Botones -->
                <div v-if="users.links && users.links.length > 3" class="mt-4 flex flex-wrap justify-between items-center space-y-3 sm:space-y-0">
                    
                    <div class="flex items-center space-x-2 order-2 sm:order-1">
                        <span class="text-sm text-gray-700">Mostrar:</span>
                        <select 
                            v-model="form.per_page" 
                            class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-sm"
                        >
                            <option value="5">5/pág</option>
                            <option value="10">10/pág</option>
                            <option value="25">25/pág</option>
                            <option value="50">50/pág</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-1 order-3 sm:order-2">
                        <template v-for="(link, key) in users.links" :key="key">
                            <!-- Enlace de paginación activo/inactivo -->
                            <Link
                                v-if="link.url"
                                :href="getPaginatedUrl(link.url)"
                                v-html="translateLabel(link.label)"
                                class="py-2 px-4 rounded-md border transition-colors duration-150 ease-in-out text-sm"
                                :class="{
                                    'bg-indigo-600 text-white border-indigo-600': link.active,
                                    'bg-white text-gray-700 hover:bg-gray-100 border-gray-300': !link.active
                                }"
                                preserve-scroll
                                preserve-state
                            />
                            <!-- Placeholder para Anterior/Siguiente cuando no hay URL -->
                            <div v-else 
                                class="py-2 px-4 rounded-md border text-gray-400 cursor-not-allowed text-sm bg-gray-50" 
                                v-html="translateLabel(link.label)" 
                            />
                        </template>
                    </div>
                    
                    <!-- Mostrando resumen de resultados -->
                    <div class="text-sm text-gray-700 order-1 sm:order-3 w-full sm:w-auto text-center sm:text-right">
                        Mostrando {{ users.from }} a {{ users.to }} de {{ users.total }} resultados.
                    </div>
                </div>

            </div>
        </div>
    </div>
</AppLayout>
</template>