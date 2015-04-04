<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Measurement;
use AppBundle\Entity\MeasurementProbe;
use AppBundle\Form\MeasurementType;
use AppBundle\Service\DeviceAPIServiceInterface;
use AppBundle\Service\MeasurementDeamonService;
use AppBundle\Service\MeasurementService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Measurement controller.
 *
 * @Route("/measurement")
 * @Security("has_role('ROLE_AUTHENTICATED')")
 */
class MeasurementController extends Controller
{
    /**
     * Creates a new Measurement entity.
     *
     * @Route("/new/{silent}", defaults={"silent" = false}, name="measurement_new")
     * @Method({"GET","POST"})
     * @Template("AppBundle:Measurement:new.html.twig")
     */
    public function newAction(Request $request)
    {
        $entity = new Measurement();

        $form = $this->createCreateForm($entity);

        if (!$request->get('silent') && $request->isMethod('POST')) {
            $form->handleRequest($request);
        }

        if (!$request->get('silent') && $request->isMethod('POST') && $form->isValid()) {
            $entity->setStart(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            /** @var MeasurementDeamonService $deamon */
            $deamon = $this->get('measurement_deamon_service');
            $deamon->start($entity);

            return $this->redirect($this->generateUrl('measurement', array(
                'id' => $entity->getId()
            )));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Measurement entity.
     *
     * @param Measurement $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Measurement $entity)
    {
        $request = $this->get('request');
        if ($this->get('request')->get('silent') || $request->isMethod('GET')) {
            $this->initMeasurement($entity);
        }

        $form = $this->createForm(new MeasurementType(), $entity, array(
            'action' => $this->generateUrl('measurement_new'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Speichern'));

        return $form;
    }

    /**
     * Initializes the measurement with defualts.
     *
     * @param Measurement $measurement
     */
    private function initMeasurement(Measurement $measurement)
    {
        $measurement->setName('Grillen ' . date('d.m.Y'));

        /** @var Request $request */
        $request = $this->get('request');

        $formData = $request->request->get('appbundle_measurement');
        $deviceId = isset($formData['device']) ? (int)$formData['device'] : 0;
        $em = $this->getDoctrine()->getManager();

        if (!$deviceId) {
            $device = $em->getRepository('AppBundle:Device')->getOne();
        }

        if (!$device) {
            $device = $em->getRepository('AppBundle:Device')->find($deviceId);
        }

        if (!$device) {
            return;
        }

        $measurement->setDevice($device);

        foreach ($device->getProbes() as $probe) {
            $tmpProbe = new MeasurementProbe();
            $tmpProbe->setProbe($probe);
            $tmpProbe->setColor($probe->getDefaultColor());
            $tmpProbe->setName($probe->getDefaultName());
            $tmpProbe->setMin(MeasurementProbe::DEFAULT_MIN);
            $tmpProbe->setMax(MeasurementProbe::DEFAULT_MAX);
            $measurement->addProbe($tmpProbe);
        }
    }

    /**
     * Displays a form to edit an existing Measurement entity.
     *
     * @Route("/{id}/edit", name="measurement_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction($id)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Measurement')->find($id);

        if (!$entity || !$entity->isActive()) {
            throw $this->createNotFoundException('Unable to find active Measurement entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($request->isMethod('POST') && $editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('measurement', array(
                'id' => $entity->getId()
            )));
        }

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Device entity.
     *
     * @param Device $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Measurement $entity)
    {
        $form = $this->createForm(new MeasurementType(), $entity, array(
            'action' => $this->generateUrl('measurement_edit', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->remove('device');

        $form->add('submit', 'submit', array('label' => 'Speichern'));

        return $form;
    }

    /**
     * Lists all past measurements entities.
     *
     * @Route("/{id}", name="measurement")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Measurement $entity */
        $entity = $em->getRepository('AppBundle:Measurement')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Measurement entity.');
        }

        $restartForm = $this->createDropdownForm(
            $this->generateUrl('measurement_restart', ['id' => $entity->getId()])
        );
        $stopForm = $this->createDropdownForm(
            $this->generateUrl('measurement_stop', ['id' => $entity->getId()])
        );
        $stopAndShutDownForm = $this->createDropdownForm(
            $this->generateUrl('measurement_stop_shut_down', ['id' => $entity->getId()])
        );

        /** @var MeasurementService $measurementService */
        $measurementService = $this->get('measurement_service');
        $snapshot = $measurementService->getSnapshot($entity);

        return [
            'entity' => $entity,
            'dropdownForms' => [
                'restart' => $restartForm->createView(),
                'stop' => $stopForm->createView(),
                'stopAndShutDown' => $stopAndShutDownForm->createView(),
            ],
            'snapshot' => $snapshot,
        ];
    }

    /**
     * Generates a form for dropdown actions (e.g. stop measurement, ...)
     *
     * @param $url
     * @return \Symfony\Component\Form\Form
     */
    private function createDropdownForm($url)
    {
        return $this->createFormBuilder()
            ->setAction($url)
            ->setMethod('POST')
            ->getForm();
    }

    /**
     * Restarts the measurements deamon.
     *
     * @Route("/{id}/restart", name="measurement_restart")
     * @Method("POST")
     * @Template()
     */
    public function restartAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Measurement')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Measurement entity.');
        }

        /** @var MeasurementDeamonService $deamon */
        $deamon = $this->get('measurement_deamon_service');
        $deamon->restart($entity);

        return $this->redirect($this->generateUrl('measurement', array(
            'id' => $entity->getId()
        )));
    }

    /**
     * Stops the measurement.
     *
     * @Route("/{id}/stop", name="measurement_stop")
     * @Method("POST")
     * @Template()
     */
    public function stopAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Measurement $entity */
        $entity = $em->getRepository('AppBundle:Measurement')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Measurement entity.');
        }

        $entity->setEnd(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        /** @var MeasurementDeamonService $deamon */
        $deamon = $this->get('measurement_deamon_service');
        $deamon->stop($entity);

        return $this->redirect($this->generateUrl('measurement', array(
            'id' => $entity->getId()
        )));
    }

    /**
     * Stops the measurement and shuts down the device.
     *
     * @Route("/{id}/stopAndShutDown", name="measurement_stop_shut_down")
     * @Method("POST")
     * @Template()
     */
    public function stopAndShutDownAction($id)
    {
        $response = $this->stopAction($id);

        /* Shut down device. */
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Measurement')->find($id);
        $deviceAPI = $this->get('device_api_service');
        $deviceAPI->shutdown($entity->getDevice());

        return $response;
    }
}
