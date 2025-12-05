<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3'; 
import { watch, reactive, defineProps, computed } from 'vue'; 

const props = defineProps({
    procesos: {
        type: Object, 
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    usuarios: {
        type: Array, 
        default: () => [],
    },
    estados: {
        type: Array, 
        default: () => [],
    },
});

const form = reactive({
    search: props.filters.search ?? '',
    estado: props.filters.estado ?? '',
    editor_id: props.filters.editor_id ?? '',
    per_page: props.filters.per_page ?? 10,
    sort_by: props.filters.sort_by ?? 'fecha_final',
    sort_direction: props.filters.sort_direction ?? 'asc', // Cambiado a 'asc' para coincidir con tu controlador
});

// CLAVE: Usamos props.procesos.links directamente, que es un array de enlaces.
// Si tuvieras un JsonResource, usarías props.procesos.meta.links
// Pero como usas un Paginator estándar en el controlador, la estructura es directa.
const paginationLinks = computed(() => {
    // Si estás usando el Paginator estándar de Laravel, los enlaces están aquí.
    return props.procesos.links || [];
});

// Función CLAVE: Construye la URL de paginación manteniendo los filtros
const getPaginatedUrl = (url) => {
    if (!url) return null;

    const urlObj = new URL(url);
    
    // Inyectamos los valores actuales del formulario en el enlace de la página
    if (form.search) urlObj.searchParams.set('search', form.search);
    if (form.estado) urlObj.searchParams.set('estado', form.estado);
    if (form.editor_id) urlObj.searchParams.set('editor_id', form.editor_id);
    if (form.per_page) urlObj.searchParams.set('per_page', form.per_page);
    if (form.sort_by) urlObj.searchParams.set('sort_by', form.sort_by);
    if (form.sort_direction) urlObj.searchParams.set('sort_direction', form.sort_direction);

    return urlObj.toString();
};

// Función para traducir las etiquetas de paginación de Laravel
const translateLabel = (label) => {
    // Esto se mantiene para traducir los &laquo; Previous y Next &raquo; si vienen del Paginator
    return label.replace('Previous', 'Anterior').replace('Next', 'Siguiente').replace(/&laquo;|&raquo;/g, '');
};

let timeout = null;
const debounce = (callback, delay) => {
    return (...args) => {
        clearTimeout(timeout); 
        timeout = setTimeout(() => {
            callback(...args);
        }, delay);
    };
};

const sendRequest = () => {
    router.get(
        route('procesos.index'),
        {
            search: form.search,
            estado: form.estado,
            editor_id: form.editor_id,
            per_page: form.per_page,
            sort_by: form.sort_by,
            sort_direction: form.sort_direction,
        },
        { 
            preserveState: true, 
            replace: true,
            preserveScroll: true // Mantiene la posición del scroll al filtrar
        }
    );
};

// Observamos el formulario para disparar la búsqueda
watch(form, debounce(sendRequest, 300));

const sort = (column) => {
    if (form.sort_by === column) {
        // Si ya está ordenando por esta columna, invierte la dirección.
        form.sort_direction = form.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        // Si es una nueva columna, establece la columna y la dirección por defecto.
        form.sort_by = column;
        form.sort_direction = 'asc'; // Establecer 'asc' como predeterminado para el primer clic
    }
};

const deleteProceso = (id) => {
    if (confirm('¿Estás seguro de que quieres eliminar este proceso y todos sus documentos asociados? Esta acción es irreversible.')) {
        router.delete(route('procesos.destroy', id), {
            preserveScroll: true,
            preserveState: true
        });
    }
};
</script>

<template>
    <AppLayout title="Procesos">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    
                    <!-- Filtros y Botón de Creación -->
                    <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                        
                        <div class="flex-grow w-full md:w-auto">
                            <label for="search" class="sr-only">Buscar Cliente</label>
                            <input
                                id="search"
                                v-model="form.search"
                                type="text"
                                placeholder="Buscar por Nombre del Cliente..."
                                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            >
                        </div>
                        
                        <div class="w-full sm:w-1/3 md:w-40">
                            <label for="estado" class="sr-only">Estado</label>
                            <select
                                id="estado"
                                v-model="form.estado"
                                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            >
                                <option value="">— Estado —</option>
                                <option v-for="estado in estados" :key="estado" :value="estado">{{ estado }}</option>
                            </select>
                        </div>

                        <div class="w-full sm:w-1/3 md:w-40">
                            <label for="editor_id" class="sr-only">Editor</label>
                            <select
                                id="editor_id"
                                v-model="form.editor_id"
                                class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            >
                                <option value="">— Editor —</option>
                                <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">{{ usuario.name }}</option>
                            </select>
                        </div>

                        <Link :href="route('procesos.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out shadow-md flex items-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Crear Nuevo
                        </Link>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button @click="sort('id')" class="flex items-center group">
                                            ID
                                            <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'id', 'opacity-100': form.sort_by === 'id', 'rotate-180': form.sort_direction === 'desc' && form.sort_by === 'id'}" class="w-4 h-4 ml-1 transition-opacity transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </th>
                                    
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button @click="sort('tipo')" class="flex items-center group">
                                            Tipo
                                            <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'tipo', 'opacity-100': form.sort_by === 'tipo', 'rotate-180': form.sort_direction === 'desc' && form.sort_by === 'tipo'}" class="w-4 h-4 ml-1 transition-opacity transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </th>
                                    
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button @click="sort('estado')" class="flex items-center group">
                                            Estado
                                            <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'estado', 'opacity-100': form.sort_by === 'estado', 'rotate-180': form.sort_direction === 'desc' && form.sort_by === 'estado'}" class="w-4 h-4 ml-1 transition-opacity transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </th>
                                    
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button @click="sort('fecha_final')" class="flex items-center group">
                                            F. Final
                                            <svg :class="{'opacity-0 group-hover:opacity-100': form.sort_by !== 'fecha_final', 'opacity-100': form.sort_by === 'fecha_final', 'rotate-180': form.sort_direction === 'desc' && form.sort_by === 'fecha_final'}" class="w-4 h-4 ml-1 transition-opacity transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </th>
                                    
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Asignado a
                                    </th>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Archivos
                                    </th> 
                                    
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="proceso in procesos.data" :key="proceso.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ proceso.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ proceso.cliente.nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ proceso.tipo }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': proceso.estado === 'Pendiente',
                                                'bg-green-100 text-green-800': proceso.estado === 'Finalizado',
                                                'bg-blue-100 text-blue-800': proceso.estado === 'Entregado',
                                                'bg-red-100 text-red-800': proceso.estado === 'En Revision',
                                            }">
                                            {{ proceso.estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ proceso.fecha_final }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center">
                                        <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 mr-2">
                                            {{ proceso.editor.name.charAt(0) }}
                                        </div>
                                        {{ proceso.editor.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div v-if="proceso.documentos && proceso.documentos.length > 0" class="flex flex-col space-y-1">
                                            <a v-for="documento in proceso.documentos" 
                                                :key="documento.id"
                                                :href="`/storage/${documento.ruta}`" 
                                                target="_blank" 
                                                class="text-blue-600 hover:text-blue-800 hover:underline flex items-center text-xs transition-colors duration-150 ease-in-out"
                                                :title="documento.nombre_original">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                {{ documento.nombre_original.length > 20 ? documento.nombre_original.substring(0, 17) + '...' : documento.nombre_original }}
                                            </a>
                                        </div>
                                        <span v-else class="text-gray-400 italic">Sin documentos</span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('procesos.show', proceso.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold">
                                            Ver
                                        </Link>
                                        <Link :href="route('procesos.edit', proceso.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold">
                                            Editar
                                        </Link>
                                        <button @click="deleteProceso(proceso.id)" class="text-red-600 hover:text-red-900 focus:outline-none">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="procesos.data.length === 0">
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                        No se encontraron procesos que coincidan con los filtros.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación con Botones -->
                    <!-- Solo muestra la paginación si hay más de 3 enlaces (es decir, más de una página de contenido) -->
                    <div v-if="paginationLinks.length > 3" class="mt-4 flex flex-wrap justify-between items-center space-y-3 sm:space-y-0">
                        
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
                            <template v-for="(link, key) in paginationLinks" :key="key">
                                <div 
                                    v-if="!link.url" 
                                    class="py-2 px-4 rounded-md border text-gray-400 cursor-not-allowed text-sm bg-gray-50"
                                    v-html="translateLabel(link.label)"
                                />
                                <!-- Usamos getPaginatedUrl para mantener filtros y translateLabel para el idioma -->
                                <Link
                                    v-else
                                    :href="getPaginatedUrl(link.url)"
                                    class="py-2 px-4 rounded-md border transition-colors duration-150 ease-in-out text-sm"
                                    :class="{
                                        'bg-indigo-600 text-white border-indigo-600': link.active,
                                        'bg-white text-gray-700 hover:bg-gray-100 border-gray-300': !link.active
                                    }"
                                    v-html="translateLabel(link.label)"
                                    preserve-scroll
                                    preserve-state
                                />
                            </template>
                        </div>
                        
                        <!-- Accediendo directamente a las propiedades del paginador (from, to, total) -->
                        <div class="text-sm text-gray-700 order-1 sm:order-3 w-full sm:w-auto text-center sm:text-right">
                            Mostrando {{ procesos.from }} a {{ procesos.to }} de {{ procesos.total }} resultados.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>