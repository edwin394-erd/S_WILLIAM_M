<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\Discipline;
use App\Models\Equipment;
use App\Models\Installation;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkSheet;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'meta',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta' => 'array',
    ];

    public const RESOURCE_MAP = [
        'App\\Models\\User' => 'Usuario',
        'App\\Models\\Department' => 'Departamento',
        'App\\Models\\Discipline' => 'Disciplina',
        'App\\Models\\Installation' => 'Instalación',
        'App\\Models\\Equipment' => 'Equipo',
        'App\\Models\\WorkSheet' => 'Sábana',
        'App\\Models\\WorkOrder' => 'Orden de Trabajo',
        'App\\Models\\OrderTask' => 'Tarea',
    ];

    protected $appends = [
        'old_values_text',
        'new_values_text',
        'resource_name',
        'action_group',
    ];

    public const ACTION_GROUPS = [
        'crear' => 'Crear',
        'editar' => 'Editar',
        'eliminar' => 'Eliminar',
        'reportar' => 'Reportar',
        'aprobar' => 'Aprobar',
        'rechazar' => 'Rechazar',
        'acceso' => 'Acceso',
        'otro' => 'Otro',
    ];

    public const ACTION_GROUP_PATTERNS = [
        'crear' => ['crear', 'creado', 'creada', 'crea', 'registro', 'registered', 'create'],
        'editar' => ['actualiz', 'editar', 'edit', 'modific', 'update', 'modificado', 'modificada'],
        'eliminar' => ['eliminar', 'eliminado', 'eliminada', 'delete', 'borrar', 'borrado', 'borrada', 'remover', 'remove'],
        'reportar' => ['reportar', 'reporte', 'reportado', 'reportada', 'report', 'denuncia', 'informar', 'informe'],
        'aprobar' => ['aprobar', 'aprobado', 'aprobada', 'approve', 'autorizar', 'autorizado'],
        'rechazar' => ['rechazar', 'rechazado', 'rechazada', 'reject'],
        'acceso' => ['login', 'inicio de sesión', 'ingreso', 'acceso', 'sesión', 'entrada', 'entrar', 'autenticado'],
        'otro' => [],
    ];

    private static array $fieldLabels = [
        'user_id' => 'Usuario',
        'subject_type' => 'Recurso',
        'subject_id' => 'ID Recurso',
        'work_sheet_id' => 'Sábana',
        'department_id' => 'Departamento',
        'discipline_id' => 'Disciplina',
        'installation_id' => 'Instalación',
        'equipment_id' => 'Equipo',
        'work_order_id' => 'Orden de Trabajo',
        'role' => 'Rol',
        'name' => 'Nombre',
        'email' => 'Email',
        'impact' => 'Impacto',
        'accion_requerida' => 'Acción requerida',
        'priority' => 'Prioridad',
        'date' => 'Fecha',
        'time_start' => 'Hora inicio',
        'time_end' => 'Hora fin',
        'is_high_risk' => 'Alto riesgo',
        'is_extraplan' => 'Extraplan',
        'grupo_telegram_id' => 'ID Telegram',
        'week_number' => 'Número de semana',
        'start_date' => 'Fecha inicio',
        'end_date' => 'Fecha fin',
        'codigo' => 'Código',
        'observacion' => 'Observación',
        'status' => 'Estado',
        'disciplines' => 'Disciplinas',
        'old_values' => 'Antes',
        'new_values' => 'Después',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getOldValuesTextAttribute(): string
    {
        return $this->formatJsonSummary($this->old_values);
    }

    public function getNewValuesTextAttribute(): string
    {
        return $this->formatJsonSummary($this->new_values);
    }

    private static function getFieldLabel(string $field): string
    {
        return self::$fieldLabels[$field] ?? Str::title(str_replace(['_', 'id'], [' ', ''], $field));
    }

    private function formatJsonSummary(?array $values): string
    {
        if (empty($values)) {
            return '';
        }

        return collect($values)
            ->map(function ($value, $key) {
                $label = self::getFieldLabel($key);
                $value = $this->resolveReadableValue($key, $value);

                if (is_array($value)) {
                    $value = collect($value)->map(fn ($item) => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $item)->implode(', ');
                }

                return "{$label}: {$value}";
            })
            ->values()
            ->implode(' | ');
    }

    private function resolveReadableValue(string $key, $value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value)) {
            if ($key === 'disciplines') {
                return collect($value)->implode(', ');
            }

            return collect($value)->map(function ($item) use ($key) {
                return $this->resolveReadableValue($key, $item);
            })->all();
        }

        if (in_array($key, ['department_id', 'installation_id', 'equipment_id', 'work_sheet_id', 'work_order_id', 'discipline_id', 'user_id'], true)) {
            return $this->resolveRelationName($key, $value);
        }

        return $value;
    }

    private function resolveRelationName(string $key, $value)
    {
        static $cache = [];

        $cacheKey = "{$key}:{$value}";
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $resolved = match ($key) {
            'department_id' => Department::find($value)?->name,
            'discipline_id' => Discipline::find($value)?->name,
            'installation_id' => Installation::find($value)?->name,
            'equipment_id' => Equipment::find($value)?->name,
            'work_sheet_id' => WorkSheet::find($value)?->week_label,
            'work_order_id' => WorkOrder::find($value)?->odm_number,
            'user_id' => User::find($value)?->name,
            default => null,
        };

        $cache[$cacheKey] = $resolved ?? $value;

        return $cache[$cacheKey];
    }

    public function getResourceNameAttribute(): string
    {
        if (! $this->subject_type) {
            return 'Sistema';
        }

        return self::RESOURCE_MAP[$this->subject_type] ?? class_basename($this->subject_type);
    }

    public function getActionGroupAttribute(): string
    {
        $action = strtolower($this->action ?? '');

        foreach (self::ACTION_GROUP_PATTERNS as $group => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($action, $pattern)) {
                    return $group;
                }
            }
        }

        return 'otro';
    }
}
