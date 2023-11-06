<?php

namespace YueChuan\Common\Service;

use Hyperf\Database\Model\Model;
use YueChuan\Common\Mapper\BaseMapper;

abstract class BaseService
{
    /**
     * @var BaseMapper
     */
    public $mapper;

    public function getPageList(?array $params = []): array
    {
        if ($params['fields'] ?? null){
            $params['fields'] = explode(',', $params['fields']);
        }

        return $this->mapper->getPageList($params);
    }

    public function save(array $data): Model
    {
        return $this->mapper->save($data);
    }

    public function read(int $id): Model
    {
        return $this->mapper->read($id);
    }

    public function update(int $id, array $data): bool
    {
        return $this->mapper->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->mapper->delete($id);
    }
}