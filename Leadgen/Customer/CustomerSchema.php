<?php

namespace Leadgen\Customer;

use Leadgen\Interaction\InteractionSchema;
use Mongolid\Schema;

/**
 * The CustomerSchema defines how a Customer document will look like.
 *
 * @SWG\Definition(
 *     type="object",
 *     definition="Customer",
 * )
 */
class CustomerSchema extends Schema
{
    /**
     * Name of the collection where this kind of Entity is going to be saved or
     * retrieved from.
     *
     * @var string
     */
    public $collection = 'customers';

    /**
     * Name of the class that will be used to represent a document of this
     * Schema when retrieve from the database.
     *
     * @var string
     */
    public $entityClass = Customer::class;

    /**
     * Tells how a document should look like.
     *
     * @var string[]
     * @SWG\Property(
     *     property="_id",
     *     type="string",
     *     description="Unique identifier of the customer. (Generated automatically)"
     * ),
     * @SWG\Property(
     *     property="docNumber",
     *     type="string",
     *     description="An document number that identify this customer. May be an CRM number for example."
     * ),
     * @SWG\Property(
     *     property="email",
     *     type="string",
     *     description="Email of the customer."
     * ),
     * @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Name of the customer."
     * ),
     * @SWG\Property(
     *     property="interactions",
     *     type="array",
     *     description="Interactions that this customer has made.",
     *     @SWG\Items(
     *         ref="#/definitions/Interaction",
     *     )
     * ),
     * @SWG\Property(
     *     property="location",
     *     type="string",
     *     default="web",
     *     description="Tells in which location the customer was in his last interaction."
     * ),
     * @SWG\Property(
     *     property="segments",
     *     type="array",
     *     description="_id of the segments that the customer is part of.",
     *     @SWG\Items(type="string")
     * ),
     * @SWG\Property(
     *     property="aggregated",
     *     type="array",
     *     description="Pre aggregated or calculated values that may be later filled.",
     *     @SWG\Items(type="object")
     * ),
     */
    public $fields = [
        '_id'           => 'string',
        'docNumber'     => 'string',
        'email'         => 'string',
        'name'          => 'string',
        'interactions'  => 'schema.'.InteractionSchema::class,
        'location'      => 'string',
        'segments'      => 'forceArray',
        'aggregated'    => 'forceArray',
        'created_at'    => 'createdAtTimestamp',
        'updated_at'    => 'updatedAtTimestamp',
        'interacted_at' => 'createdAtTimestamp', // But will be updated manually
    ];

    /**
     * Forces that the field is an array
     *
     * @param  mixed $value Original (or non existent) value.
     *
     * @return array
     */
    public function forceArray($value): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return $value;
    }
}
