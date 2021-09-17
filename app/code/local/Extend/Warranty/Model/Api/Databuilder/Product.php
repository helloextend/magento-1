<?php

class Extend_Warranty_Model_Api_Databuilder_Product
{
    /**
     * @param $productSubject
     * @return array
     */
    public function build($productSubject)
    {
        $description = !empty($productSubject->getShortDescription()) ? (string)$productSubject->getShortDescription() : 'No description';
        try {
            $imgUrl = $productSubject->getImageUrl();
        } catch (Exception $e) {
            Mage::getModel('warranty/logger')->error(
                [
                    'id'   => $productSubject->getId(),
                    'sku'  => $productSubject->getSku(),
                    'name' => $productSubject->getName(),
                ],
                $e->getMessage()
            );
        }

        $data = [
            'title'       => (string)$productSubject->getName(),
            'description' => $description,
            'price'       => [
                "amount"       => $productSubject->getFinalPrice(),
                "currencyCode" => "USD"
            ],
            'referenceId' => (string)$productSubject->getSku(),
            'category'    => $this->getCategories($productSubject),
            'identifiers' => [
                'sku'  => (string)$productSubject->getSku(),
                'type' => (string)$productSubject->getTypeId()
            ]
        ];

        if (!empty($imgUrl)) {
            $data['imageUrl'] = $imgUrl;
        }

        $parentId = $productSubject->getTypeInstance()->getParentIdsByChild($productSubject->getId());
        $parentId = reset($parentId);
        if (!empty($parentId)) {
            $_parentSKU = $this->productRepository->getById($parentId)->getSku();
            $data['parentReferenceId'] = $_parentSKU;
            $data['identifiers']['parentSku'] = $_parentSKU;
            $data['identifiers']['type'] = 'configurableChild';
        }

        return $data;
    }

    /**
     * @param $productSubject
     * @return string
     */
    private function getCategories($productSubject)
    {
        $categoryIds = $productSubject->getCategoryIds();

        sort($categoryIds);

        $names = [];
        $categoryCollection = Mage::getResourceModel('catalog/category_collection');
        $categoryCollection->addAttributeToSelect('name');
        $categoryCollection->addFieldToFilter(
            array(
                array('attribute' => 'entity_id', 'in' => $categoryIds)
            )
        );
        foreach ($categoryCollection as $category) {
            if (!$category->hasChildren()) {
                if (in_array($category->getEntityId(), $categoryIds)) {
                    $names[] = $category->getName();
                }
            } else {
                $cat = $this->checkChildren($category, $category->getName(), $categoryIds);
                if ($cat != null) {
                    $names[] = $cat;
                }
            }
        }

        return implode(",", $names);
    }

    /**
     * @param CategoryInterface $category
     * @param string $catName
     * @param array $ids
     * @return string|null
     */
    private function checkChildren($category, $catName, &$ids)
    {
        $names = [];
        $children = $category->getChildrenCategories();
        foreach ($children as $child) {
            if (in_array($child->getEntityId(), $ids)) {
                $new = $catName . '/' . $child->getName();
                $ids[array_search($child->getEntityId(), $ids)] = '';
                if (!$child->hasChildren()) {
                    $names[] = $new;
                } else {
                    $names[] = $this->checkChildren($child, $new, $ids);
                }
            }
        }

        return !empty($names) ? implode(",", $names) : null;
    }
}
