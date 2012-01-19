<?php
require_once("AwsTask.php");

/**
 * Create AMI From Instance ID
 * Amazon Web Services Phing Task
 *
 * @package AWS-Phing
 * @author  James Main <jam@jms.mn>
 */
class CreateAmiFromInstanceTask extends AwsTask
{
    const STATE_AVAILABLE = "available";
    const STATE_PENDING   = "pending";

    /**
     * EC2 Region.
     *
     * @var string
     */
    private $_region = null;

    /**
     * Instance ID to create AMI from.
     *
     * @var string
     */
    private $_instanceId = null;

    /**
     * AMI Name.
     *
     * @var string
     */
    private $_name = null;

    /**
     * Poll timeout in seconds.
     *
     * @var integer
     */
    private $_timeout = 300;

    /**
     * Time in seconds between polling requests.
     *
     * @var integer
     */
    private $_pollPeriod = 5;

    /**
     * The build property to set with the resulting AMI ID.
     *
     * @var string
     */
    private $outputProperty = null;

    /**
     * The variable to set with the created ami id
     *
     * @param string $str property name
     *
     * @return void
     */
    public function setOutput($str)
    {
        $this->outputProperty = $str;
    }

    /**
     * Set EC2 region.
     *
     * @param string $str region
     *
     * @return void
     */
    public function setRegion($str)
    {
        $this->_region = $str;
    }

    /**
     * Set Instance ID of instance to create AMI from
     *
     * @param string $str instance id
     *
     * @return void
     */
    public function setInstance($str)
    {
        $this->_instanceId = $str;
    }

    /**
     * Set name of the resulting AMI
     *
     * @param string $str AMI name
     *
     * @return void
     */
    public function setName($str)
    {
        $this->_name = $str;
    }

    /**
     * Set timeout for polling status
     *
     * @param integer $str timeout in seconds
     *
     * @return void
     */
    public function setTimeout($str)
    {
        $this->_timeout = (int)$str;
    }

    /**
     * Set time in seconds between polling requests
     *
     * @param integer $str polling period in seconds
     *
     * @return void
     */
    public function setPollPeriod($str)
    {
        $this->_pollPeriod = (int)$str;
    }

    /**
     * The init method: Do init steps.
     *
     * @return void
     */
    public function init()
    {
      // nothing to do here
    }

    /**
     * Create an AMI Image for the given instance
     *
     * @param AmazonEC2 $ec2        Amazon EC2 Class Instance
     * @param string    $instanceId valid EC2 Instance ID
     * @param string    $imageName  resulting AMI Name
     *
     * @return string
     */
    private function _createImage(AmazonEC2 $ec2, $instanceId, $imageName)
    {
        $response = $ec2->create_image($this->_instanceId, $this->_name);
        return (string)$response->body->imageId;
    }

    /**
     * Get AMI Image State
     *
     * @param AmazonEC2 $ec2     Amazon EC2 Class Instance
     * @param string    $imageId valid EC2 AMI ID
     *
     * @return string
     */
    private function _getImageState(AmazonEC2 $ec2, $imageId)
    {
        $imageStatus = $ec2->describe_images(array("ImageId" => $imageId));
        return (string)$imageStatus->body->imagesSet->item->imageState;
    }

    /**
     * The main entry point method.
     *
     * @return void
     */
    public function main()
    {
        if(is_null($this->_name))
            throw new InvalidArgumentException("Must provide a name for the AWS AMI");

        if(is_null($this->_instanceId))
            throw new InvalidArgumentException("Must provide a valid AWS Instance ID");

        $ec2 = new AmazonEC2(
            array(
             'key'    => $this->_key,
             'secret' => $this->_secretKey,
            )
        );

        if(!is_null($this->_region))
            $ec2->set_region($this->_region);

        $imageId = $this->_createImage($ec2, $this->_instanceId, $this->_name);

        if(is_null($imageId) || $imageId == "")
            throw new UnexpectedValueException("An Image ID was not returned from AWS");

        $startTime = time();
        $status    = self::STATE_PENDING;

        while($status === self::STATE_PENDING)
        {
            if(time() - $startTime >= $this->_timeout)
                throw new Exception("AMI Creation Timeout");

            sleep($this->_pollPeriod);
            $status = $this->_getImageState($ec2, $imageId);
        }

        if($status != self::STATE_AVAILABLE)
            throw new UnexpectedValueException("AMI Status incorrect. Excepted available, returned " . $status);
        
        $this->project->setProperty($this->outputProperty, $imageId);
    }
}
