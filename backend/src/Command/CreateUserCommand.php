<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un nouvel utilisateur',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addArgument('name', InputArgument::REQUIRED, 'Nom de l\'utilisateur')
            ->addArgument('roles', InputArgument::OPTIONAL, 'Rôles (séparés par des virgules)', 'ROLE_USER')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');
        $rolesString = $input->getArgument('roles');
        
        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }
        
        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        
        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Définir les rôles
        $roles = array_map('trim', explode(',', $rolesString));
        $user->setRoles($roles);
        
        // Persister en base
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $io->success(sprintf('Utilisateur "%s" créé avec succès!', $email));
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['ID', $user->getId()],
                ['Email', $user->getEmail()],
                ['Nom', $user->getName()],
                ['Rôles', implode(', ', $user->getRoles())],
                ['Créé le', $user->getCreatedAt()->format('Y-m-d H:i:s')],
            ]
        );

        return Command::SUCCESS;
    }
}