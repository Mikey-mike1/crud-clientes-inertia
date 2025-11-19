<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\Cliente; // Importar modelo Cliente
use App\Models\User;    // Importar modelo User (para editores)
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Importar Storage para manejar archivos

class ProcesoController extends Controller
{
    // Constante con los estados válidos para evitar errores tipográficos y facilitar cambios
    private const ESTADOS_VALIDOS = ['Pendiente', 'Finalizado', 'Entregado', 'En Revision'];

    /**
     * Muestra el listado de procesos.
     */
    public function index()
    {
        // Usamos 'with' para evitar el problema N+1 al cargar relaciones
        $procesos = Proceso::with(['cliente', 'editor'])
            ->orderBy('fecha_inicio', 'desc') // Ordenar por fecha más reciente
            ->get();

        return Inertia::render('Proceso/Index', [
            'procesos' => $procesos
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo proceso.
     */
    public function create()
    {
        // Obtenemos listas simples para los selects del formulario
        $clientes = Cliente::all(['id', 'nombre']);
        $usuarios = User::all(['id', 'name']);
        
        return Inertia::render('Proceso/Create', [
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'estados' => self::ESTADOS_VALIDOS, // Pasamos los estados al frontend
        ]);
    }

    /**
     * Almacena un nuevo proceso en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validación
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'estado' => ['required', 'string', Rule::in(self::ESTADOS_VALIDOS)],
            'fecha_inicio' => ['required', 'date'],
            'fecha_final' => ['nullable', 'date', 'after_or_equal:fecha_inicio'], // Fecha final debe ser posterior a inicio
            'editor_id' => ['required', 'exists:users,id'],
            // Validación de archivo: opcional, tipos pdf/doc/docx, máx 10MB (10240 KB)
            'archivo' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'], 
        ]);

        $filePath = null;

        // 2. Manejo de subida de archivo
        if ($request->hasFile('archivo')) {
            // Guarda en 'storage/app/public/procesos' y devuelve la ruta relativa
            $filePath = $request->file('archivo')->store('procesos', 'public');
        }

        // 3. Creación del registro
        Proceso::create([
            'cliente_id' => $validated['cliente_id'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'estado' => $validated['estado'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_final' => $validated['fecha_final'],
            'editor_id' => $validated['editor_id'],
            'archivos' => $filePath, // Guardamos la ruta del archivo subido
        ]);

        // 4. Redirección
        return redirect()->route('procesos.index')->with('success', 'Proceso creado exitosamente.');
    }

    public function edit(Proceso $proceso)
    {
        // Cargamos las listas necesarias para los selectores
        $clientes = Cliente::all(['id', 'nombre']);
        $usuarios = User::all(['id', 'name']);

        return Inertia::render('Proceso/Edit', [
            'proceso' => $proceso, // Pasamos el proceso a editar
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'estados' => self::ESTADOS_VALIDOS,
        ]);
    }

    /**
     * Actualiza el recurso especificado en la base de datos.
     */
    public function update(Request $request, Proceso $proceso)
    {
        // Validación (similar al store, pero el archivo es opcional)
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'estado' => ['required', 'string', Rule::in(self::ESTADOS_VALIDOS)],
            'fecha_inicio' => ['required', 'date'],
            'fecha_final' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'editor_id' => ['required', 'exists:users,id'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // Opcional al editar
        ]);

        // Manejo del archivo: Si suben uno nuevo, borramos el anterior
        if ($request->hasFile('archivo')) {
            // 1. Borrar archivo viejo si existe
            if ($proceso->archivos) {
                Storage::disk('public')->delete($proceso->archivos);
            }
            // 2. Subir nuevo
            $filePath = $request->file('archivo')->store('procesos', 'public');
            $proceso->archivos = $filePath;
        }

        // Actualización de datos
        $proceso->update([
            'cliente_id' => $validated['cliente_id'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'estado' => $validated['estado'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_final' => $validated['fecha_final'],
            'editor_id' => $validated['editor_id'],
            // 'archivos' ya se actualizó arriba si era necesario
        ]);

        return redirect()->route('procesos.index')->with('success', 'Proceso actualizado correctamente.');
    }

    /**
     * Elimina el recurso especificado.
     */
    public function destroy(Proceso $proceso)
    {
        // Borrar el archivo asociado si existe
        if ($proceso->archivos) {
            Storage::disk('public')->delete($proceso->archivos);
        }

        $proceso->delete();

        return redirect()->route('procesos.index')->with('success', 'Proceso eliminado.');
    }
}