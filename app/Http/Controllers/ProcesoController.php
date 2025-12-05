<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Services\TwilioService;

class ProcesoController extends Controller
{
    private const ESTADOS_VALIDOS = ['Pendiente', 'Finalizado', 'Entregado', 'En Revision', 'En Ejecucion'];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $estado = $request->input('estado');
        $editor_id = $request->input('editor_id');
        $perPage = $request->input('per_page', 10);

        $sortBy = $request->input('sort_by', 'fecha_final');
        $sortDirection = $request->input('sort_direction', 'asc');

        $procesos = Proceso::query()
            ->with(['cliente:id,nombre', 'editor:id,name', 'documentos:id,proceso_id,ruta,nombre_original'])

            ->when($search, function ($query, $search) {
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                });
            })

            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })

            ->when($editor_id, function ($query, $editor_id) {
                $query->where('editor_id', $editor_id);
            });

        // Aplicar Ordenamiento Principal (Dinámico)
        $procesos->orderBy($sortBy, $sortDirection);

        // Aplicar Ordenamiento Secundario (Urgencia: fecha_final más cercana)
        if ($sortBy !== 'fecha_final') {
            $procesos->orderBy('fecha_final', 'asc');
        }

        $procesos = $procesos->paginate($perPage)->withQueryString();

        $usuarios = User::all(['id', 'name']);
        $estados = self::ESTADOS_VALIDOS;

        return Inertia::render('Proceso/Index', [
            'procesos' => $procesos,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
                'editor_id' => $editor_id,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
            'usuarios' => $usuarios,
            'estados' => $estados,
        ]);
    }

    public function create()
    {
        $clientes = Cliente::all(['id', 'nombre']);
        $usuarios = User::all(['id', 'name']);

        return Inertia::render('Proceso/Create', [
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'estados' => self::ESTADOS_VALIDOS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'estado' => ['required', 'string', Rule::in(self::ESTADOS_VALIDOS)],
            'fecha_inicio' => ['required', 'date'],
            'fecha_final' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'editor_id' => ['required', 'exists:users,id'],

            'archivos' => ['nullable', 'array', 'max:10'],
            'archivos.*' => ['file', 'mimes:pdf,doc,docx,zip', 'max:10240'],
        ]);

        $proceso = Proceso::create([
            'cliente_id' => $validated['cliente_id'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'estado' => $validated['estado'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_final' => $validated['fecha_final'],
            'editor_id' => $validated['editor_id'],
        ]);

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $file) {
                $filePath = $file->store('procesos/documentos', 'public');

                $proceso->documentos()->create([
                    'ruta' => $filePath,
                    'nombre_original' => $file->getClientOriginalName(),
                ]);
            }
        }

$this->notificarClienteCreacion($proceso, 'HXaae316044970e0e747d702e0dd313ff3');

        return redirect()->route('procesos.index')->with('success', 'Proceso creado exitosamente.');
    }

    public function edit(Proceso $proceso)
    {
        $proceso->load('documentos');

        $clientes = Cliente::all(['id', 'nombre']);
        $usuarios = User::all(['id', 'name']);

        return Inertia::render('Proceso/Edit', [
            'proceso' => $proceso,
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'estados' => self::ESTADOS_VALIDOS,
        ]);
    }

    public function update(Request $request, Proceso $proceso)
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'estado' => ['required', 'string', Rule::in(self::ESTADOS_VALIDOS)],
            'fecha_inicio' => ['required', 'date'],
            'fecha_final' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'editor_id' => ['required', 'exists:users,id'],
            'archivos' => ['nullable', 'array', 'max:10'],
            'archivos.*' => ['file', 'mimes:pdf,doc,docx,zip', 'max:10240'],
        ]);

        $proceso->update([
            'cliente_id' => $validated['cliente_id'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'estado' => $validated['estado'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_final' => $validated['fecha_final'],
            'editor_id' => $validated['editor_id'],
        ]);

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $file) {
                $filePath = $file->store('procesos/documentos', 'public');
                $proceso->documentos()->create([
                    'ruta' => $filePath,
                    'nombre_original' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('procesos.index')->with('success', 'Proceso actualizado correctamente.');
    }

    public function destroy(Proceso $proceso)
    {
        foreach ($proceso->documentos as $documento) {
            Storage::disk('public')->delete($documento->ruta);
            $documento->delete();
        }

        $proceso->delete();

        return redirect()->route('procesos.index')->with('success', 'Proceso eliminado.');
    }

    public function destroyDocumento(Proceso $proceso, $documentoId)
    {
        $documento = $proceso->documentos()->findOrFail($documentoId);

        // Borrar del storage
        Storage::disk('public')->delete($documento->ruta);

        // Borrar de la tabla
        $documento->delete();

        // Retornar con flash
        return redirect()->back()->with('success', 'Documento del proceso eliminado correctamente.');
    }

    public function show(Proceso $proceso)
    {
        $proceso->load([
            'cliente',
            'editor',
            'documentos',
            'cambios.editor',
            'cambios.documentos'
        ]);

        return Inertia::render('Proceso/Show', [
            'proceso' => $proceso,
        ]);
    }

    public function calendario()
    {
        if (request()->wantsJson()) {
            // Traer procesos con relaciones necesarias, excepto documentos
            $procesos = Proceso::with(['cliente:id,nombre', 'editor:id,name'])
                ->select('id', 'cliente_id', 'editor_id', 'tipo', 'estado', 'fecha_inicio', 'fecha_final')
                ->get()
                ->map(function ($proceso) {
                    return [
                        'id' => $proceso->id,
                        'title' => $proceso->tipo,
                        'start' => $proceso->fecha_inicio,
                        'end' => $proceso->fecha_final,
                        'estado' => $proceso->estado,
                        'cliente' => $proceso->cliente,
                        'editor' => $proceso->editor,
                        'tipo' => $proceso->tipo,
                    ];
                });

            return response()->json($procesos);
        }

        return Inertia::render('CalendarioProcesos');
    }


    //FUNCION PARA MANDAR MENSAJE WHATSAPP USANDO TWILIO
private function notificarClienteCreacion(Proceso $proceso, $templateSid = null)
{
    $cliente = $proceso->cliente;

    if (!$cliente || !$cliente->telefono) {
        return;
    }

    $telefono = '+504' . preg_replace('/[^0-9]/', '', $cliente->telefono);

    // Obtener el nombre del editor desde la tabla users
    $editorNombre = optional($proceso->editor)->name ?? 'Sin editor';

    $variables = [
        "1" => $cliente->nombre,
        "2" => $proceso->tipo,
        "3" => $proceso->estado,
        "4" => $proceso->descripcion,
        "5" => $proceso->fecha_inicio,
        "6" => $proceso->fecha_final,
        "7" => $editorNombre,
    ];

    try {
        $twilio = app(TwilioService::class);
        $twilio->enviarTemplate($telefono, $variables, $templateSid);
    } catch (\Exception $e) {
        \Log::error("Error enviando WhatsApp al cliente: " . $e->getMessage());
    }
}}

