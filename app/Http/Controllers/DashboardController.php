<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\Cambio;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
public function index()
{
    $user = Auth::user();

    // ¿Es administrador?
    $verTodo = request()->query('all', false); // ?all=1 para ver todos

    // Si es admin y eligió ver todo:
    if ($user->role === 'administrador' && $verTodo) {

        // Procesos de todos
        $procesosAsignados = Proceso::with(['cliente', 'editor'])
            ->orderBy('fecha_final', 'asc')
            ->get();

        // Procesos por vencer
        $procesosPorVencer = Proceso::with(['cliente', 'editor'])
            ->whereDate('fecha_final', '<=', now()->addDays(5))
            ->whereNotIn('estado', ['Finalizado', 'Entregado'])
            ->orderBy('fecha_final', 'asc')
            ->get();

        // Cambios recientes de todos
        $cambios = Cambio::with(['proceso', 'editor'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Conteo por estado (global)
        $estados = ['Pendiente', 'En Revision', 'En Ejecucion', 'Finalizado', 'Entregado'];
        $procesosPorEstado = [];
        foreach ($estados as $estado) {
            $procesosPorEstado[$estado] = Proceso::where('estado', $estado)->count();
        }

        // Procesos por mes (global)
        $procesosPorMes = Proceso::selectRaw("MONTH(fecha_inicio) as mes, COUNT(*) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes');

    } else {
        // VISTA NORMAL (NO ADMIN O ADMIN QUE NO ACTIVÓ 'VER TODO')

        $procesosAsignados = Proceso::with('cliente')
            ->where('editor_id', $user->id)
            ->get();

        $procesosPorVencer = Proceso::with('cliente')
            ->where('editor_id', $user->id)
            ->whereDate('fecha_final', '<=', now()->addDays(5))
            ->whereNotIn('estado', ['Finalizado', 'Entregado'])
            ->orderBy('fecha_final', 'asc')
            ->get();

        $cambios = Cambio::with('proceso')
            ->where('editor_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $estados = ['Pendiente', 'En Revision', 'En Ejecucion', 'Finalizado', 'Entregado'];
        $procesosPorEstado = [];
        foreach ($estados as $estado) {
            $procesosPorEstado[$estado] = $procesosAsignados->where('estado', $estado)->count();
        }

        $procesosPorMes = Proceso::selectRaw("MONTH(fecha_inicio) as mes, COUNT(*) as total")
            ->where('editor_id', $user->id)
            ->groupBy('mes')
            ->pluck('total', 'mes');
    }

    return Inertia::render('Dashboard', [
        'procesosAsignados' => $procesosAsignados,
        'procesosPorVencer' => $procesosPorVencer,
        'cambios' => $cambios,
        'procesosPorMes' => $procesosPorMes,
        'procesosPorEstado' => $procesosPorEstado,
        'user' => $user,
        'verTodo' => $verTodo, // Para el botón en Vue
    ]);
}

}
