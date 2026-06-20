<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = collect([
            ['ar_name' => 'باراسيتامول', 'en_name' => 'Paracetamol', 'generic_name_en' => 'Paracetamol', 'strength' => '500mg', 'form' => 'tablet'],
            ['ar_name' => 'إيبوبروفين', 'en_name' => 'Ibuprofen', 'generic_name_en' => 'Ibuprofen', 'strength' => '400mg', 'form' => 'tablet'],
            ['ar_name' => 'أموكسيسيلين', 'en_name' => 'Amoxicillin', 'generic_name_en' => 'Amoxicillin', 'strength' => '500mg', 'form' => 'capsule'],
            ['ar_name' => 'ميتفورمين', 'en_name' => 'Metformin', 'generic_name_en' => 'Metformin Hydrochloride', 'strength' => '850mg', 'form' => 'tablet'],
            ['ar_name' => 'أوميبرازول', 'en_name' => 'Omeprazole', 'generic_name_en' => 'Omeprazole', 'strength' => '20mg', 'form' => 'capsule'],
            ['ar_name' => 'لوسارتان', 'en_name' => 'Losartan', 'generic_name_en' => 'Losartan Potassium', 'strength' => '50mg', 'form' => 'tablet'],
            ['ar_name' => 'أتورفاستاتين', 'en_name' => 'Atorvastatin', 'generic_name_en' => 'Atorvastatin Calcium', 'strength' => '10mg', 'form' => 'tablet'],
            ['ar_name' => 'سالبوتامول', 'en_name' => 'Salbutamol', 'generic_name_en' => 'Salbutamol Sulfate', 'strength' => '100mcg', 'form' => 'injection'],
            ['ar_name' => 'سيتريزين', 'en_name' => 'Cetirizine', 'generic_name_en' => 'Cetirizine Hydrochloride', 'strength' => '10mg', 'form' => 'tablet'],
            ['ar_name' => 'ديكلوفيناك', 'en_name' => 'Diclofenac', 'generic_name_en' => 'Diclofenac Sodium', 'strength' => '50mg', 'form' => 'tablet'],
            ['ar_name' => 'أملوديبين', 'en_name' => 'Amlodipine', 'generic_name_en' => 'Amlodipine Besylate', 'strength' => '5mg', 'form' => 'tablet'],
            ['ar_name' => 'أزيثروميسين', 'en_name' => 'Azithromycin', 'generic_name_en' => 'Azithromycin', 'strength' => '250mg', 'form' => 'tablet'],
            ['ar_name' => 'بريدنيزولون', 'en_name' => 'Prednisolone', 'generic_name_en' => 'Prednisolone', 'strength' => '5mg', 'form' => 'tablet'],
            ['ar_name' => 'فيتامين د', 'en_name' => 'Vitamin D', 'generic_name_en' => 'Cholecalciferol', 'strength' => '1000IU', 'form' => 'tablet'],
            ['ar_name' => 'حديد', 'en_name' => 'Iron', 'generic_name_en' => 'Ferrous Sulfate', 'strength' => '200mg', 'form' => 'tablet'],
        ])->map(fn(array $data) => Medicine::firstOrCreate(
            ['en_name' => $data['en_name']],
            [...$data, 'is_custom' => false]
        ));
    }
}
