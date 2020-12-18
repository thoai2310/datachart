<?php

namespace App\Http\Controllers;

use App\Repositories\DataRepository;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->dataRepo = new DataRepository();
    }

    public function getDataChart()
    {
        $dataJson = file_get_contents(env('APP_URL', 'http://192.168.231.1/').'data_exam/test.json');
        $dataFirst = json_decode($dataJson, true);
        $dataSql = $this->dataRepo->all([], []);
        $dataSql = $this->dataRepo->formatAllRecord($dataSql);
        $monthSecond = [];
        $valueSecond = [];
        $dataSecond = [];
        foreach ($dataSql as $item) {
            array_push($monthSecond, $item->monthName);
            array_push($valueSecond, $item->value);
        }
        $dataSecond['labels'] = $monthSecond;
        $dataSecond['data'] = $valueSecond;
        return response()->json([
            'dataFirst' => $dataFirst,
            'dataSecond' => $dataSecond
        ]);
    }

    private $dataRepo;
}
