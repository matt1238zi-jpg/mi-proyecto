<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\DB;

class RecursoImportController extends Controller
{
    // Mapea codigos de unidad -> id (puedes sacarlo de la BD si lo prefieres)
    protected function unidadIdPorCodigo(): array
    {
        // Ejemplo simple: si tienes tabla unidad, usa pluck:
        // return \DB::table('unidad')->pluck('id','codigo')->map(fn($id)=> (int)$id)->all();
        return [
            'm'  => 1,
            'm2' => 2,
            'm3' => 3,
            'kg' => 4,
            'ud' => 5,
        ];
    }

    protected function monedaId(string $codigo = null): int
    {
        // BOB=1, USD=2 (según tu tabla "moneda")
        if (!$codigo) return 1;
        $codigo = strtoupper(trim($codigo));
        return match($codigo) {
            'USD' => 2,
            default => 1, // BOB
        };
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:xlsx,xls'],
        ]);

        $errors = [];   // [{row,col,msg}]
        $warnings = [];
        $imported = 0;

        // Leemos todo el Excel a arrays
        try {
            $sheets = Excel::toArray([], $request->file('file'));
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo leer el Excel: '.$e->getMessage(),
            ], 422);
        }

        if (empty($sheets) || empty($sheets[0])) {
            return response()->json([
                'ok' => false,
                'message' => 'El archivo no contiene filas.',
            ], 422);
        }

        $rows = $sheets[0]; // primera hoja
        if (count($rows) < 2) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay datos (¿falta la cabecera o filas?).',
            ], 422);
        }

        // Cabecera esperada (insensible a may/minus)
        // codigo, nombre, tipo, unidad, precio_unitario, moneda, vigente_desde, vigente_hasta, fuente
        $header = array_map(fn($h)=> strtolower(trim($h ?? '')), $rows[0]);

        $required = ['codigo','nombre','tipo','unidad','precio_unitario','moneda','vigente_desde'];
        foreach ($required as $col) {
            if (!in_array($col, $header, true)) {
                return response()->json([
                    'ok' => false,
                    'message' => "Falta la columna requerida '{$col}' en la cabecera.",
                ], 422);
            }
        }

        // Índices de columna
        $idx = fn($name) => array_search($name, $header, true);

        $iCodigo = $idx('codigo');
        $iNombre = $idx('nombre');
        $iTipo   = $idx('tipo');
        $iUnidad = $idx('unidad');
        $iPrecio = $idx('precio_unitario');
        $iMoneda = $idx('moneda');
        $iDesde  = $idx('vigente_desde');
        $iHasta  = $idx('vigente_hasta');
        $iFuente = $idx('fuente');

        $uniMap = $this->unidadIdPorCodigo();

        // Para detectar duplicados dentro del mismo archivo:
        $codsArchivo = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 0) continue; // cabecera

                // Normaliza valores
                $codigo = trim((string)($row[$iCodigo] ?? ''));
                $nombre = trim((string)($row[$iNombre] ?? ''));
                $tipo   = strtoupper(trim((string)($row[$iTipo] ?? '')));
                $unidad = strtolower(trim((string)($row[$iUnidad] ?? '')));
                $precio = $row[$iPrecio] ?? null;
                $moneda = trim((string)($row[$iMoneda] ?? ''));
                $desde  = $row[$iDesde] ?? null;
                $hasta  = $iHasta !== false ? ($row[$iHasta] ?? null) : null;
                $fuente = $iFuente !== false ? trim((string)($row[$iFuente] ?? '')) : null;

                // Salta filas en blanco (según código y nombre)
                if ($codigo === '' && $nombre === '') continue;

                // Validaciones:
                if ($codigo === '') $errors[] = ['row'=>$rowIndex+1,'col'=>'codigo','msg'=>'Código requerido'];
                if ($nombre === '') $errors[] = ['row'=>$rowIndex+1,'col'=>'nombre','msg'=>'Nombre requerido'];

                if (!in_array($tipo, ['MATERIAL','OBRERO','EQUIPO'], true)) {
                    $errors[] = ['row'=>$rowIndex+1,'col'=>'tipo','msg'=>"Tipo inválido ($tipo)"];
                }

                $unidadId = $uniMap[$unidad] ?? null;
                if (!$unidadId) {
                    $errors[] = ['row'=>$rowIndex+1,'col'=>'unidad','msg'=>"Unidad desconocida ($unidad)"];
                }

                if (!is_numeric($precio)) {
                    $errors[] = ['row'=>$rowIndex+1,'col'=>'precio_unitario','msg'=>'Precio unitario debe ser numérico'];
                }

                // Fecha excel puede venir como número serial
                $desdeStr = $this->asDateString($desde);
                if (!$desdeStr) {
                    $errors[] = ['row'=>$rowIndex+1,'col'=>'vigente_desde','msg'=>'Fecha inválida (YYYY-MM-DD)'];
                }
                $hastaStr = null;
                if ($hasta !== null && $hasta !== '') {
                    $hastaStr = $this->asDateString($hasta);
                    if (!$hastaStr) {
                        $errors[] = ['row'=>$rowIndex+1,'col'=>'vigente_hasta','msg'=>'Fecha inválida (YYYY-MM-DD)'];
                    }
                }

                // Duplicados dentro del archivo
                if ($codigo !== '') {
                    if (isset($codsArchivo[$codigo])) {
                        $warnings[] = "Código duplicado en archivo: {$codigo} (primera aparición en fila ".$codsArchivo[$codigo].")";
                    } else {
                        $codsArchivo[$codigo] = $rowIndex+1;
                    }
                }

                if (!empty($errors) && end($errors)['row'] === $rowIndex+1) {
                    // Esta fila tiene errores; no intentamos insertarla
                    continue;
                }

                // Inserta/actualiza recurso y su precio
                $monedaId = $this->monedaId($moneda ?: 'BOB');

                // upsert a recurso por código
                $recurso = DB::table('recurso')->where('codigo', $codigo)->first();
                if ($recurso) {
                    DB::table('recurso')->where('id', $recurso->id)->update([
                        'nombre'    => $nombre,
                        'tipo'      => $tipo,
                        'unidad_id' => $unidadId,
                        'actualizado_en' => now(),
                    ]);
                    $recursoId = $recurso->id;
                } else {
                    $recursoId = DB::table('recurso')->insertGetId([
                        'codigo'    => $codigo,
                        'nombre'    => $nombre,
                        'tipo'      => $tipo,
                        'unidad_id' => $unidadId,
                        'activo'    => 1,
                        'creado_en' => now(),
                        'actualizado_en' => now(),
                    ]);
                }

                // Insertar registro de precio (no controlamos solapamiento aquí; puedes añadirlo si quieres)
                DB::table('recurso_precio')->insert([
                    'recurso_id'     => $recursoId,
                    'moneda_id'      => $monedaId,
                    'precio_unitario'=> (float)$precio,
                    'vigente_desde'  => $desdeStr,
                    'vigente_hasta'  => $hastaStr,
                    'fuente'         => $fuente,
                ]);

                $imported++;
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Error en importación: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok' => $imported > 0,
            'imported' => $imported,
            'warnings' => array_values(array_unique($warnings)),
            'errors'   => $errors,
        ]);
    }

    private function asDateString($cell): ?string
    {
        if ($cell === null || $cell === '') return null;

        // Si viene como número Excel
        if (is_numeric($cell) && (int)$cell > 25569) {
            try {
                $ts = ExcelDate::excelToTimestamp($cell);
                return date('Y-m-d', $ts);
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Si viene como string YYYY-MM-DD (o similar aceptado por strtotime)
        $d = date('Y-m-d', strtotime((string)$cell));
        return $d !== '1970-01-01' ? $d : null;
    }
}
