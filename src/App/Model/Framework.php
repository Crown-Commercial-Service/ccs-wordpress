<?php

namespace App\Model;

use App\Traits\SalesforceMappingTrait;
use App\Utils\YamlLoader;
use \Datetime;

class Framework extends AbstractModel
{
    use SalesforceMappingTrait;

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $rmNumber;
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
    protected $title;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $summary;
    /**
     * @var string
     */
    protected $availability;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $cannotUse;
    /**
     * @var string
     */
    protected $updates;
    /**
     * @var string
     */
    protected $benefits;
    /**
     * @var array
     */
    protected $lots;
    /**
     * @var string
     */
    protected $howToBuy;
    /**
     * @var string
     */
    protected $terms;
    /**
     * @var string
     */
    protected $pillar;
    /**
     * @var string
     */
    protected $category;
    /**
     * @var string
     */
    protected $status;
    /**
     * @var string
     */
    protected $regulation;
    /**
     * @var string
     */
    protected $regulationType;
    /**
     * @var string
     */
    protected $policyCompliance;
    /**
     * @var \DateTime
     */
    protected $startDate;
    /**
     * @var \DateTime
     */
    protected $endDate;
    /**
     * @var \DateTime
     */
    protected $tendersOpenDate;
    /**
     * @var \DateTime
     */
    protected $tendersCloseDate;
    /**
     * @var \DateTime
     */
    protected $expectedLiveDate;
    /**
     * @var \DateTime
     */
    protected $expectedAwardDate;
    /**
     * @var string
     */
    protected $documentUpdates;
    /**
     * @var array
     */
    protected $documents;
    /**
     * @var bool
     */
    protected $publishOnWebsite;
    /**
     * @var string
     */
    protected $publishedStatus = 'draft';
    /**
     * @var string
     */
    protected $keywords;

    /**
     * @var string
     */
    protected $createDraftPage;

    /**
     * @var string
     */
    protected $upcomingDealDetails;

    /**
     * @var string
     */
    protected $upcomingDealSummary;


    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Framework
     */
    public function setId(string $id): Framework
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getRmNumber(): ?string
    {
        return $this->rmNumber;
    }

    /**
     * @param string $rmNumber
     * @return Framework
     */
    public function setRmNumber(?string $rmNumber): Framework
    {
        $this->rmNumber = $rmNumber;
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
     * @return Framework
     */
    public function setSalesforceId(?string $salesforceId): Framework
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
     * @return Framework
     */
    public function setWordpressId(?string $wordpressId): Framework
    {
        $this->wordpressId = $wordpressId;
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
     * @return Framework
     */
    public function setTitle(?string $title): Framework
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Framework
     */
    public function setType(?string $type): Framework
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     * @return Framework
     */
    public function setSummary(?string $summary): Framework
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    /**
     * @param string $availability
     * @return Framework
     */
    public function setAvailability(?string $availability): Framework
    {
        $this->availability = $availability;
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
     * @return Framework
     */
    public function setDescription(?string $description): Framework
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getCannotUse(): ?string
    {
        return $this->cannotUse;
    }

    /**
     * @param string $cannotUse
     * @return Framework
     */
    public function setCannotUse(?string $cannotUse): Framework
    {
        $this->cannotUse = $cannotUse;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdates(): ?string
    {
        return $this->updates;
    }

    /**
     * @param string $updates
     * @return Framework
     */
    public function setUpdates(?string $updates): Framework
    {
        $this->updates = $updates;
        return $this;
    }

    /**
     * @return string
     */
    public function getBenefits(): ?string
    {
        return $this->benefits;
    }

    /**
     * @param string $benefits
     * @return Framework
     */
    public function setBenefits(?string $benefits): Framework
    {
        $this->benefits = $benefits;
        return $this;
    }

    /**
     * @return array
     */
    public function getLots(): array
    {
        return $this->lots;
    }

    /**
     * @param array $lots
     * @return Framework
     */
    public function setLots(?array $lots): Framework
    {
        $this->lots = $lots;
        return $this;
    }

    /**
     * @param array $lots
     */
    public function getLotIds(): ?array
    {
        $ids = [];
        /** @var \App\Model\Lot $lot */
        foreach ($this->getLots() as $lot) {
            $ids[] = $lot->getId();
        }

        return $ids;
    }

    /**
     * @return string
     */
    public function getHowToBuy(): ?string
    {
        return $this->howToBuy;
    }

    /**
     * @param string $howToBuy
     * @return Framework
     */
    public function setHowToBuy(?string $howToBuy): Framework
    {
        $this->howToBuy = $howToBuy;
        return $this;
    }

    /**
     * @return string
     */
    public function getTerms(): ?string
    {
        return $this->terms;
    }

    /**
     * @param string $terms
     * @return Framework
     */
    public function setTerms(?string $terms): Framework
    {
        $this->terms = $terms;
        return $this;
    }

    /**
     * @return string
     */
    public function getPillar(): ?string
    {
        return $this->pillar;
    }

    /**
     * @param string $pillar
     * @return Framework
     */
    public function setPillar(?string $pillar): Framework
    {
        $this->pillar = $pillar;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return Framework
     */
    public function setCategory(?string $category): Framework
    {
        $this->category = $category;
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
     * @return Framework
     */
    public function setStatus(?string $status): Framework
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegulationType(): ?string
    {
        return $this->regulationType;
    }

    /**
     * @param string $regulationType
     * @return Framework
     */
    public function setRegulationType(?string $regulationType): Framework
    {
        $this->regulationType = $regulationType;
        return $this;
    }

    /**
     * @return string
     */
    public function getPolicyCompliance(): ?string
    {
        return $this->policyCompliance;
    }

    /**
     * @param string $regulationType
     * @return Framework
     */
    public function setPolicyCompliance(?string $policyCompliance): Framework
    {
        $this->policyCompliance = $policyCompliance;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): ?\DateTime
    {
        if (!$this->startDate) {
            return null;
        }

        return is_string($this->startDate)? new DateTime($this->startDate) : $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @param string $format
     * @return Framework
     */
    public function setStartDate($startDate, $format = 'Y-m-d'): Framework
    {
        if ($startDate === null) {
            $this->startDate = null;
            return $this;
        }

        if (!$startDate instanceof \DateTime) {
            if (!$startDate = date_create_from_format($format, $startDate)) {
                $startDate = null;
            }
        }

        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): ?\DateTime
    {
        if (!$this->endDate) {
            return null;
        }

        return is_string($this->endDate)? new DateTime($this->endDate) : $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @param string $format
     * @return Framework
     */
    public function setEndDate($endDate, $format = 'Y-m-d'): Framework
    {
        if ($endDate === null) {
            $this->endDate = null;
            return $this;
        }

        if (!$endDate instanceof \DateTime) {
            if (!$endDate = date_create_from_format($format, $endDate)) {
                $endDate = null;
            }
        }

        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTendersOpenDate(): ?\DateTime
    {
        if (!$this->tendersOpenDate) {
            return null;
        }

        return is_string($this->tendersOpenDate)? new DateTime($this->tendersOpenDate) : $this->tendersOpenDate;
    }

    /**
     * @param $tendersOpenDate
     * @param string $format
     * @return Framework
     */
    public function setTendersOpenDate($tendersOpenDate, $format = 'Y-m-d'): Framework
    {
        if ($tendersOpenDate === null) {
            $this->tendersOpenDate = null;
            return $this;
        }

        if (!$tendersOpenDate instanceof \DateTime) {
            if (!$tendersOpenDate = date_create_from_format($format, $tendersOpenDate)) {
                $tendersOpenDate = null;
            }
        }

        $this->tendersOpenDate = $tendersOpenDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTendersCloseDate(): ?\DateTime
    {
        if (!$this->tendersCloseDate) {
            return null;
        }

        return is_string($this->tendersCloseDate)? new DateTime($this->tendersCloseDate) : $this->tendersCloseDate;
    }

    /**
     * @param $tendersCloseDate
     * @param string $format
     * @return Framework
     */
    public function setTendersCloseDate($tendersCloseDate, $format = 'Y-m-d'): Framework
    {
        if ($tendersCloseDate === null) {
            $this->tendersCloseDate = null;
            return $this;
        }

        if (!$tendersCloseDate instanceof \DateTime) {
            if (!$tendersCloseDate = date_create_from_format($format, $tendersCloseDate)) {
                $tendersCloseDate = null;
            }
        }

        $this->tendersCloseDate = $tendersCloseDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpectedLiveDate(): ?\DateTime
    {
        if (!$this->expectedLiveDate) {
            return null;
        }

        return is_string($this->expectedLiveDate)? new DateTime($this->expectedLiveDate) : $this->expectedLiveDate;
    }

    /**
     * @param \DateTime $expectedLiveDate
     * @param string $format
     * @return Framework
     */
    public function setExpectedLiveDate($expectedLiveDate, $format = 'Y-m-d'): Framework
    {
        if ($expectedLiveDate === null) {
            $this->expectedLiveDate = null;
            return $this;
        }
        if (!$expectedLiveDate instanceof \DateTime) {
            if (!$expectedLiveDate = date_create_from_format($format, $expectedLiveDate)) {
                $expectedLiveDate = null;
            }
        }

        $this->expectedLiveDate = $expectedLiveDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpectedAwardDate(): ?\DateTime
    {
        if (!$this->expectedAwardDate) {
            return null;
        }

        return is_string($this->expectedAwardDate)? new DateTime($this->expectedAwardDate) : $this->expectedAwardDate;
    }

    /**
     * @param $expectedAwardDate
     * @param string $format
     * @return Framework
     */
    public function setExpectedAwardDate($expectedAwardDate, $format = 'Y-m-d'): Framework
    {
        if ($expectedAwardDate === null) {
            $this->expectedAwardDate = null;
            return $this;
        }

        if (!$expectedAwardDate instanceof \DateTime) {
            if (!$expectedAwardDate = date_create_from_format($format, $expectedAwardDate)) {
                $expectedAwardDate = null;
            }
        }

        $this->expectedAwardDate = $expectedAwardDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentUpdates(): ?string
    {
        return $this->documentUpdates;
    }

    /**
     * @param string $documentUpdates
     * @return Framework
     */
    public function setDocumentUpdates(?string $documentUpdates): Framework
    {
        $this->documentUpdates = $documentUpdates;
        return $this;
    }

    /**
     * @return array
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @param array $documents
     * @return Framework
     */
    public function setDocuments(array $documents): Framework
    {
        $this->documents = $documents;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublishOnWebsite(): bool
    {
        if (is_null($this->publishOnWebsite)) {
            return false;
        }
        return $this->publishOnWebsite;
    }

    /**
     * @param bool $publishOnWebsite
     * @return \App\Model\Framework
     */
    public function setPublishOnWebsite(?bool $publishOnWebsite): Framework
    {
        if (!empty($publishOnWebsite)) {
            $this->publishOnWebsite = $publishOnWebsite;
        } else {
            $this->publishOnWebsite = false;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPublishedStatus(): ?string
    {
        return $this->publishedStatus;
    }

    /**
     * @param string $publishedStatus
     * @return Framework
     */
    public function setPublishedStatus(?string $publishedStatus): Framework
    {
        $this->publishedStatus = $publishedStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     * @return Framework
     */
    public function setKeywords(?string $keywords): Framework
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return string
     */
    public function getcreateDraftPage(): ?string
    {
        return $this->createDraftPage;
    }

    /**
     * @param string $createDraftPage
     * @return Framework
     */
    public function setcreateDraftPage(?string $createDraftPage): Framework
    {
        $this->createDraftPage = $createDraftPage;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpcomingDealDetails(): ?string
    {
        return $this->upcomingDealDetails;
    }

    /**
     * @param string $upcomingDealDetails
     * @return Framework
     */
    public function setUpcomingDealDetails(?string $upcomingDealDetails): Framework
    {
        $this->upcomingDealDetails = $upcomingDealDetails;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpcomingDealSummary(): ?string
    {
        return $this->upcomingDealSummary;
    }

    /**
     * @param string $upcomingDealSummary
     * @return Framework
     */
    public function setUpcomingDealSummary(?string $upcomingDealSummary): Framework
    {
        $this->upcomingDealSummary = $upcomingDealSummary;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegulation(): ?string
    {
        return $this->regulation;
    }

    /**
     * @param string $regulation
     * @return Framework
     */
    public function setRegulation(?string $regulation): Framework
    {
        $this->regulation = $regulation;
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
          'id'                      => $this->getId(),
          'rm_number'               => $this->getRmNumber(),
          'wordpress_id'            => $this->getWordpressId(),
          'salesforce_id'           => $this->getSalesforceId(),
          'title'                   => $this->getTitle(),
          'type'                    => $this->getType(),
          'summary'                 => $this->getSummary(),
          'availability'            => $this->getAvailability(),
          'description'             => $this->getDescription(),
          'cannot_use'              => $this->getCannotUse(),
          'updates'                 => $this->getUpdates(),
          'benefits'                => $this->getBenefits(),
          'how_to_buy'              => $this->getHowToBuy(),
          'terms'                   => $this->getTerms(),
          'pillar'                  => $this->getPillar(),
          'category'                => $this->getCategory(),
          'status'                  => $this->getStatus(),
          'regulation'              => $this->getRegulation(),
          'regulation_type'         => $this->getRegulationType(),
          'policy_compliance'       => $this->getPolicyCompliance(),
          'start_date'              => !empty($this->getStartDate()) ? $this->getStartDate()->format('Y-m-d') : null,
          'end_date'                => !empty($this->getEndDate()) ? $this->getEndDate()->format('Y-m-d') : null,
          'tenders_open_date'       => !empty($this->getTendersOpenDate()) ? $this->getTendersOpenDate()->format('Y-m-d') : null,
          'tenders_close_date'      => !empty($this->getTendersCloseDate()) ? $this->getTendersCloseDate()->format('Y-m-d') : null,
          'expected_live_date'      => !empty($this->getExpectedLiveDate()) ? $this->getExpectedLiveDate()->format('Y-m-d') : null,
          'expected_award_date'     => !empty($this->getExpectedAwardDate()) ? $this->getExpectedAwardDate()->format('Y-m-d') : null,
          'document_updates'        => $this->getDocumentUpdates(),
          'lots'                    => null,
          'documents'               => null,
          'published_status'        => $this->getPublishedStatus(),
          'keywords'                => $this->getKeywords(),
          'upcoming_deal_details'   => $this->getUpcomingDealDetails(),
          'upcoming_deal_summary'   => $this->getUpcomingDealSummary(),
        ];
    }

    public function setData($data) {
        $mappings = YamlLoader::loadMappings('MDM_agreement');

        foreach ($mappings as $property => $apiField) {
            if (array_key_exists($apiField, $data) && property_exists($this, $property)) {
                $this->$property = $data[$apiField];
            }
        }
    }
}
