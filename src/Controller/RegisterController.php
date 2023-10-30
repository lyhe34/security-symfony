<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function addUser(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManger): Response
    {
        $msg = "";
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if($userRepository->findOneBy(['email' => $user->getEmail()]) != null)
            {
                $msg = "L'utilisateur existe déjà";
            }
            else
            {
                $pass = $request->request->all('register')['password']['first'];
                
                $hash = $hasher->hashPassword($user, $pass);
    
                $user->setPassword($hash);
                $user->setRoles(['ROLE_USER']);
                $user->setActivated(false);
    
                $entityManger->persist($user);
                $entityManger->flush();
    
                $msg = "Le compte a été ajouté en BDD";
            }    
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'msg' => $msg,
        ]);
    }
}
