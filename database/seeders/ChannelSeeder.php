<?php

namespace Database\Seeders;

use App\Core\Enums\ChannelType;
use App\Core\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            [
                'name' => 'Foodics',
                'code' => 'foodics',
                'type' => ChannelType::API->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
            [
                'name' => 'Email',
                'code' => 'email',
                'type' => ChannelType::API->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
            [
                'name' => 'WhatsApp',
                'code' => 'whatsapp',
                'type' => ChannelType::API->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
            [
                'name' => 'Apple Pay',
                'code' => 'applepay',
                'type' => ChannelType::PAYMENT->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
            [
                'name' => 'Mastercard',
                'code' => 'mastercard',
                'type' => ChannelType::PAYMENT->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
            [
                'name' => 'Urway',
                'code' => 'urway',
                'type' => ChannelType::PAYMENT->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
            [
                'name' => 'Wish Money',
                'code' => 'wishmoney',
                'type' => ChannelType::PAYMENT->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
            [
                'name' => 'Mada',
                'code' => 'mada',
                'type' => ChannelType::PAYMENT->value,
                'status' => 'active',
                'credentials' => null,
                'settings' => null,
                'webhook_url' => null,
            ],
        ];

        foreach ($channels as $channelData) {
            Channel::firstOrCreate(
                ['code' => $channelData['code']],
                $channelData
            );
        }

        $this->command->info('Channels seeded successfully!');
    }
}

