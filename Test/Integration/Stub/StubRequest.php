<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Backend\Test\Integration\Stub;

use Laminas\Stdlib\Parameters;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Stub Request class for testing.
 * 
 * Provides a testable HTTP request implementation that allows setting POST data
 * without requiring an actual HTTP request. Useful for integration tests that need
 * to simulate request data.
 * 
 * Usage:
 * ```php
 * $stubRequest = $objectManager->create(StubRequest::class);
 * $stubRequest->setPostData(['key' => 'value']);
 * $value = $stubRequest->getPost('key'); // Returns 'value'
 * ```
 */
class StubRequest extends HttpRequest
{
    /**
     * @var array
     */
    private array $postData = [];

    /**
     * Set POST data for testing
     *
     * @param array|Parameters $data POST data as array or Parameters object
     * @return void
     */
    public function setPostData($data): void
    {
        if ($data instanceof Parameters) {
            $this->postData = $data->toArray();
        } else {
            $this->postData = $data;
        }
    }

    /**
     * Get POST data
     *
     * @param string|null $name Parameter name or null to get all data
     * @param mixed $default Default value if parameter not found
     * @return mixed
     */
    public function getPost($name = null, $default = null)
    {
        if ($name === null) {
            return $this->postData;
        }
        return $this->postData[$name] ?? $default;
    }
}
