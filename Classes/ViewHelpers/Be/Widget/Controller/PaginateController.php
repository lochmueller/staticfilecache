<?php

/**
 * Override original to add array support.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\ViewHelpers\Be\Widget\Controller;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Override original to add array support.
 */
class PaginateController extends \TYPO3\CMS\Fluid\ViewHelpers\Be\Widget\Controller\PaginateController
{
    /**
     * Index action.
     *
     * @param int $currentPage
     */
    public function indexAction($currentPage = 1)
    {
        // set current page
        $this->currentPage = (int)$currentPage;
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }
        if ($this->currentPage > $this->numberOfPages) {
            // set $modifiedObjects to NULL if the page does not exist
            $modifiedObjects = null;
        } else {
            // modify query
            $this->itemsPerPage = (int)$this->configuration['itemsPerPage'];
            $this->offset = $this->itemsPerPage * ($this->currentPage - 1);
            // use slice here for array support
            $modifiedObjects = $this->prepareObjectsSlice($this->itemsPerPage, $this->offset);
        }
        $this->view->assign('contentArguments', [
            $this->widgetConfiguration['as'] => $modifiedObjects,
        ]);
        $this->view->assign('configuration', $this->configuration);
        $this->view->assign('pagination', $this->buildPagination());
    }

    /**
     * Copy the prepareObjectsSlice from the frontend paginate viewhelper.
     *
     * @param int $itemsPerPage
     * @param int $offset
     *
     * @throws \InvalidArgumentException
     *
     * @return array|QueryResultInterface
     */
    protected function prepareObjectsSlice($itemsPerPage, $offset)
    {
        if ($this->objects instanceof QueryResultInterface) {
            $currentRange = $offset + $itemsPerPage;
            $endOfRange = \min($currentRange, \count($this->objects));
            $query = $this->objects->getQuery();
            $query->setLimit($itemsPerPage);
            if ($offset > 0) {
                $query->setOffset($offset);
                if ($currentRange > $endOfRange) {
                    $newLimit = $endOfRange - $offset;
                    $query->setLimit($newLimit);
                }
            }
            $modifiedObjects = $query->execute();

            return $modifiedObjects;
        }
        if ($this->objects instanceof ObjectStorage) {
            $modifiedObjects = [];
            $objectArray = $this->objects->toArray();
            $endOfRange = \min($offset + $itemsPerPage, \count($objectArray));
            for ($i = $offset; $i < $endOfRange; ++$i) {
                $modifiedObjects[] = $objectArray[$i];
            }

            return $modifiedObjects;
        }
        if (\is_array($this->objects)) {
            $modifiedObjects = \array_slice($this->objects, $offset, $itemsPerPage);

            return $modifiedObjects;
        }
        throw new \InvalidArgumentException(
            'The view helper "' . static::class
            . '" accepts as argument "QueryResultInterface", "\SplObjectStorage", "ObjectStorage" or an array. '
            . 'given: ' . \get_class($this->objects),
            1385547291
        );
    }
}
