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
     * @inheritDoc
     */
    public function getPost($name = null, $default = null)
    {
        if ($name === null) {
            return $this->postData;
        }

        return $this->postData[$name] ?? $default;
    }
}
