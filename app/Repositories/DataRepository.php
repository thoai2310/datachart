<?php


namespace App\Repositories;


use App\Models\Data;
use Carbon\Carbon;

class DataRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(Data::class);

        $this->fields = [
            'code', 'title', 'description', 'source_type', 'created_at', 'updated_at', 'deleted_at'];
    }

    public function formatAllRecord($records)
    {
        if (!empty($records)) {
            foreach ($records as &$record) {
                $this->formatRecord($record);
            }
        }
        return $records;
    }

    function formatRecord($record)
    {
        $mothName = '';
        if(!empty($record->date)) {
            $mothName = Carbon::parse($record->date)->format('F');
        }
        $record->monthName = $mothName;
        return $record;
    }
}
