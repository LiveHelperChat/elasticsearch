<?php

namespace LiveHelperChatExtension\elasticsearch\providers\Helpers;

#[\AllowDynamicProperties]
class Embeder
{
    private static $instance = null;

    private $embedServerUrl;
    private $timeout = 60;

    public static function getInstance($embedServerUrl = null, $timeout = 60)
    {
        if (self::$instance === null) {
            self::$instance = new self($embedServerUrl, $timeout);
        }

        return self::$instance;
    }

    private function __construct($embedServerUrl = null, $timeout = 60)
    {
        if ($embedServerUrl === null) {
            $esSettings = \erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings;
            $embedServerUrl = $esSettings['embed_server'] ?? '';

            if (isset($esSettings['embed_server_url']) && $esSettings['embed_server_url'] != '') {
                $embedServerUrl = $esSettings['embed_server_url'];
            }

            if (isset($esSettings['embed_server_timeout']) && $esSettings['embed_server_timeout'] > 0) {
                $this->timeout = (int)$esSettings['embed_server_timeout'];
            }
        }

        $this->embedServerUrl = rtrim($embedServerUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Get embeddings for multiple documents.
     * Handles chunking server-side.
     *
     * @param array $docs Array of document texts
     * @return array Response with 'embeddings' and 'chunk_texts'
     * @throws \Exception on failure
     */
    public function embedDocuments(array $docs)
    {
        $response = $this->request('/embed_documents', array('docs' => $docs));

        if (!isset($response['embeddings']) || !isset($response['chunk_texts'])) {
            throw new \Exception('Unexpected response format from embed server');
        }

        return $response;
    }

    /**
     * Get embedding for a single query string.
     *
     * @param string $query
     * @return array Response with 'embed' key containing the vector
     * @throws \Exception on failure
     */
    public function embedQuery($query)
    {
        $response = $this->request('/embed_query', array('query' => $query));

        if (!isset($response['embed'])) {
            throw new \Exception('Invalid embed response from embed server');
        }

        return $response;
    }

    /**
     * Get the configured embed server URL.
     *
     * @return string
     */
    public function getServerUrl()
    {
        return $this->embedServerUrl;
    }

    /**
     * Low-level POST request to the embed server.
     *
     * @param string $endpoint URL path (e.g. '/embed_documents')
     * @param array  $payload  Request body
     * @return array Decoded JSON response
     * @throws \Exception on connection, HTTP, or JSON errors
     */
    private function request($endpoint, array $payload)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->embedServerUrl . $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);

        if ($curlError) {
            throw new \Exception('cURL error: ' . $curlError);
        }

        $response = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from embed server');
        }

        if (isset($response['error'])) {
            throw new \Exception('Embed server error: ' . $response['error']);
        }

        return $response;
    }
}
