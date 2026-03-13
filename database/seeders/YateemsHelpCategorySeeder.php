<?php

namespace Database\Seeders;

use App\Models\YateemsHelpCategory;
use Illuminate\Database\Seeder;

class YateemsHelpCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Zakat (Obligatory Charity)',
                'description' => 'Zakat Islam ke 5 stambhon (pillars) mein se ek hai. Har Saheb-e-Nisab (jo ek tay shuda daulat ka malik ho) musalman par saal mein ek baar apni bachat aur sampatti ka 2.5% hissa garibon aur zaruratmandon ko dena farz hai.',
                'status' => 'active',
            ],
            [
                'name' => 'Sadaqah (Voluntary Charity)',
                'description' => 'Sadaqah ek nafil khairat hai jo Allah ki raza ke liye kisi bhi waqt di ja sakti hai. Iski koi tay shuda miqdar (amount) nahi hoti. Yeh paise, khana, kapde daan karne se lekar, kisi ki madad karna ya yahan tak ki muskurana bhi ho sakta ha',
                'status' => 'active',
            ],
            [
                'name' => 'Zakat al-Fitr / Fitra',
                'description' => 'Yeh Ramzan ke mahine mein Eid-ul-Fitr ki namaz se pehle dena farz hota hai. Iska maqsad garib aur nadar logon ko Eid ki khushiyon mein shamil karna aur roze ke dauran hui choti-moti galtiyon ka kaffarah ada karna hota hai.',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            YateemsHelpCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
