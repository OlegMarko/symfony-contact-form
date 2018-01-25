<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RegisterController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/register", name="registration")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();

        $form = $this->createUserRegistrationForm($user);

        return $this->render('auth/register.html.twig', array(
            'registration_form' => $form->createView()
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/registration-form-submission", name="handle_registration_form_submission")
     * @Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function handleFormSubmissionAction(Request $request)
    {
        $user = new User();

        $form = $this->createUserRegistrationForm($user);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('auth/register.html.twig', array(
                'registration_form' => $form->createView()
            ));
        }

        $password = $this
            ->get('security.password_encoder')
            ->encodePassword(
                $user,
                $user->getPlainPassword()
            )
        ;

        $user->setPassword($password);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // Log In user after registration
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main',
            $user->getRoles()
        );

        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
        // end Log In user.

        $this->addFlash('success','You are now successfully registered!');

        return $this->redirectToRoute('homepage');
    }

    private function createUserRegistrationForm($user)
    {
        return $this->createForm(UserType::class, $user, [
            'action' => 'registration-form-submission'
        ]);
    }
}
