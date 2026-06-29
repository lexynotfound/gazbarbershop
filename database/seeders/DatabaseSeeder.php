<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Capster;
use App\Models\CapsterSchedule;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@gazbarbershop.com'],
            [
                'name' => 'Admin GAZ',
                'phone' => '6281234567001',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ],
        );

        $member = User::query()->updateOrCreate(
            ['email' => 'user@gazbarbershop.com'],
            [
                'name' => 'Member Demo',
                'phone' => '6281234567002',
                'role' => 'user',
                'password' => Hash::make('password'),
            ],
        );

        $services = collect([
            ['name' => 'Cukur Rambut', 'description' => 'Potongan presisi dengan finishing premium.', 'price' => 40000, 'duration_minutes' => 30],
            ['name' => 'Cukur + Cuci', 'description' => 'Cukur rapi lengkap dengan hair wash.', 'price' => 60000, 'duration_minutes' => 45],
            ['name' => 'Warnai Rambut', 'description' => 'Coloring modern dengan konsultasi warna.', 'price' => 150000, 'duration_minutes' => 90],
            ['name' => 'Perawatan Jenggot', 'description' => 'Trim, shaping, dan perawatan beard oil.', 'price' => 50000, 'duration_minutes' => 30],
        ])->map(fn (array $service): Service => Service::query()->updateOrCreate(
            ['name' => $service['name']],
            $service + ['is_active' => true],
        ));

        $capsters = collect([
            ['name' => 'Rudi', 'rating' => 4.9, 'service_fee' => 50000, 'description' => 'Specialist fade dan gentleman cut.'],
            ['name' => 'Dika', 'rating' => 4.8, 'service_fee' => 45000, 'description' => 'Cepat, detail, dan nyaman untuk daily style.'],
            ['name' => 'Fahmi', 'rating' => 4.7, 'service_fee' => 40000, 'description' => 'Andalan grooming jenggot dan classic cut.'],
            ['name' => 'Bayu', 'rating' => 4.9, 'service_fee' => 60000, 'description' => 'Senior capster untuk styling premium.'],
        ])->map(fn (array $capster): Capster => Capster::query()->updateOrCreate(
            ['name' => $capster['name']],
            $capster + ['is_active' => true],
        ));

        $capsters->each(function (Capster $capster): void {
            foreach (range(0, 6) as $dayOffset) {
                CapsterSchedule::query()->updateOrCreate(
                    ['capster_id' => $capster->id, 'work_date' => now()->addDays($dayOffset)->toDateString()],
                    ['start_time' => '10:00:00', 'end_time' => '22:00:00', 'is_available' => true],
                );
            }
        });

        $booking = Booking::query()->updateOrCreate(
            ['booking_code' => 'GAZ-260531-001'],
            [
                'user_id' => $member->id,
                'capster_id' => $capsters->first()->id,
                'booking_start' => now()->addDay()->setTime(10, 0),
                'booking_end' => now()->addDay()->setTime(11, 15),
                'service_total' => 100000,
                'capster_fee' => 50000,
                'grand_total' => 150000,
                'status' => 'WAITING_CUSTOMER_CONFIRMATION',
                'admin_confirmed_at' => now(),
                'customer_response_deadline' => now()->addMinutes(15),
            ],
        );

        BookingItem::query()->updateOrCreate(
            ['booking_id' => $booking->id, 'service_id' => $services->first()->id],
            ['price' => 40000, 'duration_minutes' => 30],
        );

        BookingItem::query()->updateOrCreate(
            ['booking_id' => $booking->id, 'service_id' => $services->get(1)->id],
            ['price' => 60000, 'duration_minutes' => 45],
        );

        Payment::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            ['amount' => 150000, 'method' => 'cash', 'status' => 'unpaid'],
        );

        Review::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $member->id,
                'capster_id' => $capsters->first()->id,
                'rating' => 5,
                'comment' => 'Pelayanan bagus dan hasil memuaskan.',
            ],
        );

        $loyalCustomer = User::query()->updateOrCreate(
            ['email' => 'loyal@gazbarbershop.com'],
            [
                'name' => 'Rizky Loyal',
                'phone' => '6281234567888',
                'role' => 'user',
                'password' => Hash::make('password'),
            ],
        );

        foreach (range(1, 3) as $index) {
            $completedBooking = Booking::query()->updateOrCreate(
                ['booking_code' => 'GAZ-REPEAT-00'.$index],
                [
                    'user_id' => $loyalCustomer->id,
                    'capster_id' => $capsters->get($index % $capsters->count())->id,
                    'booking_start' => now()->subDays(7 * $index)->setTime(10 + $index, 0),
                    'booking_end' => now()->subDays(7 * $index)->setTime(10 + $index, 30),
                    'service_total' => $services->first()->price,
                    'capster_fee' => $capsters->get($index % $capsters->count())->service_fee,
                    'grand_total' => $services->first()->price + $capsters->get($index % $capsters->count())->service_fee,
                    'status' => 'COMPLETED',
                    'completed_at' => now()->subDays(7 * $index)->setTime(10 + $index, 30),
                ],
            );

            BookingItem::query()->updateOrCreate(
                ['booking_id' => $completedBooking->id, 'service_id' => $services->first()->id],
                ['price' => $services->first()->price, 'duration_minutes' => $services->first()->duration_minutes],
            );

            Payment::query()->updateOrCreate(
                ['booking_id' => $completedBooking->id],
                ['amount' => $completedBooking->grand_total, 'method' => 'cash', 'status' => 'paid'],
            );
        }

        $admin->touch();
    }
}
