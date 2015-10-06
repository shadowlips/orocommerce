<?php

namespace OroB2B\Bundle\AccountBundle\Entity\Visibility;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use OroB2B\Bundle\AccountBundle\Entity\AccountGroup;
use OroB2B\Bundle\CatalogBundle\Entity\Category;

/**
 * @ORM\Entity(repositoryClass="OroB2B\Bundle\AccountBundle\Entity\Repository\AccountGroupCategoryVisibilityRepository")
 * @ORM\Table(name="orob2b_acc_grp_ctgr_visibility")
 * @Config
 */
class AccountGroupCategoryVisibility implements VisibilityInterface
{
    const PARENT_CATEGORY = 'parent_category';
    const CATEGORY = 'category';
    const VISIBLE = 'visible';
    const HIDDEN = 'hidden';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\CatalogBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $category;

    /**
     * @var AccountGroup
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\AccountBundle\Entity\AccountGroup")
     * @ORM\JoinColumn(name="account_group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $accountGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="visibility", type="string", length=255, nullable=true)
     */
    protected $visibility;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return $this
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return AccountGroup
     */
    public function getAccountGroup()
    {
        return $this->accountGroup;
    }

    /**
     * @param AccountGroup $accountGroup
     *
     * @return $this
     */
    public function setAccountGroup(AccountGroup $accountGroup)
    {
        $this->accountGroup = $accountGroup;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefault(Category $category = null)
    {
        return self::CATEGORY;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * {@inheritdoc}
     */
    public static function getVisibilityList(Category $category = null)
    {
        $visibilityList = [
            self::CATEGORY,
            self::PARENT_CATEGORY,
            self::HIDDEN,
            self::VISIBLE
        ];
        if ($category instanceof Category && !$category->getParentCategory()) {
            unset($visibilityList[array_search(self::PARENT_CATEGORY, $visibilityList)]);
        }
        return $visibilityList;
    }
}
