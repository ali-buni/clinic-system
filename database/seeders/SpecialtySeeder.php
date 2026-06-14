<?php
namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $model = Specialty::class;
    public function run(): void
    {
        $specialties = collect([
            ['ar_name' => 'الطب العام', 'en_name' => 'General Medicine'],
            ['ar_name' => 'الطب الباطني', 'en_name' => 'Internal Medicine'],
            ['ar_name' => 'أمراض القلب', 'en_name' => 'Cardiology'],
            ['ar_name' => 'أمراض الجهاز الهضمي', 'en_name' => 'Gastroenterology'],
            ['ar_name' => 'أمراض الصدر والجهاز التنفسي', 'en_name' => 'Pulmonology'],
            ['ar_name' => 'أمراض الكلى', 'en_name' => 'Nephrology'],
            ['ar_name' => 'الغدد الصم والسكري', 'en_name' => 'Endocrinology & Diabetes'],
            ['ar_name' => 'أمراض الأعصاب', 'en_name' => 'Neurology'],
            ['ar_name' => 'أمراض الأورام', 'en_name' => 'Oncology'],
            ['ar_name' => 'أمراض الروماتيزم والمفاصل', 'en_name' => 'Rheumatology'],

            // Age & Gender Specific
            ['ar_name' => 'طب الأطفال', 'en_name' => 'Pediatrics'],
            ['ar_name' => 'التوليد وأمراض النساء', 'en_name' => 'Obstetrics & Gynecology'],
            ['ar_name' => 'طب الشيخوخة', 'en_name' => 'Geriatrics'],

            // Specialized Clinics
            ['ar_name' => 'الأمراض الجلدية', 'en_name' => 'Dermatology'],
            ['ar_name' => 'طب وجراحة العيون', 'en_name' => 'Ophthalmology'],
            ['ar_name' => 'أذن وأنف وحنجرة', 'en_name' => 'Otolaryngology (ENT)'],
            ['ar_name' => 'طب الأسنان', 'en_name' => 'Dentistry'],
            ['ar_name' => 'الطب النفسي', 'en_name' => 'Psychiatry'],

            // Surgical
            ['ar_name' => 'الجراحة العامة', 'en_name' => 'General Surgery'],
            ['ar_name' => 'جراحة العظام', 'en_name' => 'Orthopedic Surgery'],
            ['ar_name' => 'جراحة التجميل والترميم', 'en_name' => 'Plastic Surgery'],
            ['ar_name' => 'أمراض وجراحة المسالك البولية', 'en_name' => 'Urology'],

            // Support Services
            ['ar_name' => 'العلاج الفيزيائي وإعادة التأهيل', 'en_name' => 'Physical Therapy & Rehabilitation'],
            ['ar_name' => 'الأشعة والتصوير الطبي', 'en_name' => 'Radiology'],
            ['ar_name' => 'التخدير وتدبير الألم', 'en_name' => 'Anesthesiology'],
        ])->map(fn(array $data) => Specialty::firstOrCreate($data));
       
    }
}