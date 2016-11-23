<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Marks12\RESTGeneratorBundle\Controller\Marks12Controller;

/**
 * User controller.
 * @RouteResource("User")
 */
class UserRESTController extends Marks12Controller
{
    /**
     * Get a User entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get User")
     *
     *
     * @return Response
     *
     */
    public function getAction(User $entity)
    {
        return $entity;
    }
    /**
     * Get all User entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     *
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="How many notes to return.")
     * @QueryParam(name="order_by", nullable=true, description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, description="Filter by fields. Must be an array ie. &filters[id]=3")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get all User")
     *

     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('AppBundle:User')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                return $entities;
            }

            return FOSView::create('Not Found', Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Create a User entity.
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Send money between two users")
     *
     * @param Request $request
     *
     * @return Response
     *
     */
    public function postAction(Request $request)
    {
        $entity = new User();
        $form = $this->createForm(get_class(new UserType()), $entity, array("method" => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $entity;
        }

        return FOSView::create(array('errors' => $form->getErrors()), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Send money from one to another.
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @RequestParam(name="reciever_id", nullable=false, description="User who gets money")
     * @RequestParam(name="cost", nullable=false, description="Money size for transfer")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Create User")
     *
     * @param Request $request
     *
     * @return Response
     *
     */
    public function postTransferAction(User $sender, ParamFetcherInterface $paramFetcher)
    {

        $em = $this->getDoctrine()->getManager();
        $reciever_id = (int)$paramFetcher->get('reciever_id');
        $cost = (float)$paramFetcher->get('cost');

        $reciever = $em->getRepository('AppBundle:User')->find($reciever_id);
        $sender_balance = $sender->getBalance();

        if(!$sender) {
            return FOSView::create(array('errors' => ['Пользователя со счета которого выполняется перевод не сущетсвует']), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(!$reciever) {
            return FOSView::create(array('errors' => ['Пользователя которому выполняется перевод не сущетсвует']), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($cost < 1) {
            return FOSView::create(array('errors' => ['Размер перевода не может быть мене 1 у.е.']), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($sender->getBalance() < $cost) {
            return FOSView::create(array('errors' => ['У отправителя недостаточно средств для перевода']), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $sender->setBalance($sender->getBalance() - $cost);

        $em->persist($sender);

        $reciever->setBalance($reciever->getBalance() + $cost);

        $em->persist($reciever);
        $em->flush();

        return FOSView::create(array('success' => true), Response::HTTP_OK);
    }


    /**
     * Update a User entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Update User")
     *
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function putAction(Request $request, User $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(get_class(new UserType()), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();

                return $entity;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Partial Update to a User entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Partial update User")
     *
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function patchAction(Request $request, User $entity)
    {
        return $this->putAction($request, $entity);
    }
    /**
     * Delete a User entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $entity
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Delete User")
     *
     * @return Response
     */
    public function deleteAction(Request $request, User $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
