<?php

namespace Database\Seeders;

use App\Models\GovernmentEntity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GovernmentEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['الكهرباء','المياه','البلدية','المالية','العقارية','الصحة','التربية','النقل','الاتصالات والتقانة','التعليم العالي','التجارة الداخلية وحماية المستهلك','الجرائم الرقمية'] ;
        foreach($categories as $category){
            GovernmentEntity::create([
                'name'=>$category ,
            ]);
        }
    }
}
