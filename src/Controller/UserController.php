<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

#[Route('/user', name: 'user')]
class UserController extends AbstractController
{
    #[Route('/register', name: '_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Codificar la contraseña antes de persistir el usuario
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword() // Obtén la contraseña sin cifrar del objeto User
            );
            $user->setPassword($hashedPassword); // Almacena la contraseña codificada

            // Se guarda el usuario en la base de datos
            $entityManager->persist($user);
            $entityManager->flush();

            // Limpiar el formulario y mostrar un mensaje de éxito
            $this->addFlash('success', 'Usuario registrado exitosamente');
            return $this->redirectToRoute('user_register'); // Asegúrate de redirigir a la ruta correcta
        }

        return $this->render('user/user_register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/delete", name: "_delete")]
    public function userDelete(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crear un formulario simple para ingresar el ID
        $defaultData = ['message' => 'Ingrese el ID del usuario a eliminar'];
        $form = $this->createFormBuilder($defaultData)
            ->add('userId', TextType::class, [
                'label' => 'ID del Usuario',
                'required' => true
            ])
            
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userId = $data['userId'];

            // Buscar al usuario por ID
            $user = $entityManager->getRepository(User::class)->find($userId);

            if ($user) {
                // Eliminar el usuario
                $entityManager->remove($user);
                $entityManager->flush();

                // Mostrar mensaje de éxito
                $this->addFlash('success', 'Usuario eliminado exitosamente');
            } else {
                // Mostrar mensaje de error si no se encuentra el usuario
                $this->addFlash('error', 'Usuario no encontrado');
            }
        }

        return $this->render('user/delete.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/modify', name: '_modify')]
    public function modifyUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Obtener todos los usuarios para el spinner
        $users = $entityManager->getRepository(User::class)->findAll();

        // Crear el formulario vacío que se cargará con los datos del usuario seleccionado
        $form = $this->createForm(UserType::class, new User());


        return $this->render('user/modify.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    

    #[Route('/get/{id}', name: 'get_user_data', methods: ['GET'])]
    public function getUserData(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Usuario no encontrado']);
        }

        return $this->json([
            'success' => true,
            'user' => [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
            ],
        ]);
    }

}
