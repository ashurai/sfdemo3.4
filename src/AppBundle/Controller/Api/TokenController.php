<?php
namespace AppBundle\Controller\Api;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use AppBundle\Entity\User;
use AppBundle\Helpers\ApiHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
/**
 * Class TokenController
 * @package AppBundle\Controller\Api
 */
class TokenController extends FOSRestController
{
    /**
     * @Rest\Post("/tokens", name="api_token_new",
     *     options={"method_prefix" = false},
     *     defaults={"_format"="json"}
     * )
     *
     * @ApiDoc(
     *    section     = "Security",
     *    description = "Get user token",
     *    parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "description"="username or email"},
     *      {"name"="pass",     "dataType"="string", "required"=true, "description"="password "},
     *    },
     *    statusCodes={
     *      200 = "Returned when success",
     *      222 = "Returned when user is not enabled",
     *      401 = "Returned when no authorization token JWT",
     *      404 = "Returned when the user is not found",
     *    }
     * )
     */
    public function newTokenAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $username = $request->get('username');
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->findOneBy(['email' => $username]);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        if (!$user->isEnabled()){
            //throw new HttpException(ApiHelper::USER_DISABLED, $this->get('translator')->trans('security.user_is_disabled'));
            return $this->handleView($this->view('user is disabled', ApiHelper::USER_DISABLED));
        }
        $pass = $request->get('pass');
        $isValid = $this->get('security.password_encoder')->isPasswordValid($user, $pass);
        if (!$isValid) {
            throw new BadCredentialsException();
        }
        $token = $this->get('lexik_jwt_authentication.encoder')->encode([
            'username' => $user->getUsername(),
            'id'       => $user->getId(),
            'roles'    => $user->getRoles(),
            'exp'      => time() + $this->getParameter('jwt_token_ttl')
        ]);
        // Force login
        $tokenLogin = new UsernamePasswordToken($user, $pass, "public", $user->getRoles());
        $this->get("security.token_storage")->setToken($tokenLogin);
        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $tokenLogin);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        $view = $this->view([
            'token' => $token,
            'user'  => $user
        ]);
        $context = new Context();
        $context->addGroups(array_merge(['Public'],$user->getRoles()));
        $view->setContext($context);
        return $this->handleView($view);
    }
    /**
     * This method is created to provide a new JWT for known clients
     *
     * @Rest\Post("/tokens/client", name="api_token_new_client",
     *     options={"method_prefix" = false},
     *     defaults={"_format"="json"}
     * )
     *
     * @ApiDoc(
     *    section     = "Security",
     *    description = "Get user token JWT for known clients",
     *    parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "description"="username or email to search"},
     *      {"name"="client_id",  "dataType"="string", "required"=true, "description"="client_id"},
     *      {"name"="client_secret",  "dataType"="string", "required"=true, "description"="client_secret"},
     *    },
     *    statusCodes={
     *      200 = "Returned when success",
     *      401 = "Returned when no authorization",
     *      404 = "Returned when the user is not found",
     *      222 = "Returned when user is not enabled",
     *    },
     *    tags={
     *      "deprecated iddoct temporally function"
     *    }
     * )
     */
    public function newTokenClientAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $username = $request->get('username');
        $apiClient = $request->get('client_id');
        $apiSecret = $request->get('client_secret');
        $apiCredentials = $this->getParameter('api_client_credentials_available');
        $isValid = (array_key_exists($apiClient,$apiCredentials) && $apiCredentials[$apiClient] === $apiSecret);
        if (!$isValid) {
            throw new BadCredentialsException();
        }
        /** @var User $user */
        $user = $em->getRepository('MedlabMGMedlabBundle:User')->findOneBy(['email' => $username]);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        if (!$user->isEnabled()){
            throw new HttpException(ApiHelper::USER_DISABLED, $this->get('translator')->trans('security.user_is_disabled'));
        }
        $user = $this->get('fos_user.user_provider.username_email')->loadUserByUsername($username);
        $isValid = (array_key_exists($apiClient,$apiCredentials) && $apiCredentials[$apiClient] === $apiSecret);
        if (!$isValid) {
            throw new BadCredentialsException();
        }
        $token = $this->get('lexik_jwt_authentication.encoder')->encode([
            'username' => $user->getUsername(),
            'id'       => $user->getId(),
            'roles'    => $user->getRoles(),
            'exp'      => time() + $this->getParameter('jwt_token_ttl')
        ]);
        // Force login
        $tokenLogin = new UsernamePasswordToken($user, null, "public", $user->getRoles());
        $this->get("security.token_storage")->setToken($tokenLogin);
        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $tokenLogin);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        $view = $this->view([
            'token' => $token,
            'user'  => $user
        ]);
        $context = new Context();
        $context->addGroups(array_merge(['Public'],$user->getRoles()));
        $view->setContext($context);
        return $this->handleView($view);
    }
    /**
     * @Security("has_role('ROLE_USER')")
     *
     * @Rest\Post("/logout", name="api_logout",
     *     options={"method_prefix" = false},
     *     defaults={"_format"="json"}
     * )
     *
     * @ApiDoc(
     *    section     = "Security",
     *    description = "Logout user",
     *    headers={
     *       {
     *          "name"="JWT",
     *          "required"=true,
     *          "description"="JWT Authorization token"
     *       }
     *    },
     *    statusCodes={
     *      200="Returned when success"
     *    }
     * )
     */
    public function logoutAction(Request $request)
    {
        $this->get('app.manager.user')->expireSession();
        return new JsonResponse('Server Logout');
    }
    /**
     * @Rest\Post("/validate", name="api_token_validate",
     *     options={"method_prefix" = false},
     *     defaults={"_format"="json"}
     * )
     *
     * @ApiDoc(
     *    section     = "Security",
     *    description = "Get user by token",
     *    parameters={
     *      {"name"="token", "dataType"="textarea", "required"=true, "description"="token"},
     *    }
     * )
     */
    public function validateUserToken(Request $request)
    {
        $token = $request->get('token');
        //get UserProviderInterface
        $fos  = $this->get('fos_user.user_provider.username_email');
        //create PreAuthToken
        $preAuthToken = new PreAuthenticationJWTUserToken($token);
        try {
            if (!$payload = $this->get('lexik_jwt_authentication.jwt_manager')->decode($preAuthToken)) {
                throw new InvalidTokenException('Invalid JWT Token');
            }
            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                throw new ExpiredTokenException();
            }
            throw new InvalidTokenException('Invalid JWT Token', 0, $e);
        }
        //get user
        /** @var User $user */
        $user = $this->get('lexik_jwt_authentication.security.guard.jwt_token_authenticator')->getUser($preAuthToken, $fos);
        $view = $this->view([
            'token' => $token,
            'user'  => $user
        ]);
        $context = new Context();
        $context->addGroups(array_merge(['Public'],$user->getRoles()));
        $view->setContext($context);
        return $this->handleView($view);
    }
    /**
     * @Rest\Post("/pin", name="api_pin_login",
     *     options={"method_prefix" = false},
     *     defaults={"_format"="json"}
     * )
     *
     * @ApiDoc(
     *    section     = "Security",
     *    description = "User pin login",
     *    parameters={
     *      {"name"="pin",   "dataType"="string",   "required"=true, "description"="pin"},
     *      {"name"="token", "dataType"="textarea", "required"=true, "description"="token"},
     *    }
     * )
     */
    public function loginPinCode(Request $request)
    {
        $pin   = $request->get('pin');
        $token = $request->get('token');
        //get UserProviderInterface
        $fos = $this->get('fos_user.user_provider.username_email');
        //create PreAuthToken
        $preAuthToken = new PreAuthenticationJWTUserToken($token);
        try {
            if (!$payload = $this->get('lexik_jwt_authentication.jwt_manager')->decode($preAuthToken)) {
                throw new InvalidTokenException('Invalid JWT Token');
            }
            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                throw new ExpiredTokenException();
            }
            throw new InvalidTokenException('Invalid JWT Token', 0, $e);
        }
        //get user
        /** @var User $user */
        $user = $this->get('lexik_jwt_authentication.security.guard.jwt_token_authenticator')->getUser($preAuthToken, $fos);
        // Only for profiles with pin code
        if (method_exists($user, 'getPinCode')) {
            /**
             * @var $factory EncoderFactory
             */
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            if ($encoder->isPasswordValid($user->getPinCode(), $pin, $user->getSalt())) {
                return new JsonResponse('', Response::HTTP_OK);
            }
        }
        return new JsonResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
    }
}
