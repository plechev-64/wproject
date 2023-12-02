<?php

namespace Core\ObjectManager;

use Core\Container\Container;
use Core\ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use JetBrains\PhpStorm\ArrayShape;

abstract class ObjectManagerAbstract
{
    protected int $total = 0;
    protected array $sort = [
        'order' => 'DESC',
        'by'    => ''
    ];
    protected ?int $page = null;
    protected int $offset = 0;
    protected int $number = 48;
    protected int $pages = 0;
    protected int $startPage = 1;
    protected array $filters = [];
    protected array $queue = [];
    protected array $workFilters = [];
    protected string $template = '';
    protected string $wrapper = '';
    protected bool $isShuffle = false;
    protected array $orderPages = [];

    public static function init(): ObjectManagerAbstract
    {
        $container = Container::getInstance();
        /** @var ObjectManagerAbstract $instance */
        $instance              = $container->get(get_called_class());
        $instance->filters     = [];
        $instance->workFilters = [];
        $instance->orderPages  = [];
        $instance->number      = 48;
        $instance->page        = null;
        $instance->isShuffle   = false;
        $instance->sort        = [
            'order' => 'DESC',
            'by'    => ''
        ];

        return $instance;
    }

    abstract protected function getEntityClassName(): string;

    abstract protected function getAlias(): string;

    abstract protected function getRoute(): string;

    abstract protected function getMainQuery(): QueryBuilder;

    /**
     * @param string $by
     * @param string $order
     *
     * @return ObjectManagerAbstract
     */
    public function setOrderBy(string $by, string $order = 'DESC'): ObjectManagerAbstract
    {
        $this->sort = [
            'by'    => $by,
            'order' => $order
        ];

        return $this;
    }

    /**
     * @param bool $isShuffle
     *
     * @return ObjectManagerAbstract
     */
    public function setIsShuffle(bool $isShuffle): ObjectManagerAbstract
    {
        $this->isShuffle = $isShuffle;

        return $this;
    }

    /**
     * @param array $orderPages
     *
     * @return ObjectManagerAbstract
     */
    public function setOrderPages(array $orderPages): ObjectManagerAbstract
    {
        $this->orderPages = $orderPages;

        return $this;
    }

    /**
     * @return int
     */
    public function getPages(): int
    {
        return $this->pages;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getData(?QueryBuilder $query = null): ArrayCollection
    {

        if (!$query) {
            $query = $this->getMainQuery();
        }

        if ($this->number > 0) {
            $this->setupPageData($query);
            $query->setMaxResults($this->number);
        }

        $result = $this->getResult(
            $query
                ->setFirstResult($this->offset)
        );

        if ($result && $this->isShuffle) {
            shuffle($result);
        }

        return new ArrayCollection($result);

    }

    public function getUnionData(array $queue): ArrayCollection
    {

        $results = new ArrayCollection();

        if (!$queue) {
            return $results;
        }

        $this->queue = $queue;

        $this->setupPageData($queue);

        $queries = [];
        $offset  = 0;
        $remain  = $this->number > 0 ? $this->number : 9999999;

        foreach ($queue as $queryClassName) {

            $pagedQuery = $this->setupPagedQuery($queries, $queryClassName, $remain, $offset);

            if ($pagedQuery) {
                $queries = $pagedQuery['queries'];
                $remain  = $pagedQuery['remain'];
                $offset  = $pagedQuery['offset'];
            }

            if ($queries && $remain <= 0) {
                return $this->getUnionResults($queries);
            }

        }

        if (!$queries) {
            return $results;
        }

        return $this->getUnionResults($queries);

    }

    public function getTotal(mixed $query = null): int
    {

        if (is_string($query)) {
            /** @var UnionQueryAbstract $queryClass */
            $queryClass     = new $query();
            $countableQuery = $queryClass->getQuery($this->getMainQuery());
        } elseif ($query instanceof QueryBuilder) {
            $countableQuery = clone $query;
        } elseif (is_array($query)) {
            $cnt = 0;
            foreach ($query as $q) {
                /** @var UnionQueryAbstract $queryClass */
                $queryClass     = new $q();
                $countableQuery = $queryClass->getQuery($this->getMainQuery());
                $cnt            += $this->getTotal($countableQuery);
            }

            return $cnt;
        } else {
            $countableQuery = $this->getMainQuery();
        }

        //		print_r($this->filterQuery( $countableQuery )
        //		             ->select( 'count(' . $this->getAlias() . ')' )
        //		             ->getQuery()
        //			         ->getSQL());

        //		print_r([
        //			$this->filterQuery( $countableQuery )
        //			     ->select( 'count(' . $this->getAlias() . ')' )
        //			     ->getQuery()
        //			     ->getSingleScalarResult()
        //		]);exit;

        return $this->filterQuery($countableQuery)
                    ->select('count(' . $this->getAlias() . ')')
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function getQuery(): string
    {
        return $this->filterQuery($this->getMainQuery())
                    ->getQuery()
                    ->getSQL();
    }

    public function setupPageData(mixed $query = null): void
    {

        if ($this->page === null) {
            $this->page = function_exists('get_query_var') && get_query_var('paged') ? get_query_var('paged') : 1;
        }

        $pageKey = 1;
        if ($this->page) {
            $pageKey = $this->page;
        }

        //в очереди запросов отключаем смешение страниц
        if ($this->isShuffle && ($query === null || $query instanceof QueryBuilder)) {
            if (!$this->orderPages) {

                $this->total = $this->getTotal($query);
                $this->pages = $this->number > 0 ? ceil($this->total / $this->number) : 1;

                for ($i = 1; $this->pages >= $i; $i++) {
                    $this->orderPages[] = $i;
                }
                shuffle($this->orderPages);
            }
            $pageKey = $this->orderPages[ $pageKey - 1 ] ?? 9999;
        }

        $this->offset = $this->number > 0 ? ($pageKey - 1) * $this->number : 0;

    }

    public function getLoadParams(string $wrapper, ?string $route = null, ?array $customArgs = []): array
    {

        if ($this->isShuffle) {
            $customArgs['pages'] = $this->orderPages;
        }

        return [
            'page'      => $this->page,
            'number'    => $this->number,
            'isShuffle' => $this->isShuffle,
            'filters'   => json_encode($this->filters),
            'queue'     => json_encode($this->queue),
            'wrapper'   => $wrapper,
            'route'     => $route ?: $this->getRoute(),
            'sort'      => json_encode($this->sort),
            'args'      => json_encode($customArgs),
        ];
    }

    public function getLoadMoreOnClick(string $wrapper, ?string $route = null, ?array $customArgs = []): string
    {
        return 'onclick=\'ObjectManagerList.addIfNotExist(' . json_encode($this->getLoadParams($wrapper, $route, $customArgs)) . ', this).load();return false;\'';
    }

    public function initPageLazyLoadScript(string $wrapper, ?string $route = null, ?array $customArgs = []): string
    {
        return '
		<div class="object-load-more" data-wrapper="' . $wrapper . '"></div>
		<script>document.addEventListener("DOMContentLoaded", function () {
	        ObjectManagerList.add(' . json_encode($this->getLoadParams($wrapper, $route, $customArgs)) . ').initLazyLoad();
		});</script>
		';
    }

    protected function getFilterRules(): array
    {
        return [];
    }

    protected function filterQueryByRule(string $filterName, QueryBuilder $query, mixed $value): QueryBuilder
    {
        $filter = $this->getFilterRules()[ $filterName ] ?? null;

        return $filter ? $filter($query, $value) : $query;
    }

    private function getUnionResults(array $queries): ArrayCollection
    {

        $collection = new ArrayCollection();
        /** @var QueryBuilder $query */
        foreach ($queries as $query) {

            $results = $this->getResult($query);

            if ($results) {

                if ($this->isShuffle) {
                    shuffle($results);
                }

                foreach ($results as $result) {
                    $collection->add($result);
                }
            }
        }

        return $collection;

    }

    private function getResult(QueryBuilder $query): array
    {

        $orm      = ORM::get();
        $metaData = $orm->getManager()->getClassMetadata($this->getEntityClassName());

        return $this->filterQuery($query)
                    ->groupBy(sprintf('%s.%s', $this->getAlias(), $metaData->getIdentifier()[0]))
                    ->getQuery()
                    ->getResult();

    }

    private function setupPagedQuery(array $queries, ?string $queryClassName = null, ?int $remainOnPage = 0, ?int $offset = 0): ?array
    {

        /** @var UnionQueryAbstract $queryClass */
        $queryClass = new $queryClassName();

        $query = $queryClass->getQuery($this->getMainQuery());

        if (!$query) {
            return null;
        }

        $cnt = $this->getTotal($query);

        if (!$cnt) {
            return null;
        }

        if ($this->number > 0) {
            if ($remainOnPage < $this->number) {
                $queries[] = $query
                    ->setFirstResult(0)
                    ->setMaxResults($remainOnPage);
            } else {
                $queries[] = $query
                    ->setFirstResult($this->offset - $offset)
                    ->setMaxResults($this->number);
            }

            if (($cnt + $offset) - $this->offset > $this->number) {
                $remainOnPage = 0;
            } elseif (($cnt + $offset) > $this->offset) {
                $remainOnPage = $this->number - (($cnt + $offset) - $this->offset);
            }
        }

        return [
            'queries' => $queries,
            'remain'  => $remainOnPage,
            'count'   => $cnt,
            'offset'  => $cnt + $offset,
        ];

    }

    private function filterQuery(QueryBuilder $query): QueryBuilder
    {

        $filters = $this->filters; //wp_parse_args( $_REQUEST, $this->filters );

        if ($filterRules = $this->getFilterRules()) {
            foreach ($filterRules as $key => $rule) {
                if (isset($filters[ $key ])) {
                    $this->workFilters[ $key ] = $filters[ $key ];
                    $query                     = $rule($query, $filters[ $key ]);
                }
            }
        }

        return $query;
    }

}
