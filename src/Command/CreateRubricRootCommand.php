<?php

namespace Argayash\CoreBundle\Command;

use Application\Argayash\CoreBundle\Entity\Rubric;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRubricRootCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('iphp:core:createrootrubric')
            ->setDescription('Create root rubric');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rubricManager = $this->getContainer()->get('iphp.core.rubric.manager');

        $rootNodes = $rubricManager->getRepository()->getRootNodes();

        if (count($rootNodes) > 0) {
            $output->writeln('root rubric already exists');

            return;
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $rootRubric = new Rubric();
        $rootRubric->setTitle('Website title')->setPath('')->setStatus(true);

        $em->persist($rootRubric);
        $em->flush();
    }
}
