<?php

namespace Core\Service;

use Core\Entity\MetaEntityInterface;
use Core\Entity\MetaInterface;
use Core\ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @method static MetaService init()
 */
class MetaService extends ServiceAbstract
{
    public function updateMetaByArray(MetaEntityInterface $entity, array $updateData): void
    {
        $orm = ORM::get();
        $metaClassName = $entity->getClassNameMeta();
        $metaData = [];
        foreach ($updateData as $key => $value) {

            $meta = $entity->getMeta($key);
            if($meta->count()) {
                /** @var MetaInterface $m */
                foreach($meta as $m) {
                    $orm->getManager()->remove($m);
                }
            }

            if(!empty($value)) {
                if(in_array($key, $entity->getMultiMetaKeys())) {
                    if(is_array($value)) {
                        foreach($value as $val) {
                            $metaData[] = $this->createMeta($metaClassName, $key, $val);
                        }
                    }
                } else {
                    $metaData[] = $this->createMeta($metaClassName, $key, is_array($value) ? maybe_serialize($value) : $value);
                }
            }
        }

        foreach ($metaData as $meta) {
            $orm->getManager()->persist($meta);
            $entity->addMeta($meta);
        }
    }

    private function createMeta(string $metaClassName, string $key, mixed $value): MetaInterface
    {
        return (new $metaClassName())
            ->setKey($key)
            ->setValue($value);
    }

}
