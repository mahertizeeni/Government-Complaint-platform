<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $cities = ['دمشق','ريف دمشق','حلب','اللاذقية','حمص','حماة','طرطوس','درعا','ادلب','السويداء','القنيطرة','الحسكة','دير الزور','الرقة'];
        foreach($cities  as $city){
            City::create([
                'name'=>$city,
            ]);
        }
    }
}
