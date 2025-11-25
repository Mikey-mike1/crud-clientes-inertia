<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3'; 
import { reactive, defineProps, watch } from 'vue';

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
        { preserveState: true, replace: true }
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
                    <Link href="/admin/users/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out shadow-md flex items-center flex-shrink-0">
                        Nuevo Usuario
                    </Link>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button @click="sort('name')" class="flex items-center group">
                                        Nombre
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button @click="sort('email')" class="flex items-center group">
                                        Email
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <button @click="sort('role')" class="flex items-center group">
                                        Rol
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.role }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <Link :href="`/admin/users/${user.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                    <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
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

                <div v-if="users.links && users.links.length > 3" class="mt-4 flex flex-wrap justify-between items-center space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-2 order-2 sm:order-1">
                        <span class="text-sm text-gray-700">Mostrar:</span>
                        <select v-model="form.per_page" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-sm">
                            <option value="5">5/pág</option>
                            <option value="10">10/pág</option>
                            <option value="25">25/pág</option>
                            <option value="50">50/pág</option>
                        </select>
                    </div>
                    <div class="flex space-x-1 order-3 sm:order-2">
                        <template v-for="(link, key) in users.links" :key="key">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="py-2 px-4 rounded-md border transition-colors duration-150 ease-in-out text-sm"
                                :class="{
                                    'bg-indigo-600 text-white border-indigo-600': link.active,
                                    'bg-white text-gray-700 hover:bg-gray-100 border-gray-300': !link.active
                                }"
                                preserve-scroll
                                preserve-state
                            />
                            <div v-else class="py-2 px-4 rounded-md border text-gray-400 cursor-not-allowed text-sm" v-html="link.label" />
                        </template>
                    </div>
                    <div class="text-sm text-gray-700 order-1 sm:order-3 w-full sm:w-auto text-center sm:text-right">
                        Mostrando {{ users.meta.from }} a {{ users.meta.to }} de {{ users.meta.total }} resultados.
                    </div>
                </div>

            </div>
        </div>
    </div>
</AppLayout>
</template>
