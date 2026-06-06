<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;

class EssentialMedicinesSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = fopen(base_path("database/data/syria_eml_2019.csv"), "r");

        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                Medicine::create([
                    'generic_name_en' => $data[0], 
                    'generic_name_ar' => $data[1],
                    'en_name'         => $data[0],
                    'ar_name'         => $data[1] ?? $data[0],
                    'strength'        => $data[2],
                    'form'            => $this->cleanForm($data[3]),
                    'api_medicine_id' => 'EML-SY-' . $data[4],
                    'is_custom'       => false
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }

    private function cleanForm($form): string
    {

        $form = strtolower($form);
        if (str_contains($form, 'tab')) return 'tablet';
        if (str_contains($form, 'cap')) return 'capsule';
        if (str_contains($form, 'syrup') || str_contains($form, 'suspension')) return 'syrup';
        if (str_contains($form, 'inj')) return 'injection';
        if (str_contains($form, 'oint') || str_contains($form, 'cream')) return 'ointment';
        return 'tablet';
    }
}
