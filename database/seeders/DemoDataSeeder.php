<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Discipline;
use App\Models\Equipment;
use App\Models\Installation;
use App\Models\OrderTask;
use App\Models\OrderTaskEvidence;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkSheet;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();

        $departments = Department::all();
        $disciplines = Discipline::all();
        $users = User::all();
        $equipment = Equipment::all();
        $installations = Installation::all();

        if ($departments->isEmpty() || $disciplines->isEmpty() || $users->isEmpty() || $equipment->isEmpty() || $installations->isEmpty()) {
            $this->command->info('No hay datos base suficientes para crear hojas, órdenes y tareas. Verifica que departamentos, disciplinas, usuarios, equipos e instalaciones existan.');
            return;
        }

        Storage::disk('public')->makeDirectory('evidences');

        $startMonth = Carbon::now()->subMonths(5)->startOfMonth();
        $statusOptions = ['PENDIENTE', 'POR REVISION', 'COMPLETADO', 'NO COMPLETADO'];
        $odmTypes = ['CORRECTIVO', 'PREVENTIVO', 'PREDICTIVO', 'DETECTIVO'];
        $actionOptions = [
            'Inspeccionar condición de conexiones eléctricas',
            'Verificar funcionamiento de panel de control',
            'Revisar fuga en sistema hidráulico',
            'Ajustar parámetros de la instalación',
            'Limpiar filtros de ventilación',
            'Registrar niveles de aceite y temperatura',
            'Comprobar aislamiento y puesta a tierra',
            'Evaluar estado de seguridad de la maquinaria',
        ];

        for ($month = 0; $month < 6; $month++) {
            $monthStart = $startMonth->copy()->addMonths($month);
            $monthStartThursday = $monthStart->copy()->modify('thursday this week');
            if ($monthStartThursday->lt($monthStart)) {
                $monthStartThursday = $monthStart->copy()->modify('thursday next week');
            }
            $worksheetCount = 4;

            for ($week = 0; $week < $worksheetCount; $week++) {
                $weekStart = $monthStartThursday->copy()->addWeeks($week);
                $weekEnd = $weekStart->copy()->addDays(6);
                $department = $departments->random();
                $weeklyDisciplines = $disciplines->where('department_id', $department->id);

                if ($weeklyDisciplines->isEmpty()) {
                    $weeklyDisciplines = $disciplines;
                }

                $worksheet = WorkSheet::create([
                    'week_number' => (int) $weekStart->format('W'),
                    'start_date' => $weekStart->format('Y-m-d'),
                    'end_date' => $weekEnd->format('Y-m-d'),
                    'total_odm_scheduled' => 8,
                    'department_id' => $department->id,
                    'codigo' => strtoupper($faker->bothify('WS-??###')),
                    'enviado' => $faker->randomElement(['POR ENVIAR', 'ENVIADO']),
                ]);

                for ($i = 0; $i < 8; $i++) {
                    $workOrder = WorkOrder::create([
                        'work_sheet_id' => $worksheet->id,
                        'odm_number' => WorkOrder::nextOdmNumber(),
                        'type' => $faker->randomElement($odmTypes),
                        'impacto' => $faker->numberBetween(10, 120),
                        'accion_requerida' => $faker->randomElement($actionOptions),
                        'installation_id' => $installations->random()->id,
                        'equipment_id' => $equipment->random()->id,
                        'is_high_risk' => $faker->boolean(15),
                        'is_extraplan' => $faker->boolean(10),
                        'comentario' => $faker->optional(0.6)->sentence(),
                    ]);

                    $taskDate = $faker->dateTimeBetween($weekStart, $weekEnd);
                    $taskStart = Carbon::instance($taskDate)->setTime($faker->numberBetween(6, 14), 0, 0);
                    $taskEnd = (clone $taskStart)->addHours($faker->numberBetween(1, 4));
                    $taskDiscipline = $weeklyDisciplines->random();
                    $userReport = $users->random();

                    $task = OrderTask::create([
                        'work_order_id' => $workOrder->id,
                        'discipline_id' => $taskDiscipline->id,
                        'priority' => $faker->randomElement(['Nivel 1', 'Nivel 2', 'Prioridad alta', 'Actividad critica']),
                        'date' => $taskStart->format('Y-m-d'),
                        'time_start' => $taskStart->format('Y-m-d H:i:s'),
                        'time_end' => $taskEnd->format('Y-m-d H:i:s'),
                        'status' => $faker->randomElement($statusOptions),
                        'observation' => 'COMPLETADO SATISFACTORIAMENTE',
                        'evidence_path' => null,
                        'user_report_id' => $userReport->id,
                    ]);

                    $fileName = 'evidences/dummy-' . Str::random(12) . '.png';
                    $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMBAOeMX0gAAAAASUVORK5CYII=');
                    Storage::disk('public')->put($fileName, $imageData);

                    OrderTaskEvidence::create([
                        'order_task_id' => $task->id,
                        'path' => $fileName,
                    ]);

                    $task->update(['evidence_path' => $fileName]);
                }
            }
        }
    }
}
