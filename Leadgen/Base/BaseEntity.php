<?php

namespace Leadgen\Base;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\MessageBag;
use MongoDB\Collection;
use MongoDB\Database;
use Mongolid\ActiveRecord;
use Mongolid\Connection\Pool;
use Mongolid\Cursor\CursorInterface;

/**
 * This class extends the Mongolid\ActiveRecord, so, in order
 * to understand the ODM implementation make sure to check the
 * base class.
 */
abstract class BaseEntity extends ActiveRecord
{
    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = null;

    /**
     * Error message bag.
     *
     * @var MessageBag
     */
    public $errors;

    /**
     * List of attribute names which should be hashed on save. For
     * example: array('password');.
     *
     * @var array
     */
    protected $hashedAttributes = [];

    /**
     * Save the model to the database if it's valid.
     *
     * @param bool $force Force save even if the object is invalid.
     *
     * @return bool
     */
    public function save(bool $force = false)
    {
        if ($force || $this->isValid()) {
            $this->hashAttributes();

            return parent::save();
        }

        return false;
    }

    /**
     * Verify if the model is valid by running its validation rules,
     * defined on static attribute `$rules`.
     *
     * @return bool
     */
    public function isValid()
    {
        // Return true if there aren't validation rules
        if (!is_array(static::$rules)) {
            return true;
        }

        // Get the attributes and the rules to validate then
        $attributes = $this->attributes;
        $rules = static::$rules;

        // Verify attributes that are hashed and that have not changed
        // those doesn't need to be validated.
        foreach ($this->hashedAttributes as $hashedAttr) {
            if (isset($this->original[$hashedAttr]) && $this->$hashedAttr == $this->original[$hashedAttr]) {
                unset($rules[$hashedAttr]);
            }
        }

        // Creates validator with attributes and the rules of the object
        $validator = app(ValidationFactory::class)->make($attributes, $rules);

        // Validate and attach errors
        if ($hasErrors = $validator->fails()) {
            $this->errors = $validator->errors();
        }

        return !$hasErrors;
    }

    /**
     * Get the contents of errors attribute.
     *
     * @return MessageBag Validation errors
     */
    public function errors(): MessageBag
    {
        if (!$this->errors) {
            $this->errors = new MessageBag();
        }

        return $this->errors;
    }

    /**
     * Returns the database object (the connection).
     *
     * @return Database
     */
    protected function db(): Database
    {
        $conn = app(Pool::class)->getConnection();
        $database = $conn->defaultDatabase;

        return $conn->getRawConnection()->$database;
    }

    /**
     * Returns the Mongo collection object.
     *
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->db()->{$this->getCollectionName()};
    }

    /**
     * Hashes the attributes specified in the hashedAttributes
     * array.
     *
     * @return void
     */
    protected function hashAttributes()
    {
        foreach ($this->hashedAttributes as $attr) {
            // Hash attribute if changed
            if (!isset($this->original[$attr]) || $this->$attr != $this->original[$attr]) {
                $this->$attr = app(Hasher::class)->make($this->$attr);
            }

            // Removes any confirmation field before saving it into the database
            $confirmationField = $attr.'_confirmation';
            if ($this->$confirmationField) {
                unset($this->$confirmationField);
            }
        }
    }

    /**
     * Returns the model instance as an Array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->getAttributes();

        foreach ($attributes as $key => $value) {
            if ($value instanceof CursorInterface || is_array($value)) {
                $cursor = [];

                foreach ($value as $subDocument) {
                    if ($subDocument instanceof ArrayableInterface || method_exists($subDocument, 'toArray')) {
                        $cursor[] = $subDocument->toArray();
                    } else {
                        $cursor[] = $subDocument;
                    }
                }

                $attributes[$key] = $cursor;
            }
        }

        return $attributes;
    }
}
