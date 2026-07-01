<?php

namespace App\Services\Analytics;

use App\Models\Appointment;
use App\Support\DatabaseHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PredictiveService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly NLAService $nla,
    ) {}

    public function getCrowdingRisk(int $clinicId, ?string $from = null, ?string $to = null): array
    {
        $now = Carbon::now();
        [$fromDate, $toDate] = $this->resolveDateRange($from, $to, $now->copy()->subWeeks(4), $now);
        $totalDays = $fromDate->diffInDays($toDate) ?: 1;

        $stats = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_time', [$fromDate, $toDate])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        $totalAppointments = (int) ($stats->total ?? 0);
        $completedAppointments = (int) ($stats->completed ?? 0);
        $dailyAvg = round($totalAppointments / $totalDays, 1);
        $threshold = (int) $this->settings->get('crowding_threshold', 20);

        $weeklyTrend = $this->getWeeklyTrend($clinicId);

        return [
            'from'              => $fromDate->toDateString(),
            'to'                => $toDate->toDateString(),
            'daily_avg'         => $dailyAvg,
            'threshold'         => $threshold,
            'level'             => $dailyAvg > $threshold ? 'High' : 'Normal',
            'total_appointments' => $totalAppointments,
            'completion_rate'   => $totalAppointments > 0
                ? round(($completedAppointments / $totalAppointments) * 100, 2) . '%'
                : '0%',
            'weekly_trend'      => $weeklyTrend,
            'trend_direction'   => $this->getTrendDirection($weeklyTrend),
        ];
    }

    public function getNoShowPrediction(int $clinicId, ?string $from = null, ?string $to = null): array
    {
        $now = Carbon::now();
        [$fromDate, $toDate] = $this->resolveDateRange($from, $to, $now->copy()->subMonths(3), $now);

        $stats = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_time', [$fromDate, $toDate])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_shows,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->first();

        $total = (int) ($stats->total ?? 0);
        $noShows = (int) ($stats->no_shows ?? 0);
        $cancelled = (int) ($stats->cancelled ?? 0);

        $noShowRate = $total > 0 ? round(($noShows / $total) * 100, 2) : 0;
        $cancellationRate = $total > 0 ? round(($cancelled / $total) * 100, 2) : 0;

        $predictedNoShows = 0;
        $upcomingTotal = Appointment::where('clinic_id', $clinicId)
            ->where('start_time', '>=', $now)
            ->where('start_time', '<=', $now->copy()->addWeek())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        if ($upcomingTotal > 0 && $noShowRate > 0) {
            $predictedNoShows = (int) round(($noShowRate / 100) * $upcomingTotal);
        }

        return [
            'from'                 => $fromDate->toDateString(),
            'to'                   => $toDate->toDateString(),
            'historical_no_show_rate' => $noShowRate . '%',
            'historical_cancellation_rate' => $cancellationRate . '%',
            'total_appointments'   => $total,
            'total_no_shows'       => $noShows,
            'total_cancelled'      => $cancelled,
            'upcoming_this_week'   => $upcomingTotal,
            'predicted_no_shows'   => $predictedNoShows,
            'risk_level'           => $noShowRate > 20 ? 'High' : ($noShowRate > 10 ? 'Medium' : 'Low'),
        ];
    }

    public function getBusyHours(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): array
    {
        $now = Carbon::now();
        [$fromDate, $toDate] = $this->resolveDateRange($from, $to, $now->copy()->subMonths(1), $now);
        $period = in_array($period, ['year', 'month', 'day', 'total']) ? $period : 'month';

        $periodExpr = match ($period) {
            'year'  => DatabaseHelper::dateFormat('start_time', '%Y'),
            'month' => DatabaseHelper::dateFormat('start_time', '%Y-%m'),
            'day'   => DatabaseHelper::dateFormat('start_time', '%Y-%m-%d'),
            'total' => "'total'",
        };

        $hourExpr = DatabaseHelper::hour('start_time');

        $rows = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_time', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->selectRaw("{$periodExpr} as period_key, {$hourExpr} as hour, COUNT(*) as count")
            ->groupBy(DB::raw("{$periodExpr}, {$hourExpr}"))
            ->orderBy('period_key')
            ->orderBy('count', 'desc')
            ->get();

        $grouped = $rows->groupBy('period_key');

        $byPeriod = $grouped->map(function ($hours, $key) use ($period, $fromDate, $toDate) {
            $top10 = $hours->take(10);
            $peak = $top10->first();
            [$pFrom, $pTo] = $this->periodDateRange($period, $key, $fromDate, $toDate);
            return [
                'label'               => $key,
                'from'                => $pFrom->toDateString(),
                'to'                  => $pTo->toDateString(),
                'hourly_distribution' => $top10->map(fn($h) => [
                    'hour'  => $h->hour . ':00',
                    'count' => (int) $h->count,
                ])->values(),
                'peak_hour'           => $peak ? $peak->hour . ':00' : null,
                'peak_count'          => $peak ? (int) $peak->count : 0,
            ];
        })->values();

        return [
            'period'    => $period,
            'from'      => $fromDate->toDateString(),
            'to'        => $toDate->toDateString(),
            'by_period' => $byPeriod,
        ];
    }

    public function getUtilizationForecast(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): array
    {
        $now = Carbon::now();
        [$fromDate, $toDate] = $this->resolveDateRange($from, $to, $now->copy()->subWeeks(6), $now);

        $weekExpr = DatabaseHelper::yearWeek('start_time');
        $weekStats = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('start_time', [$fromDate, $toDate])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("{$weekExpr} as week, COUNT(*) as count")
            ->groupBy(DB::raw($weekExpr))
            ->orderBy('week')
            ->get()
            ->pluck('count');

        $weeks = $weekStats->values();

        $last3 = $weeks->take(-3);
        $prior3 = $weeks->slice(-6, 3);

        $recentAvg = $last3->avg();
        $priorAvg = $prior3->avg();

        $trend = $priorAvg > 0
            ? round((($recentAvg - $priorAvg) / $priorAvg) * 100, 1)
            : 0;

        $forecastNextWeek = $recentAvg > 0
            ? (int) round(max(0, $recentAvg * (1 + ($trend / 100))))
            : 0;

        return [
            'period'             => $period,
            'from'               => $fromDate->toDateString(),
            'to'                 => $toDate->toDateString(),
            'prior_3_weeks_avg'  => round($priorAvg, 1),
            'recent_3_weeks_avg' => round($recentAvg, 1),
            'trend_percentage'   => ($trend >= 0 ? '+' : '') . $trend . '%',
            'forecast_next_week' => $forecastNextWeek,
            'direction'          => $trend > 5 ? 'increasing' : ($trend < -5 ? 'decreasing' : 'stable'),
        ];
    }

    public function getAiInsight(int $clinicId, string $period = 'month', ?string $from = null, ?string $to = null): string
    {
        if (!$this->nla) {
            return 'AI service not configured.';
        }

        $crowding = $this->getCrowdingRisk($clinicId, $from, $to);
        $noShow = $this->getNoShowPrediction($clinicId, $from, $to);
        $busy = $this->getBusyHours($clinicId, $period, $from, $to);

        $context = json_encode([
            'crowding'   => $crowding,
            'no_show'    => $noShow,
            'busy_hours' => $busy,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $question = 'بناءً على بيانات التحليل المتوقعة، ما هي أهم التوصيات لتحسين أداء العيادة؟ قدم تحليلاً باللغة العربية أريد الجواب باللغة en و ar.';

        return $this->nla->askAnalytics($question, $context);
    }

    private function resolveDateRange(?string $from, ?string $to, Carbon $defaultFrom, Carbon $defaultTo): array
    {
        return [
            $from ? Carbon::parse($from) : $defaultFrom,
            $to   ? Carbon::parse($to)   : $defaultTo,
        ];
    }

    private function periodDateRange(string $period, string $key, Carbon $outerFrom, Carbon $outerTo): array
    {
        return match ($period) {
            'year' => [
                Carbon::parse($key . '-01-01'),
                Carbon::parse($key . '-12-31'),
            ],
            'month' => [
                Carbon::parse($key . '-01'),
                Carbon::parse($key . '-01')->endOfMonth(),
            ],
            'day' => [
                Carbon::parse($key)->startOfDay(),
                Carbon::parse($key)->endOfDay(),
            ],
            'total' => [$outerFrom, $outerTo],
        };
    }

    private function getWeeklyTrend(int $clinicId): Collection
    {
        $weekExpr = DatabaseHelper::yearWeek('start_time');
        $weeks = Appointment::where('clinic_id', $clinicId)
            ->where('start_time', '>=', Carbon::now()->subWeeks(4)->startOfWeek())
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("{$weekExpr} as week, COUNT(*) as count")
            ->groupBy(DB::raw($weekExpr))
            ->orderBy('week')
            ->get()
            ->keyBy('week');

        $result = collect();
        for ($i = 4; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekKey = (int) $startOfWeek->format('oW');
            $result->push([
                'week'  => $startOfWeek->format('Y-m-d'),
                'count' => (int) ($weeks->get($weekKey)?->count ?? 0),
            ]);
        }

        return $result;
    }

    private function getTrendDirection(Collection $weeklyTrend): string
    {
        if ($weeklyTrend->count() < 2) return 'insufficient_data';

        $counts = $weeklyTrend->pluck('count');
        $first = $counts->first();
        $last = $counts->last();

        if ($last > $first * 1.1) return 'increasing';
        if ($last < $first * 0.9) return 'decreasing';
        return 'stable';
    }
}
