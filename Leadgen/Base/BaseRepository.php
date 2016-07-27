<?php
namespace Leadgen\Base;

use Mongolid\ActiveRecord;
use Mongolid\Cursor\CursorInterface;

/**
 * Class BaseRepository
 *
 * The base repository contains the base implementation of a service that is
 * responsible for abstracting database queries regarding a resource in order
 * to have cleaner controllers and a better code-reuse.
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The entity that the repository manipulates.
     * @var ActiveRecord
     */
    protected $resource;

    /**
     * Errors of the last operation
     * @var array
     */
    protected $errors = [];

    /**
     * Retrieves all resources with support to pagination.
     *
     * @param  integer $page    Page number being displayed.
     * @param  integer $perPage Results per page.
     *
     * @return CursorInterface
     */
    public function all(int $page = 1, int $perPage = 10): CursorInterface
    {
        $cursor = $this->resource::all()->limit($perPage);

        if ($page > 1) {
            $cursor->skip(($page - 1) * $perPage);
        }

        return $cursor;
    }

    /**
     * Find an resource that exists
     *
     * @throws ModelNotFoundException If no document was found.
     *
     * @param  mixed $id Id of the resource to be found.
     *
     * @return ActiveRecord
     */
    public function findExisting($id)
    {
        return $this->resource::firstOrFail($id);
    }

    /**
     * Creates a new resource based in the given $data. In case of failure
     * the errors can be retrieved calling 'getLastErrors'.
     *
     * @param  array $data Resource attributes.
     *
     * @return ActiveRecord|null resource in case of success or false on failure
     */
    public function createNew(array $data)
    {
        $entity = new $this->resource;
        $entity->fill($data);

        if ($entity->save()) {
            return $entity;
        }

        $this->errors = $entity->errors()->all();

        return false;
    }

    /**
     * Updated the given resource based in $data. In case of failure the
     * errors can be retrieved calling 'getLastErrors'.
     *
     * @param  ActiveRecord $entity Instance being updated.
     * @param  array        $data   Resource attributes.
     *
     * @return boolean Success
     */
    public function updateExisting(ActiveRecord $entity, array $data): bool
    {
        $entity->fill($data);

        if ($entity->save()) {
            return true;
        }

        $this->errors = $entity->errors()->all();

        return false;
    }

    /**
     * Updated the given resource based in $data. In case of failure the
     * errors can be retrieved calling 'getLastErrors'.
     *
     * @param  ActiveRecord $entity Instance being updated.
     *
     * @return boolean Success
     */
    public function deleteExisting(ActiveRecord $entity): bool
    {
        if ($entity->delete()) {
            return true;
        }

        $this->errors = $entity->errors()->all();

        return false;
    }

    /**
     * Retrieves the error of the last operation
     *
     * @return array
     */
    public function getLastErrors(): array
    {
        return $this->errors;
    }
}
