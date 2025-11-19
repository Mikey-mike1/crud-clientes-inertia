<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    // Muestra el listado de clientes
    public function index()
    {
        $clientes = Cliente::orderBy('id', 'desc')->paginate(10);
        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
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