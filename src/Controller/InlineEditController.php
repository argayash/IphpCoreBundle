<?php
/**
 * Created by Vitiko
 * Date: 10.07.12
 * Time: 16:43.
 */

namespace Iphp\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InlineEditController extends Controller
{
    public function formAction(Request $request)
    {
        $entityClassPath = $request->query->get('entityClassPath');
        $entityId = $request->query->get('entityId');

        //if (! $entityClassPath ||  !$entityId )

        $entity = $this->getDoctrine()->getRepository($entityClassPath)->findOneById($entityId);

        if (!$entity) {
            return $this->render('IphpCoreBundle:InlineEdit:form.html.twig', []);
        }

        $formClass = $this->getDefaultInlineFormClass($entity);

        if (!class_exists($formClass)) {
            throw new \Exception('class ' . $formClass . ' not found');
        }
        $form = $this->createForm(new $formClass(), $entity);

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            $this->getDoctrine()->getManager()->persist($entity);
            $this->getDoctrine()->getManager()->flush();

            return $this->render('IphpCoreBundle:InlineEdit:form-finishedit.html.twig', [
                /*'form' => $form->createView(),
              'entityClassPath' => $entityClassPath,
              'entityId' => $entityId*/
            ]);
        }

        return $this->render('IphpCoreBundle:InlineEdit:form.html.twig', [
                'form' => $form->createView(),
                'entityClassPath' => $entityClassPath,
                'entityId' => $entityId,
            ]);
    }

    protected function getDefaultInlineFormClass($entity)
    {
        return str_replace('\\Entity\\', '\\Form\\InlineEdit\\', get_class($entity)) . 'Type';
    }
}
