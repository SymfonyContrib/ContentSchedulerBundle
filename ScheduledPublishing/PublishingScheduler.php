<?php

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\ScheduledPublishing;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use SymfonyContrib\Bundle\ContentSchedulerBundle\Entity\Schedule;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PublishingScheduler
{
    /**
     * @var EntityManager
     */
    public $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function handleForm(Request $request, Form $form, $propertyPath, $entityClass, $entityId = null)
    {
        $schedulerData = null;
        $formName      = $form->getName();
        $accessor      = PropertyAccess::createPropertyAccessor();

        if ($request->isMethod('POST')) {
            $formData      = $request->request->get($formName, null) ?: [];
            $schedulerData = $accessor->getValue($formData, $propertyPath);
        } elseif ($entityId) {
            $schedulerData = $this->getDefaultFormData($entityClass, $entityId);
        }
        if (!empty($schedulerData)) {
            $schedulerForm = $accessor->getValue($form->all(), $propertyPath);
            $this->formSetData($schedulerForm, $schedulerData);
        }

        return $schedulerData;
    }

    public function getSchedule($entityClass, $entityId, $method)
    {
        $repo   = $this->em->getRepository('ContentSchedulerBundle:Schedule');
        $action = $entityClass . ':' . $entityId . ':' . $method;

        return $repo->findOneBy(['action' => $action]);
    }

    public function getDefaultFormData($entityClass, $entityId)
    {
        $publishSchedule   = $this->getSchedule($entityClass, $entityId, 'publish');
        $unpublishSchedule = $this->getSchedule($entityClass, $entityId, 'unpublish');

        return $this->prepareFormData($publishSchedule, $unpublishSchedule);
    }

    public function prepareFormData(Schedule $publish = null, Schedule $unpublish = null)
    {
        $formData = [
            'publishWhen' => [
                'date' => '',
                'time' => ''
            ],
            'unpublishWhen' => [
                'date' => '',
                'time' => ''
            ],
        ];

        if ($publish) {
            $formData['schedulePublish'] = 1;
            $formData['publishWhen']['date'] = $publish->getWhen()->format('Y-m-d');
            $formData['publishWhen']['time'] = $publish->getWhen()->format('H:i');
        }

        if ($unpublish) {
            $formData['scheduleUnpublish'] = 1;
            $formData['unpublishWhen']['date'] = $unpublish->getWhen()->format('Y-m-d');
            $formData['unpublishWhen']['time'] = $unpublish->getWhen()->format('H:i');
        }

        return $formData;
    }

    public function formSetData(Form $form, array $data)
    {
        // Datetime does not inherit properly so they are being done individually.
        if (isset($data['schedulePublish'])) {
            if (!empty($data['publishWhen']['date']) &&
                !empty($data['publishWhen']['time']) &&
                !$form->isSubmitted()
            ) {
                $form->get('schedulePublish')->setData(true);
                $data['publishWhen'] = new \DateTime(implode(' ', $data['publishWhen']));
                $publishWhen = $form->get('publishWhen');
                $publishWhen->get('date')->setData([
                    'year' => $data['publishWhen']->format('Y'),
                    'month' => $data['publishWhen']->format('m'),
                    'day' => $data['publishWhen']->format('d'),
                ]);
                $publishWhen->get('time')->setData([
                    'hour' => $data['publishWhen']->format('H'),
                    'minute' => $data['publishWhen']->format('i'),
                ]);
            } else {
                if (empty($data['publishWhen']['date']) ||
                    empty($data['publishWhen']['time'])
                ) {
                    $form->get('publishWhen')->get('date')
                        ->addError(new FormError('Publish date and time are required for scheduling.'));
                }
            }
        }

        if (isset($data['scheduleUnpublish'])) {
            if (!empty($data['unpublishWhen']['date']) &&
                !empty($data['unpublishWhen']['time']) &&
                !$form->isSubmitted()
            ) {
                $form->get('scheduleUnpublish')->setData(true);
                $data['unpublishWhen'] = new \DateTime(implode(' ', $data['unpublishWhen']));
                $unpublishWhen = $form->get('unpublishWhen');
                $unpublishWhen->get('date')->setData([
                    'year' => $data['unpublishWhen']->format('Y'),
                    'month' => $data['unpublishWhen']->format('m'),
                    'day' => $data['unpublishWhen']->format('d'),
                ]);
                $unpublishWhen->get('time')->setData([
                    'hour' => $data['unpublishWhen']->format('H'),
                    'minute' => $data['unpublishWhen']->format('i'),
                ]);
            } else {
                if (empty($data['unpublishWhen']['date']) ||
                    empty($data['unpublishWhen']['time'])
                ) {
                    $form->get('unpublishWhen')
                        ->addError(new FormError('Unpublish date and time are required for scheduling.'));
                }
            }
        }
    }

    public function formSubmit($formValues, $entityClass, $entityId)
    {
        $methods = [
            'publish',
            'unpublish',
        ];

        foreach ($methods as $method) {
            if (isset($formValues['schedule' . ucfirst($method)])) {
                $date = $formValues[$method . 'When']['date'];
                $time = $formValues[$method . 'When']['time'];

                if (!$date || !$time) {
                    // Date and time are required.
                    //throw new \Exception();
                }

                // Create schedule for publishing.
                $this->createOrUpdateSchedule($entityClass, $entityId, $method, new \DateTime($date . ' ' . $time));
            } else {
                $this->deleteScheduleByAction($entityClass, $entityId, $method);
            }
        }
    }

    public function createOrUpdateSchedule($entityClass, $entityId, $method, $when)
    {
        $action = $entityClass . ':' . $entityId . ':' . $method;
        $schedule = $this->em->getRepository('ContentSchedulerBundle:Schedule')
            ->findOneBy(['action' => $action]) ?: new Schedule();
        $schedule->setAction($action);
        $schedule->setWhen($when);

        $this->em->persist($schedule);
        //$this->em->flush($schedule);
    }

    public function deleteScheduleByAction($entityClass, $entityId, $method)
    {
        $action = $entityClass . ':' . $entityId . ':' . $method;
        $this->em->createQueryBuilder()
            ->delete('ContentSchedulerBundle:Schedule', 'c')
            ->where('c.action = :action')
            ->setParameter('action', $action)
            ->getQuery()
            ->execute();
    }

    public function runDueActions()
    {
        $repo = $this->em->getRepository('ContentSchedulerBundle:Schedule');
        $due  = $repo->findDue();

        foreach ($due as $schedule) {
            $this->executeSchedule($schedule);
        }
    }

    public function executeSchedule(Schedule $schedule)
    {
        // Split out action into relevant parts.
        list($bundle, $class, $id, $method) = explode(':', $schedule->getAction());
        // Execute the action.
        $this->em->find($bundle . ':' . $class, $id)->{$method}();
        // Delete the schedule.
        $this->em->remove($schedule);
        $this->em->flush();
    }
}
