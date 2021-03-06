<?php

namespace Oro\Bundle\SaleBundle\Provider;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SaleBundle\Entity\Quote;

class QuoteAddressSecurityProvider
{
    const MANUAL_EDIT_ACTION = 'oro_quote_address_%s_allow_manual';

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var QuoteAddressProvider */
    protected $QuoteAddressProvider;

    /** @var string */
    protected $customerAddressClass;

    /** @var string */
    protected $customerUserAddressClass;

    /**
     * @param SecurityFacade $securityFacade
     * @param QuoteAddressProvider $quoteAddressProvider
     * @param string $customerAddressClass
     * @param string $customerUserAddressClass
     */
    public function __construct(
        SecurityFacade $securityFacade,
        QuoteAddressProvider $quoteAddressProvider,
        $customerAddressClass,
        $customerUserAddressClass
    ) {
        $this->securityFacade = $securityFacade;
        $this->QuoteAddressProvider = $quoteAddressProvider;
        $this->customerAddressClass = $customerAddressClass;
        $this->customerUserAddressClass = $customerUserAddressClass;
    }

    /**
     * @param Quote $quote
     * @param string $type
     *
     * @return bool
     */
    public function isAddressGranted(Quote $quote, $type)
    {
        return $this->isCustomerAddressGranted($type, $quote->getCustomer()) ||
            $this->isCustomerUserAddressGranted($type, $quote->getCustomerUser());
    }

    /**
     * @param string $type
     * @param Customer $customer
     *
     * @return bool
     */
    public function isCustomerAddressGranted($type, Customer $customer = null)
    {
        if ($this->isManualEditGranted($type)) {
            return true;
        }

        $hasPermissions = $this->securityFacade->isGranted(
            $this->getClassPermission('VIEW', $this->customerAddressClass)
        );

        if (!$hasPermissions) {
            return false;
        }

        if (!$customer) {
            return false;
        }

        return (bool)$this->QuoteAddressProvider->getCustomerAddresses($customer, $type);
    }

    /**
     * @param string $type
     * @param CustomerUser $customerUser
     *
     * @return bool
     */
    public function isCustomerUserAddressGranted($type, CustomerUser $customerUser = null)
    {
        if ($this->isManualEditGranted($type)) {
            return true;
        }

        $hasPermissions = $this->securityFacade
                ->isGranted($this->getClassPermission('VIEW', $this->customerUserAddressClass))
            && $this->securityFacade->isGranted($this->getTypedPermission($type));

        if (!$hasPermissions) {
            return false;
        }

        if (!$customerUser) {
            return false;
        }

        return (bool)$this->QuoteAddressProvider->getCustomerUserAddresses($customerUser, $type);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getTypedPermission($type)
    {
        QuoteAddressProvider::assertType($type);

        return $this->getPermission(QuoteAddressProvider::ADDRESS_SHIPPING_ACCOUNT_USER_USE_ANY);
    }

    /**
     * @param string $permission
     * @param string $className
     *
     * @return string
     */
    protected function getClassPermission($permission, $className)
    {
        return sprintf('%s;entity:%s', $permission, $className);
    }

    /**
     * @param string $permission
     *
     * @return string
     */
    protected function getPermission($permission)
    {
        if (!$this->securityFacade->getLoggedUser() instanceof CustomerUser) {
            $permission .= QuoteAddressProvider::ADMIN_ACL_POSTFIX;
        }

        return $permission;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isManualEditGranted($type)
    {
        QuoteAddressProvider::assertType($type);

        $permission = sprintf(self::MANUAL_EDIT_ACTION, $type);

        return $this->securityFacade->isGranted($this->getPermission($permission));
    }
}
