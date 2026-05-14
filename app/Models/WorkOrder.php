<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Equipment;

class WorkOrder extends Model
{
    public const ODM_START = '600601300200';

    protected $fillable = [
        'work_sheet_id', 'odm_number', 'type', 
        'impacto', 'accion_requerida', 
        'installation_id','equipment_id',
        'is_high_risk', 'is_extraplan','comentario'
    ];

    public static function nextOdmNumber(): string
    {
        $last = static::orderByRaw('CAST(odm_number AS UNSIGNED) desc')->value('odm_number');

        if (!$last || !ctype_digit($last)) {
            return self::ODM_START;
        }

        return self::incrementNumericString($last, '1');
    }

    protected static function incrementNumericString(string $a, string $b): string
    {
        if (function_exists('bcadd')) {
            return bcadd($a, $b);
        }

        $carry = 0;
        $result = '';
        $a = strrev($a);
        $b = strrev($b);
        $maxLength = max(strlen($a), strlen($b));

        for ($i = 0; $i < $maxLength; $i++) {
            $digitA = $i < strlen($a) ? intval($a[$i]) : 0;
            $digitB = $i < strlen($b) ? intval($b[$i]) : 0;
            $sum = $digitA + $digitB + $carry;
            $result .= $sum % 10;
            $carry = intdiv($sum, 10);
        }

        if ($carry) {
            $result .= $carry;
        }

        return strrev($result);
    }

    public function workSheet(): BelongsTo
    {
        return $this->belongsTo(WorkSheet::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(Installation::class);
    }

    // Una ODM puede tener múltiples tareas para distintos departamentos
    public function tasks(): HasMany
    {
        return $this->hasMany(OrderTask::class);
    }
}