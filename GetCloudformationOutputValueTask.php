<?php
require_once("AwsTask.php");

/**
 * Gets an output value for a Cloudformation Stack
 * Amazon Web Services Phing Task
 *
 * @package AWS-Phing
 * @author  Rafael Felix Correa <rafael.felix@gmail.com>
 */
class GetCloudformationOutputValueTask extends AwsTask
{
    /**
     * Stack Name.
     *
     * @var string
     */
    private $_stackName = null;

    /**
     * Output Key (to retrieve value from).
     *
     * @var string
     */
    private $_outputKey = null;

    /**
     * The build property to set with the resulting output value.
     *
     * @var string
     */
    private $_outputProperty = null;

    /**
     * The variable to set with the output value retrieved from the API
     *
     * @param string $str property name
     *
     * @return void
     */
    public function setOutput($str)
    {
        $this->_outputProperty = $str;
    }

    /**
     * Set Cloudformation StackName.
     *
     * @param string $str stackName
     *
     * @return void
     */
    public function setStackName($str)
    {
        $this->_stackName = $str;
    }

    /**
     * Set Output Key.
     *
     * @param string $str outputKey
     *
     * @return void
     */
    public function setOutputKey($str)
    {
        $this->_outputKey = $str;
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
     * Get CloudFormation output value
     *
     * @param AmazonCloudFormation $cf        Amazon CloudFormation Class Instance
     * @param string               $stackName Stack Name
     *
     * @return CFResponse
     */
    private function _getOutputValue(AmazonCloudFormation $cf, $stackName)
    {
        $response = $cf->describe_stacks(array("StackName" => $stackName));
        return $response;
    }

    /**
     * The main entry point method.
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     *
     * @return void
     */
    public function main()
    {

        if(is_null($this->_stackName))
            throw new InvalidArgumentException("Must provide a stack name");

        if(is_null($this->_outputKey))
            throw new InvalidArgumentException("Must provide an output key");

        $cf = new AmazonCloudFormation($this->getOptions());

        $describeStacksResponse = $this->_getOutputValue($cf, $this->_stackName);

        if(is_null($describeStacksResponse) || $describeStacksResponse == "")
            throw new UnexpectedValueException("A response for the describe_stacks method was not returned from AWS");

        $outputValue = null;
        foreach($describeStacksResponse->body->DescribeStacksResult->Stacks->member->Outputs as $output) {
            if($output->member->OutputKey == $this->_outputKey) {
                $outputValue = $output->member->OutputValue;
            }
        }

        if(is_null($outputValue))
            throw new UnexpectedValueException("The output {$this->_outputKey} was not found.");

        $this->project->setProperty($this->_outputProperty, $outputValue);
    }
}
