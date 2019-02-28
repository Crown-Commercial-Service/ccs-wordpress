<?php

namespace App\Model;

use App\Traits\SalesforceMappingTrait;
use Nayjest\StrCaseConverter\Str;

class LotSupplier extends AbstractModel {

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $lotId;
    /**
     * @var string
     */
    protected $supplierId;
    /**
     * @var string
     */
    protected $contactName;
    /**
     * @var string
     */
    protected $contactEmail;
    /**
     * @var bool
     */
    protected $websiteContact;
    /**
     * @var string
     */
    protected $tradingName;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return LotSupplier
     */
    public function setId(string $id): LotSupplier
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLotId(): ?string
    {
        return $this->lotId;
    }

    /**
     * @param string $lotId
     * @return LotSupplier
     */
    public function setLotId(string $lotId): LotSupplier
    {
        $this->lotId = $lotId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    /**
     * @param string $supplierId
     * @return LotSupplier
     */
    public function setSupplierId(string $supplierId): LotSupplier
    {
        $this->supplierId = $supplierId;
        return $this;
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
     * @return LotSupplier
     */
    public function setContactName(?string $contactName): LotSupplier
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
     * @return LotSupplier
     */
    public function setContactEmail(?string $contactEmail): LotSupplier
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWebsiteContact(): bool
    {
        if (is_null($this->websiteContact))
        {
            return false;
        }

        return $this->websiteContact;
    }

    /**
     * @param bool $websiteContact
     * @return LotSupplier
     */
    public function setWebsiteContact(?bool $websiteContact): LotSupplier
    {
        if (!empty($websiteContact))
        {
            $this->websiteContact = $websiteContact;
        } else {
            $this->websiteContact = false;
        }

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
     * @param string $tradingName
     * @return LotSupplier
     */
    public function setTradingName(?string $tradingName): LotSupplier
    {
        $this->tradingName = $tradingName;
        return $this;
    }



}
