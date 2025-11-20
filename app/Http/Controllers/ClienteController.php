<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    // Muestra el listado de clientes, manejando búsqueda y ordenamiento desde el frontend (Inertia)
    public function index(Request $request)
    {
        // Obtener parámetros de la URL enviados por Inertia
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'id'); // Valor predeterminado: 'id'
        $sortDirection = $request->input('sort_direction', 'desc'); // Valor predeterminado: 'desc'

        $clientes = Cliente::query()
            // 1. Aplicar Búsqueda (Filtro)
            ->when($search, function ($query, $search) {
                // Buscar en DNI, nombre y correo
                $query->where('dni', 'like', "%{$search}%")
                      ->orWhere('nombre', 'like', "%{$search}%")
                      ->orWhere('correo', 'like', "%{$search}%");
            })
            // 2. Aplicar Ordenamiento
            ->orderBy($sortBy, $sortDirection)
            // 3. Aplicar Paginación
            ->paginate(10)
            ->withQueryString(); // Asegura que los parámetros de búsqueda/ordenamiento persistan en los enlaces de paginación

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            // También enviamos los parámetros de filtro y ordenamiento de vuelta al frontend para que se muestren en los inputs
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ]
        ]);
    }

    // Muestra el formulario para crear
    public function create()
    {
        return Inertia::render('Clientes/Create');
    }

    // Guarda el nuevo cliente
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'dni' => ['required', 'string', 'max:255', 'unique:clientes'],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'max:255', 'unique:clientes'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        Cliente::create($validatedData);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado con éxito.');
    }

    // Muestra el formulario para editar
    public function edit(Cliente $cliente)
    {
        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    // Actualiza el cliente
    public function update(Request $request, Cliente $cliente)
    {
        // Validación con excepción al cliente actual
        $validatedData = $request->validate([
            'dni' => ['required', 'string', 'max:255', 'unique:clientes,dni,' . $cliente->id],
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'max:255', 'unique:clientes,correo,' . $cliente->id],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $cliente->update($validatedData);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado con éxito.');
    }

    // Elimina el cliente
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado con éxito.');
    }
}