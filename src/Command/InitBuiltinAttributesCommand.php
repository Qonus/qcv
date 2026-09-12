<?php
namespace App\Command;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:init-builtin-attributes',
    description: 'Ensures all candidates have AttributeValue records for all built-in attributes.',
)]
class InitBuiltinAttributesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $builtinAttributes = $this->em->getRepository(Attribute::class)->findBy(['isBuiltin' => true]);

        if (empty($builtinAttributes)) {
            $io->warning('No built-in attributes found in the database.');
            return Command::SUCCESS;
        }

        $users = $this->em->getRepository(User::class)->findAll();
        
        $createdCount = 0;
        $batchSize = 100;

        foreach ($users as $user) {
            $existingAttributeIds = [];
            foreach ($user->getAttributeValues() as $av) {
                $existingAttributeIds[$av->getAttribute()->getId()] = true;
            }

            foreach ($builtinAttributes as $builtin) {
                if (!isset($existingAttributeIds[$builtin->getId()])) {
                    $av = new AttributeValue();
                    $av->setCandidate($user);
                    $av->setAttribute($builtin);
                    
                    $this->em->persist($av);
                    $createdCount++;

                    if ($createdCount % $batchSize === 0) {
                        $this->em->flush();
                    }
                }
            }
        }

        $this->em->flush();

        $io->success("Finished! Created {$createdCount} missing built-in attribute records.");

        return Command::SUCCESS;
    }
}