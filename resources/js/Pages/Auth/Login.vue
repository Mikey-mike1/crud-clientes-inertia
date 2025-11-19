<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <Head title="Iniciar Sesión" />

  <div class="min-h-screen flex">

    <!-- Imagen / fondo izquierda con degradado azul -->
    <div class="hidden md:flex w-1/2 relative">
      <div class="absolute inset-0 bg-gradient-to-b from-blue-800 to-blue-500"></div>
      <img src="/images/bg.webp" alt="Fondo" class="object-cover w-full h-full opacity-30" />
      <div class="absolute inset-0 flex items-center justify-center">
        <h1 class="text-white text-4xl font-bold text-center px-6">Bienvenido/a <br>
DoctorTesis Edt</h1>
      </div>
    </div>

    <!-- Formulario derecha -->
    <div class="flex w-full md:w-1/2 items-center justify-center bg-gray-100">
      <div class="bg-white/40 backdrop-blur-md rounded-2xl shadow-2xl p-10 w-full max-w-md animate-fadeIn">

        <!-- Logo -->
        <div class="flex justify-center mb-6">
          <img src="/images/logo.png" class="w-64 h-20 object-contain" alt="Logo" />
        </div>

        <!-- Mensaje de estado -->
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center">
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <InputLabel for="email" value="Correo" class="text-blue-700" />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              required
              autofocus
              autocomplete="username"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div>
            <InputLabel for="password" value="Contraseña" class="text-blue-700" />
            <TextInput
              id="password"
              v-model="form.password"
              type="password"
              class="mt-1 block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              required
              autocomplete="current-password"
            />
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div class="flex items-center">
            <Checkbox v-model:checked="form.remember" name="remember" />
            <span class="ml-2 text-sm text-gray-700">Guardar Sesión</span>
          </div>

          <div class="flex items-center justify-between mt-4">
            <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-blue-700 hover:text-blue-900 underline">
              Olvidé mi contraseña
            </Link>

            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="bg-orange-500 hover:bg-orange-600 text-white font-semibold"
            >
              Iniciar Sesión
            </PrimaryButton>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animación sutil al cargar el formulario */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease-out forwards;
}
</style>
