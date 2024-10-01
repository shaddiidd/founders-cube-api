<?php

namespace App\Http\Controllers;

use App\Models\PackageBulltet;
use Illuminate\Http\Request;
use App\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::select('id', 'name', 'price', 'star', 'months')->with('bullets')->get();

        // Extract only the 'text' values from the bullets relationship
        $packages = $packages->map(function ($package) {
            $package['bullets'] = $package->bullets->pluck('text')->toArray();
            return $package;
        });

        return response($packages, 200);
    }

    public function show($id)
    {
        $package = Package::with('bullets:id,package_id,text')
            ->select('id', 'name', 'price', 'star', 'months')
            ->findOrFail($id);

        $package['bullets'] = $package->bullets->pluck('text')->toArray();

        return response($package, 200);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'string|required',
            'price' => 'string|required',
            'months' => 'string|required',
            'star' => 'boolean|required',
            'bullets' => 'array|nullable',
            'bullets.*' => 'string|nullable'
        ]);
    
        $package = Package::create($request->only(['name', 'price', 'months', 'star']));
    
        foreach ($request->input('bullets') as $bullet) {
            $package->bullets()->create(['text' => $bullet]);
        }
    
        return response('Package Created', 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|required',
            'price' => 'integer|required',
            'months' => 'integer|required',
            'star' => 'boolean|required',
            'bullets' => 'array|nullable',
            'bullets.*' => 'string|nullable'
        ]);
    
        $package = Package::findOrFail($id);
        $package->update($request->only(['name', 'price', 'months', 'star']));
        $package->bullets()->delete();
    
        if ($request->input('bullets')) {
            foreach ($request->input('bullets') as $bullet) {
                $package->bullets()->create(['text' => $bullet]);
            }
        }
    
        return response('Package Updated', 200);
    }
    


    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $package->bullets()->delete();
        $package->delete();
    
        return response('Deleted', 200);
    }
}
