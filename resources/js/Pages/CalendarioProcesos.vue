<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const daysOfWeek = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
const monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

const eventos = ref([]);
const eventoSeleccionado = ref(null); // ⭐ Modal

// Traer eventos desde /calendario
onMounted(async () => {
  try {
    const { data } = await axios.get('/calendario');
    eventos.value = data;
  } catch (error) {
    console.error("Error al cargar eventos:", error);
  }
});

// Navegación
function prevMonth() {
  if(currentMonth.value === 0){
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
}

function nextMonth() {
  if(currentMonth.value === 11){
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
}

// Construir celdas
const calendarCells = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1);
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);

  const startDay = firstDay.getDay(); // domingo=0
  const cells = [];

  const offset = startDay === 0 ? 6 : startDay - 1;

  for(let i=0; i<offset; i++){
    cells.push({ day: '', date: null, isCurrentMonth: false });
  }

  for(let d=1; d<=lastDay.getDate(); d++){
    const dateStr = `${currentYear.value}-${String(currentMonth.value+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    cells.push({ day: d, date: dateStr, isCurrentMonth: true });
  }

  return cells;
});

function eventosDelDia(date) {
  if(!date) return [];
  return eventos.value.filter(e => e.end === date);
}

// Modal
function verEvento(e) {
  eventoSeleccionado.value = e;
}

function cerrarModal() {
  eventoSeleccionado.value = null;
}
</script>

<style scoped>
.grid div {
  transition: background-color 0.2s;
}

.grid div:hover {
  background-color: #e2e8f0;
  cursor: pointer;
}
</style>

<template>
  <AppLayout title="Calendario de Procesos">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Calendario de Procesos</h1>

      <!-- Navegación -->
      <div class="flex justify-between items-center mb-4">
        <button @click="prevMonth" class="px-2 py-1 bg-gray-200 rounded">&lt;</button>
        <span class="font-semibold">{{ monthNames[currentMonth] }} {{ currentYear }}</span>
        <button @click="nextMonth" class="px-2 py-1 bg-gray-200 rounded">&gt;</button>
      </div>

      <!-- Días de semana -->
      <div class="grid grid-cols-7 text-center font-bold mb-2">
        <div v-for="day in daysOfWeek" :key="day">{{ day }}</div>
      </div>

      <!-- Celdas -->
      <div class="grid grid-cols-7 gap-1">
        <div 
          v-for="cell in calendarCells" 
          :key="cell.date || Math.random()" 
          class="border h-24 p-1 relative"
          :class="{ 'bg-gray-100': cell.isCurrentMonth, 'bg-gray-50': !cell.isCurrentMonth }"
        >
          <div class="text-sm font-medium">{{ cell.day }}</div>

          <!-- Eventos del día -->
          <div 
            v-for="evento in eventosDelDia(cell.date)" 
            :key="evento.id" 
            class="bg-blue-500 text-white text-xs rounded px-1 mt-1 truncate cursor-pointer"
            :title="evento.title"
            @click="verEvento(evento)"
          >
            {{ evento.title + ' ' + evento.cliente.nombre }}
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL -->
    <div 
      v-if="eventoSeleccionado" 
      class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center p-4 z-50"
    >
      <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg relative">

        <!-- Cerrar -->
        <button 
          @click="cerrarModal"
          class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl"
        >×</button>

        <h2 class="text-xl font-bold mb-4">Información del Proceso</h2>

        <div class="space-y-2 text-sm">
          <p><strong>ID:</strong> {{ eventoSeleccionado.id }}</p>
          <p><strong>Tipo:</strong> {{ eventoSeleccionado.tipo }}</p>
          <p><strong>Estado:</strong> {{ eventoSeleccionado.estado }}</p>
          <p><strong>Fecha Inicio:</strong> {{ eventoSeleccionado.start }}</p>
          <p><strong>Fecha Final:</strong> {{ eventoSeleccionado.end }}</p>
          <p><strong>Cliente:</strong> {{ eventoSeleccionado.cliente?.nombre }}</p>
          <p><strong>Editor:</strong> {{ eventoSeleccionado.editor?.name }}</p>
        </div>

        <div class="mt-4 text-right">
          <button 
            @click="cerrarModal"
            class="px-4 py-1 bg-blue-600 text-white rounded"
          >
            Cerrar
          </button>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
