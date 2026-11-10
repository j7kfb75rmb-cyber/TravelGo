<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class TravelSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Пляжный отдых', 'slug' => 'beach'],
            ['name' => 'Горы', 'slug' => 'mountains'],
            ['name' => 'Экскурсии', 'slug' => 'excursions'],
            ['name' => 'Семейный отдых', 'slug' => 'family'],
        ])->map(fn ($c) => Category::firstOrCreate(['slug' => $c['slug']], $c));

        $destinations = collect([
            ['country' => 'Турция', 'city' => 'Анталья', 'description' => 'Курорт на берегу Средиземного моря.'],
            ['country' => 'Египет', 'city' => 'Хургада', 'description' => 'Красное море, дайвинг и пляжи.'],
            ['country' => 'Грузия', 'city' => 'Тбилиси', 'description' => 'Древний город с богатой кухней.'],
            ['country' => 'Казахстан', 'city' => 'Алматы', 'description' => 'Город у подножия Заилийского Алатау.'],
        ])->map(fn ($d) => Destination::firstOrCreate(
            ['country' => $d['country'], 'city' => $d['city']],
            $d
        ));

        $tours = [
            ['destination' => 0, 'title' => 'Неделя в Анталье', 'price' => 650, 'seats' => 20, 'cats' => [0, 3]],
            ['destination' => 1, 'title' => 'Хургада: всё включено', 'price' => 720, 'seats' => 15, 'cats' => [0]],
            ['destination' => 2, 'title' => 'Выходные в Тбилиси', 'price' => 380, 'seats' => 12, 'cats' => [2]],
            ['destination' => 3, 'title' => 'Алматы и горы', 'price' => 300, 'seats' => 10, 'cats' => [1, 2]],
        ];

        foreach ($tours as $i => $t) {
            $tour = Tour::firstOrCreate(
                ['title' => $t['title']],
                [
                    'destination_id' => $destinations[$t['destination']]->id,
                    'description'    => 'Описание тура «' . $t['title'] . '».',
                    'price'          => $t['price'],
                    'start_date'     => now()->addDays(30 + $i * 10)->toDateString(),
                    'end_date'       => now()->addDays(37 + $i * 10)->toDateString(),
                    'seats'          => $t['seats'],
                    'is_active'      => true,
                ]
            );
            $tour->categories()->syncWithoutDetaching(
                collect($t['cats'])->map(fn ($idx) => $categories[$idx]->id)->all()
            );
        }
    }
}