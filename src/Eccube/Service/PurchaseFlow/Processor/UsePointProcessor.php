<?php

namespace Eccube\Service\PurchaseFlow\Processor;

use Doctrine\ORM\EntityManager;
use Eccube\Annotation\Inject;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Service\PurchaseFlow\ItemHolderProcessor;
use Eccube\Service\PurchaseFlow\ProcessResult;
use Eccube\Service\PurchaseFlow\PurchaseContext;

/**
 * 使用ポイント値引明細追加.
 */
class UsePointProcessor implements ItemHolderProcessor
{
    /**
     * @Inject("orm.em")
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * DeliveryFeeProcessor constructor.
     *
     * @param $app
     */
    public function __construct(EntityManager $entityManager, BaseInfo $BaseInfo)
    {
        $this->entityManager = $entityManager;
        $this->BaseInfo = $BaseInfo;
    }

    /**
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext     $context
     *
     * @return ProcessResult
     */
    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        if ($this->containsPointDiscountItem($itemHolder) == false) {
            if ($itemHolder->getUsePoint() > 0) {
                $this->addPointDiscountItem($itemHolder);
                $this->usePointToCustomer($itemHolder);
            }
        }

        return ProcessResult::success();
    }

    /**
     * @param ItemHolderInterface $itemHolder
     *
     * @return bool
     */
    protected function containsPointDiscountItem(ItemHolderInterface $itemHolder)
    {
        foreach ($itemHolder->getItems() as $item) {
            if ($item->isDiscount() && $item->getProductName() == 'ポイント値引') {
                return true;
            }
        }

        return false;
    }

    /**
     * 明細追加処理
     *
     * @param ItemHolderInterface $itemHolder
     */
    protected function addPointDiscountItem(ItemHolderInterface $itemHolder)
    {
        $DiscountType = $this->entityManager
            ->find(OrderItemType::class, OrderItemType::DISCOUNT);
        // TODO
        $TaxInclude = $this->entityManager
            ->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
        $Taxion = $this->entityManager
            ->find(TaxType::class, TaxType::TAXATION);

        /** @var Order $Order */
        $Order = $itemHolder;

        $priceOfUsePoint = $Order->getUsePoint() * -1; // TODO ポイント換算率
        $OrderItem = new OrderItem();
        $OrderItem->setProductName('ポイント値引')
            ->setPrice($priceOfUsePoint)
            ->setPriceIncTax($priceOfUsePoint)
            ->setTaxRate(8)
            ->setQuantity(1)
            ->setOrderItemType($DiscountType)
            ->setTaxDisplayType($TaxInclude)
            ->setTaxType($Taxion);
        $itemHolder->addItem($OrderItem);
    }

    /**
     * Customer::point の減算処理.
     * TODO ここでやるのが適切?？
     */
    protected function usePointToCustomer(ItemHolderInterface $itemHolder)
    {
        /** @var Order $Order */
        $Order = $itemHolder;
        $Customer = $Order->getCustomer();
        $Customer->setPoint($Customer->getPoint() + $Order->getUsePoint() * -1);
    }
}
