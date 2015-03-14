<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MeasurementProbe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\MeasurementProbeRepository")
 */
class MeasurementProbe
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Measurement
     *
     * @ORM\ManyToOne(targetEntity="Measurement")
     * @ORM\JoinColumn(name="measurementId", referencedColumnName="id")
     */
    private $measurement;

    /**
     * @var Probe
     *
     * @ORM\ManyToOne(targetEntity="Probe")
     * @ORM\JoinColumn(name="probeId", referencedColumnName="id")
     */
    private $probe;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="measurementProbes.deaultName.notBlank")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=9)
     * @Assert\NotBlank(message="measurementProbes.Color.notBlank")
     * @Assert\Regex(pattern="/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/", message="probes.Color.notBlank")
     */
    private $color;

    /**
     * @var integer
     *
     * @ORM\Column(name="min", type="smallint")
     */
    private $min;

    /**
     * @var integer
     *
     * @ORM\Column(name="max", type="smallint")
     */
    private $max;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set measurement
     *
     * @param Measurement $measurement
     * @return MeasurementProbe
     */
    public function setMeasurement(Measurement $measurement)
    {
        $this->measurement = $measurement;

        return $this;
    }

    /**
     * Get measurement
     *
     * @return Measurement
     */
    public function getMeasurement()
    {
        return $this->measurement;
    }

    /**
     * Set probe
     *
     * @param Probe $probe
     * @return MeasurementProbe
     */
    public function setProbe(Probe $probe)
    {
        $this->probe = $probe;

        return $this;
    }

    /**
     * Get probe
     *
     * @return Probe
     */
    public function getProbe()
    {
        return $this->probe;
    }

    /**
     * Set Name
     *
     * @param string $name
     * @return Probe
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Color
     *
     * @param string $color
     * @return Probe
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get Color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }
}