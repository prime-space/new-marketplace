<?php namespace App\Entity;

use App\Api\Item\Admin\AdminApi;
use Ewll\DBBundle\Annotation as Db;
use Symfony\Contracts\Translation\TranslatorInterface;

class Product
{
    const FIELD_USER_ID = 'userId';
    const FIELD_STATUS_ID = 'statusId';
    const FIELD_IMPORT_ID = 'importId';
    const FIELD_NAME = 'name';
    const FIELD_SALES_NUM = 'salesNum';
    const FIELD_IN_STOCK_NUM = 'inStockNum';
    const FIELD_REVIEWS_NUM = 'reviewsNum';
    const FIELD_GOOD_REVIEWS_NUM = 'goodReviewsNum';
    const FIELD_REVIEWS_PERCENT = 'reviewsPercent';
    const FIELD_GROUP_IDS = 'productGroupIds';

    const TYPE_ID_UNIVERSAL = 1;
    const TYPE_ID_UNIQUE = 2;

    const TYPES = [
        self::TYPE_ID_UNIVERSAL,
        self::TYPE_ID_UNIQUE,
    ];

    const STATUS_ID_FETUS = 1;
    const STATUS_ID_DRAFT = 2;
    const STATUS_ID_VERIFICATION = 3;
    const STATUS_ID_REJECTED = 4;
    const STATUS_ID_OK = 5;
    const STATUS_ID_DISCONTINUED = 6;
    const STATUS_ID_OUT_OF_STOCK = 7;
    const STATUS_ID_BLOCKED = 8;

    const STATUSES_CHECK_AGAIN_AFTER_EDITION = [
        self::STATUS_ID_OK,
        self::STATUS_ID_DISCONTINUED,
        self::STATUS_ID_OUT_OF_STOCK,
    ];

    /** @Db\BigIntType() */
    public $id;
    /** @Db\BigIntType() */
    public $userId;
    /** @Db\TinyIntType() */
    public $typeId;
    /** @Db\TinyIntType() */
    public $statusId = self::STATUS_ID_FETUS;
    /** @Db\BigIntType() */
    public $imageStorageFileId;
    /** @Db\BigIntType() */
    public $backgroundStorageFileId;
    /** @Db\TinyIntType() */
    public $currencyId;
    /** @Db\IntType() */
    public $productCategoryId;
    /** @Db\VarcharType() */
    public $importId;
    /** @Db\VarcharType() */
    public $name;
    /** @Db\DecimalType() */
    public $price;
    /** @Db\DecimalType() */
    public $partnershipFee = 1;
    /** @Db\TextType() */
    public $description;
    /** @Db\IntType() */
    public $salesNum = 0;
    /** @Db\IntType() */
    public $inStockNum = 0;
    /** @Db\IntType() */
    public $reviewsNum = 0;
    /** @Db\IntType() */
    public $goodReviewsNum = 0;
    /** @Db\TinyIntType() */
    public $reviewsPercent;
    /** @Db\TextType() */
    public $verificationRejectReason;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    /** @Db\ManyToMany(EntityClassName=ProductGroup::class, RelationClassName=Product_ProductGroup::class) */
    public $productGroupIds;

    //@TODO AbstractEntity
    private $dynamicalProperties = [];

    public function __get($name)
    {
        if (isset($this->dynamicalProperties[$name])) {
            return call_user_func($this->dynamicalProperties[$name]);
        }

        throw new \RuntimeException("Property '$name' not found");
    }

    public function addDynamicalProperty(string $name, callable $method)
    {
        $this->dynamicalProperties[$name] = $method;
    }

    //@TODO AbstractEntity

    public function compileAdminVerificationListView()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'createdDate' => $this->createdTs->format(AdminApi::DATE_FORMAT),
        ];
    }

    public function compileAdminView(
        TranslatorInterface $translator,
        ProductCategory $productCategory,
        StorageFile $imageStorageFile,
        string $cdn,
        StorageFile $backgroundStorageFile = null
    ) {
        $data = [
            'data' => [
                'id' => $this->id,
                'userId' => $this->userId,
                'statusId' => $this->statusId,
            ],
            'fields' => [
                ['title' => 'ID', 'type' => 'string', 'value' => $this->id],
                ['title' => 'User ID', 'type' => 'string', 'value' => $this->userId],
                [
                    'title' => 'Type',
                    'type' => 'string',
                    'value' => $translator->trans("type.$this->typeId", [], 'product')
                ],
                [
                    'title' => 'Status',
                    'type' => 'string',
                    'value' => $translator->trans("status.$this->statusId", [], 'product')
                ],
                ['title' => 'Reject Reason', 'type' => 'alert', 'value' => $this->verificationRejectReason],
                ['title' => 'Category', 'type' => 'string', 'value' => $productCategory->name],
                ['title' => 'Name', 'type' => 'string', 'value' => $this->name],
                [
                    'title' => 'Price',
                    'type' => 'string',
                    'value' => sprintf(
                        '%s %s',
                        number_format($this->price, 2), //@TODO
                        $translator->trans("currency.$this->currencyId.short-latin", [], 'payment')
                    )
                ],
                ['title' => 'Sales', 'type' => 'string', 'value' => $this->salesNum],
                ['title' => 'Is Deleted', 'type' => 'string', 'value' => $this->isDeleted],
                [
                    'title' => 'Created Date',
                    'type' => 'string',
                    'value' => $this->createdTs->format(AdminApi::DATE_FORMAT)
                ],
                ['title' => 'Description', 'type' => 'text', 'value' => $this->description],
                ['title' => 'Image', 'type' => 'image', 'value' => $imageStorageFile->compilePath($cdn)],
            ]
        ];
        if (null === $backgroundStorageFile) {
            $data['fields'][] = ['title' => 'Background', 'type' => 'text', 'value' => 'Not set'];
        } else {
            $data['fields'][] = [
                'title' => 'Background',
                'type' => 'image',
                'value' => $backgroundStorageFile->compilePath($cdn)
            ];
        }

        return $data;
    }

    public function compileApiPartnerListView(
        StorageFile $imageStorageFile,
        string $cdn,
        string $domainBuy,
        int $partnerUserId = null
    ) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => number_format($this->price, 2, '.', ''),
            'currencyId' => $this->currencyId,
            'inStock' => $this->inStockNum > 0,
            'sellerId' => $this->userId,
            'categoryId' => $this->productCategoryId,
            'pictureUrl' => $imageStorageFile->compilePath($cdn),
            'url' => $this->compileBuyingUrl($domainBuy, $partnerUserId),
        ];
    }

    public function compileApiPartnerView(
        StorageFile $imageStorageFile,
        string $cdn,
        string $domainBuy,
        int $partnerUserId = null
    ) {
        return $this->compileApiPartnerListView($imageStorageFile, $cdn, $domainBuy, $partnerUserId);
    }

    public function compileBuyingUrl(string $domainBuy, int $partnerUserId = null): string
    {
        $url = "https://{$domainBuy}/add/{$this->id}";
        if (null !== $partnerUserId) {
            $url .= "/{$partnerUserId}";
        }

        return $url;
    }

    public function compilePageUrl(string $domain): string
    {
        $url = "https://{$domain}/#/product/{$this->id}";

        return $url;
    }
}

