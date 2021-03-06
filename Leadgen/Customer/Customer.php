<?php

namespace Leadgen\Customer;

use Leadgen\Base\BaseEntity;
use Leadgen\Interaction\Interaction;
use Leadgen\Segment\Repository as SegmentRepository;
use Mongolid\Cursor\CursorInterface;

/**
 * Represents an individual customer.
 */
class Customer extends BaseEntity
{
    /**
     * Describes the Schema fields of the model.
     *
     * @var string
     */
    protected $fields = CustomerSchema::class;

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = [
        'email' => 'email',
    ];

    /**
     * Customer embeds many Interaction.
     *
     * @return CursorInterface
     */
    public function interactions(): CursorInterface
    {
        return $this->embedsMany(Interaction::class, 'interactions');
    }

    /**
     * Customer belongs to many segments.
     *
     * @return CursorInterface
     */
    public function segments(): CursorInterface
    {
        return app()->make(SegmentRepository::class)
            ->where(['slug' => ['$in' => array_values($this->segments)]]);
    }
}
