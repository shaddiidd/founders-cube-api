<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    
    public function graph_applications()
    {
        $interval = request()->get('interval', 'weekly');
        $data = [];

        if ($interval == 'weekly') {
            $currentDate = Carbon::today();
            for ($i = 0; $i < 9; $i++) {
                $toDate = Carbon::today()->subDays(7 * ($i + 1));
                $applicationsCount = Application::whereDate('created_at', '<', $currentDate->toDateString())->whereDate('created_at', '>=', $toDate->toDateString())->count();
                $data[] = [
                    'text' => '' . (9 - $i),
                    'number' => $applicationsCount,
                    'from_date' => $currentDate->toDateString(),
                    'to_date' => $toDate->toDateString(),
                ];
                $currentDate = $toDate->copy();
            }
            $data = array_reverse($data);
        } else if ($interval == 'monthly') {
            $currentDate = Carbon::today();
            for ($i = 0; $i < 12; $i++) {
                $startOfMonth = $currentDate->copy()->startOfMonth();
                $endOfMonth = $currentDate->copy()->endOfMonth();
                $applicationsCount = Application::whereDate('created_at', '<=', $endOfMonth->toDateString())->whereDate('created_at', '>=', $startOfMonth->toDateString())->count();
                $data[] = [
                    'text' => $startOfMonth->formatLocalized('%b'),
                    'number' => $applicationsCount,
                    'from_date' => $startOfMonth->toDateString(),
                    'to_date' => $endOfMonth->toDateString(),
                ];
                $currentDate = $startOfMonth->copy()->subDays(1);
            }
            $data = array_reverse($data);
        } else if ($interval == 'yearly') {
            $currentDate = Carbon::today();
            for ($i = 0; $i < 5; $i++) {
                $startOfYear = $currentDate->copy()->startOfYear();
                $endOfYear = $currentDate->copy()->endOfYear();
                $applicationsCount = Application::whereDate('created_at', '<=', $endOfYear->toDateString())->whereDate('created_at', '>=', $startOfYear->toDateString())->count();
                $data[] = [
                    'text' => $startOfYear->format('Y'),
                    'number' => $applicationsCount,
                    'from_date' => $startOfYear->toDateString(),
                    'to_date' => $endOfYear->toDateString(),
                ];
                $currentDate = $startOfYear->copy()->subDays(1);
            }
            $data = array_reverse($data);
        }

        return response()->json($data, 200);
    }

    public function graph_transactions()
    {
        $interval = request()->get('interval', 'weekly');
        $data = [];

        if ($interval == 'weekly') {
            $currentDate = Carbon::today();
            for ($i = 0; $i < 9; $i++) {
                $toDate = Carbon::today()->subDays(7 * ($i + 1));
                $transactionsCount = Transaction::whereDate('created_at', '<', $currentDate->toDateString())->whereDate('created_at', '>=', $toDate->toDateString())->count();
                $data[] = [
                    'text' => '' . (9 - $i),
                    'number' => $transactionsCount,
                    'from_date' => $currentDate->toDateString(),
                    'to_date' => $toDate->toDateString(),
                ];
                $currentDate = $toDate->copy();
            }
            $data = array_reverse($data);
        } else if ($interval == 'monthly') {
            $currentDate = Carbon::today();
            for ($i = 0; $i < 12; $i++) {
                $startOfMonth = $currentDate->copy()->startOfMonth();
                $endOfMonth = $currentDate->copy()->endOfMonth();
                $transactionsCount = Transaction::whereDate('created_at', '<=', $endOfMonth->toDateString())->whereDate('created_at', '>=', $startOfMonth->toDateString())->count();
                $data[] = [
                    'text' => $startOfMonth->formatLocalized('%b'),
                    'number' => $transactionsCount,
                    'from_date' => $startOfMonth->toDateString(),
                    'to_date' => $endOfMonth->toDateString(),
                ];
                $currentDate = $startOfMonth->copy()->subDays(1);
            }
            $data = array_reverse($data);
        } else if ($interval == 'yearly') {
            $currentDate = Carbon::today();
            for ($i = 0; $i < 5; $i++) {
                $startOfYear = $currentDate->copy()->startOfYear();
                $endOfYear = $currentDate->copy()->endOfYear();
                $transactionsCount = Transaction::whereDate('created_at', '<=', $endOfYear->toDateString())->whereDate('created_at', '>=', $startOfYear->toDateString())->count();
                $data[] = [
                    'text' => $startOfYear->format('Y'),
                    'number' => $transactionsCount,
                    'from_date' => $startOfYear->toDateString(),
                    'to_date' => $endOfYear->toDateString(),
                ];
                $currentDate = $startOfYear->copy()->subDays(1);
            }
            $data = array_reverse($data);
        }

        return response()->json($data, 200);
    }

}
