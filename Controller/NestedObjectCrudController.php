<?php
namespace Jarves\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;

class NestedObjectCrudController extends ObjectCrudController
{
    /**
     * @ApiDoc(
     *    description="Adds a new item (nested set)"
     * )
     *
     * @Rest\RequestParam(name="_pk", map=true, strict=false, description="The target object item's primaryKey as url encoded string. Only for nested sets.")
     * @Rest\RequestParam(name="_position", requirements=".*", strict=false, description="The position we place this new entry relative to _pk given position. `first` (child), `last` (child), `prev` (sibling), `next` (sibling).")
     * @Rest\RequestParam(name="_targetObjectKey", requirements=".*", strict=false, description="The target object key. Only for nested sets.")
     *
     * @Rest\View()
     * @Rest\Post("/")
     *
     * @param Request $request
     * @param ParamFetcher $paramFetcher
     *
     * @return mixed
     */
    public function addItemAction(Request $request, ParamFetcher $paramFetcher)
    {
        return $this->add($request, $paramFetcher->get('_pk'), $paramFetcher->get('_position'), $paramFetcher->get('_targetObjectKey'));
    }

    /**
     * @ApiDoc(
     *    description="Adds multiple %object% items #todo-doc"
     * )
     *
     * @Rest\RequestParam(name="_pk", map=true, strict=false, description="The target object item's primaryKey as url encoded string. Only for nested sets.")
     * @Rest\RequestParam(name="_position", requirements=".*", strict=false, description="The position we place this new entry relative to _pk given position. `first` (child), `last` (child), `prev` (sibling), `next` (sibling).")
     * @Rest\RequestParam(name="_targetObjectKey", requirements=".*", strict=false, description="The target object key. Only for nested sets.")
     *
     * @Rest\View()
     * @Rest\Post("/:multiple")
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function addMultipleItemAction(Request $request)
    {
        return $this->addMultiple($request);
    }


    /**
     * @ApiDoc(
     *    description="Deletes a object root"
     * )
     *
     * @Rest\View()
     * @Rest\Delete("/:root")
     *
     * @return boolean
     */
    public function removeRootAction()
    {
        return '#todo';
//        return $obj->removeRoot();
//
//        if (count($primaryKeys) > 0) {
//            $result = false;
//            foreach ($pk as $item) {
//                $result |= $this->removeItem($item);
//            }
//
//            return (boolean)$result;
//        }
    }


    /**
     * @ApiDoc(
     *    description="Returns all the root branch (nested set)"
     * )
     *
     * @Rest\QueryParam(name="fields", requirements=".+", description="Comma separated list of field names")
     * @Rest\QueryParam(name="filter", map=true, requirements=".*", description="Simple filtering per field")
     * @Rest\QueryParam(name="limit", requirements="[0-9]+", description="Limits the result")
     * @Rest\QueryParam(name="offset", requirements="[0-9]+", description="Offsets the result")
     * @Rest\QueryParam(name="scope", requirements=".*", description="Nested set scope")
     * @Rest\QueryParam(name="depth", requirements="[0-9]+", default=1, description="Max depth")
     *
     * @Rest\View()
     * @Rest\Get("/:branch")
     *
     * @param string $fields
     * @param string $scope
     * @param integer $depth
     * @param string $limit
     * @param string $offset
     * @param string $filter
     *
     * @return array
     */
    public function getRootBranchItemsAction(
        $scope = null,
        $fields = null,
        $depth = null,
        $limit = null,
        $offset = null,
        $filter = null
    )
    {
        return $this->getBranchItems(null, $filter, $fields, $scope, $depth, $limit, $offset);
    }

    /**
     * @ApiDoc(
     *    description="Returns a branch (nested set)"
     * )
     *
     * @Rest\QueryParam(name="fields", requirements=".+", description="Comma separated list of field names")
     * @Rest\QueryParam(name="filter", map=true, requirements=".*", description="Simple filtering per field")
     * @Rest\QueryParam(name="limit", requirements="[0-9]+", description="Limits the result")
     * @Rest\QueryParam(name="offset", requirements="[0-9]+", description="Offsets the result")
     * @Rest\QueryParam(name="scope", requirements=".*", description="Nested set scope")
     * @Rest\QueryParam(name="depth", requirements="[0-9]+", default=1, description="Max depth")
     * @Rest\QueryParam(name="withAcl", requirements=".+", default=false, description="With ACL information")
     *
     * @Rest\View()
     * @Rest\Get("/{pk}/:branch")
     *
     * @param Request $request
     * @param string $fields
     * @param string $scope
     * @param integer $depth
     * @param string $limit
     * @param string $offset
     * @param string $filter
     * @param bool $withAcl
     *
     * @return array
     */
    public function getBranchItemsAction(
        Request $request,
        $fields = null,
        $scope = null,
        $depth = null,
        $limit = null,
        $offset = null,
        $filter = null,
        $withAcl = null
    )
    {
        $primaryKey = $this->extractPrimaryKey($request);
        return $this->getBranchItems($primaryKey, $filter, $fields, $scope, $depth, $limit, $offset, $withAcl);
    }

    /**
     * @ApiDoc(
     *    description="Returns a branch direct children count (nested set)"
     * )
     *
     * @Rest\QueryParam(name="filter", map=true, requirements=".*", description="Simple filtering per field")
     * @Rest\QueryParam(name="scope", requirements=".*", description="Nested set scope")
     *
     * @Rest\View()
     * @Rest\Get("/{pk}/:children-count")
     * @Rest\Get("/:children-count")
     *
     * @param Request $request
     * @param string $scope
     * @param string $filter
     *
     * @return array
     */
    public function getBranchChildrenCountAction(Request $request, $scope = null, $filter = null)
    {
        $primaryKey = $this->extractPrimaryKey($request);

        if ($primaryKey) {
            return $this->getBranchChildrenCount($primaryKey, $scope, $filter);
        } else {
            return $this->getBranchChildrenCount(null, $scope, $filter);
        }

    }

    /**
     * @ApiDoc(
     *    description="Moves a item (nested set)"
     * )
     *
     * @Rest\RequestParam(name="target", requirements=".+", description="The target PK")
     * @Rest\RequestParam(name="position", strict=false, requirements="prev|next|insert", default="first", description="The position")
     * @Rest\RequestParam(name="targetObjectKey", strict=false, description="Target object key. Usually blank.")
     * @ //Rest\RequestParam(name="overwrite", strict=false, requirements="true|false", default="false", description="If the target should be replaced when exist")
     *
     * @Rest\View()
     * @Rest\Post("/{pk}/:move")
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function moveItemAction(Request $request, ParamFetcher $paramFetcher)
    {
        $primaryKey = $this->extractPrimaryKey($request);
        $target = $paramFetcher->get('target');
        $position = $paramFetcher->get('position') ?: 'first';
        $targetObjectKey = $paramFetcher->get('targetObjectKey');
//        $overwrite = $paramFetcher->get('overwrite');

        return $this->moveItem(
            $primaryKey,
            $target,
            $position,
            $targetObjectKey//,
//            filter_var($overwrite, FILTER_VALIDATE_BOOLEAN)
        );

    }

    /**
     * @ApiDoc(
     *    description="Returns all roots items (nested set)"
     * )
     *
     * @Rest\QueryParam(name="domain", requirements=".+", description="If the root object is domainDepended, filter by it")
     * @Rest\QueryParam(name="lang", requirements=".+", description="If the root object is multiLanguage, filter by it")
     *
     * @Rest\View()
     * @Rest\Get("/:roots")
     *
     * @return mixed
     */
    public function getRootsAction(ParamFetcher $paramFetcher)
    {
        return $this->getRoots(null, $paramFetcher->get('lang'), $paramFetcher->get('domain'));
    }

    /**
     * @ApiDoc(
     *    description="Returns the root item (for a scope) (nested set)"
     * )
     *
     * @Rest\QueryParam(name="scope", requirements=".+", description="The scope of the root item, if available.")
     *
     * @Rest\View()
     * @Rest\Get("/:root")
     *
     * @param string $scope
     *
     * @return mixed
     */
    public function getRootAction($scope = null)
    {
        return $this->getRoot($scope);
    }

    /**
     * @ApiDoc(
     *    description="Returns the parent (nested set)"
     * )
     *
     * @Rest\View()
     * @Rest\Get("/{pk}/:parent")
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function getParentAction(Request $request)
    {
        $primaryKey = $this->extractPrimaryKey($request);

        return $this->getParent($primaryKey);
    }

    /**
     * @ApiDoc(
     *    description="Returns all parents (nested set)"
     * )
     *
     * @Rest\View()
     * @Rest\Get("/{pk}/:parents")
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function getParentsAction(Request $request)
    {
        $primaryKey = $this->extractPrimaryKey($request);

        return $this->getParents($primaryKey);
    }

} 