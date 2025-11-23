<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { watch, reactive, defineProps } from 'vue';

const props = defineProps({
    cambios: { type: Object, required: true },
    proceso: { type: Object, required: true },
    filtros: { type: Object, default: () => ({}) },
    usuarios: { type: Array, default: () => [] },
    estados: { type: Array, default: () => ['Pendiente', 'En Proceso', 'Finalizado'] },
});

const form = reactive({
    search: props.filtros.search ?? '',
    estado: props.filtros.estado ?? '',
    editor_id: props.filtros.editor_id ?? '',
    per_page: props.filtros.per_page ?? 10,
    sort_by: props.filtros.sort_by ?? 'fecha',
    sort_direction: props.filtros.sort_direction ?? 'desc',
});

let timeout = null;
const debounce = (callback, delay) => {
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback(...args), delay);
    };
};

const sendRequest = () => {
    router.get(
        route('procesos.cambios.index', proceso.id),
        { ...form },
        { preserveState: true, replace: true }
    );
};

watch(form, debounce(sendRequest, 300));

const sort = (column) => {
    if (form.sort_by === column) {
        form.sort_direction = form.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        form.sort_by = column;
        form.sort_direction = 'desc';
    }
};

const deleteCambio = (id) => {
    if (confirm('¿Estás seguro de eliminar este cambio?')) {
        router.delete(route('procesos.cambios.destroy', [proceso.id, id]), { preserveScroll: true });
    }
};
</script>

<template>
<AppLayout :title="`Cambios del Proceso #${proceso.id}`">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <!-- FILTROS Y BOTÓN CREAR -->
                <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <input
                        v-model="form.search"
                        type="text"
                        placeholder="Buscar por título..."
                        class="w-full md:w-auto border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:border-indigo-300"
                    />

                    <select v-model="form.estado" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">— Estado —</option>
                        <option v-for="estado in estados" :key="estado" :value="estado">{{ estado }}</option>
                    </select>

                    <select v-model="form.editor_id" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">— Editor —</option>
                        <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">{{ usuario.name }}</option>
                    </select>

                    <Link
                        :href="route('procesos.cambios.create', proceso.id)"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center shadow-md"
                    >
                        Crear Nuevo Cambio
                    </Link>
                </div>

                <!-- TABLA DE CAMBIOS -->
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th @click="sort('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                                    ID
                                </th>
                                <th @click="sort('titulo')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                                    Título
                                </th>
                                <th @click="sort('estado')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                                    Estado
                                </th>
                                <th @click="sort('editor_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                                    Editor
                                </th>
                                <th @click="sort('fecha')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Archivos
                                </th>
                                <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="cambio in cambios.data" :key="cambio.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ cambio.id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ cambio.titulo }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs font-semibold rounded-full"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': cambio.estado === 'Pendiente',
                                            'bg-blue-100 text-blue-800': cambio.estado === 'En Proceso',
                                            'bg-green-100 text-green-800': cambio.estado === 'Finalizado',
                                        }">
                                        {{ cambio.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ cambio.editor.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ cambio.fecha }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div v-if="cambio.documentos && cambio.documentos.length" class="flex flex-col space-y-1">
                                        <a v-for="doc in cambio.documentos" :key="doc.id"
                                           :href="`/storage/${doc.ruta}`" target="_blank"
                                           class="text-blue-600 hover:text-blue-800 hover:underline text-xs">
                                           {{ doc.nombre_original }}
                                        </a>
                                    </div>
                                    <span v-else class="text-gray-400 italic">Sin documentos</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('procesos.cambios.edit', [proceso.id, cambio.id])" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</Link>
                                    <button @click="deleteCambio(cambio.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>

                            <tr v-if="cambios.data.length === 0">
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                    No se encontraron cambios para este proceso.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN -->
                <div v-if="cambios.links && cambios.links.length > 3" class="mt-4 flex flex-wrap justify-between items-center space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700">Mostrar:</span>
                        <select v-model="form.per_page" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="5">5/pág</option>
                            <option value="10">10/pág</option>
                            <option value="25">25/pág</option>
                            <option value="50">50/pág</option>
                        </select>
                    </div>

                    <div class="flex space-x-1">
                        <template v-for="(link, key) in cambios.links" :key="key">
                            <div v-if="!link.url" v-html="link.label" class="py-2 px-4 rounded-md border text-gray-400 cursor-not-allowed text-sm"/>
                            <Link v-else :href="link.url" v-html="link.label"
                                  class="py-2 px-4 rounded-md border text-sm"
                                  :class="{'bg-indigo-600 text-white border-indigo-600': link.active, 'bg-white text-gray-700 hover:bg-gray-100 border-gray-300': !link.active}"
                                  preserve-scroll preserve-state
                            />
                        </template>
                    </div>

                    <div class="text-sm text-gray-700">
                        Mostrando {{ cambios.meta.from }} a {{ cambios.meta.to }} de {{ cambios.meta.total }} resultados.
                    </div>
                </div>

            </div>
        </div>
    </div>
</AppLayout>
</template>
