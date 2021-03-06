<?php

namespace Leadgen\InteractionType;

use Elasticsearch\Client;

/**
 * Updates the Mapping of an InteractionType in Elasticsearch.
 */
class ElasticsearchMapper
{
    /**
     * Elasticsearch client.
     *
     * @var Client
     */
    protected $elasticsearch;

    /**
     * Constructs a new instance.
     *
     * @param Client $elasticsearch Elasticsearch Client to be injected.
     */
    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    /**
     * Updates the mapping of the given InteractionType in ES.
     *
     * @param InteractionType $interactionType InteractionType being updated.
     *
     * @return bool Success
     */
    public function map(InteractionType $interactionType)
    {
        return $this->mapInteractions($interactionType) &&
            $this->mapCustomers($interactionType);
    }

    /**
     * Updates the mapping of the given InteractionType in ES.
     *
     * @param InteractionType $interactionType InteractionType being updated.
     *
     * @return bool Success
     */
    public function mapInteractions(InteractionType $interactionType)
    {
        $indexName = app('config')->get('elasticsearch.defaultIndex', 'main');

        $mapping = [
            'index' => $indexName,
            'type'  => 'Interaction',
            'body'  => [
                'Interaction' => [
                    'properties' => array_merge(
                        $this->buildProperties($interactionType),
                        [
                            'author' => [
                                'type'  => 'string',
                                'index' => 'not_analyzed',
                            ],
                            'authorId' => [
                                'type'  => 'string',
                                'index' => 'not_analyzed',
                            ],
                            'interaction' => [
                                'type'  => 'string',
                                'index' => 'not_analyzed',
                            ],
                            'channel' => [
                                'type'  => 'string',
                                'index' => 'not_analyzed',
                            ],
                            'location' => [
                                'type'  => 'string',
                                'index' => 'not_analyzed',
                            ],
                            'created_at' => [
                                'type'   => 'date',
                                'format' => 'date_hour_minute',
                            ],
                        ]
                    ),
                ],
            ],
        ];

        // Update the index mapping
        $result = $this->elasticsearch->indices()->putMapping($mapping);

        return $result['acknowledged'] ?? false;
    }

    /**
     * Updates the mapping of the given InteractionType in ES.
     *
     * @param InteractionType $interactionType InteractionType being updated.
     *
     * @return bool Success
     */
    public function mapCustomers(InteractionType $interactionType)
    {
        $indexName = app('config')->get('elasticsearch.defaultIndex', 'main');

        $mapping = [
            'index' => $indexName,
            'type'  => 'Customer',
            'body'  => [
                'Customer' => [
                    'dynamic' => false,
                    'properties' => [
                        'docNumber' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed',
                        ],
                        'email' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed',
                        ],
                        'name' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed',
                        ],
                        'location' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed',
                        ],
                        'interactions' => [
                            'type'       => 'nested',
                            'properties' => array_merge(
                                $this->buildProperties($interactionType),
                                [
                                    'interaction' => [
                                        'type'  => 'string',
                                        'index' => 'not_analyzed',
                                    ],
                                    'channel' => [
                                        'type'  => 'string',
                                        'index' => 'not_analyzed',
                                    ],
                                    'location' => [
                                        'type'  => 'string',
                                        'index' => 'not_analyzed',
                                    ],
                                    'created_at' => [
                                        'type'   => 'date',
                                        'format' => 'date_hour_minute',
                                    ],
                                ]
                            ),
                        ],
                        'created_at' => [
                            'type'   => 'date',
                            'format' => 'date_hour_minute',
                        ],
                        'updated_at' => [
                            'type'   => 'date',
                            'format' => 'date_hour_minute',
                        ],
                    ],
                ],
            ],
        ];

        // Update the index mapping
        $result = $this->elasticsearch->indices()->putMapping($mapping);

        return $result['acknowledged'] ?? false;
    }

    /**
     * Build the properties of the given interactionType to be mapped in elasticsearch.
     *
     * @param InteractionType $interactionType That will have its properties parsed for es.
     *
     * @return array
     */
    protected function buildProperties(InteractionType $interactionType)
    {
        $properties = [];

        foreach ($interactionType->params() as $param) {
            $paramEsType = $param->type == 'string' ? 'string' : 'float';
            $properties['params/'.$param->name."/$paramEsType"] = [
                'type'  => $paramEsType,
                'index' => 'not_analyzed',
            ];
        }

        return $properties;
    }
}
