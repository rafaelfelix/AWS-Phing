<?php
require_once "phing/Task.php";
require_once "aws-sdk-for-php/sdk.class.php";

/**
 * Amazon Web Services Core Phing Task
 *
 * @package AWS-Phing
 * @author  James Main <jam@jms.mn>
 */
class AwsTask extends Task
{
    /**
     * Amazon Web Services Key.
     *
     * @var string
     */
    protected $_key = null;

    /**
     * Amazon Web Services Secret Key.
     *
     * @var string
     */
    protected $_secret = null;


    /**
     * Main method
     *
     * @return void
     */
    public function main()
    {
        // Do nothing
    }

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        // Do Nothing
    }

    /**
     * Set Amazon Web Services Key
     *
     * @param string $key AWS Key
     *
     * @return AwsTask
     */
    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    /**
     * Set Amazon Web Services Secret Key
     *
     * @param string $secretKey AWS Secret Key
     *
     * @return AwsTask
     */
    public function setSecret($secret)
    {
        $this->_secret = $secret;
        return $this;
    }
    
    /**
     * Get Options Array for AWS Class Constructors
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
                'key'    => $this->_key,
                'secret' => $this->_secret,
            );
    }
}