<?php

namespace AppBundle\Constraint;


use AppBundle\Entity\Device;
use AppBundle\Service\DeviceAPIServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class DeviceAuthenticationConstraintValidator
 * @package AppBundle\Constraint
 */
class DeviceAuthenticationConstraintValidator extends ConstraintValidator
{
    /** @var DeviceAPIServiceInterface */
    private $deviceService;

    /**
     * Instantiates the service.
     *
     * @param DeviceAPIServiceInterface $deviceService
     */
    public function __construct(DeviceAPIServiceInterface $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var Device $device */
        $device = $value;

        if (!$this->deviceService->checkAuthentication($device)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
