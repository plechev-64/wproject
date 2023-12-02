<?php

namespace Core\Service;

use Core\Entity\Taxonomy\Term;
use Core\Repository\TermsRepository;
use Core\Service\ServiceAbstract;

/**
 * @method static TaxonomyService init()
 */
class TaxonomyService extends ServiceAbstract
{
    private TermsRepository $termsRepository;

    /**
     * @param TermsRepository $termsRepository
     */
    public function __construct(TermsRepository $termsRepository)
    {
        $this->termsRepository = $termsRepository;
    }

    public function getTermBySlug(string $slug): ?Term
    {
        return $this->termsRepository->getTermBySlug($slug);
    }


}
