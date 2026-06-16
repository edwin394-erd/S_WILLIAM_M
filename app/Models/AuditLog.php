<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    private function formatJsonSummary(?array $values): string
    {
        if (empty($values)) {
            return '';
        }

        return collect($values)
            ->map(function ($value, $key) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return "{$key}: {$value}";
            })
            ->values()
            ->implode(' | ');
    }
}
