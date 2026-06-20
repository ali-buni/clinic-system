<?php

namespace Database\Seeders;

use App\Models\Disease;
use Illuminate\Database\Seeder;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = collect([
            ['code' => 'I10', 'ar_name' => 'ارتفاع ضغط الدم', 'en_name' => 'Hypertension', 'disease_nature' => 'chronic'],
            ['code' => 'E11', 'ar_name' => 'السكري من النوع الثاني', 'en_name' => 'Type 2 Diabetes', 'disease_nature' => 'chronic'],
            ['code' => 'J00', 'ar_name' => 'نزلة برد حادة', 'en_name' => 'Acute Nasopharyngitis', 'disease_nature' => 'acute'],
            ['code' => 'J02', 'ar_name' => 'التهاب البلعوم الحاد', 'en_name' => 'Acute Pharyngitis', 'disease_nature' => 'acute'],
            ['code' => 'J15', 'ar_name' => 'ذات الرئة البكتيري', 'en_name' => 'Bacterial Pneumonia', 'disease_nature' => 'infectious'],
            ['code' => 'M54', 'ar_name' => 'آلام الظهر', 'en_name' => 'Back Pain', 'disease_nature' => 'acute'],
            ['code' => 'M17', 'ar_name' => 'الفصال العظمي في الركبة', 'en_name' => 'Knee Osteoarthritis', 'disease_nature' => 'chronic'],
            ['code' => 'K21', 'ar_name' => 'ارتجاع معدي مريئي', 'en_name' => 'GERD', 'disease_nature' => 'chronic'],
            ['code' => 'K29', 'ar_name' => 'التهاب المعدة', 'en_name' => 'Gastritis', 'disease_nature' => 'acute'],
            ['code' => 'E78', 'ar_name' => 'فرط شحميات الدم', 'en_name' => 'Hyperlipidemia', 'disease_nature' => 'chronic'],
            ['code' => 'J45', 'ar_name' => 'الربو', 'en_name' => 'Asthma', 'disease_nature' => 'chronic'],
            ['code' => 'N39', 'ar_name' => 'التهاب المسالك البولية', 'en_name' => 'Urinary Tract Infection', 'disease_nature' => 'infectious'],
            ['code' => 'L20', 'ar_name' => 'التهاب الجلد التأتبي', 'en_name' => 'Atopic Dermatitis', 'disease_nature' => 'chronic'],
            ['code' => 'E03', 'ar_name' => 'قصور الغدة الدرقية', 'en_name' => 'Hypothyroidism', 'disease_nature' => 'chronic'],
            ['code' => 'D50', 'ar_name' => 'فقر الدم بعوز الحديد', 'en_name' => 'Iron Deficiency Anemia', 'disease_nature' => 'chronic'],
        ])->map(fn(array $data) => Disease::firstOrCreate(
            ['code' => $data['code']],
            [...$data, 'is_custom' => false, 'description' => null]
        ));
    }
}
