<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use Validator;

class ConfigController extends Controller
{
    public function index()
    {
        $configs = Config::all();

        $data = [];

        foreach ($configs as $config) {
            $data[$config->config_key] = $config->config_value;
        }

        return response()->json($data, 200);
    }

    public function update($key)
    {
        $config = Config::where('config_key', $key)->first();

        if (!$config)
        {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $validate = Validator::make(request()->all(), [
            'config_value' => 'required|string',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->messages()->first()], 400);
        }

        $config->config_value = request()->config_value;
        $config->save();

        return response()->json(['message' => 'Success'], 200);
    }

    public function updateAll()
    {
        $validate = Validator::make(request()->all(), [
            'bank_account_owner_name' => 'required|string',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_iban' => 'required|string',
            'bank_account_cliq' => 'required|string',
            'whatsapp_group_url' => 'required|string',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->messages()->first()], 400);
        }

        $config = Config::where('config_key', 'bank_account_owner_name')->first();
        $config->config_value = request()->bank_account_owner_name;
        $config->save();

        $config = Config::where('config_key', 'bank_name')->first();
        $config->config_value = request()->bank_name;
        $config->save();

        $config = Config::where('config_key', 'bank_account_number')->first();
        $config->config_value = request()->bank_account_number;
        $config->save();

        $config = Config::where('config_key', 'bank_account_iban')->first();
        $config->config_value = request()->bank_account_iban;
        $config->save();

        $config = Config::where('config_key', 'bank_account_cliq')->first();
        $config->config_value = request()->bank_account_cliq;
        $config->save();

        $config = Config::where('config_key', 'whatsapp_group_url')->first();
        $config->config_value = request()->whatsapp_group_url;
        $config->save();

        return response()->json(['message' => 'Success'], 200);
    }
}
