<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-user';
    protected static $defaultDescription = 'Creates a new user.';

    private $entityManager;

    public function __construct(bool $requirePassword = false , bool $requireUsername = false ,EntityManagerInterface $entityManager )
    {
        $this->entityManager = $entityManager;
        $this->requireUsername = $requireUsername;
        $this->requirePassword = $requirePassword;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            ->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password')
        ;


    }

    protected function execute(InputInterface $input, OutputInterface $output ): int
    {

        $output->writeln($this->createUser($input));

        $output->writeln([
            'Admin User Creator',
            '============',
            '',
        ]);

        // retrieve the argument value using getArgument()
        $output->writeln('Username: '.$input->getArgument('username'));
        $output->writeln('Password: '.$input->getArgument('password'));

        return Command::SUCCESS;
    }

    private function createUser($input)
    {
        $user = new User();
        $em = $this->entityManager;
        $user->setUsername($input->getArgument('username'));
        $user->setEmail(rand(100,500).'@gmail.com');
        $user->setPhone(rand(100000000,999999999));
        $user->setAddress(rand(100,999));
        $user->setPassword(
            password_hash($input->getArgument('password'), PASSWORD_DEFAULT)
        );
        $user->setCreated(new \DateTime(date('Y-m-d')));
        $user->addRole($em->getRepository(Role::class)->findOneBy(['roleName'=>'ROLE_ADMIN']));
        $em->persist($user);
        $em->flush();

        return true;
    }
}