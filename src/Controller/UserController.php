<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $manager;
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManager();
        $this->passwordHasher = $passwordHasher;

        header('Access-Control-Allow-Origin: *');

    }

    private function validateDate($date, $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    #[Route('/register', name: 'app_user', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $last_name = $request->request->get('last_name');
        $first_name = $request->request->get('first_name');
        $birthday = $request->request->get('birthday');
        $error = false;
        $data = [
            'email' => $email,
            'password' => $password,
            'last_name' => $last_name,
            'first_name' => $first_name,
            'birthday' => $birthday,
        ];

        $user = new User();

        if (empty($email) || empty($password) || empty($last_name) || empty($first_name) || empty($birthday)) {
            $error = true;
            return $this->json([
                'message' => 'Missing parameters',
                'data' => $data,
            ]);
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = true;
            return $this->json([
                'message' => 'Invalid email',
            ]);
        } elseif (strlen($password) < 8) {
            $error = true;
            return $this->json([
                'message' => 'Password must be at least 8 characters',
            ]);
        } elseif (strlen($last_name) < 2) {
            $error = true;
            return $this->json([
                'message' => 'Last name must be at least 2 characters',
            ]);
        } elseif (strlen($first_name) < 2) {
            $error = true;
            return $this->json([
                'message' => 'First name must be at least 2 characters',
            ]);
        } elseif (!$this->validateDate($birthday)) {
            $error = true;
            return $this->json([
                'message' => 'Invalid birthday',
            ]);
        } elseif ($this->manager->getRepository(User::class)->findOneBy(['email' => $email])) {
            $error = true;
            return $this->json([
                'message' => 'Email already exists',
            ]);
        }


        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setName($last_name);
        $user->setFirstName($first_name);
        $user->setBirthday($birthday);
        $user->setRoles(['ROLE_USER']);
        $this->manager->persist($user);
        $this->manager->flush();
        return $this->json([
            'message' => 'User created',
        ], 201);

    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): JsonResponse
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $error = false;

        if (empty($email) || empty($password)) {
            $error = true;
            return $this->json([
                'message' => 'Missing parameters',
            ], 200);
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = true;

            return $this->json([
                'message' => 'Invalid email',
            ], 200);
        }

        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $error = true;
            return $this->json([
                'message' => 'User not found',
            ], 200);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            $error = true;
            return $this->json([
                'message' => 'Invalid password',
            ], 200);
        }
        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'last_name' => $user->getName(),
            'first_name' => $user->getFirstName(),
            'birthday' => $user->getBirthday(),
            'roles' => $user->getRoles(),
        ];

        return $this->json([
            'message' => 'Logged in',
            'data_user' => $data,
        ], 200);
    }
}
