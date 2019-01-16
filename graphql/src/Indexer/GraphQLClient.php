<?php

namespace App\Indexer;

use ErrorException;

/**
 * A really basic GraphQL client.
 */
class GraphQLClient
{
    /**
     * @var string $endpoint    API endpoint.
     */
    private $endpoint;

    /**
     * @var string $token       OAuth2 token.
     */
    private $token;

    /**
     * Initializes a new GraphQLClient instance.
     *
     * @param   string  $endpoint   API endpoint
     * @param   string  $token      OAuth2 token
     * @return  void
     */
    public function __construct($endpoint, $token)
    {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    /**
     * Executes provided query, with given parameters (if any).
     *
     * @see https://gist.github.com/dunglas/05d901cb7560d2667d999875322e690a
     *
     * @param   string      $query      GraphQL query
     * @param   array|null  $variables  Optional variables, as associative array
     * @throws  ErrorException
     * @return  array
     */
    public function execute($query, $params)
    {
        /** @var array $streamOptions */
        $streamOptions = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'User-Agent: Simple GraphQL client',
                    sprintf('Authorization: bearer %s', $this->token)
                ],
                'content' => json_encode([
                    'query' => $query,
                    'variables' => $params
                ]),
            ],
        ];

        /** @var string|false $data */
        $data = @file_get_contents(
            $this->endpoint,
            false,
            stream_context_create($streamOptions)
        );

        // Catch low level errors
        if ($data === false) {
            $error = error_get_last();
            throw new ErrorException($error['message'], $error['type']);
        }

        $jsonData = json_decode($data, true);

        // Catch high level errors
        if (isset($jsonData['errors'])) {
            throw new ErrorException($jsonData['errors'][0]['message']);
        }

        return $jsonData;
    }
}
