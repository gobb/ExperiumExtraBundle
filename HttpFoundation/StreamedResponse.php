<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Experium\ExtraBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * StreamedResponse represents a streamed HTTP response.
 *
 * A StreamedResponse uses a callback for its content.
 *
 * The callback should use the standard PHP functions like echo
 * to stream the response back to the client. The flush() method
 * can also be used if needed.
 *
 * @see flush()
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class StreamedResponse extends Response
{
    protected $callback;
    protected $streamed;

    /**
     * Constructor.
     *
     * @param mixed   $callback A valid PHP callback
     * @param integer $status   The response status code
     * @param array   $headers  An array of response headers
     *
     * @api
     */
    public function __construct($callback = null, $status = 200, $headers = array())
    {
        parent::__construct(null, $status, $headers);

        if (null !== $callback) {
            $this->setCallback($callback);
        }
        $this->streamed = false;
    }

    /**
     * Sets the PHP callback associated with this Response.
     *
     * @param mixed $callback A valid PHP callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        if (!is_callable($this->callback)) {
            throw new \LogicException('The Response callback must be a valid PHP callable.');
        }
    }

    /**
     * @{inheritdoc}
     */
    public function prepare()
    {
        $this->headers->set('Cache-Control', 'no-cache');

        parent::prepare();
    }

    /**
     * @{inheritdoc}
     *
     * This method only sends the content once.
     */
    public function sendContent()
    {
        if ($this->streamed) {
            return;
        }

        $this->streamed = true;

        if (null === $this->callback) {
            throw new \LogicException('The Response callback must not be null.');
        }

        call_user_func($this->callback);
    }

    /**
     * @{inheritdoc}
     *
     * @throws \LogicException when the content is not null
     */
    public function setContent($content)
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a StreamedResponse instance.');
        }
    }

    /**
     * @{inheritdoc}
     *
     * @return false
     */
    public function getContent()
    {
        return false;
    }
}