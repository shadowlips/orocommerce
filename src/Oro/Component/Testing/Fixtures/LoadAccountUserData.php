<?php

namespace Oro\Component\Testing\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\BaseUserManager;

use OroB2B\Bundle\CustomerBundle\Entity\AccountUser;
use OroB2B\Bundle\CustomerBundle\Entity\AccountUserRole;

class LoadAccountUserData extends AbstractFixture implements ContainerAwareInterface
{
    const AUTH_USER = 'account_user@example.com';
    const AUTH_PW = 'account_user';

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var BaseUserManager $userManager */
        $userManager = $this->container->get('orob2b_account_user.manager');
        if ($userManager->getRepository()->findBy(['username' => self::AUTH_USER])) {
            return;
        }

        $organization = $manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();

        /** @var AccountUser $entity */
        $entity = $userManager->createUser();

        $website = $this->container->get('orob2b_website.manager')->getCurrentWebsite();
        $role = $this->container
            ->get('doctrine')
            ->getManagerForClass('OroB2BCustomerBundle:AccountUserRole')
            ->getRepository('OroB2BCustomerBundle:AccountUserRole')
            ->getDefaultAccountUserRoleByWebsite($website);

        $entity
            ->setFirstName('AccountUser')
            ->setLastName('AccountUser')
            ->setEmail(self::AUTH_USER)
            ->setEnabled(true)
            ->setSalt('')
            ->setPlainPassword(self::AUTH_PW)
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->addRole($role);

        $userManager->updateUser($entity);
    }
}
