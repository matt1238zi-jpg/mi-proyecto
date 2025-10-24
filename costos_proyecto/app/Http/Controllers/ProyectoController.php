<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProyectoController extends Controller
{
    public function nuevo()
    {
        return Inertia::render('Proyecto/Editor', [
            'openNewModal' => true,
            'proyecto'     => null,
        ]);
    }

    public function editor(Proyecto $proyecto)
    {
        return Inertia::render('Proyecto/Editor', [
            'openNewModal' => false,
            'proyecto'     => $proyecto->only([
                'id','codigo','nombre','cliente','ubicacion','moneda_id',
                'responsable_id','fecha_inicio','fecha_fin','estado'
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nombre'       => ['required','string','max:200'],
            'cliente'      => ['nullable','string','max:160'],
            'ubicacion'    => ['nullable','string','max:200'],
            'fecha_inicio' => ['required','date'],
            'fecha_fin'    => ['nullable','date','after_or_equal:fecha_inicio'],
        ]);

        $data['moneda_id']      = 1; // BOB
        $data['responsable_id'] = $user->id;
        $data['estado']         = 'PLANIFICACION';
        $data['codigo']         = $this->makeUniqueCode();

        $p = Proyecto::create($data);

        return response()->json([
            'ok'       => true,
            'id'       => $p->id,
            'codigo'   => $p->codigo,
            'redirect' => url("/proyectos/{$p->id}/presupuesto"),
        ]);
    }
public function index()
{
    return response()->json(
        Proyecto::orderByDesc('id')->get(['id', 'codigo', 'nombre', 'cliente', 'estado'])
    );
}

    private function makeUniqueCode(): string
    {
        $prefix = 'PRJ-'.now()->format('Ym').'-';
        do {
            $code = $prefix . str_pad((string)random_int(1,999), 3, '0', STR_PAD_LEFT);
        } while (Proyecto::where('codigo', $code)->exists());
        return $code;
    }
}
