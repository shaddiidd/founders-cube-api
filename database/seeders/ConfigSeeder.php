<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Config;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config1 = new Config;
        $config1->config_key = 'bank_account_owner_name';
        $config1->config_value = 'Sari Awwad';
        $config1->save();

        $config2 = new Config;
        $config2->config_key = 'bank_name';
        $config2->config_value = 'Jordan Islamic Bank';
        $config2->save();

        $config3 = new Config;
        $config3->config_key = 'bank_account_number';
        $config3->config_value = '1032237212510400006';
        $config3->save();

        $config4 = new Config;
        $config4->config_key = 'bank_account_iban';
        $config4->config_value = 'JO10JIBA1030002237212510400006';
        $config4->save();

        $config5 = new Config;
        $config5->config_key = 'bank_account_cliq';
        $config5->config_value = '00962775624524';
        $config5->save();

        $config6 = new Config;
        $config6->config_key = 'whatsapp_group_url';
        $config6->config_value = 'https://chat.whatsapp.com/IwdPk92_d5qo8921dh2783hfDw2';
        $config6->save();
    }
}
