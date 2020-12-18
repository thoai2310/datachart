<?php

namespace App\Repositories;

use DB;

class Repository
{

    private $model;
    protected $fields;

    public function __construct($model)
    {
        $this->model = $model;
        $this->fields = [];
    }

    public function getModel() {
        return $this->model;
    }

    public function store($data)
    {
        if (empty($this->fields) || empty($data)) {
            return false;
        }

        $object = new $this->model;
        foreach ($this->fields as $field) {
            if (array_key_exists($field, $data)) {
                $object->$field = $data[$field];
            }
        }
        if ($object->save()) {
            return $object;
        }
        return false;
    }

    public function update($object, $data)
    {
        if (empty($this->fields) || empty($data)) {
            return false;
        }

        foreach ($this->fields as $field) {
            if (array_key_exists($field, $data)) {
                $object->$field = $data[$field];
            }
        }

        if ($object->save()) {
            return $object;
        }
        return false;
    }

    public function get($filters = [], $take = 30, $sort = [], $relations = [])
    {
        $data = new $this->model;

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($value === "NOT_FILTER") {
                    continue;
                }
                if (in_array($key, $this->fields)) {
                    if ($value === "NULL") {
                        $data = $data->whereNull($key);
                    } elseif ($value === "NOT_NULL") {
                        $data = $data->whereNotNull($key);
                    } elseif (is_array($value) && count($value) == 2) {
                        $columnName = $key;
                        $startDate = date($value[0] . " 00:00:00");
                        $endDate = date($value[1] . " 23:59:59");
                        $arrDate = [$startDate, $endDate];
                        $data = $data->whereBetween($columnName, $arrDate);
                    } else {
                        $data = $data->where($key, $value);
                    }
                }
                // Search Where not like
                // nhận mảng key notlike, bên trong là các mảng con có dạng: ['columnname' => 'columnvalue']
                else if ($key === 'notlike') {
                    foreach ($value as $key => $item) {
                        $data = $data->where($key, 'NOT LIKE', "%$item%");
                    }
                }
                // Search Where like
                // nhận mảng key like, bên trong là các mảng con có dạng: ['columnname' => 'columnvalue']
                else if ($key === 'like') {
                    foreach ($value as $key => $item) {
                        $data = $data->where($key, 'LIKE', "%$item%");
                    }
                } // search where in
                else if ($key == 'wherein') {
                    $colName = $filters[$key]['col'];
                    $arrayValue = $filters[$key]['array_value'];
                    $data = $data->whereIn($colName, $arrayValue);
                } //
                else if ($key == 'wherenotin') {
                    foreach ($value as $colName => $item) {
                        $data = $data->whereNotIn($colName, $item);
                    }
                }
                // Search theo month:
                // Mang month co dang: MONTH => [$columnName, $month]
                else if ($key == 'MONTH') {
                    $data = $data->whereMonth($value[0], $value[1]);
                }

                // Search theo year:
                // Mang month co dang: YEAR => [$columnName, $year]
                else if ($key == 'YEAR') {
                    $data = $data->whereYear($value[0], $value[1]);
                }
            }
        }

        if (is_array($sort) && !empty($sort['by']) && !empty($sort['type'])) {
            $data = $data->orderBy($sort['by'], $sort['type']);
        } else {
            $data = $data->orderBy('id', 'desc');
        }
        // return $data->toSql();
        if(count($relations) > 0) {
            return $data->with($relations)->paginate($take);
        }
        return $data->paginate($take);
    }

    public function all($filters = [], $sort = [], $relations = [])
    {
        $data = new $this->model;

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (in_array($key, $this->fields)) {
                    if ($value === "NULL") {
                        $data = $data->whereNull($key);
                    } elseif ($value === "NOT_NULL") {
                        $data = $data->whereNotNull($key);
                    } elseif (is_array($value) && count($value) == 2) {
                        $columnName = $key;
                        $data = $data->where($columnName, '>=', $value[0]);
                        $data = $data->where($columnName, '<=', $value[1]);
                    } else {
                        $data = $data->where($key, $value);
                    }
                } else {
                    if ($key == 'wherein') {
                        $colName = $filters[$key]['col'];
                        $arrayValue = $filters[$key]['array_value'];
                        $data = $data->whereIn($colName, $arrayValue);
                    } else if ($key == 'wherenotin') {
                        foreach ($value as $colName => $item) {
                            $data = $data->whereNotIn($colName, $item);
                        }
                    }
                    // Search theo month:
                    // Mang month co dang: MONTH => [$columnName, $month]
                    else if ($key == 'MONTH') {
                        $data = $data->whereMonth($value[0], $value[1]);
                    }

                    // Search theo year:
                    // Mang month co dang: YEAR => [$columnName, $year]
                    else if ($key == 'YEAR') {
                        $data = $data->whereYear($value[0], $value[1]);
                    }
                }
            }
        }

        if (is_array($sort) && !empty($sort['by']) && !empty($sort['type'])) {
            $data = $data->orderBy($sort['by'], $sort['type']);
        }
        if(count($relations) > 0) {
            return $data->with($relations)->get();
        }
        return $data->get();
    }

    public function count($filters)
    {
        $data = new $this->model;

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (in_array($key, $this->fields)) {
                    if ($value === "NULL") {
                        $data = $data->whereNull($key);
                    } elseif ($value === "NOT_NULL") {
                        $data = $data->whereNotNull($key);
                    } elseif (is_array($value) && count($value) == 2) {
                        $columnName = $key;
                        $data = $data->where($columnName, '>=', $value[0]);
                        $data = $data->where($columnName, '<=', $value[1]);
                    } else {
                        $data = $data->where($key, $value);
                    }
                } else {
                    if ($key == 'wherein') {
                        foreach ($value as $index => $item) {
                            $data = $data->whereIn($index, $item);
                        }
                    } else if ($key == 'wherenotin') {
                        foreach ($value as $colName => $item) {
                            $data = $data->whereNotIn($colName, $item);
                        }
                    }
                    // Search theo month:
                    // Mang month co dang: MONTH => [$columnName, $month]
                    else if ($key == 'MONTH') {
                        $data = $data->whereMonth($value[0], $value[1]);
                    }

                    // Search theo year:
                    // Mang month co dang: YEAR => [$columnName, $year]
                    else if ($key == 'YEAR') {
                        $data = $data->whereYear($value[0], $value[1]);
                    }
                }
            }
        }

        if (@$filters['fromDate']) {
            $data = $data->where('created_at', '>=', $filters['fromDate'] . " 00:00:00");
        }

        if (@$filters['toDate']) {
            $data = $data->where('created_at', '<=', $filters['toDate'] . " 23:59:59");
        }

//        return $data->toSql();
        return $data->count();
    }

    public function sum($filters, $column)
    {
        $data = new $this->model;

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (in_array($key, $this->fields)) {
                    $data = $data->where($key, $value);
                }
            }
        }

        if (@$filters['fromDate']) {
            $data = $data->where('created_at', '>=', $filters['fromDate'] . " 00:00:00");
        }

        if (@$filters['toDate']) {
            $data = $data->where('created_at', '<=', $filters['toDate'] . " 23:59:59");
        }

        return $data->sum($column);
    }

    public function sumInIds($filters, $column)
    {
        $data = new $this->model;
        $sum = 0;
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                $data = $data->orWhere(['id' => $value]);
            }
        } else {
            return 0;
        }
        // $data = DB::table($table)->whereIn('id',$filters)->sum($column);
        // return $data->toSql();
        return $data->sum($column);
    }

    public function getById($id)
    {
        if (!$id) {
            return false;
        }
        return $this->model::find($id);
    }

    public function getBySlug($slug, $filters = [])
    {
        if (!$slug) {
            return false;
        }
        if (empty($filters)) {
            return $this->model::where('slug', $slug)->first();
        } else {
            $filters['slug'] = $slug;
            return $this->model::where($filters)->first();
        }
    }

    public function insertMultiRecord($data) {
        foreach ($data as $key => $item) {
            if(empty($item['created_at'])) {
                $data[$key]['created_at'] = date('Y-m-d H:i:s');
            }
            if(empty($item['updated_at'])) {
                $data[$key]['updated_at'] = date('Y-m-d H:i:s');
            }
        }
        return $this->model::insert($data);
    }

    public function pluckCol($col, $filters = []) {
        if(count($filters) > 0) {
            return $this->model::where($filters)->pluck($col);
        }
        return $this->model::pluck($col);
    }

    public function getByCol($col, $value, $relations = [], $sort = []) {
        if(count($relations) > 0) {
            if (is_array($sort) && !empty($sort['by']) && !empty($sort['type'])) {
                return $this->model::where($col, $value)->orderBy($sort['by'], $sort['type'])->with($relations)->first();
            }
            return $this->model::where($col, $value)->with($relations)->first();
        }
        if (is_array($sort) && !empty($sort['by']) && !empty($sort['type'])) {
            return $this->model::where($col, $value)->orderBy($sort['by'], $sort['type'])->first();
        }
        return $this->model::where($col, $value)->first();
    }

    public function updateById(int $id, array $data)
    {
        return $this->model::where('id', $id)->update($data);
    }

    public function getByCols($filters, $relations = [], $sort = []) {
        if(count($relations) > 0) {
            if (is_array($sort) && !empty($sort['by']) && !empty($sort['type'])) {
                return $this->model::where($filters)->orderBy($sort['by'], $sort['type'])->with($relations)->first();
            }
            return $this->model::where($filters)->with($relations)->first();
        }
        if (is_array($sort) && !empty($sort['by']) && !empty($sort['type'])) {
            return $this->model::where($filters)->orderBy($sort['by'], $sort['type'])->first();
        }
        return $this->model::where($filters)->first();
    }

    public function insertOrUpdate($id, array $data) {
        if(!empty($id)) {
            $id = intval($id);
            unset($data['created_at']);
            unset($data['updated_at']);
            return $this->updateById($id, $data);
        } else {
            return $this->store($data);
        }
    }

}
