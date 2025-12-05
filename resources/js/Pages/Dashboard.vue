<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import ApexChart from 'vue3-apexcharts';
import AppLayout from '@/Layouts/AppLayout.vue';

const apexchart = ApexChart;

const page = usePage();

const user = computed(() => page.props.user);
const verTodo = computed(() => page.props.verTodo);

const procesosAsignados = computed(() => page.props.procesosAsignados ?? []);
const procesosPorVencer = computed(() => page.props.procesosPorVencer ?? []);
const cambios = computed(() => page.props.cambios ?? []);
const procesosPorMes = computed(() => page.props.procesosPorMes ?? {});
const procesosPorEstado = computed(() => page.props.procesosPorEstado ?? {});

const estados = Object.keys(procesosPorEstado.value);

const chartEstado = {
    series: Object.values(procesosPorEstado.value),
    options: {
        labels: estados,
        legend: { position: 'bottom' },
        colors: ["#2563eb", "#f59e0b", "#10b981", "#6b7280", "#dc2626"]
    }
};

const meses = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

const chartMes = {
    series: [
        {
            name: "Procesos",
            data: meses.map((_, i) => procesosPorMes.value[i + 1] ?? 0)
        }
    ],
    options: {
        chart: { toolbar: { show: false } },
        xaxis: { categories: meses },
        colors: ["#3b82f6"],
    }
};
</script>

<template>
<AppLayout :title="'Dashboard'">

    <!-- HEADER -->
    <template #header>
        <div class="flex justify-between items-center w-full">
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>

            <!-- BOTÓN VER TODO PARA ADMINISTRADORES -->
            <Link
                v-if="user.role === 'administrador'"
                :href="verTodo ? route('dashboard') : route('dashboard', { all: 1 })"
                class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700 transition"
            >
                {{ verTodo ? 'Ver solo mis procesos' : 'Ver todos los procesos' }}
            </Link>
        </div>
    </template>

    <div class="p-6 space-y-8">

        <!-- TARJETAS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="p-5 bg-white shadow rounded-lg">
                <h2 class="text-lg font-semibold text-gray-600">Usuario</h2>
                <p class="text-xl font-bold text-gray-800">{{ user.name }}</p>
                <p class="text-sm text-gray-500">{{ user.email }}</p>
                <p class="text-sm text-gray-500 font-medium">{{ user.role }}</p>

                <p class="mt-2 text-xs text-blue-600 font-bold" v-if="verTodo">
                    MODO ADMIN: VIENDO TODO
                </p>
            </div>

            <div class="p-5 bg-white shadow rounded-lg">
                <h2 class="text-lg font-semibold text-gray-600">Procesos</h2>
                <p class="text-4xl font-bold text-blue-600">{{ procesosAsignados.length }}</p>
            </div>

            <div class="p-5 bg-white shadow rounded-lg">
                <h2 class="text-lg font-semibold text-gray-600">Por Vencer</h2>
                <p class="text-4xl font-bold text-red-600">{{ procesosPorVencer.length }}</p>
            </div>

            <div class="p-5 bg-white shadow rounded-lg">
                <h2 class="text-lg font-semibold text-gray-600">Cambios Recientes</h2>
                <p class="text-4xl font-bold text-green-600">{{ cambios.length }}</p>
            </div>
        </div>

        <!-- GRAFICAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4">Procesos por Estado</h2>
                <apexchart
                    type="donut"
                    height="360"
                    :options="chartEstado.options"
                    :series="chartEstado.series"
                />
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4">Procesos por Mes</h2>
                <apexchart
                    type="bar"
                    height="360"
                    :options="chartMes.options"
                    :series="chartMes.series"
                />
            </div>
        </div>

        <!-- PROCESOS POR VENCER -->
        <div class="bg-white p-6 rounded shadow mt-8">
            <h2 class="text-xl font-bold mb-4 text-red-600">Procesos Próximos a Vencer</h2>
            <table class="min-w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="p-2">Cliente</th>
                        <th class="p-2">Tipo</th>
                        <th class="p-2">Fecha Final</th>
                        <th class="p-2">Estado</th>
                        <th class="p-2" v-if="user.role === 'Administrador'">Asignado a</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in procesosPorVencer" :key="p.id" class="border-b hover:bg-gray-100">
                        <td class="p-2">{{ p.cliente?.nombre }}</td>
                        <td class="p-2">{{ p.tipo }}</td>
                        <td class="p-2">{{ p.fecha_final }}</td>
                        <td class="p-2 font-semibold">{{ p.estado }}</td>
                        <td class="p-2" v-if="user.role === 'Administrador'">{{ p.editor?.name }}</td>
                    </tr>

                    <tr v-if="procesosPorVencer.length === 0">
                        <td class="p-4 text-gray-500" colspan="5">No hay procesos próximos a vencer.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- CAMBIOS RECIENTES -->
        <div class="bg-white p-6 rounded shadow mt-8">
            <h2 class="text-xl font-bold mb-4">Últimos Cambios</h2>
            <ul>
                <li v-for="c in cambios" :key="c.id" class="py-2 border-b">
                    <span class="font-bold">{{ c.titulo }}</span> —
                    <span class="text-gray-600">{{ c.descripcion }}</span>
                    <span class="text-sm text-gray-400">({{ c.fecha }})</span>

                    <span
                        v-if="user.role === 'Administrador'"
                        class="text-xs text-indigo-600 ml-2"
                    >
                        — {{ c.editor?.name }}
                    </span>
                </li>

                <li v-if="cambios.length === 0" class="text-gray-500">
                    No hay cambios recientes.
                </li>
            </ul>
        </div>

    </div>
</AppLayout>
</template>

<style scoped>
</style>
