<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register',methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SerializerInterface $serializer,UserRepository $userRepository): Response
    {
        $json = $request->getContent();
        $user = $serializer->deserialize($json,User::class,'json');

        if ($user) {
            $check= $userRepository->findBy(["username"=>$user->getUsername()]);
            if($check){
                return $this->json("le username ". $user->getUsername() ." est deja pris",400);
            }
            $user->setPassword(
                $userPasswordHasher->hashPassword($user,$user->getPassword())
            );
            $profile= new Profile();
            $profile->setOfUser($user);
            $profile->setUsername($user->getUsername());
            $entityManager->persist($profile);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json("user ajouter");
        }
        return $this->json("error");
    }

    #[Route('/api/profile', name: 'app_profile',methods: ['GET'])]
    public function index(): Response
    {
        return $this->json($this->getUser()->getProfile(),200,[],['groups'=>'group:order-all']);

    }
}
