<?php
/**
 * To Handle Product table
 * @author Ashutosh Rai <a.kumar@medlamg.com>
 * @createdAt 30/05/18 12:35
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Product
 * @package AppBundle\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="product")
 */
class Product
{
    /**
     * @var Integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var String
     *
     * @ORM\Column(name="name", type="string", length=1000, nullable=false)
     */
    private $name;

    /**
     * @var String
     *
     * @ORM\Column(name="description", type="string", length=5555, nullable=false)
     */
    private $desc;

    /**
     * @var Integer
     *
     * @ORM\Column(name="price", type="integer", nullable=false)
     */
    private $price;

    /**
     * @var String
     *
     * @ORM\Column(name="product_url", type="string", length=5000, nullable=true)
     */
    private $productUrl;

    /**
     * @var String
     *
     * @ORM\Column(name="product_image", type="string", length=1000, nullable=true)
     */
    private $productImage;

    /**
     * @var Integer
     *
     * @ORM\Column(name="enabled", type="integer", length=1, nullable=false)
     */

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set desc
     *
     * @param string $desc
     *
     * @return Product
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;

        return $this;
    }

    /**
     * Get desc
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set productUrl
     *
     * @param string $productUrl
     *
     * @return Product
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = $productUrl;

        return $this;
    }

    /**
     * Get productUrl
     *
     * @return string
     */
    public function getProductUrl()
    {
        return $this->productUrl;
    }

    /**
     * Set productImage
     *
     * @param string $productImage
     *
     * @return Product
     */
    public function setProductImage($productImage)
    {
        $this->productImage = $productImage;

        return $this;
    }

    /**
     * Get productImage
     *
     * @return string
     */
    public function getProductImage()
    {
        return $this->productImage;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Product
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Product
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
