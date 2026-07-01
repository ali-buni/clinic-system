<?php

namespace App\Services\Analytics;

class RecommendationService
{
    public function generateOperationalRecommendations(array $utilizationData)
    {
        $recommendations = [];

        foreach ($utilizationData as $data) {
            // منطق ذكي: إذا زادت نسبة الإشغال عن 85%، اقترح توسعة
            if (isset($data['utilization_rate']) && (float)$data['utilization_rate'] > 85) {
                $recommendations[] = [
                    'doctor' => $data['doctor_name'],
                    'message' => "الطبيب {$data['doctor_name']} لديه ضغط مرتفع (إشغال {$data['utilization_rate']}). يُنصح بنقل بعض المواعيد لطبيب آخر أو تمديد ساعات العمل.",
                    'priority' => 'high'
                ];
            }
        }

        return $recommendations;
    }

    public function generateFinancialRecommendations(float $outstandingBalance)
    {
        $recommendations = [];

        // منطق ذكي: تنبيه إذا تجاوزت المبالغ غير المحصلة حداً معيناً
        if ($outstandingBalance > 5000) { // حد افتراضي
            $recommendations[] = [
                'type' => 'warning',
                'message' => "تنبيه: هناك مبالغ غير محصلة تتجاوز 5,000. يُنصح بإرسال تذكيرات دفع للمرضى المعنيين.",
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    public function generatePatientRecommendations(int $lostPatientsCount)
    {
        if ($lostPatientsCount > 0) {
            return [
                'type' => 'marketing',
                'message' => "لديك {$lostPatientsCount} مريض لم يزر العيادة منذ أكثر من 6 أشهر. يُنصح بإرسال حملة SMS تذكيرية أو خصم خاص لاسترجاعهم.",
                'action' => 'Create SMS Campaign'
            ];
        }
        return null;
    }

    public function generateMedicalInsights(array $topDiseases)
    {
        $insights = [];
        foreach ($topDiseases as $disease) {
            if ($disease->cases_count > 50) {
                $insights[] = [
                    'type' => 'medical',
                    'message' => "زيادة في حالات ({$disease->ar_name}) لاحظناها في السجلات الأخيرة. يُنصح بتجهيز مخزون الأدوية المناسبة أو إطلاق حملة توعية عنها."
                ];
            }
        }
        return $insights;
    }
    public function getProactiveAdvice(int $appointmentId, float $probability)
    {
        if ($probability > 70) {
            return [
                'type' => 'alert',
                'message' => "تنبيه: الموعد رقم {$appointmentId} لديه احتمالية عدم حضور تصل إلى {$probability}%. يُنصح بإرسال رسالة واتساب تأكيدية فوراً.",
                'action' => 'Send Auto-Reminder'
            ];
        }
        return null;
    }
}
