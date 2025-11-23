<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100">
            <!-- NAV BAR FONDO AZUL Y BORDE AZUL OSCURO -->
            <nav class="bg-blue-800 border-b border-blue-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo / Nombre de la App -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto text-yellow-400" />
                                </Link>
                            </div>

                            <!-- ENLACES DE NAVEGACIÓN PRINCIPAL (ESCRITORIO) -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex text-white font-bold">
                                <NavLink
                                    :href="route('dashboard')"
                                    :class="[
                                        'block text-xl px-3 py-2 rounded font-bold', 
                                        route().current('dashboard') 
                                            ? 'bg-[#EA580C] text-white'  
                                            : 'text-white hover:text-white'
                                    ]"
                                >
                                    Dashboard
                                </NavLink>

                                <NavLink 
                                    :href="route('clientes.index')" 
                                    :class="[
                                        'block text-xl px-3 py-2 rounded font-bold', 
                                        route().current('clientes.*') 
                                            ? 'bg-[#EA580C] text-white'  
                                            : 'text-white hover:text-white'
                                    ]"
                                > 
                                    Clientes
                                </NavLink>

<NavLink 
    :href="route('procesos.index')" 
    :class="[
        'block text-xl px-3 py-2 rounded font-bold', 
        route().current('procesos.*') && !route().current('procesos.cambios.*')
            ? 'bg-[#EA580C] text-white'  
            : 'text-white hover:text-white'
    ]"
>
    Procesos
</NavLink>

<NavLink 
    :href="route('cambios.mostrarTodos')" 
    :class="[
        'block text-xl px-3 py-2 rounded font-bold', 
        route().current('cambios.mostrarTodos') || route().current('procesos.cambios.*')
            ? 'bg-[#EA580C] text-white'  
            : 'text-white hover:text-white'
    ]"
> 
    Cambios
</NavLink>

                            </div>
                        </div>

                        <!-- Configuración de usuario (perfil / logout) -->
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-700 hover:text-blue-300 focus:outline-none focus:bg-blue-600 active:bg-blue-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button" class="text-orange-600 hover:text-orange-800">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Menú de hamburguesa (Responsive) -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-blue-300 hover:text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 focus:text-white transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MENÚ RESPONSIVO (Ajustado para fondo azul) -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden bg-blue-700">
                    <div class="pt-2 pb-3 space-y-1 font-bold">
                        <ResponsiveNavLink 
                            :href="route('dashboard')" 
                            :active="route().current('dashboard')" 
                            active-class="bg-blue-600 border-l-4 border-white text-orange-400"
                            class="text-xl text-white hover:bg-blue-600 hover:text-white"
                        >
                            Dashboard
                        </ResponsiveNavLink>

                        <ResponsiveNavLink 
                            :href="route('clientes.index')" 
                            :active="route().current('clientes.*')" 
                            active-class="bg-blue-600 border-l-4 border-white text-orange-400"
                            class="text-xl text-white hover:bg-blue-600 hover:text-white"
                        >
                            Clientes
                        </ResponsiveNavLink>

                        <ResponsiveNavLink 
                            :href="route('procesos.index')" 
                            :active="route().current('procesos.*')" 
                            active-class="bg-blue-600 border-l-4 border-white text-orange-400"
                            class="text-xl text-white hover:bg-blue-600 hover:text-white"
                        >
                            Procesos
                        </ResponsiveNavLink>

                        <ResponsiveNavLink 
                            :href="route('cambios.mostrarTodos')" 
                            :active="route().current('cambios.mostrarTodos')" 
                            active-class="bg-blue-600 border-l-4 border-white text-orange-400"
                            class="text-xl text-white hover:bg-blue-600 hover:text-white"
                        >
                            Cambios
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-4 pb-1 border-t border-blue-900">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-white">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-blue-200">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')" active-class="bg-blue-600 border-l-4 border-white text-white font-bold">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')" active-class="bg-blue-600 border-l-4 border-white text-white font-bold">
                                API Tokens
                            </ResponsiveNavLink>

                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button" class="text-orange-300 hover:text-orange-100 font-bold">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
