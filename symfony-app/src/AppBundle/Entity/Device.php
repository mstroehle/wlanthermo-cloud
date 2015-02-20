<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Device
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\DeviceRepository")
 * @UniqueEntity("name", message="devices.name.uniqueEntity")
 * @UniqueEntity("url", message="devices.url.uniqueEntity")
 */
class Device
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="devices.name.notBlank")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="devices.url.notBlank")
     * @Assert\Url(message="devices.url.url")
     */
    private $url;

    // ToDo Min max probes

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Probe", mappedBy="device", cascade={"persist"})
     */
    private $probes;

    /**
     * Instantiates the Device.
     */
    public function __construct()
    {
        $this->probes = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return Device
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Device
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns the associated probes.
     *
     * @return ArrayCollection
     */
    public function getProbes()
    {
        return $this->probes;
    }

    /**
     * Adds a probe.
     *
     * @param Probe $probe
     */
    public function addProbe(Probe $probe)
    {
        $probe->setDevice($this);
        $this->probes->add($probe);
    }

    /**
     * Removes a probe.
     *
     * @param Probe $probe
     */
    public function removeProbe(Probe $probe)
    {
        $this->probes->removeElement($probe);
    }

    /**
     * Adds two default probes to the device.
     */
    public function addDefaultProbes()
    {
        $tmpProbe1 = new Probe();
        $tmpProbe1->setChannel(0);
        $tmpProbe1->setType(1);
        $tmpProbe1->setDefaultName('Garraum');
        $tmpProbe1->setDefaultColor('#FF0000FF');
        $this->addProbe($tmpProbe1);

        $tmpProbe2 = new Probe();
        $tmpProbe2->setChannel(1);
        $tmpProbe2->setType(1);
        $tmpProbe2->setDefaultName('Gargut');
        $tmpProbe2->setDefaultColor('#00FF00FF');
        $this->addProbe($tmpProbe2);
    }
}
