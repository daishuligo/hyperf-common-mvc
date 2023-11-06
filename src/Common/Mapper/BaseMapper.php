<?php

namespace YueChuan\Common\Mapper;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use YueChuan\Common\Model\BaseModel;
use Hyperf\Contract\LengthAwarePaginatorInterface;

abstract class BaseMapper
{
    /**
     * @var BaseModel
     */
    public $model;

    abstract public function assignModel();

    public function __construct()
    {
        $this->assignModel();
    }

    public function getModel(): Model
    {
        return new $this->model;
    }

    public function getPageList(?array $params): array
    {
        $paginate = $this->listQuerySetting($params)->paginate(
            $params['page_size'] ?? $this->model::PAGE_SIZE, ['*'], 'page', $params['page'] ?? 1
        );

        return $this->setPaginate($paginate);
    }

    public function getTreeList(?array $params = null, string $id = 'id', string $parentField = 'parent_id',
                                string $children='children'): array
    {
        $data = $this->listQuerySetting($params)->get()->toArray();
        return $this->toTree($data, $data[0]->{$parentField} ?? 0, $id, $parentField, $children);
    }

    public function save(array $data): Model
    {
        $this->filterExecuteAttributes($data, $this->getModel()->getIncrementing());

        return $this->model::create($data);
    }

    public function read(int|string $id): Model|null
    {
        return ($model = $this->model::find($id)) ? $model : null;
    }

    public function update(int|string $id, array $data): bool
    {
        $this->filterExecuteAttributes($data, true);
        return $this->model::find($id)->update($data) > 0;
    }

    public function delete(int|string $id): bool
    {
        if (empty($this->model->getSoftDeleteKey())){
            try {
                return $this->model::find($id)->delete();
            } catch (\Exception $e) {
                return false;
            }
        }else{
            $updateData = [
                $this->model->getSoftDeleteKey() => $this->model::SOFT_DELETED
            ];

            return $this->update($id, $updateData);
        }
    }

    private function listQuerySetting(?array $params): Builder
    {
        $query = $this->getModel()->newQuery();

        if ($params['fields'] ?? null){
            $query->select($this->filterQueryAttributes($params['fields']));
        }

        $query = $this->handleWith($query, $params);

        $query = $this->handleOrder($query, $params);

        return $this->handleSearch($query, $params);
    }

    private function filterQueryAttributes(mixed $fields)
    {
        $model = $this->getModel();
        $attrs = $model->getFillable();

        foreach ($fields as $key => $field){
            if (!in_array(trim($field), $attrs) && mb_strpos(str_replace('AS', 'as', $field), 'as') === false){
                unset($fields[$key]);
            }else{
                $fields[$key] = trim($field);
            }
        }

        $model = null;
        return (count($fields) < 1) ? ['*'] : $fields;
    }

    public function handleWith(Builder $query, ?array $params): Builder
    {
        if ($params['with'] ?? false){
            $query->with($params['with']);
        }

        return $query;
    }

    public function handleOrder(Builder $query, ?array $params): Builder
    {
        if ($params['order_by'] ?? false) {
            if (is_array($params['order_by'])) {
                foreach ($params['order_by'] as $key => $order) {
                    $query->orderBy($key, $order == 'desc' ? 'desc' : 'asc');
                }
            } else {
                $query->orderBy($params['order_by'], $params['order_type'] ?? 'asc');
            }
        }

        return $query;
    }

    public function handleSearch(Builder $query, ?array $params): Builder
    {
        if (!empty($this->model->getSoftDeleteKey())){
            $query->where($this->model->getSoftDeleteKey(), '=', $this->model::SOFT_NO_DELETED);
        }

        return $query;
    }

    public function setPaginate(LengthAwarePaginatorInterface $paginate): array
    {
        return [
            'items' => method_exists($this, 'handlePageItems') ? $this->handlePageItems($paginate->items()) : $paginate->items(),
            'pageInfo' => [
                'total' => $paginate->total(),
                'currentPage' => $paginate->currentPage(),
                'totalPage' => $paginate->lastPage()
            ]
        ];
    }

    private function filterExecuteAttributes(array &$data, bool $getIncrementing): void
    {
        $model = $this->getModel();
        $attrs = $model->getFillable();
        foreach ($data as $name => $val){
            if (!in_array($name, $attrs)){
                unset($data[$name]);
            }
        }

        if ($getIncrementing && isset($data[$model->getKeyName()])){
            unset($data[$model->getKeyName()]);
        }

        $model = null;
    }

    protected function toTree(array $data = [], int $parentId = 0, string $id = 'id', string $parentField = 'parent_id', string $children='children'): array
    {
        if (empty($data)) return [];

        $tree = [];

        foreach ($data as $value) {
            if ($value[$parentField] == $parentId) {
                $child = $this->toTree($data, $value[$id], $id, $parentField, $children);
                if (!empty($child)) {
                    $value[$children] = $child;
                }
                array_push($tree, $value);
            }
        }

        unset($data);
        return $tree;
    }
}