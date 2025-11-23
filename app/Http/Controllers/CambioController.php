<?php

namespace App\Http\Controllers;

use App\Models\Cambio;
use App\Models\Proceso;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CambioController extends Controller
{
    public function index(Proceso $proceso, Request $request)
    {
        $search = $request->input('search');
        $estado = $request->input('estado');

        $cambios = Cambio::query()
            ->where('proceso_id', $proceso->id)
            ->with(['editor:id,name', 'documentos:id,cambio_id,ruta,nombre_original'])
            ->when($search, fn($q) => $q->where('titulo', 'like', "%{$search}%"))
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->orderBy('fecha', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Cambios/Index', [
            'cambios' => $cambios,
            'proceso' => $proceso,
            'estados' => Cambio::ESTADOS,
        ]);
    }

    public function create(Proceso $proceso)
    {
        $usuarios = User::all(['id', 'name']);

        return Inertia::render('Cambios/Create', [
            'proceso' => $proceso,
            'usuarios' => $usuarios,
            'estados' => Cambio::ESTADOS,
        ]);
    }

    public function store(Request $request, Proceso $proceso)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', Rule::in(Cambio::ESTADOS)],
            'fecha' => ['required', 'date'],
            'editor_id' => ['required', 'exists:users,id'],
            'archivos' => ['nullable', 'array', 'max:10'],
            'archivos.*' => ['file', 'mimes:pdf,doc,docx,zip', 'max:10240'],
        ]);

        $cambio = $proceso->cambios()->create($validated);

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $file) {
                $path = $file->store('cambios/documentos', 'public');
                $cambio->documentos()->create([
                    'ruta' => $path,
                    'nombre_original' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('procesos.cambios.index', $proceso)
            ->with('success', 'Cambio creado correctamente.');
    }

    public function edit(Proceso $proceso, Cambio $cambio)
    {
        $cambio->load('documentos');
        $usuarios = User::all(['id', 'name']);

        return Inertia::render('Cambios/Edit', [
            'proceso' => $proceso,
            'cambio' => $cambio,
            'usuarios' => $usuarios,
            'estados' => Cambio::ESTADOS,
        ]);
    }

public function update(Request $request, Proceso $proceso, Cambio $cambio)
{
    $validated = $request->validate([
        'titulo' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'estado' => ['required', Rule::in(Cambio::ESTADOS)],
        'archivos' => 'nullable|array|max:10',
        'archivos.*' => 'file|mimes:pdf,doc,docx,zip|max:10240',
    ]);

    $cambio->update([
        'titulo' => $validated['titulo'],
        'descripcion' => $validated['descripcion'] ?? '',
        'estado' => $validated['estado'],
    ]);

    if (!empty($validated['archivos'])) {
        foreach ($validated['archivos'] as $file) {
            $cambio->documentos()->create([
                'nombre_original' => $file->getClientOriginalName(),
                'ruta' => $file->store('cambios', 'public'),
            ]);
        }
    }

    // Retornamos los datos actualizados para que Inertia refresque
    $cambio->load('documentos');

    return back()->with('success', 'Cambio actualizado correctamente.')
                 ->with('cambio', $cambio);
}


    public function destroy(Proceso $proceso, Cambio $cambio)
    {
        foreach ($cambio->documentos as $doc) {
            Storage::disk('public')->delete($doc->ruta);
            $doc->delete();
        }

        $cambio->delete();

        return redirect()->route('procesos.cambios.index', $proceso)
            ->with('success', 'Cambio eliminado.');
    }

public function mostrarCambios(Request $request)
{
    $search = $request->input('search');
    $estado = $request->input('estado');
    $editor_id = $request->input('editor_id');
    $per_page = $request->input('per_page', 10);
    $sort_by = $request->input('sort_by', 'fecha');
    $sort_direction = $request->input('sort_direction', 'desc');

    $cambios = Cambio::query()
        ->with(['editor:id,name', 'documentos:id,cambio_id,ruta,nombre_original', 'proceso:id,cliente_id,tipo,estado', 'proceso.cliente:id,nombre'])
        ->when($search, fn($q) => $q->where('titulo', 'like', "%{$search}%"))
        ->when($estado, fn($q) => $q->where('estado', $estado))
        ->when($editor_id, fn($q) => $q->where('editor_id', $editor_id))
        ->orderBy($sort_by, $sort_direction)
        ->paginate($per_page)
        ->withQueryString();

    $usuarios = User::all(['id', 'name']);

    return Inertia::render('Cambios/MostrarCambios', [ // <-- mismo componente que index
        'cambios' => $cambios,
        'usuarios' => $usuarios,
        'estados' => Cambio::ESTADOS,
        'filters' => $request->only(['search','estado','editor_id','per_page','sort_by','sort_direction']),
        'proceso' => null, // como no viene un proceso específico
    ]);
}

public function destroyDocumento(Proceso $proceso, Cambio $cambio, $documentoId)
{
    $documento = $cambio->documentos()->findOrFail($documentoId);

    Storage::disk('public')->delete($documento->ruta);
    $documento->delete();

    return redirect()->back()->with('success', 'Documento eliminado.');
}



}

