<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Capitulo;   // crea modelos si aún no
use App\Models\Partida;
use App\Models\Apu;
use App\Models\ApuDetalle;
use App\Models\Recurso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresupuestoController extends Controller
{
    public function editor(Proyecto $proyecto)
    {
        return Inertia::render('Proyecto/Presupuesto', [
            'proyecto' => $proyecto->only(['id','codigo','nombre']),
        ]);
    }

    // Árbol izquierda: capítulos y partidas
    public function estructura(Proyecto $proyecto)
    {
        $capitulos = Capitulo::where('proyecto_id', $proyecto->id)
            ->orderBy('orden')->get(['id','nombre','codigo','orden']);

        $partidas = Partida::where('proyecto_id', $proyecto->id)
            ->orderBy('capitulo_id')->orderBy('orden')
            ->get(['id','capitulo_id','codigo','descripcion','unidad_id','cantidad','precio_unitario','subtotal']);

        return response()->json(compact('capitulos','partidas'));
    }

    // Base de datos de recursos (panel derecho)
    public function recursos(Request $req, Proyecto $proyecto)
    {
        $q    = $req->get('q');
        $tipo = $req->get('tipo'); // MATERIAL|OBRERO|EQUIPO|null

        $rec = Recurso::query()
            ->when($q, fn($w)=>$w->where(function($x) use ($q){
                $x->where('codigo','like',"%$q%")
                  ->orWhere('nombre','like',"%$q%");
            }))
            ->when($tipo, fn($w)=>$w->where('tipo', $tipo))
            ->orderBy('nombre')
            ->limit(100)
            ->get(['id','codigo','nombre','unidad_id','precio_unitario','tipo']);

        return response()->json($rec);
    }

    // APU de una partida
    public function apuShow(Partida $partida)
    {
        $apu = Apu::where('partida_id', $partida->id)->first();
        if (!$apu) {
            return response()->json([
                'apu' => null, 'lineas' => [],
                'resumen' => ['MATERIAL'=>0,'OBRERO'=>0,'EQUIPO'=>0,'TOTAL'=>0]
            ]);
        }
        $lineas = ApuDetalle::where('apu_id', $apu->id)
            ->with('recurso:id,codigo,nombre,unidad_id,precio_unitario,tipo')
            ->orderBy('id')->get(['id','apu_id','recurso_id','cantidad','precio_unitario','parcial']);

        $resumen = [
            'MATERIAL' => (float)$lineas->where('recurso.tipo','MATERIAL')->sum('parcial'),
            'OBRERO'   => (float)$lineas->where('recurso.tipo','OBRERO')->sum('parcial'),
            'EQUIPO'   => (float)$lineas->where('recurso.tipo','EQUIPO')->sum('parcial'),
        ];
        $resumen['TOTAL'] = array_sum($resumen);

        return response()->json(compact('apu','lineas','resumen'));
    }

    // Crear o actualizar APU (rendimiento, %)
    public function apuUpsert(Request $req, Partida $partida)
    {
        $data = $req->validate([
            'rendimiento'  => ['required','numeric','min:0.000001'],
            'indirectos_pct'=>['nullable','numeric'],
            'utilidad_pct'  =>['nullable','numeric'],
            'iva_pct'       =>['nullable','numeric'],
        ]);

        $apu = Apu::firstOrNew(['partida_id'=>$partida->id]);
        $apu->fill($data);
        $apu->version = $apu->version ? $apu->version+1 : 1;
        $apu->activo = 1;
        $apu->save();

        return response()->json(['ok'=>true,'apu'=>$apu]);
    }

    // Agregar línea de insumo a APU
    public function lineaAdd(Request $req, Apu $apu)
    {
        $data = $req->validate([
            'recurso_id'     => ['required','exists:recurso,id'],
            'cantidad'       => ['required','numeric','min:0'],
            'precio_unitario'=> ['nullable','numeric','min:0'],
        ]);

        $recurso = Recurso::find($data['recurso_id']);
        $precio = $data['precio_unitario'] ?? $recurso->precio_unitario;
        $parcial= round($data['cantidad'] * $precio, 6);

        $linea = ApuDetalle::create([
            'apu_id'         => $apu->id,
            'recurso_id'     => $recurso->id,
            'cantidad'       => $data['cantidad'],
            'precio_unitario'=> $precio,
            'parcial'        => $parcial,
        ]);

        return response()->json(['ok'=>true,'linea'=>$linea]);
    }

    public function lineaUpdate(Request $req, ApuDetalle $linea)
    {
        $data = $req->validate([
            'cantidad'        => ['nullable','numeric','min:0'],
            'precio_unitario' => ['nullable','numeric','min:0'],
        ]);
        $linea->fill($data);
        $linea->parcial = round(($linea->cantidad ?? 0) * ($linea->precio_unitario ?? 0), 6);
        $linea->save();

        return response()->json(['ok'=>true,'linea'=>$linea]);
    }

    public function lineaDelete(ApuDetalle $linea)
    {
        $linea->delete();
        return response()->json(['ok'=>true]);
    }
}
