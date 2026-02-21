<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AppointmentsChart extends ChartWidget
{
    protected ?string $heading = 'تحليل حالات الحجوزات';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    // 🚀 1. تحديد الفلتر الافتراضي عند فتح الصفحة
    public ?string $filter = 'week';

    // 🚀 2. إنشاء قائمة الفلاتر الزمنية
    protected function getFilters(): ?array
    {
        return [
            'day' => 'اليوم',
            'week' => 'آخر 7 أيام',
            'two_weeks' => 'آخر 14 يوماً',
            'month' => 'آخر 30 يوماً',
        ];
    }

    protected function getData(): array
    {
        // 🚀 3. تحديد عدد الأيام بناءً على الفلتر المختار
        $daysCount = match ($this->filter) {
            'two_weeks' => 14,
            'month' => 30,
            'week' => 7,
            default => 1
        };

        $labels = [];
        $completedData = [];
        $confirmedData = [];
        $pendingData = [];
        $cancelledData = [];

        // حلقة تكرار ديناميكية حسب عدد الأيام
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            // إذا كانت المدة طويلة (شهر مثلاً) نعرض اليوم والتاريخ، وإذا كانت أسبوع نعرض اسم اليوم فقط
            if ($daysCount > 7) {
                $labels[] = $date->translatedFormat('d M'); // مثلاً: 21 فبراير

            } elseif ($daysCount = 1) {
                $labels[] = $date->translatedFormat('d'); // مثلاً: 21 فبراير
            } else {
                $labels[] = $date->translatedFormat('l');   // مثلاً: السبت
            }

            $dailyStats = Appointment::whereDate('appointment_date', $date->toDateString())
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $completedData[] = $dailyStats['Completed'] ?? 0;
            $confirmedData[] = $dailyStats['Confirmed'] ?? 0;
            $pendingData[] = $dailyStats['Pending'] ?? 0;
            $cancelledData[] = $dailyStats['Cancelled'] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'مكتمل (Completed)',
                    'data' => $completedData,
                    'borderColor' => '#10b981',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'مؤكد (Confirmed)',
                    'data' => $confirmedData,
                    'borderColor' => '#3b82f6',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'قيد الانتظار (Pending)',
                    'data' => $pendingData,
                    'borderColor' => '#f59e0b',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'ملغي (Cancelled)',
                    'data' => $cancelledData,
                    'borderColor' => '#ef4444',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return auth()->user()->can('view_revenue_stats');
    }
}
