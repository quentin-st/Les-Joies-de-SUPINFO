<?php

namespace LjdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="PushRegistration")
 * @ORM\Entity()
 */
class PushRegistration
{
	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="registrationId", unique=true, nullable=false)
	 */
	private $registrationId;


	public static function fromId($id)
	{
		$registration = new PushRegistration();
		$registration->setRegistrationId($id);

		return $registration;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getRegistrationId()
	{
		return $this->registrationId;
	}

	/**
	 * @param string $registrationId
	 * @return PushRegistration
	 */
	public function setRegistrationId($registrationId)
	{
		$this->registrationId = $registrationId;
		return $this;
	}
}
