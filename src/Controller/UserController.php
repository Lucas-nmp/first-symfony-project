<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user')]
class UserController extends AbstractController
{
    #[Route('/register', name: '_register')]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Se guarda el usuario en la base de datos
            $entityManager->persist($user);
            $entityManager->flush();

            // Limpiar el formulario y mostrar un mensaje de éxito
            $this->addFlash('success', 'Usuario registrado exitosamente');
            return $this->redirectToRoute('user_register'); // Asegúrate de redirigir a la ruta correcta
        }

        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

