<?php

namespace App\Model;

use App\Traits\SalesforceMappingTrait;

class Supplier extends AbstractModel
{
    use SalesforceMappingTrait;

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $salesforceId;
    /**
     * @var string
     */
    protected $dunsNumber;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $phoneNumber;
    /**
     * @var string
     */
    protected $street;
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $postcode;
    /**
     * @var string
     */
    protected $website;
    /**
     * @var string
     */
    protected $contactEmail;
    /**
     * @var string
     */
    protected $contactName;
    /**
     * @var string
     */
    protected $tradingName;
    /**
     * @var array
     */
    protected $alternativeTradingNames;
    /**
     * @var bool
     */
    protected $onLiveFrameworks = false;
    /**
     * @var bool
     */
    protected $haveGuarantor = false;
    
    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Supplier
     */
    public function setId(?string $id): Supplier
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalesforceId(): ?string
    {
        return $this->salesforceId;
    }

    /**
     * @param string $salesforceId
     * @return Supplier
     */
    public function setSalesforceId(?string $salesforceId): Supplier
    {
        $this->salesforceId = $salesforceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDunsNumber(): ?string
    {
        return $this->dunsNumber;
    }

    /**
     * @param string $dunsNumber
     * @return Supplier
     */
    public function setDunsNumber(?string $dunsNumber): Supplier
    {
        $this->dunsNumber = $dunsNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Supplier
     */
    public function setName(?string $name): Supplier
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return Supplier
     */
    public function setPhoneNumber(?string $phoneNumber): Supplier
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return Supplier
     */
    public function setStreet(?string $street): Supplier
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Supplier
     */
    public function setCity(?string $city): Supplier
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return Supplier
     */
    public function setCountry(?string $country): Supplier
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     * @return Supplier
     */
    public function setPostcode(?string $postcode): Supplier
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @param string $website
     * @return Supplier
     */
    public function setWebsite(?string $website): Supplier
    {
        $this->website = $website;
        return $this;
    }

    /**
     * @return string
     */
    public function getTradingName(): ?string
    {
        return $this->tradingName;
    }

    /**
     * @return string
     */
    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    /**
     * @param string $contactName
     * @return string
     */
    public function setContactName($contactName): Supplier
    {
        $this->contactName = $contactName;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }
    /**
     * @param string $contactEmail
     * @return string
     */
    public function setContactEmail($contactEmail): Supplier
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    /**
     * @param string $tradingName
     * @return Supplier
     */
    public function setTradingName(?string $tradingName): Supplier
    {
        $this->tradingName = $tradingName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnLiveFrameworks(): ?bool
    {
        return $this->onLiveFrameworks;
    }

    /**
     * @param bool $onLiveFrameworks
     * @return Supplier
     */
    public function setOnLiveFrameworks(?bool $onLiveFrameworks): Supplier
    {
        $this->onLiveFrameworks = $onLiveFrameworks;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHaveGuarantor(): ?bool
    {
        return $this->haveGuarantor;
    }

    /**
     * @param bool $haveGuarantor
     * @return Supplier
     */
    public function setHaveGuarantor(?bool $haveGuarantor): Supplier
    {
        $this->haveGuarantor = $haveGuarantor;
        return $this;
    }
    /**
     * @return array
     */
    public function getAlternativeTradingNames(): ?array
    {
        return $this->alternativeTradingNames;
    }

    /**
     * @param array $alternativeTradingNames
     * @return \App\Model\Supplier
     */
    public function setAlternativeTradingNames(array $alternativeTradingNames): Supplier
    {
        $this->alternativeTradingNames = $alternativeTradingNames;

        return $this;
    }

    /**
     * Returns a simple text array representing the object
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'                  => $this->getId(),
            'salesforce_id'       => $this->getSalesforceId(),
            'duns_number'         => $this->getDunsNumber(),
            'name'                => $this->getName(),
            'phone_number'        => $this->getPhoneNumber(),
            'street'              => $this->getStreet(),
            'city'                => $this->getCity(),
            'country'             => $this->getCountry(),
            'postcode'            => $this->getPostcode(),
            'website'             => $this->getWebsite(),
            'trading_name'        => $this->getTradingName(),

        ];
    }
}
