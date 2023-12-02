<?php

namespace Core\Service;

use Core\MainConfig;
use Core\Container\Container;
use Core\DTO\DTOTransformerAbstract;
use Core\Exception\TransformException;
use Core\Service\ServiceAbstract;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @method static TransformService init()
 */
final class TransformService extends ServiceAbstract
{
    private Container $container;
    private array $transformers;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->transformers = MainConfig::TRANSFORMERS;
    }

    public function getDto($data, string $to = null, array $context = [])
    {
        $transformersClass = $this->transformers[$to] ?? null;

        if (!$transformersClass) {
            return null;
        }

        try {
            foreach ($transformersClass as $transformerClass) {

                $transformer = $this->container->get($transformerClass);

                if (is_subclass_of($transformer, DTOTransformerAbstract::class)) {

                    if ($transformer->supportsTransformation($data, $to, $context)) {
                        return $transformer->transform($data, $context);
                    }
                }

            }
        } catch (\Exception $e) {
            throw new TransformException('Не удалось трансформировать данные в объект ' . $to . ': '. $e->getMessage());
        }

        return null;
    }

    public function getDtoList(array|ArrayCollection $dataArr, string $to = null, array $context = []): array
    {

        $transformersClass = $this->transformers[$to] ?? null;

        if (!$transformersClass) {
            return [];
        }

        $transformed = [];

        try {
            foreach ($transformersClass as $transformerClass) {

                $transformer = $this->container->get($transformerClass);

                if (is_subclass_of($transformer, DTOTransformerAbstract::class)) {

                    foreach ($dataArr as $data) {
                        if ($transformer->supportsTransformation($data, $to, $context)) {
                            $transformed[] = $transformer->transform($data, $context);
                        } else {
                            break;
                        }
                    }
                    if($transformed) {
                        return $transformed;
                    }

                }

            }
        } catch (\Exception $e) {
            throw new TransformException($e->getMessage());
        }

        return $transformed;
    }
}
