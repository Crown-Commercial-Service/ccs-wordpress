<?php

namespace App\Model;

use App\Traits\SalesforceMappingTrait;

class Lot extends AbstractModel {

    use SalesforceMappingTrait;

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $frameworkId;
    /**
     * @var string
     */
    protected $wordpressId;
    /**
     * @var string
     */
    protected $salesforceId;
    /**
     * @var string
     */
    protected $lotNumber;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $status;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var \DateTime
     */
    protected $expiryDate;

    /**
     * @var string
     */
    protected $publishOnWebsite;

    /**
     * @var bool
     */
    protected $hideSuppliers = false;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Lot
     */
    public function setId(string $id): Lot
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrameworkId(): ?string
    {
        return $this->frameworkId;
    }

    /**
     * @param string $frameworkId
     * @return \App\Model\Lot
     */
    public function setFrameworkId(?string $frameworkId): Lot
    {
        $this->frameworkId = $frameworkId;
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
     * @return Lot
     */
    public function setSalesforceId(?string $salesforceId): Lot
    {
        $this->salesforceId = $salesforceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getWordpressId(): ?string
    {
        return $this->wordpressId;
    }

    /**
     * @param string $wordpressId
     * @return Lot
     */
    public function setWordpressId(?string $wordpressId): Lot
    {
        $this->wordpressId = $wordpressId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLotNumber(): ?string
    {
        return $this->lotNumber;
    }

    /**
     * @param string $lotNumber
     * @return Lot
     */
    public function setLotNumber(?string $lotNumber): Lot
    {
        $this->lotNumber = $lotNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Lot
     */
    public function setTitle(?string $title): Lot
    {
        $this->title = $title;
        return $this;
    }

     /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Lot
     */
    public function setStatus(?string $status): Lot
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Lot
     */
    public function setDescription(?string $description): Lot
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryDate(): ?\DateTime
    {
        if (!$this->expiryDate) {
            return null;
        }
        return $this->expiryDate;
    }

    /**
     * @param \DateTime $expiryDate
     * @param string $format
     * @return Lot
     */
    public function setExpiryDate($expiryDate, $format = 'Y-m-d'): Lot
    {
        if (!$expiryDate instanceof \DateTime)
        {
            $expiryDate = date_create_from_format($format, $expiryDate);
        }

        $this->expiryDate = $expiryDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublishOnWebsite(): ?string
    {
        return $this->publishOnWebsite;
    }

    /**
     * @param string $publishOnWebsite
     * @return Lot
     */
    public function setPublishOnWebsite(?string $publishOnWebsite): Lot
    {
        $this->publishOnWebsite = $publishOnWebsite;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHideSuppliers(): bool
    {
        return $this->hideSuppliers;
    }

    /**
     * @param bool $hideSuppliers
     */
    public function setHideSuppliers(bool $hideSuppliers): void
    {
        $this->hideSuppliers = $hideSuppliers;
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
            'framework_id'        => $this->getFrameworkId(),
            'wordpress_id'        => $this->getWordpressId(),
            'salesforce_id'       => $this->getSalesforceId(),
            'lot_number'          => $this->getLotNumber(),
            'title'               => $this->getTitle(),
            'status'              => $this->getStatus(),
            'description'         => $this->getDescription(),
            'expiry_date'         => !empty($this->getExpiryDate()) ? $this->getExpiryDate()->format('Y-m-d') : null,
            'suppliers'           => '',
        ];
    }
}
